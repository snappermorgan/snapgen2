<?php

/**
 * Reverse Lookup - uses whitepages service to find information on a phone number such as an address or owner
 *
 * @param string $phone An unformatted phone number with no dashes or parenthesis
 * @param boolean $test specify whether this is a test or live. For debugging and testing purposes only
 *
 * @return array An array of address information
 */
function reverse_lookup($phone = "7704145683", $test = false) {

	$lookup = remote_call(array("type" => "phone", "payload" => array("phone" => $phone)), $test);
	if (is_array($lookup) && isset($lookup['body'])) {

		$lookup_array = json_decode($lookup['body']);
		if (isset($lookup_array->results)) {
			foreach ($lookup_array->results as $results_val) {
				$results_phone[] = $results_val;
			}

			//echo "<h2>Results parsed</h2>";
			$dictionaryData = $lookup_array->dictionary;
			$count_obj = count($dictionaryData);

			//let's see if best location is really best

			$results = best_location($results_phone, $dictionaryData);

			if ($results) {
				//echo "<h2>returning best location</h2>";
				return $results;
			} else {
				$backup_results = belongs_to($results_phone, $dictionaryData);
				if ($backup_results) {
					//echo "<h2>returning backup results</h2>";
					return $backup_results;
				} else {
					//echo "<h2>returning nothing</h2>";
					return false;
				}
			}

		} else {
			//echo "<h2>returning nothing</h2>";
			return false;
		}
	} else {
		return false;
	}

}

function belongs_to($results_phone, $dictionaryData) {
	if (count($results_phone) > 0) {
		foreach ($results_phone as $resultKey => $resultVal) {
			foreach ($dictionaryData as $dictionaryKey_a => $dictionaryVal_a) {
				if ($resultVal == $dictionaryKey_a) {
					//echo "<h3>Found our phone in dictionary</h3>";
					foreach ($dictionaryVal_a as $sub_dict_keys_a => $sub_dict_vales_a) {

						if ($sub_dict_keys_a == "belongs_to") {
							//echo print_r($sub_dict_vales,true);
							$belongs = $sub_dict_vales_a[0]->id->key;
							//echo "Belongs: " . $belongs;
						}
					}
				}
			}
		}
	}
	if ($belongs) {
		foreach ($dictionaryData as $dictionaryKey_b => $dictionaryVal_b) {
			if ($belongs == $dictionaryKey_b) {
				foreach ($dictionaryVal_b as $sub_dict_keys_b => $sub_dict_vales_b) {
					if ($sub_dict_keys_b == "locations") {
						//echo print_r($sub_dict_vales,true);
						$location = $sub_dict_vales_b[0]->id->key;
						//echo "Location: " . $location;
					}
				}
			}
		}
	}

	if ($location) {
		foreach ($dictionaryData as $dictionaryKey_c => $dictionaryVal_c) {
			if ($location == $dictionaryKey_c) {
				$location_obj = $dictionaryVal_c;

				if ($location_obj->standard_address_line1 != "") {
					return $location_obj;
				} else {
					return false;
				}
			}
		}
	}
}

function best_location($results_phone, $dictionaryData) {

	if (count($results_phone) > 0) {
		foreach ($results_phone as $resultKey => $resultVal) {
			foreach ($dictionaryData as $dictionaryKey_a => $dictionaryVal_a) {
				if ($resultVal == $dictionaryKey_a) {
					//echo "<h3>Found our phone in dictionary</h3>";
					foreach ($dictionaryVal_a as $sub_dict_keys_a => $sub_dict_vales_a) {

						if ($sub_dict_keys_a == "best_location") {
							//echo print_r($sub_dict_vales,true);
							$best = $sub_dict_vales_a->id->key;
							//echo "Belongs: ".$belongs;
						}
					}
				}
			}
		}
	}
	if ($best) {
		foreach ($dictionaryData as $dictionaryKey_c => $dictionaryVal_c) {
			if ($best == $dictionaryKey_c) {
				$location_obj = $dictionaryVal_c;

				if ($location_obj->standard_address_line1 != "") {
					return $location_obj;
				} else {
					return false;
				}
			}
		}

	}

	return false;

}

/**
 * Remote call to service
 *
 * @param array $data associated array for call various apis. "type" can be phone,address,business, or person. "payload" is associated array of key=>value pairs for querystring
 * @param boolean $test For testing and debug
 * @return string Returns JSON string for parsing
 */
function remote_call($data, $test = false) {

	$url = "https://proapi.whitepages.com/2.0/";
	$path = "";
	$query = "";
	switch ($data['type']) {
		case "phone":

			$path = "phone.json";

			break;

		case "address":

			$path = "location.json";

			break;

		case "business":
			$path = "business.json";
			break;

		case "person":
			$path = "person.json";
			break;

		default:
			return false;
			break;
	}

	if ($test) {
		$api_key = $_SERVER['SNAPGEN_WP_WHITEPAGES_KEY'];

		$service_url = 'http://proapi.whitepages.com/2.0/phone.json?phone=' . urlencode($data['payload']['phone']) . '&api_key=' . $api_key;
		$curl = curl_init($service_url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$curl_response = curl_exec($curl);
		return $curl_response;
	} else {
		$payload = $data['payload'];

		$query = build_query($payload);
		//_log("payload: " . print_r($data, true));
		$options = get_option('sg_settings');
		$key = $options['sg_api_string'];
		//_log("URL post:" . $url . $path . "?" . $query . "&api_key=" . $key);
		$response = wp_remote_get($url . $path . "?" . $query . "&api_key=" . $key);
		//_log("whitepages response: ".print_r($response,true));
		// echo "<pre>whitepages response: ".print_r($response,true)."</pre>";
		return $response;
	}
}

?>