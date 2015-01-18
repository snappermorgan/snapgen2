<?php
/*

Plugin Name: SnapGen
Plugin URI: https://radaralley.com
Description: Adds conditional posting and field concatenations
Author: Snapper Morgan
Version: 1.0
Author URI: http://radaralley.com
Changelog:
1.0 - Initial Release.
 */
include_once 'whitepages.php';
class Forms3rdpartySnapGen {

	const B = 'Forms3rdpartySnapGen';
	const N = 'Forms3rdPartyIntegration';
	const pluginPageTitle = 'Forms: 3rd Party Integration';
	const pluginPageShortTitle = '3rdparty Services';

	/**
	 * How long (seconds) before considering timeout
	 */
	const DEFAULT_TIMEOUT = 10;

	/**
	 * Parameter index for mapping - administrative label (reminder)
	 */
	const PARAM_LBL = 'lbl';

	/**
	 * Parameter index for mapping - source plugin (i.e. GravityForms, CF7, etc)
	 */
	const PARAM_SRC = 'src';

	/**
	 * Parameter index for mapping - 3rdparty destination
	 */
	const PARAM_3RD = '3rd';

	private $_response_message;

	public function Forms3rdpartySnapGen() {

		//add_action( 'admin_menu', 'snapgen_menu' );
		//add_action('admin_init', 'snapgen_admin_init');

		add_action('admin_menu', array($this, 'sg_add_admin_menu'));
		add_action('admin_init', array($this, 'sg_settings_init'));

		// only first form
		add_filter(self::N . '_alter_submission', array(&$this, 'post_filter'), 10, 2);
		//determine if this service should be bypassed
		add_filter(self::N . '_service_filter_args', array(&$this, 'bypass_service'), 10, 4);

		// configure whether this is conditional post and which service to post
		add_filter(self::N . '_service_settings', array(&$this, 'service_settings'), 10, 3);

		//if success let's see if there is a trigger
		add_filter(self::N . '_remote_success', array(&$this, 'remote_success'), 10, 5);

		// attach to response message
		add_filter(self::N . '_service', array(&$this, 'adjust_response'), 10, 5);

		//determine if this form should even be used
		//add_filter(self::N.'_use_form', array(&$this,'use_form'),20,4);
		add_filter('init', array($this, 'vendor_post'));

		$_response_message = array();

	}

	public function sg_add_admin_menu() {

		add_options_page('SnapGen Settings', 'SnapGen2 Settings', 'manage_options', 'snapgen', array($this, 'sg_options_page'));

	}

	public function sg_settings_init() {

		register_setting('pluginPage', 'sg_settings');

		add_settings_section(
			'sg_pluginPage_section',
			__('Settings', 'wordpress'),
			array($this, 'sg_settings_section_callback'),
			'pluginPage'
		);

		add_settings_field(
			'sg_api_string',
			__('Production API String', 'wordpress'),
			array($this, 'sg_text_field_0_render'),
			'pluginPage',
			'sg_pluginPage_section'
		);

	}

	public function sg_text_field_0_render() {

		$options = get_option('sg_settings');
		?>
	<input type='text' name='sg_settings[sg_api_string]' size='40' value='<?php echo $options['sg_api_string'];?>'>
<?php

	}

	public function sg_settings_section_callback() {

		echo __('Lookup API', 'wordpress');

	}

	public function sg_options_page() {

		?>
<form action='options.php' method='post'>

		<h1>SnapGen2</h1>

<?php
settings_fields('pluginPage');
		do_settings_sections('pluginPage');
		submit_button();
		?>
</form>
<?php

	}

	public function vendor_post() {

		if (isset($_REQUEST['external']) && ($_REQUEST['external'] == 'yes')) {

//            echo "<pre>".print_r($_REQUEST,true)."</pre>";
			//            exit(0);
			//header("Content-Type:text/xml");
			$boberdoo = "https://leads.metrixinteractive.com/genericPostlead.php";
			//$boberdoo = "http://requestb.in/1hhyvit1";

			if (isset($_REQUEST['Primary_Phone']) && $_REQUEST['Primary_Phone'] != "") {
				$response = reverse_lookup($_REQUEST['Primary_Phone']);
				_log("Incoming REQUEST:" . print_r($_REQUEST, true));
				$number = "";
				$zip = "";

				if ($response) {

					if ($response->house) {
						$number = $response->house;
					}

					if ($response->apt_number) {
						$number = $response->apt_number;
					}

					if ($response->zip4) {
						$zip = $response->postal_code . "-" . $response->zip4;

					} else {
						$zip = $response->postal_code;
					}
					$address = $number . " " . $response->street_name . " " . $response->street_type;
					$test_address = str_replace(' ', '', $address);
					_log("Test Address: {" . $test_address . "}");
					if ($test_address != "") {
						_log("SRC: {" . $_REQUEST['SRC'] . "}");
						$_REQUEST['Address'] = $address;
						$_REQUEST['SRC'] .= "match";
						$_REQUEST['City'] = $response->city;
						$_REQUEST['State'] = $response->state_code;
						$_REQUEST['ZipCode'] = $zip;
					}
				}
			}
			$post_args = array(
				'timeout' => self::DEFAULT_TIMEOUT, 'body' => $_REQUEST, 'method' => 'POST');
			_log("Post Args to boberdoo" . print_r($post_args, true));
			$response = wp_remote_post($boberdoo, $post_args);
			//echo "<pre>".print_r($response,true)."</pre>";

			//exit(0);

			ob_start();
			echo trim($response['body']);
			//echo print_r($_REQUEST,true);
			ob_end_flush();
			exit(0);
		}

	}
	public function url() {
		return sprintf(
			"%s://%s",
			isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
			$_SERVER['SERVER_NAME']
		);
	}
	public function gform_url($route) {
		$api_key = "4cec96bc3f";
		$private_key = "7a2a4a3064f5d6c";
		$method = "GET";

		$expires = strtotime("+60 mins");
		$string_to_sign = sprintf("%s:%s:%s:%s", $api_key, $method, $route, $expires);
		$sig = $this->calculate_sig($string_to_sign, $private_key);
		$server_url = $this->url();
		return $server_url . "/gravityformsapi/" . $route . "?api_key=" . $api_key . "&signature=" . $sig . "&expires=" . $expires;
	}
	public function validate_form($id) {
		if ($id) {
			$route = "forms/" . $id;
			$url = $this->gform_url($route);
			$response = wp_remote_get($url);

			if ($response['response']['code'] == "200") {
				$body = json_decode($response['body'], true);
				if ($body['status'] == "200" || $body['status'] == "201") {

					return true;

				} else {

					if ($body['status'] == "404") {
						return false;
					} else {
						return false;
					}

				}

			}

		}
	}

	public function post_filter($service, $submission = false) {

		//_log("ALTER SUBMISSION TRIGGERED");
		if (isset($service['whitepages']) && !empty($service['whitepages'])) {
			if ((isset($service['whitepages-address-field']) && !empty($service['whitepages-address-field'])) && (isset($service['whitepages-city-field']) && !empty($service['whitepages-city-field']))
				&& (isset($service['whitepages-state-field']) && !empty($service['whitepages-state-field'])) && (isset($service['whitepages-zip-field']) && !empty($service['whitepages-zip-field']))
				&& (isset($service['whitepages-phone-field']) && !empty($service['whitepages-phone-field']))) {

				$phone = $submission[trim(strtolower($service['whitepages-phone-field']))];

				$response = reverse_lookup($phone);

//                $address = "150 Sweetwood Way";
				//                $city = "Roswell";
				//                $state = "GA";
				//                $zip = "30076";
				_log("submission passed: " . print_r($submission, true));
				$address = "";
				$number = "";
				$zip = "";

				$input = "";
				if (isset($service['mapping'])) {
					$mapping = $service['mapping'];
					foreach ($mapping as $map) {
						if ($map['3rd'] == 'SRC') {
							$input = $map['src'];
						}
					}
				}

				if ($response) {
					foreach ($submission as $field => &$value) {
						//_log("field: " . print_r($field, true));
						if (trim(strtolower($service['whitepages-address-field'])) == trim(strtolower($field))) {
							if ($response->house) {
								$number = $response->house;
							}

							if ($response->apt_number) {
								$number = $response->apt_number;
							}
							$value = $number . " " . $response->street_name . " " . $response->street_type;
							_log("address=" . $address . "\n");
						}
						if (trim(strtolower($service['whitepages-city-field'])) == trim(strtolower($field))) {
							$value = $response->city;
							_log("city=" . $city . "\n");
						}
						if (trim(strtolower($service['whitepages-state-field'])) == trim(strtolower($field))) {
							$value = $response->state_code;
							_log("state=" . $state . "\n");
						}
						if (trim(strtolower($service['whitepages-zip-field'])) == trim(strtolower($field))) {
							if ($response->zip4) {
								$zip = $response->postal_code . "-" . $response->zip4;

							} else {
								$zip = $response->postal_code;
							}
							$value = $zip;
							//_log("zip=".$zip."\n");
						}
						if (trim(strtolower($input)) == trim(strtolower($field))) {
							$value .= 'match';
						}

					}
				}
			}
		}
		_log("post transformed and returned: " . print_r($post, true));
		return $submission;
	}

	function calculate_sig($string, $private_key) {
		$hash = hash_hmac("sha1", $string, $private_key, true);
		$sig = rawurlencode(base64_encode($hash));
		return $sig;

	}
	public function adjust_response($body, $refs, $sid, $submission, $service) {

		_log("refs that are passed: " . print_r($refs, true));

		//$refs['attach'] = 'custom message in email';
		if (isset($service['confirmation']) && !empty($service['confirmation'])) {
			$submission_str = "<div class='service-title'>Service: " . $service['name'] . "</div><div class='submissions'>";
			foreach ($submission as $k => $v) {
				$submission_str .= sprintf("%s : %s<br> ", $k, $v);
			}
			$submission_str .= "</div>";
			$_response_message[] = $submission_str;
			_log("showing submission");
		}
		if (isset($service['success-results']) && !empty($service['success-results'])) {
			$str = "<div class='service-title'>Service: " . $service['name'] . "</div><div class='submissions'>";

			_log("parse format: " . print_r($service['success-parsed-format'], true));

			switch ($service['success-parsed-format'][0]) {
				case 'XML':

					_log("parsing xml response");
					$xmldoc = new DOMDocument();
					$xmldoc->loadXML($body);

					$xpathvar = new Domxpath($xmldoc);

					$queryResult = $xpathvar->query('/response/status | /response/lead_id');

					foreach ($queryResult as $result) {
						$str .= $result->nodeName . " : " . $result->textContent . "<br>";
					}
					break;

				default:
					$str .= $body;
					break;
			}

			$str .= "</div>";
			$_response_message[] = $str;
			_log("showing results");
		}
		$new_message = implode("\n", $_response_message);
		$combined_message = $refs['message'] . $new_message;
		$refs['message'] = $combined_message;
		_log("refs are now: " . print_r($refs, true));
	}

	public function use_form($result, $form, $service_id, $service_forms) {
		$services = $this->get_services();
		$service = $services[$sid];
		if (isset($service['conditional-field']) && !empty($service['conditional-field'])) {

			if (isset($service['conditional-match']) && !empty($service['conditional-match'])) {

			}
		}
	}

	public function bypass_service($args, $service, $form, $submission) {

		$post_args = array(
			'timeout' => empty($service['timeout']) ? self::DEFAULT_TIMEOUT : $service['timeout']
			, 'body' => $args['body']);

		_log("bypass_args" . print_r($args['body'], true));
		if (isset($service['conditional']) && !empty($service['conditional'])) {
			_log("conditional triggered");
			if (isset($service['conditional-field']) && !empty($service['conditional-field'])) {
				_log("conditional field present: " . $service['conditional-field'] . " " . $submission[$service['conditional-field']]);
				if (isset($service['conditional-match']) && !empty($service['conditional-match'])) {
					_log("conditional match present:" . $service['conditional-match']);
					_log("submission: " . print_r($submission, true));
					if (trim(strtolower($service['conditional-match'])) !== trim(strtolower($submission[$service['conditional-field']]))) {
						_log("NO MATCH");
						$response = array('headers' => array(), 'body' => '', 'response' => array('code' => 200, 'message' => 'OK'), 'cookies' => array(), 'response_bypass' => 'Conditional Rule not triggered. %s = %s ');
						return $response;
					} else {
						_log("MATCH");

						return $post_args;
					}
				}
			}
		} else {
			return $post_args;
		}
	}

	public function service_settings($eid, $P, $entity) {
		$services = $this->get_services();
//echo "<pre>".print_r($entity,true)."</pre>";
		?>

        <fieldset><legend><span><?php _e('Trigger Posting', $P);?></span></legend>
            <div class="inside">
<?php $field = 'trigger';?>
                <div class="field">
                    <label for="<?php echo $field, '-', $eid?>"><?php _e('Trigger remote posting on Success?', $P);?></label>
                    <input id="<?php echo $field, '-', $eid?>" type="checkbox" class="checkbox" name="<?php echo $P, '[', $eid, '][', $field, ']'?>" value="yes"<?php echo isset($entity[$field]) ? ' checked="checked"' : ''?> />
                    <em class="description"><?php _e('If the success string validates, do you want to trigger another 3rd Party service posting? If checked, please select the service(s) you wish to trigger.', $P);?></em>
                </div>
<?php $field = 'triggered-services';?>
                <div class="field">
                    <label for="<?php echo $field, '-', $eid?>"><?php _e('Available Services', $P);?></label>
                    <select class="multiple" multiple="multiple" id="<?php echo $field, '-', $eid?>" name="<?php echo $P;?>[<?php echo $eid?>][triggered-services][]">
<?php
foreach ($services as $sid => $s) {
			if ($s['name'] != $entity['name']) {
				if (isset($entity['triggered-services']) && is_array($entity['triggered-services'])) {
					$triggered = $entity['triggered-services'];
				} else {
					$triggered = array();
				}
				?>
                                <option <?php if ($entity && in_array($sid, $triggered)): ?>selected="selected" <?php endif;?>value="<?php echo esc_attr($sid);?>"><?php echo esc_html($s['name']);?></option>
<?php
}
		}//	foreach
		?>
                    </select>

                    <em class="description"><?php _e('Choose which service(s) you want to trigger. <p><strong>NOTE: Be sure to unselect the same Form from your triggered service, otherwise it will submit the same values a 2nd time
						</strong></p>', $P);?></em>
                </div>

<?php $field = 'notify-admin';?>
                <div class="field">
                    <label for="<?php echo $field, '-', $eid?>"><?php _e('Send email to admin if Success message does not validate?', $P);?></label>
                    <input id="<?php echo $field, '-', $eid?>" type="checkbox" class="checkbox" name="<?php echo $P, '[', $eid, '][', $field, ']'?>" value="yes"<?php echo isset($entity[$field]) ? ' checked="checked"' : ''?> />
                    <em class="description"><?php _e('If the success string does not validate, an email will be sent to the debug address above.', $P);?></em>
                </div>

            </div>
        </fieldset>
        <fieldset><legend><span><?php _e('Conditional Posting', $P);?></span></legend>
            <div class="field">
<?php $field = 'conditional';?>
                <label for="<?php echo $field, '-', $eid?>"><?php _e('Submit form to service based on submission value?', $P);?></label>
                <input id="<?php echo $field, '-', $eid?>" type="checkbox" data-actn="toggle-sibling" data-after=".conditional-field-match" data-rel=".postbox" class="checkbox " name="<?php echo $P, '[', $eid, '][', $field, ']'?>" value="yes"<?php echo isset($entity[$field]) ? ' checked="checked"' : ''?> />
                <em class="description"><?php _e('Based on the value of one of the submitted fields, conditionally submit post.', $P);?></em>
            </div>
            <div class="conditional-field-match ">
                <div class="inside">
                    <div class="field">
<?php $field = 'conditional-field';?>
                        <label for="<?php echo $field, '-', $eid?>"><?php _e('Field Name', $P);?></label>
                        <input id="<?php echo $field . "-", $eid?>" style="width:200px;" type="text" class="text" name="<?php echo $P, '[', $eid, '][', $field, ']'?>" value="<?php echo esc_attr($entity['conditional-field']);?>"?>
                    </div>
                    <div class="field">
<?php $field = 'conditional-match';?>
                        <label for="<?php echo $field, '-', $eid?>"><?php _e('Match Value', $P);?></label>
                        <input id="<?php echo $field . "-", $eid?>" style="width:200px;" type="text" class="text" name="<?php echo $P, '[', $eid, '][', $field, ']'?>" value="<?php echo esc_attr($entity['conditional-match']);?>"?>
                        <em class="description"><?php _e('The value to match against in order to submit this post.', $P);?></em>
                    </div>
                </div>
            </div>

        </fieldset>
        <fieldset><legend><span><?php _e('Whitepages Pro', $P);?></span></legend>
            <div class="field">
<?php $field = 'whitepages';?>
                <label for="<?php echo $field, '-', $eid?>"><?php _e('Submit phone to Whitepages?', $P);?></label>
                <input id="<?php echo $field, '-', $eid?>" type="checkbox" data-actn="toggle-sibling" data-after=".conditional-field-match" data-rel=".postbox" class="checkbox " name="<?php echo $P, '[', $eid, '][', $field, ']'?>" value="yes"<?php echo isset($entity[$field]) ? ' checked="checked"' : ''?> />
                <em class="description"><?php _e('Submit Phone Number to White Pages for reverse lookup of address', $P);?></em>
            </div>
            <div class="whitepages-phone-field">
                <div class="inside">
                    <div class="field">
<?php $field = 'whitepages-phone-field';?>
                        <label for="<?php echo $field, '-', $eid?>"><?php _e('Phone Field Name', $P);?></label>
                        <input id="<?php echo $field . "-", $eid?>" style="width:200px;" type="text" class="text" name="<?php echo $P, '[', $eid, '][', $field, ']'?>" value="<?php echo esc_attr($entity[$field]);?>"?>
                    </div>

                </div>
            </div>
             <div class="whitepages-address-field">
                <div class="inside">
                    <div class="field">
<?php $field = 'whitepages-address-field';?>
                        <label for="<?php echo $field, '-', $eid?>"><?php _e('Address Field Name', $P);?></label>
                        <input id="<?php echo $field . "-", $eid?>" style="width:200px;" type="text" class="text" name="<?php echo $P, '[', $eid, '][', $field, ']'?>" value="<?php echo esc_attr($entity[$field]);?>"?>
                    </div>

                </div>
            </div>
             <div class="whitepages-city-field">
                <div class="inside">
                    <div class="field">
<?php $field = 'whitepages-city-field';?>
                        <label for="<?php echo $field, '-', $eid?>"><?php _e('City Field Name', $P);?></label>
                        <input id="<?php echo $field . "-", $eid?>" style="width:200px;" type="text" class="text" name="<?php echo $P, '[', $eid, '][', $field, ']'?>" value="<?php echo esc_attr($entity[$field]);?>"?>
                    </div>

                </div>
            </div>
            <div class="whitepages-state-field">
                <div class="inside">
                    <div class="field">
<?php $field = 'whitepages-state-field';?>
                        <label for="<?php echo $field, '-', $eid?>"><?php _e('State Field Name', $P);?></label>
                        <input id="<?php echo $field . "-", $eid?>" style="width:200px;" type="text" class="text" name="<?php echo $P, '[', $eid, '][', $field, ']'?>" value="<?php echo esc_attr($entity[$field]);?>"?>
                    </div>

                </div>
            </div>

             <div class="whitepages-zip-field">
                <div class="inside">
                    <div class="field">
<?php $field = 'whitepages-zip-field';?>
                        <label for="<?php echo $field, '-', $eid?>"><?php _e('Zip Field Name', $P);?></label>
                        <input id="<?php echo $field . "-", $eid?>" style="width:200px;" type="text" class="text" name="<?php echo $P, '[', $eid, '][', $field, ']'?>" value="<?php echo esc_attr($entity[$field]);?>"?>
                    </div>

                </div>
            </div>
        </fieldset>
        <fieldset><legend><span><?php _e('Confirmation Page', $P);?></span></legend>
            <div class="field">
<?php $field = 'confirmation';?>
                <label for="<?php echo $field, '-', $eid?>"><?php _e('Show submitted values on confirmation page?', $P);?></label>
                <input id="<?php echo $field, '-', $eid?>" type="checkbox" class="checkbox" name="<?php echo $P, '[', $eid, '][', $field, ']'?>" value="yes"<?php echo isset($entity[$field]) ? ' checked="checked"' : ''?>/>

            </div>
            <div class="field">
<?php $field = 'success-results';?>
                <label for="<?php echo $field, '-', $eid?>"><?php _e('Show results from service response?', $P);?></label>
                <input id="<?php echo $field, '-', $eid?>" type="checkbox" class="checkbox " data-actn="toggle-sibling" data-after=".success-parsed" data-rel=".postbox" name="<?php echo $P, '[', $eid, '][', $field, ']'?>" value="yes"<?php echo isset($entity[$field]) ? ' checked="checked"' : ''?>/>

            </div>
            <div class="field success-parsed">
                <div class="inside">
<?php $field = 'success-parsed-format';?>
                    <label for="<?php echo $field, '-', $eid?>"><?php _e('Response Format', $P);?></label>
                    <select class="single" id="<?php echo $field, '-', $eid?>" name="<?php echo $P;?>[<?php echo $eid?>][success-parsed-format][]">
                        <option <?php if ($entity && $entity[$field] == 'XML'): ?>selected="selected" <?php endif;?>value="XML">XML</option>
                        <option <?php if ($entity && $entity[$field] == 'JSON'): ?>selected="selected" <?php endif;?>value="JSON">JSON</option>
                        <option <?php if ($entity && $entity[$field] == 'RAW'): ?>selected="selected" <?php endif;?>value="RAW">Raw Text</option>
                    </select>
                </div>
            </div>
            <div class="field">
<?php $field = 'response-xpath';?>
                <label for="<?php echo $field, '-', $eid?>"><?php _e('Enter XPath Query for results to extract', $P);?></label>
                <input id="<?php echo $field, '-', $eid?>" type="text" class="text" name="<?php echo $P, '[', $eid, '][', $field, ']'?>" value="<?php echo esc_attr($entity['response-xpath']);?>" />

            </div>


        </fieldset>
<?php
}

	function remote_success($form, $callback_results, $service, $submission) {

		_log("let's check for triggered services: " . print_r($service, true));
		$first_form = $form;
		if (isset($service['conditional']) && !empty($service['conditional'])) {
			return $form;
		} else {

			if (isset($service['trigger']) && !empty($service['trigger'])) {
				if (isset($service['triggered-services']) && is_array($service['triggered-services'])) {
					$services = $this->get_services();
					_log("all services" . print_r($services, true));
					foreach ($service['triggered-services'] as $t => $sid) {
						_log("let's send the triggered post");
						$this->send_submission($services[$sid], $form, $submission, $sid, $callback_results);

						_log("calling send submission with " . print_r($submission, true));
					}

					_log("Success Received. Triggering service for post: " . print_r($submission, true));

					return $form;
				} else {
					return $form;
				}
			} else {
				_log("callback results: " . print_r($callback_results, true) . "bam");

				return $first_form;
			}
		}
	}

	private $_settings;
	private $_services;

	function get_services($stashed = true) {
		if ($stashed && isset($this->_services)) {
			return $this->_services;
		}

		$this->_services = get_option(self::N . '_settings');
		// but we only want service listing, not the settings
		// TODO: this will go away once we move to custom post type like CF7
		unset($this->_services['debug']);

		return $this->_services;
	}

	function get_settings($stashed = true) {
		// TODO: if this ever changes, make sure to correspondingly fix 'upgrade.php'

		if ($stashed && isset($this->_settings)) {
			return $this->_settings;
		}

		$this->_settings = get_option(self::N . '_settings');
		// but we only want the actual settings, not the services
		$this->_settings = $this->_settings['debug'];

		return $this->_settings;
	}

//---	get_settings

	private function send_submission($service, $form, $submission, $sid, $callback_results) {
		_log("hey");
		$debug = $this->get_settings();
		$post = array();

		$service['separator'] = $debug['separator']; // alias here for reporting
		_log("mapping for service: " . print_r($service, true));
		//find mapping
		foreach ($service['mapping'] as $mid => $mapping) {
			$third = $mapping[self::PARAM_3RD];

			//is this static or dynamic (userinput)?
			if (v($mapping['val'])) {
				$input = $mapping[self::PARAM_SRC];
			} else {
				//check if we have that field in post data
				if (!isset($submission[$mapping[self::PARAM_SRC]])) {
					continue;
				}

				$input = $submission[$mapping[self::PARAM_SRC]];
			}

			//allow multiple values to attach to same entry
			if (isset($post[$third])) {
				###echo "multiple @$mid - $fsrc, $third :=\n";

				if (!is_array($post[$third])) {
					$post[$third] = array($post[$third]);
				}
				$post[$third][] = $input;
			} else {
				$post[$third] = $input;
			}
		}// foreach mapping
		_log("submission-:" . print_r($submission, true));
		//extract special tags;
		$post = apply_filters(self::N . '_service_filter_post_' . $sid, $post, $service, $form);
		$post = apply_filters(self::N . '_service_filter_post', $post, $service, $form, $sid, $submission);

		// fix for multiple values
		switch ($service['separator']) {
			case '[#]':
				// don't do anything to include numerical index (default behavior of `http_build_query`)
				break;
			case '[]':
				// must build as querystring then strip `#` out of `[#]=`
				$post = http_build_query($post);
				$post = preg_replace('/%5B[0-9]+%5D=/', '%5B%5D=', $post);
				break;
			default:
				// otherwise, find the arrays and implode
				foreach ($post as $f => &$v) {
					###_log('checking array', $f, $v, is_array($v) ? 'array' : 'notarray');

					if (is_array($v)) {
						$v = implode($service['separator'], $v);
					}
				}

				break;
		}

		$post_args = apply_filters(self::N . '_service_filter_args', array(
			'timeout' => empty($service['timeout']) ? self::DEFAULT_TIMEOUT : $service['timeout']
			, 'body' => $post,
		)
			, $service
			, $form
			, $submission
		);
		$can_hook = true;
		//remote call
		// optional bypass -- replace with a SOAP call, etc
		if (isset($post_args['response_bypass'])) {
			$response = $post_args['response_bypass'];

			if ($callback_results['message'] !== '') {

				$callback_results = array('success' => false, 'errors' => false, 'attach' => '', 'message' => $callback_results['message'] . $response);
			} else {
				$callback_results = array('success' => false, 'errors' => false, 'attach' => '', 'message' => $response);
			}
			$param_ref = array();
			foreach ($callback_results as $k => &$v) {
				$param_ref[$k] = &$v;
			}
			$form = apply_filters(self::N . '_remote_success', $form, $callback_results, $service, $submission, false);
			$can_hook = false;
		} else {
			//@see http://planetozh.com/blog/2009/08/how-to-make-http-requests-with-wordpress/
			_log("sending triggered" . print_r($post_args, true));
			$response = wp_remote_post($service['url'], $post_args);
		}

		_log('response from ' . $service['url'] . print_r($response, true));
		//if something went wrong with the remote-request "physically", warn
		if (!is_array($response)) {
			//new occurrence of WP_Error?????
			$response_array = array('safe_message' => 'error object', 'object' => $response);
			//$form = $Forms3rdPartyIntegration::on_response_failure($form, $debug, $service, $post_args, $response_array);
			$can_hook = false;
		} elseif (!$response || !isset($response['response']) || !isset($response['response']['code']) || 200 != $response['response']['code']) {
			$response['safe_message'] = 'physical request failure';
			$form = apply_filters(self::N . '_remote_failure', $form, $debug, $service, $post_args, $response);
			$can_hook = false;
		}
		//otherwise, check for a success "condition" if given
		elseif (!empty($service['success'])) {
			if (strpos($response['body'], $service['success']) === false) {
				//$failMessage = array(
				//	'reason'=>'Could not locate success clause within response'
				//	, 'safe_message' => 'Success Clause not found'
				//	, 'clause'=>$service['success']
				//	, 'response'=>$response['body']
				//);
				//$form = apply_filters(self::N.'_remote_failure', $form, $debug, $service, $post_args, $failMessage);
				if ($callback_results['message'] !== '') {

					$callback_results = array('success' => false, 'errors' => false, 'attach' => '', 'message' => $callback_results['message'] . $response['body']);
				} else {
					$callback_results = array('success' => false, 'errors' => false, 'attach' => '', 'message' => '');
				}

				$param_ref = array();
				foreach ($callback_results as $k => &$v) {
					$param_ref[$k] = &$v;
				}
				do_action($this->N('service'), $response['body'], $param_ref, $sid, $post, $service);
				$form = apply_filters($this->N('remote_success'), $form, $callback_results, $service, $submission, false);

				$can_hook = false;
			}
		}

		if ($can_hook && isset($service['hook']) && $service['hook']) {
			//_log('performing hooks for:', self::N.'_service_'.$sid);
			//hack for pass-by-reference
			//holder for callback return results
			//$callback_results = array('success'=>false, 'errors'=>false, 'attach'=>'', 'message' => '');
			// TODO: use object?
			if ($callback_results['message'] !== '') {
				$callback_results = array('success' => false, 'errors' => false, 'attach' => '', 'message' => $callback_results['message'] . $response['body']);
			} else {

				$callback_results = array('success' => false, 'errors' => false, 'attach' => '', 'message' => '');
			}
			$param_ref = array();
			foreach ($callback_results as $k => &$v) {
				$param_ref[$k] = &$v;
			}

			//allow hooks
			do_action(self::N . '_service_a' . $sid, $response['body'], $param_ref);
			do_action(self::N . '_service', $response['body'], $param_ref, $sid, $post, $service, $submission);

			###_log('after success', $form);
			//check for callback errors; if none, then attach stuff to message if requested
			if (!empty($callback_results['errors'])) {
				$failMessage = array(
					'reason' => 'Service Callback Failure'
					, 'safe_message' => 'Service Callback Failure'
					, 'errors' => $callback_results['errors']);
				$form = apply_filters(self::N . '_remote_failure', $form, $debug, $service, $post_args, $failMessage);
			} else {
				_log('checking for attachments on triggered submit' . print_r($callback_results, true));
				$form = apply_filters(self::N . '_remote_success', $form, $callback_results, $service, $submission, true);
			}
		}// can hook
		//forced debug contact
		if ($debug['mode'] == 'debug') {
			$this->send_debug_message($debug['email'], $service, $post_args, $response, $submission);
		}

		//return $form;
	}

	/**
	 * How to send the debug message
	 * @param  string $email      recipient
	 * @param  array $service    service options
	 * @param  array $post       details sent to 3rdparty
	 * @param  object $response   the response object
	 * @param  object $submission the form submission
	 * @return void             n/a
	 */
	private function send_debug_message($email, $service, $post, $response, $submission) {
		// did the debug message send?
		if (!wp_mail($email
			, self::pluginPageTitle . " Debug: {$service['name']}"
			, "*** Service ***\n" . print_r($service, true) . "\n*** Post (Form) ***\n" . get_bloginfo('url') . $_SERVER['REQUEST_URI'] . "\n" . print_r($submission, true) . "\n*** Post (to Service) ***\n" . print_r($post, true) . "\n*** Response ***\n" . print_r($response, true)
			, array('From: "' . self::pluginPageTitle . ' Debug" <' . $this->N . '-debug@' . str_replace('www.', '', $_SERVER['HTTP_HOST']) . '>')
		)) {
			///TODO: log? another email? what?
		}
	}

}

//---	class	Forms3partySnapGen
// engage!
if (!function_exists('_log')) {

	function _log($message) {
		if (WP_DEBUG === true) {
			if (is_array($message) || is_object($message)) {
				error_log(print_r($message, true));
			} else {
				error_log($message);
			}
		}
	}

}
new Forms3rdpartySnapGen();

function enqueue_select2_jquery() {
	$plugins_url = plugins_url();
	wp_register_style('select2css', 'http://cdnjs.cloudflare.com/ajax/libs/select2/3.4.8/select2.css', false, '1.0', 'all');
	wp_register_script('select2', 'http://cdnjs.cloudflare.com/ajax/libs/select2/3.4.8/select2.js', array('jquery'), '1.0', true);
	wp_register_script('snapgen2', $plugins_urls . '/snapgen2/snapgen2.js', false, '1.0', false);
	wp_enqueue_style('select2css');
	wp_enqueue_script('select2');
	// wp_enqueue_script('snapgen2');
}

add_action('admin_enqueue_scripts', 'enqueue_select2_jquery');
add_action('wp_ajax_validate_address', 'validate_address_callback');
add_action('wp_ajax_nopriv_validate_address', 'validate_address_callback');
add_action('wp_ajax_validate_email', 'validate_email_callback');
add_action('wp_ajax_nopriv_validate_email', 'validate_email_callback');
add_shortcode('email_validate', 'email_validate_shortcode');
add_shortcode('address_validate', 'address_validate_shortcode');

function validate_email_callback() {

	$email = (isset($_REQUEST['email']) ? $_REQUEST['email'] : false);

	$url = "https://bpi.briteverify.com/emails.json?";
	$qs = "address=" . urlencode($email) . "&apikey=a2d8cb8f-cae7-4b74-bd32-cd5e1fe7d833";

	$response = wp_remote_get($url . $qs);

	if ($response['response']['code'] != "200") {
		echo '{"status":"invalid","error":"Response code: ' . $response["response"]["code"] . ' -- ' . $response["response"]["message"] . '"}';
	} else {

		echo $response['body'];
	}
	echo die();
}

function validate_address_callback() {

	$street = (isset($_REQUEST['street']) ? $_REQUEST['street'] : false);
	$unit = (isset($_REQUEST['unit']) ? $_REQUEST['unit'] : false);
	$zip = (isset($_REQUEST['zip']) ? $_REQUEST['zip'] : false);

	$url = "https://bpi.briteverify.com/addresses.json?";
	$qs = "address[street]=" . urlencode($street) . "&address[unit]=" . urlencode($unit) . "&address[zip]=" . urlencode($zip) . "&apikey=a2d8cb8f-cae7-4b74-bd32-cd5e1fe7d833&corrected=true";

	$response = wp_remote_get($url . $qs);

	if ($response['response']['code'] != "200") {
		echo '{"status":"invalid","error":"Response code: ' . $response["response"]["code"] . ' -- ' . $response["response"]["message"] . '"}';
	} else {

		echo $response['body'];
	}
	echo die();
}

function email_validate_shortcode($atts) {
	$a = shortcode_atts(array(
		'field_selector' => '#email',
		'form_selector' => '.gform_wrapper form',
		'submit_selector' => 'input[type=submit]',
		'prevent_submit' => true,
		'submit_text' => 'Submit',
		'disabled_text' => 'Invalid Email',
	), $atts);
	ob_start();
	?>
	<script>
		jQuery("document").ready(function($){

			jQuery("<?php echo $a['field_selector'];?>").bind("blur",  function( e ) {
				e.preventDefault();
				validateEmailAddress(e);

			});

		});
		function validateEmailAddress(e){
				email=jQuery("<?php echo $a['field_selector'];?>").val();


		email_data = {
			'action':'validate_email',
			'email':email
		}

		jQuery.ajax({
					url: "/wp-admin/admin-ajax.php",
					data: email_data,
					dataType: "JSON",
					type: "GET",
					success: function(response){


					if(response.status == "invalid"){
						alert("Email Address Error: "+response.error);
<?php
if ($a['prevent_submit']) {
		?>
							jQuery("<?php echo $a['submit_selector'];?>").prop("disabled",true);
							jQuery("<?php echo $a['submit_selector'];?>").val("<?php echo $a['disabled_text'];?>");
							jQuery("<?php echo $a['submit_selector'];?>").addClass("hover");
<?php
}?>
}else{
<?php

	if ($a['prevent_submit']) {
		?>
							jQuery("<?php echo $a['submit_selector'];?>").prop("disabled",false);
							jQuery("<?php echo $a['submit_selector'];?>").val("<?php echo $a['submit_text'];?>");
							jQuery("<?php echo $a['submit_selector'];?>").removeClass("hover");
<?php
}?>
}
				}



				});


			}
 	</script>
<?php

	return ob_get_clean();

}

function address_validate_shortcode($atts) {
	$a = shortcode_atts(array(
		'form_selector' => '.gform_wrapper form',
		'submit_selector' => 'input[type=submit]',
		'prevent_submit' => true,
		'submit_text' => 'Submit',
		'disabled_text' => 'Invalid Address',
		'address_selector' => '#address',
		'address2_selector' => '#address2',
		'zip_selector' => '#zip',
		'city_selector' => '#city',
		'state_selector' => '#state',
	), $atts);
	ob_start();
	?>
<script>
		jQuery("document").ready(function($){

			jQuery(".gform_wrapper form").bind("submit.address",  function( e ) {
				e.preventDefault();
				validateAddress(e);

			});

		});


		function validateAddress(e){
			form_id = jQuery("<?php echo $a['form_selector'];?>").attr("id").replace("gform_","");
			address=jQuery("<?php echo $a['address_selector'];?>").val();
			address2=jQuery("<?php echo $a['address2_selector'];?>").val();
			zip = jQuery("<?php echo $a['zip_selector'];?>").val();


			address_data = {
				'action':'validate_address',
				'street':address,
				'unit':address2,
				'zip':zip
			}

			jQuery.ajax({
						url: "/wp-admin/admin-ajax.php",
						data: address_data,
						dataType: "JSON",
						type: "GET",
						success: function(response){


						if(response.status == "invalid"){
							alert("Address was not found. Message: "+response.error);
							 submitting = "gf_submitting_"+form_id;
							 eval(submitting + "=false");
							e.preventDefault();
							return false;
						}else{
							jQuery("<?php echo $a['city_selector'];?>").val(response.city);
							jQuery("<?php echo $a['state_selector'];?>").val(response.state);
							jQuery("<?php echo $a['form_selector'];?>")[0].submit();
							return true;
						}}



			});


		}
 </script>

<?php

	return ob_get_clean();

}
