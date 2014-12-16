<?php
/**
 * Form Builder Class
 * Friend Classes (quasi-)Design Pattern
 */
class CRED_Form_Builder implements CRED_Friendable, CRED_FriendableStatic 
{
    // CONSTANTS
    const METHOD='POST';                                         // form method POST
    const PREFIX='_cred_cred_prefix_';                           // prefix for various hidden auxiliary fields
    const NONCE='_cred_cred_wpnonce';                            // nonce field name
    const POST_CONTENT_TAG='%__CRED__CRED__POST__CONTENT__%';    // placeholder for post content
    const FORM_TAG='%__CRED__CRED__FORM___FORM__%';              // 
    const DELAY=0;                                               // seconds delay before redirection
    
    // STATIC Properties
    private static $_staticGlobal=array(
        'ASSETS_PATH'=>null,                                    // physical path to files needed for Zebra form
        'ASSETS_URL'=>null,                                     // url for this physical path
        'MIMES'=>array(),                                       // WP allowed mime types (for file uploads)
        'LOCALES'=>null,                                        // global strings localization
        'RECAPTCHA'=>false,                                     // settings for recaptcha API
        'RECAPTCHA_LOADED'=>false,                              // flag indicating whether recaptcha API has been loaded
        'COUNT'=>0,                                             // number of forms rendered on same page
        'CACHE'=>array(),                                       // cache rendered forms here for future reference (eg by shortcodes)
        'CSS_LOADED'=>array(),                                  // references to CSS files that have been loaded
        'CURRENT_USER'=>null                                    // info about current user using the forms
    );

    // INSTANCE  properties
    private $_shortcodeParser=null;                             // instance of shortcode parser
    private $_formHelper=null;                                  // instance of form helper
    private $_zebraForm=null;                                   // instance of Zebra form, to render frontend forms
    private $_formData=null;                                    // current CRED form data, like content, fields, settings etc..
    private $_post_ID=null;                                     // ID of currently edited or created post
    private $_postData=null;                                    // currently edited post data
    private $_content='';                                       // currently parsed form content (whole)
    private $_form_content='';                                  // currently parsed form content (strictly inside the form tags)
    private $_attributes=array();                               // currently parsed form extra attributes
    private $out_=array(                                        // info about currently output form
        'count'=>null,
        'prg_id'=>null,
        'js'=>'',
        'has_recaptcha'=>false,
        'fields'=>array(),
        'form_fields'=>array(),
        'form_fields_info'=>array(),
        'field_values_map'=>array(),
        'conditionals'=>array(),
        'current_group'=>null,
        'child_groups'=>null,
        'generic_fields'=>array(),
        'taxonomy_map'=>array('taxonomy'=>array(),'aux'=>array()),
        'controls'=>array(),
        'nonce_field'=>null,
        'form_id_field'=>null,
        'form_count_field'=>null,
        'post_id_field'=>null,
        'notification_data'=>'',
    );
    private $_supportedDateFormats = array(                  //  supported date formats
                'F j, Y', //December 23, 2011
                'Y/m/d', // 2011/12/23
                'm/d/Y', // 12/23/2011
                'd/m/Y' // 23/12/2011
            );
       
    /*
    *   Implement Friendable Interface
    */
    private $_____friends_____=array(/* Friend Instances Hashes as keys Here..*/);
    private static $_____friendsStatic_____=array(/* Friend Class Hashes as keys Here..*/);
    //private static $_______class_______='CRED_Form_Builder';
    /*
    *   /END Implement Friendable Interface
    */
    
    /*=============================== STATIC METHODS ========================================*/

    // true if forms have been built for current page
    public static function has_form() { return (self::$_staticGlobal['COUNT']>0); }

    // init public function
    public static function init()
    {
        //CRED_Loader::load('CLASS/Form_Helper');
        // form helper is a friend of form builder, so they can share access between them
        self::addFriendStatic('CRED_Form_Builder_Helper', array(
            'methods'=>array(),
            'properties'=>array('_staticGlobal')
        ));
        // parse cred form output
        add_action('wp_loaded', array('CRED_Form_Builder', '_init_'), 10);
        // load front end form assets
        add_action('wp_head', array('CRED_Form_Builder_Helper', 'loadFrontendAssets'));
        add_action('wp_footer', array('CRED_Form_Builder_Helper', 'unloadFrontendAssets'));
    }

    // check for form submissions on init
    public static function _init_()
    {
        // check for cred form submissions
        if (!is_admin())
        {
            // reference to the form submission method
            global ${'_' . self::METHOD};
            $method = & ${'_' . self::METHOD};
            
            if (array_key_exists(self::PREFIX.'form_id',$method) && array_key_exists(self::PREFIX.'form_count',$method))
            {
                $form_id=intval($method[self::PREFIX.'form_id']);
                $form_count=intval($method[self::PREFIX.'form_count']);
                
                // edit form
                if (array_key_exists(self::PREFIX.'post_id',$method))
                    $post_id=intval($method[self::PREFIX.'post_id']);
                else
                    $post_id=false;
                    
                // preview form
                if (array_key_exists(self::PREFIX.'form_preview_content',$method))
                    $preview=true;
                else
                    $preview=false;
                
                // parce and cache form
                self::getCachedForm($form_id, $post_id, $preview, $form_count);
            }
        }
    }

    private static function getCachedForm($form_id, $post_id, $preview, $force_count=false)
    {
        $form_count=(false!==$force_count)?$force_count:self::$_staticGlobal['COUNT'];
        
        if (
            false!==$force_count ||
            (!array_key_exists($form_id.'_'.self::$_staticGlobal['COUNT'], self::$_staticGlobal['CACHE']))
        )
        {
            // parse and cache form
            $fb=new CRED_Form_Builder();
            // process/build form
            $form=$fb->form($form_id, $post_id, $preview, $form_count);
            self::$_staticGlobal['CACHE'][$form_id.'_'.$form_count]=array(
                    'form' =>  $form,
                    'count' => $form_count,
                    'extra' => $fb->getExtra(),
                    'css_to_use' => $fb->getCSS(),
                    'js' => $fb->getJS(),
                    'hide_comments' =>  $fb->hasHideComments(),
                    'has_recaptcha' =>  $fb->hasRecaptcha()
            );
        }
        
        // add filter to hide comments (new method)
        if (
            //false!==$force_count && // do lazy, dont hide comments immediately, maybe the form will not show when page loads finally
            self::$_staticGlobal['CACHE'][$form_id.'_'.$form_count]['hide_comments']
        )
            CRED_Form_Builder_Helper::hideComments();
        
        return  self::$_staticGlobal['CACHE'][$form_id.'_'.$form_count]['form'];
    }
    
    // get form html output for given form (form is processed if data submitted)
    public static function getForm($form_id, $post_id=null, $preview=false)
    {
        //CRED_Loader::load('CLASS/Form_Helper');
        CRED_Form_Builder_Helper::initVars();
        ++self::$_staticGlobal['COUNT'];
        
        if (is_string($form_id) && !is_numeric($form_id))
        {
            $form=get_page_by_title( $form_id, OBJECT, CRED_FORMS_CUSTOM_POST_NAME );
            if ($form && is_object($form))
                $form_id=$form->ID;
            else return '';
        }
            
        return  self::getCachedForm($form_id, $post_id, $preview);
    }
    

    /*=============================== INSTANCE METHODS ========================================*/

    // constuctor, return a CRED form object
    public function __construct()
    {
        //CRED_Loader::load('CLASS/Form_Helper');
        CRED_Form_Builder_Helper::initVars();

        // shortcodes parsed by custom shortcode parser
        $this->_shortcodeParser=CRED_Loader::get('CLASS/Shortcode_Parser');
        // various functions performed by custom form helper
        $this->_formHelper=CRED_Loader::get('CLASS/Form_Helper', $this);
        // form helper is a friend of form builder, so they can share access between them
        $this->addFriend($this->_formHelper, array(
            'methods'=>array(),
            'properties'=>array(
                '_formData', 
                '_postData', 
                'out_',
                '_supportedDateFormats',
                '_zebraForm', 
                '_shortcodeParser'
            )
        ));
    }
       
    private function destroy()
    {
    }
    
    // whether this form attempts to hide comments
    public function hasHideComments() {return $this->_formData->fields['form_settings']->form['hide_comments'];}
    // get extra javascript/css attached to this form
    public function getExtra() {return $this->_formData->fields['extra'];}
    // get css file used by this form
    public function getCSS() {return $this->_formData->fields['form_settings']->form['css_to_use'];}
    // whether this form has recaptcha field
    public function hasRecaptcha() {return $this->out_['has_recaptcha'];}
    // get zebra javascript needed by this form
    public function getJS() {return $this->out_['js'];}

    
    // manage form submission / validation and rendering and return rendered html 
    public function form($form_id, $post_id=null, $preview=false, $force_form_count=false)
    {
        $bypass_form=apply_filters('cred_bypass_process_form_'.$form_id, false, $form_id, $post_id, $preview);
        $bypass_form=apply_filters('cred_bypass_process_form', $bypass_form, $form_id, $post_id, $preview);
        
        $formHelper=$this->_formHelper;
        // if some error happened, display a message instead
        $parse=$this->parseInputs($form_id, $post_id, $preview, $force_form_count);
        if ($formHelper->isError($parse))
            return $formHelper->getError($parse);
            
        $zebraForm=$this->_zebraForm;
        $form=&$this->_formData;
        $form_id=$form->form->ID;
        $prg_id=$this->out_['prg_id'];
        $form_count=$this->out_['count'];
        $form_type=$form->fields['form_settings']->form['type'];
        $post_type=$form->fields['form_settings']->post['post_type'];
        $post_id=$this->_post_ID;

		// define global $post from $post_id
		global $post;
		if (is_int($post_id) && $post_id>0)
		{
			if (!isset($post->ID) || (isset($post->ID) && $post->ID != $post_id))
			{
				$post = get_post($post_id);
			}
		}
        
        // show display message from previous submit of same create form (P-R-G pattern)
        if (
            !$zebraForm->preview && /*'edit'!=$form_type && (isset($_GET['action']) && $_GET['action'] == 'edit_translation' && 'translation'!=$form_type) && */
            isset($_GET['_success_message']) &&
            $_GET['_success_message']==$prg_id &&
            'message'==$form->fields['form_settings']->form['action']
            )
        {
            return $formHelper->displayMessage($form);
        }
       
        // build form
        $this->build();
        
        // no message to display if not submitted
        $message=false;
        
        // add success message from previous submit of same any form (P-R-G pattern)
        if (
            !$zebraForm->preview && /*'edit'!=$form_type && */
            isset($_GET['_success']) &&
            $_GET['_success']==$prg_id
            )
        {
            $saved_message=$formHelper->getLocalisedMessage('post_saved');
            $saved_message=apply_filters('cred_data_saved_message_'.$form_id, $saved_message, $form_id, $post_id, $preview);
            $saved_message=apply_filters('cred_data_saved_message', $saved_message, $form_id, $post_id, $preview);
            $zebraForm->add_form_message('data-saved', $saved_message);
        }
        // add notification message from previous submit of same create form (P-R-G pattern)
        /*if (($n_data=$formHelper->readCookie('_cred_cred_notifications'.$prg_id)))
        {
            $formHelper->clearCookie('_cred_cred_notifications'.$prg_id);
            if (isset($n_data['sent']))
            {
                foreach ((array)$n_data['sent'] as $ii)
                     $zebraForm->add_form_message('notification_'.$ii, $formHelper->getLocalisedMessage('notification_was_sent'));
            }
            if (isset($n_data['failed']))
            {
                foreach ((array)$n_data['failed'] as $ii)
                     $zebraForm->add_form_message('notification_'.$ii, $formHelper->getLocalisedMessage('notification_failed'));
            }
        }*/
        
        $thisform=array(
            'id' => $form_id,
            'post_type' => $post_type,
            'form_type' => $form_type
        );
        
        if (!$bypass_form && $this->validate())
        {
            if (!$zebraForm->preview)
            {
                // save post data
                $bypass_save_form_data=apply_filters('cred_bypass_save_data_'.$form_id, false, $form_id, $post_id, $thisform);
                $bypass_save_form_data=apply_filters('cred_bypass_save_data', $bypass_save_form_data, $form_id, $post_id, $thisform);
                
                if (!$bypass_save_form_data)
                {
                    $post_id=$this->save($post_id);
                }
                
                if (is_int($post_id) && $post_id>0)
                {
					// set global $post
					$post = get_post($post_id);

                    // send notifications
                    //list($n_sent, $n_failed)=$this->notify($post_id);
                    // enable notifications and notification events if any
                    $this->notify($post_id);
                    // save results for later messages if PRG
                    //$formHelper->setCookie('_cred_cred_notifications'.$prg_id, array('sent'=>$n_sent, 'failed'=>$n_failed));
                    
                    // do custom action here
                    // user can redirect, display messages, overwrite page etc..
					$bypass_credaction=apply_filters('cred_bypass_credaction_'.$form_id, false, $form_id, $post_id, $thisform);
					$bypass_credaction=apply_filters('cred_bypass_credaction', $bypass_credaction, $form_id, $post_id, $thisform);
					
                    do_action('cred_submit_complete_'.$form_id, $post_id, $thisform);
                    do_action('cred_submit_complete', $post_id, $thisform);
                    
                    // no redirect url
                    $url=false;
                    // do success action
					if ($bypass_credaction)
					{
						$credaction='form';
					}
					else
					{
						$credaction=$form->fields['form_settings']->form['action'];
					}
                    // do default or custom actions
                    switch($credaction)
                    {
                        case 'post':
                            $url=$formHelper->getLocalisedPermalink($post_id, $form->fields['form_settings']->post['post_type']); //get_permalink($post_id);
                            break;
                        case 'page':
                            $url=(!empty($form->fields['form_settings']->form['action_page']))?$formHelper->getLocalisedPermalink($form->fields['form_settings']->form['action_page'], 'page')/*get_permalink($form->fields['form_settings']->form['action_page'])*/:false;
                            break;
                        case 'message':
                        case 'form':
                        // custom 3rd-party action
                        default:
                            if ('form'!=$credaction && 'message'!=$credaction)
                            {
                                // add hooks here, to do custom action when custom cred action has been selected
                                do_action('cred_custom_success_action_'.$form_id, $credaction, $post_id, $thisform);
                                do_action('cred_custom_success_action', $credaction, $post_id, $thisform);
                            }
                            
                            // if previous did not do anything, default to display form
                            if ('form'!=$credaction && 'message'!=$credaction)
                                $credaction='form';
                                
                            // no redirect url
                            $url=false;
                            
                            // PRG (POST-REDIRECT-GET) pattern, 
                            // to avoid resubmit on browser refresh issue, and also keep defaults on new form !! :)
                            if ('message'==$credaction)
                            {
                                $url=$formHelper->currentURI(array(
                                    '_tt'=>time(),
                                    '_success_message'=>$prg_id
                                ));
                            }
                            else
                            {
                                $url=$formHelper->currentURI(array(
                                    '_tt'=>time(),
                                    '_success'=>$prg_id
                                ));
                            }
                            // do PRG, redirect now
                            $formHelper->redirect($url, array("HTTP/1.1 303 See Other"));
                            exit;  // just in case
                            break;
                    }
                    
                    // do redirect action here
                    if (false!==$url)
                    {
                        if ('form'!=$credaction && 'message'!=$credaction)
                        {
                            $url = apply_filters('cred_success_redirect_'.$form_id, $url, $post_id, $thisform);
                            $url = apply_filters('cred_success_redirect', $url, $post_id, $thisform);
                        }
                        
                        if (false!==$url)
                        {
                            $redirect_delay=$form->fields['form_settings']->form['redirect_delay'];
                            if ($redirect_delay <= 0)
                                $formHelper->redirect($url);
                            else
                                $formHelper->redirectDelayed($url, $redirect_delay);
                        }
                    }                        
                     
                    // reset form if needed, NOT NEEDED ANY MORE since USE PRG Pattern (at least for any forms)
                    if (/*'edit' != $form_type &&*/ !$message)
                    {
                        // restore nonce value
                        $nonce=$zebraForm->controls[$this->out_['nonce_field']]->attributes['value'];
                        $zebraForm->reset();
                        $zebraForm->controls[$this->out_['nonce_field']]->set_attributes(array('value'=>$nonce));
                        // regenerate dummy post id
                        if (isset($this->out_['post_id_field']))
                        {
                            $post_id=get_default_post_to_edit( $post_type, true )->ID;
                            $zebraForm->controls[$this->out_['post_id_field']]->set_attributes(array('value'=>$post_id),true);
                        }
                        // restore form_id
                        $zebraForm->controls[$this->out_['form_id_field']]->set_attributes(array('value'=>$form_id));
                        // restore form_count
                        $zebraForm->controls[$this->out_['form_count_field']]->set_attributes(array('value'=>$form_count));
                    }
                    
                    // add success message
                    $zebraForm->add_form_message('data-saved', $formHelper->getLocalisedMessage('post_saved'));
               }
                else
                {
                    // else just show the form again
                    $zebraForm->add_form_message('data-saved', $formHelper->getLocalisedMessage('post_not_saved'));
                }
            }
            else
            {
                $zebraForm->add_form_message('preview-form',__('Preview Form submitted','wp-cred'));
            }
        }
        else if ($this->isSubmitted())
        {
            $not_saved_message=$formHelper->getLocalisedMessage('post_not_saved');
            $not_saved_message=apply_filters('cred_data_not_saved_message_'.$form_id, $not_saved_message, $form_id, $post_id, $preview);
            $not_saved_message=apply_filters('cred_data_not_saved_message', $not_saved_message, $form_id, $post_id, $preview);
            $zebraForm->add_form_message('data-saved', $not_saved_message);
        }
        
        if (false!==$message)
            $output=$message;
        else
            $output=$this->render();
            
        return $output;
    }
       
    private function parseInputs($form_id, $post_id=null, $preview=false, $force_form_count=false)
    {
        global $post;
        
        // reference to the form submission method
        global ${'_' . self::METHOD};
        $method = & ${'_' . self::METHOD};
        
        // get post inputs
        if (isset($post_id) && !empty($post_id) && null!==$post_id && false!==$post_id && !$preview)
            $post_id=intval($post_id);
        elseif (isset($post->ID) && !$preview)
            $post_id=$post->ID;
        else
            $post_id=false;
        
        $formHelper=$this->_formHelper;
        // get recaptcha settings
        self::$_staticGlobal['RECAPTCHA']=$formHelper->getRecaptchaSettings(self::$_staticGlobal['RECAPTCHA']);
        
        // load form data
        $this->_formData=$formHelper->loadForm($form_id, $preview);
        if ($formHelper->isError($this->_formData))
            return $this->_formData;
        
        $form_id = $this->_formData->form->ID;
        $form_type = $this->_formData->fields['form_settings']->form['type'];
        $post_type = $this->_formData->fields['form_settings']->post['post_type'];
        
        // if this is an edit form and no post id given
        if ((('edit'==$form_type && false===$post_id && !$preview) || (isset($_GET['action']) && $_GET['action'] == 'edit_translation' && 'translation'==$form_type)) && false===$post_id && !$preview)
        {
            return $formHelper->error(__('No post specified','wp-cred'));
        }
        
        // if this is a new form or preview
        if ('new'==$form_type || $preview || (isset($_GET['action']) && $_GET['action'] == 'create_translation' && 'translation'==$form_type) || $preview)
        {
            // always get new dummy id, to avoid the issue of editing the post on browser back button
            $post_id=get_default_post_to_edit( $post_type, true )->ID;
        }
        
        // get existing post data if edit form and post given
        if ((('edit'==$form_type && !$preview) || (isset($_GET['action']) && $_GET['action'] == 'edit_translation' && 'translation'==$form_type)) && !$preview)
        {
            $this->_postData=$formHelper->getPostData($post_id);
            
            if ($formHelper->isError($this->_postData))
                return $this->_postData;
                
            if ($this->_postData->post->post_type!=$post_type)
                return $formHelper->error(__('Form type and post type do not match','wp-cred'));
        }
        
        // check if user has access to this form
        if (!$preview && !$formHelper->checkFormAccess($form_type, $form_id, $this->_postData))
            return $formHelper->error();
        
        // set allowed file types
        self::$_staticGlobal['MIMES']=$formHelper->getAllowedMimeTypes();
        
        // get custom post fields
        $fields=$formHelper->getFieldSettings($post_type);
        
        // instantiate Zebra Form
        if (false!==$force_form_count)
            $form_count = $force_form_count;
        else
            $form_count = self::$_globalStatic['COUNT'];
            
        // strip any unneeded parsms from current uri
        $actionUri = $formHelper->currentURI(array(
                    '_tt'=>time()       // add time get bypass cache
                    ),array(
                    '_success',         // remove previous success get if set
                    '_success_message'   // remove previous success get if set
                )); 
        $prg_form_id = $formHelper->createPrgID($form_id, $form_count);
        $zebra_form_id = $formHelper->createFormID($form_id, $form_count);
        $this->_zebraForm=$formHelper->getZebraForm($zebra_form_id, $actionUri, $preview);
        if ($formHelper->isError($this->_zebraForm))
            return $this->_zebraForm;
        
        // all fine here
        $this->_post_ID=$post_id;
        $this->_content=$this->_formData->form->post_content;
        $this->out_['fields']=$fields;
        $this->out_['count']=$form_count;
        $this->out_['prg_id']=$prg_form_id;
        return true;
    }
    
    // build form (parse CRED shortcodes and build Zebra_Form object)
    private function build()
    {
        // get refs here
        $out_=&$this->out_;
        $formHelper=$this->_formHelper;
        $shortcodeParser=$this->_shortcodeParser;
        $zebraForm=$this->_zebraForm;
        
        if ($zebraForm->preview)
            $preview_content=$this->_content;

        // remove any HTML comments before parsing, allow to comment-out parts
        $this->_content=$shortcodeParser->removeHtmlComments($this->_content);
        // do WP shortcode here for final output, moved here to avoid replacing post_content
        $this->_content=do_shortcode($this->_content);
        // parse all shortcodes internally
        $shortcodeParser->remove_all_shortcodes();
        $shortcodeParser->add_shortcode( 'credform', array(&$this,'cred_form_shortcode') );
        $this->_content=$shortcodeParser->do_shortcode($this->_content);
        $shortcodeParser->remove_shortcode( 'credform', array(&$this,'cred_form_shortcode') );
        
        // add any custom attributes eg class
        if (
            isset($zebraForm->form_properties['attributes']) 
            && is_array($zebraForm->form_properties['attributes']) 
            && !empty($zebraForm->form_properties['attributes'])
        )
            $zebraForm->form_properties['attributes']=array_merge($zebraForm->form_properties['attributes'], $this->_attributes);
        else
            $zebraForm->form_properties['attributes']=$this->_attributes;
        
        // render any external third-party shortcodes first (enables using shortcodes as values to cred shortcodes)
        $this->_form_content=do_shortcode($this->_form_content);
        // build shortcodes, (backwards compatibility, render first old shortcode format with dashes)
        $shortcodeParser->add_shortcode( 'cred-field', array(&$this, 'cred_field_shortcodes') );
        $shortcodeParser->add_shortcode( 'cred-generic-field', array(&$this, 'cred_generic_field_shortcodes') );
        $shortcodeParser->add_shortcode( 'cred-show-group', array(&$this, 'cred_conditional_shortcodes') );
        // build shortcodes, render new shortcode format with underscores
        $shortcodeParser->add_shortcode( 'cred_field', array(&$this, 'cred_field_shortcodes') );
        $shortcodeParser->add_shortcode( 'cred_generic_field', array(&$this, 'cred_generic_field_shortcodes') );
        $shortcodeParser->add_shortcode( 'cred_show_group', array(&$this, 'cred_conditional_shortcodes') );
        $out_['child_groups']=array();
        $this->_form_content=$shortcodeParser->do_recursive_shortcode('cred-show-group', $this->_form_content);
        $this->_form_content=$shortcodeParser->do_recursive_shortcode('cred_show_group', $this->_form_content);
        $out_['child_groups']=array();
        $this->_form_content=$shortcodeParser->do_shortcode($this->_form_content);
        $shortcodeParser->remove_shortcode( 'cred_show_group', array(&$this, 'cred_conditional_shortcodes') );
        $shortcodeParser->remove_shortcode( 'cred_generic_field', array(&$this, 'cred_generic_field_shortcodes') );
        $shortcodeParser->remove_shortcode( 'cred_field', array(&$this, 'cred_field_shortcodes') );
        $shortcodeParser->remove_shortcode( 'cred-show-group', array(&$this, 'cred_conditional_shortcodes') );
        $shortcodeParser->remove_shortcode( 'cred-generic-field', array(&$this, 'cred_generic_field_shortcodes') );
        $shortcodeParser->remove_shortcode( 'cred-field', array(&$this, 'cred_field_shortcodes') );
        // add some auxilliary fields to form
        // add nonce hidden field
        $nonceobj=$zebraForm->add('hidden', self::NONCE, wp_create_nonce($zebraForm->form_properties['name']), array('style'=>'display:none;'));
        $out_['nonce_field']=$nonceobj->attributes['id'];
        
        // add post_id hidden field
        if ($this->_post_ID)
        {
            $post_id_obj=$zebraForm->add('hidden', self::PREFIX.'post_id', $this->_post_ID, array('style'=>'display:none;'));
            $out_['post_id_field']=$post_id_obj->attributes['id'];
        }
        // add to form
        $form_type=$this->_formData->fields['form_settings']->form['type'];
        $form_id=$this->_formData->form->ID;
        $form_count=$out_['count'];
        $post_type=$this->_formData->fields['form_settings']->post['post_type'];
        if ($zebraForm->preview)
        {
            // add temporary content for form preview
            $obj=$zebraForm->add('textarea', self::PREFIX.'form_preview_content', $preview_content, array('style'=>'display:none;'));
            // add temporary content for form preview (not added automatically as there is no shortcode to render this)
            $this->_form_content.=$obj->toHTML();
            // hidden fields are rendered automatically
            $obj=$zebraForm->add('hidden', self::PREFIX.'form_preview_post_type', $post_type, array('style'=>'display:none;'));
            $obj=$zebraForm->add('hidden',self::PREFIX.'form_preview_form_type', $form_type, array('style'=>'display:none;'));
            
            if ($this->_formData->fields['form_settings']->form['has_media_button'])
                $zebraForm->add_form_error('preview_media', __('Media Upload will not work with form preview','wp-cred'));
            
            $zebraForm->add_form_message('preview_mode', __('Form Preview Mode','wp-cred'));
        }
        // hidden fields are rendered automatically
        // add form id
        $obj=$zebraForm->add('hidden', self::PREFIX.'form_id', $form_id, array('style'=>'display:none;'));
        $out_['form_id_field']=$obj->attributes['id'];
        // add form count
        $obj=$zebraForm->add('hidden', self::PREFIX.'form_count', $form_count, array('style'=>'display:none;'));
        $out_['form_count_field']=$obj->attributes['id'];
        
        // check conditional expressions for javascript
        $formHelper->parseConditionalExpressions($out_);
    }
       
    // check if submitted
    private function isSubmitted()
    {
        return ($this->_zebraForm->isSubmitted());
    }
       
    // validate form
    private function validate()
    {
        // reference to the form submission method
        global ${'_' . self::METHOD};
        $method = & ${'_' . self::METHOD};
        
        $zebraForm=$this->_zebraForm;
        $formHelper=$this->_formHelper;
        $result=false;
        if ($zebraForm->isSubmitted())
        {
            // verify nonce field
            if (!array_key_exists(self::NONCE, $method) || !wp_verify_nonce($method[self::NONCE], $zebraForm->form_properties['name']))
            {
                 $zebraForm->add_form_error('security', $formHelper->getLocalisedMessage('invalid_form_submission'));
                 $result=false;
                 return $result;
            }
            // get values
            $form_id=$this->_formData->form->ID;
            $form_type=$this->_formData->fields['form_settings']->form['type'];
            $post_type=$this->_formData->fields['form_settings']->post['post_type'];
            $thisform=array(
                'id'=>$form_id,
                'post_type'=>$post_type,
                'form_type'=>$form_type
            );
            $zebraForm->get_submitted_values();
            $fields=$formHelper->get_form_field_values();
            $errors=array();
            list($fields, $errors)=apply_filters('cred_form_validate_'.$form_id, array($fields, $errors), $thisform);
            list($fields, $errors)=apply_filters('cred_form_validate', array($fields, $errors), $thisform);
            if (!empty($errors))
            {
                foreach ($errors as $fname=>$err)
                {
                    if (array_key_exists($fname, $this->out_['form_fields']))
                        $zebraForm->controls[$this->out_['form_fields'][$fname][0]]->addError($err);
                }
            }
            else
            {
                $formHelper->set_form_field_values($fields);
            }
            $result=$zebraForm->validate(true);
        }
        return $result;
    }
       
    // save form data (if form valid)
    private function save($post_id=null)
    {
        // reference to the form submission method
        global ${'_' . self::METHOD};
        $method = & ${'_' . self::METHOD};
        
        $formHelper=$this->_formHelper;
        $zebraForm=$this->_zebraForm;
        $form=&$this->_formData;
        $out_=&$this->out_;
        $form_id=$form->form->ID;
        $form_type=$form->fields['form_settings']->form['type'];
        $post_type=$form->fields['form_settings']->post['post_type'];
        $thisform=array(
            'id'=>$form_id,
            'post_type'=>$post_type,
            'form_type'=>$form_type
        );
        
        // do custom actions before post save
        do_action('cred_before_save_data_'.$form_id, $thisform);
        do_action('cred_before_save_data', $thisform);
        
        // track form data for notification mail
        $trackNotification=false;
        if (
            isset($form->fields['notification']->enable) && 
            $form->fields['notification']->enable &&
            !empty($form->fields['notification']->notifications)
        )
            $trackNotification=true;
        
        // save result (on success this is post ID)
        $new_post_id=false;
        // default post fields
        $post = $formHelper->extractPostFields($post_id, $trackNotification);
        // custom fields, taxonomies and file uploads
        list($fields, $fieldsInfo, $taxonomies, $files, $removed_fields) = $formHelper->extractCustomFields($post_id, $trackNotification);
        // upload attachments
        $extra_files=array();
        $all_ok = $formHelper->uploadAttachments($post_id, $fields, $files, $extra_files, $trackNotification);
        
        if ($all_ok)
        {
            // save everything
            $model=CRED_Loader::get('MODEL/Forms');
            if (empty($post->ID))  $new_post_id=$model->addPost($post, array('fields'=>$fields, 'info'=>$fieldsInfo, 'removed'=>$removed_fields), $taxonomies);
            else  $new_post_id=$model->updatePost($post, array('fields'=>$fields, 'info'=>$fieldsInfo, 'removed'=>$removed_fields), $taxonomies);
            //cred_log(array('fields'=>$fields, 'info'=>$fieldsInfo, 'removed'=>$removed_fields));
            if (is_int($new_post_id) && $new_post_id>0)
            {
                $formHelper->attachUploads($new_post_id, $fields, $files, $extra_files);
                // save notification data (pre-formatted)
                if ($trackNotification) $out_['notification_data']=$formHelper->trackData(null, true);
				// for WooCommerce products only (update prices in products)
				if (class_exists('Woocommerce') && 'product'==get_post_type($new_post_id))
				{
					if (isset($fields['_regular_price']) && !isset($fields['_price'])) 
					{
						$regular_price = $fields['_regular_price'];
						update_post_meta($new_post_id, '_price', $regular_price);
						$sale_price = get_post_meta($new_post_id, '_sale_price', true);
						// Update price if on sale
						if ($sale_price!='')
						{
							$sale_price_dates_from = get_post_meta($new_post_id, '_sale_price_dates_from', true);
							$sale_price_dates_to = get_post_meta($new_post_id, '_sale_price_dates_to', true);
							if ($sale_price_dates_to=='' && $sale_price_dates_to=='')
								update_post_meta($new_post_id, '_price', $sale_price);
							else if ($sale_price_dates_from && strtotime($sale_price_dates_from)<strtotime('NOW',current_time('timestamp')))
								update_post_meta($new_post_id, '_price', $sale_price);
							if ($sale_price_dates_to && strtotime($sale_price_dates_to)<strtotime('NOW',current_time('timestamp')))
								update_post_meta($new_post_id, '_price', $regular_price);
						}
					}
					else if (isset($fields['_price']) && !isset($fields['_regular_price'])) 
					{
						update_post_meta($new_post_id, '_regular_price', $fields['_price']);
					}
				}

                // do custom actions on successful post save
                do_action('cred_save_data_'.$form_id, $new_post_id, $thisform);
                do_action('cred_save_data', $new_post_id, $thisform);
            }
        }
        // return saved post_id as result
        return $new_post_id;
    }
       
    // send notifications for the form
    private function notify($post_id)
    {
        $form=&$this->_formData;
        
        // init notification manager if needed
        if (
            isset($form->fields['notification']->enable) && 
            $form->fields['notification']->enable &&
            !empty($form->fields['notification']->notifications)
        )
        {
            // add extra plceholder codes
            add_filter('cred_subject_notification_codes', array(&$this, 'extraSubjectNotificationCodes'), 5, 3);
            add_filter('cred_body_notification_codes', array(&$this, 'extraBodyNotificationCodes'), 5, 3);
            
			CRED_Loader::load('CLASS/Notification_Manager');
            // add the post to notification management
            CRED_Notification_Manager::add($post_id, $form->form->ID, $form->fields['notification']->notifications);
            // send any notifications now if needed
            CRED_Notification_Manager::triggerNotifications($post_id, array(
                'event'=>'form_submit',
                'form_id'=>$form->form->ID, 
                'notification'=>$form->fields['notification']
            ));
            
            // remove extra plceholder codes
            remove_filter('cred_subject_notification_codes', array(&$this, 'extraSubjectNotificationCodes'), 5, 3);
            remove_filter('cred_body_notification_codes', array(&$this, 'extraBodyNotificationCodes'), 5, 3);
        }
    }
    
    public function extraSubjectNotificationCodes($codes, $form_id, $post_id)
    {
        if ($form_id==$this->_formData->form->ID)
        {
            $codes['%%POST_PARENT_TITLE%%']=$this->cred_parent(array('get'=>'title'));
        }
        return $codes;
    }
    
    public function extraBodyNotificationCodes($codes, $form_id, $post_id)
    {
        if ($form_id==$this->_formData->form->ID)
        {
            $codes['%%FORM_DATA%%']=isset($this->out_['notification_data'])?$this->out_['notification_data']:'';
            $codes['%%POST_PARENT_TITLE%%']=$this->cred_parent(array('get'=>'title'));
            $codes['%%POST_PARENT_LINK%%']=$this->cred_parent(array('get'=>'url'));
        }
        return $codes;
    }
    
    // render form (return actual HTML code)
    private function render()
    {
        $shortcodeParser=$this->_shortcodeParser;
        $zebraForm=$this->_zebraForm;
        
        $shortcodeParser->remove_all_shortcodes();
        $shortcodeParser->add_shortcode( 'render_cred_field', array(&$this, 'render_cred_shortcodes') );
        list($this->_form_content, $form_js)=$zebraForm->render(array(&$this, 'render_callback'), true);
        $shortcodeParser->remove_shortcode( 'render_cred_field', array(&$this,'render_cred_shortcodes') );
        $this->out_['js']=$form_js;
        
        // post content area might contain shortcodes, so return them raw by replacing with a dummy placeholder
        $this->_content=str_replace(self::FORM_TAG.'_'.$zebraForm->form_properties['name'].'%', $this->_form_content, $this->_content);
        
        // parse old shortcode first (with dashes)
        $shortcodeParser->add_shortcode( 'cred-post-parent', array(&$this, 'cred_parent') );
        $this->_content=$shortcodeParser->do_shortcode($this->_content);
        $shortcodeParser->remove_shortcode( 'cred-post-parent', array(&$this, 'cred_parent') );
        // parse new shortcode (with underscores)
        $shortcodeParser->add_shortcode( 'cred_post_parent', array(&$this, 'cred_parent') );
        $this->_content=$shortcodeParser->do_shortcode($this->_content);
        $shortcodeParser->remove_shortcode( 'cred_post_parent', array(&$this, 'cred_parent') );
        return $this->_content;
    }
    
    // parse form shortcode [credform]
    public function cred_form_shortcode($atts, $content='')
    {
        extract( shortcode_atts( array(
            'class'=>''
        ), $atts ) );
        
        if (!empty($class))
            $this->_attributes['class']=esc_attr($class);
        // return a placeholder instead and store the content in _form_content var
        $this->_form_content=$content;
        return self::FORM_TAG.'_'.$this->_zebraForm->form_properties['name'].'%';
    }
       
       
/**
 * CRED-Shortcode: cred_show_group
 *
 * Description: Show/Hide a group of fields based on conditional logic and values of form fields
 *
 * Parameters:
 * 'if' => Conditional Expression
 * 'mode' => Effect for show/hide group, values are: "fade-slide", "fade", "slide", "none"
 *  
 *   
 * Example usage:
 * 
 *    [cred_show_group if="$(date) gt TODAY()" mode="fade-slide"]
 *       //rest of content to be hidden or shown
 *      // inside the shortcode body..
 *    [/cred_show_group]
 *
 * Link:
 *
 *
 * Note:
 *
 *
 **/
    // parse conditional shortcodes (nested allowed) [cred_show_group]
    public function cred_conditional_shortcodes($atts, $content='')
    {
        static $condition_id=0;
        
        shortcode_atts( array(
            'if' => '',
            'mode'=> 'fade-slide'
        ), $atts ); //);
        
        if (empty($atts['if']) || !isset($content) || empty($content))
            return ''; // ignore
        
        $form=&$this->_formData;
        $out_=&$this->out_;
        $zebraForm=$this->_zebraForm;
        $shortcodeParser=$this->_shortcodeParser;
        // render conditional group
        ++$condition_id;
        $group=$zebraForm->add_conditional_group( $form->form->ID.'_condition_'.$condition_id );
        
        // add child groups from prev level
        if ($shortcodeParser->depth>0 && isset($shortcodeParser->child_groups[$shortcodeParser->depth-1]))
        {
            foreach ($out_['child_groups'][$shortcodeParser->depth-1] as $child_group)
                $group->addControl($child_group);
        }
        // add this group to child groups for next level
        if (!isset($out_['child_groups'][$shortcodeParser->depth]))
            $out_['child_groups'][$shortcodeParser->depth]=array();
        $out_['child_groups'][$shortcodeParser->depth][]=$group;
        
        // render conditional groups hierarchically
        if (null!==$out_['current_group'])
            $out_['current_group']->addControl($group);
        $prev_group=$out_['current_group'];
        $out_['current_group']=$group;
        $content=$shortcodeParser->do_shortcode($content);
        $out_['current_group']=$prev_group;
        // process this later, before render
        $condition=array(
            'id'=>$group->attributes['id'],
            'container_id'=>$group->attributes['id'],
            'condition' => $atts['if'],
            'replaced_condition'=>'',
            'mode' => isset($atts['mode'])?$atts['mode']:'fade-slide',
            'valid'=>false,
            'var_field_map'=>array()
            );
        $out_['conditionals'][$group->attributes['id']]=$condition;
        return $group->render($content);
    }
       
/**
 * CRED-Shortcode: cred_generic_field
 *
 * Description: Render a form generic field (general fields not associated with types plugin)
 *
 * Parameters:
 * 'field' => Field name (name like used in html forms)
 * 'type' => Type of input field (eg checkbox, email, select, radio, checkboxes, date, file, image etc..)
 * 'class'=> [optional] Css class to apply to the element
 * 'urlparam'=> [optional] URL parameter to be used to give value to the field
 * 'placeholder'=>[optional] Text to be used as placeholder (HTML5) for text fields, default none
 *  
 *  Inside shortcode body the necessary options and default values are defined as JSON string (autogenerated by GUI)
 *   
 * Example usage:
 * 
 *    [cred_generic_field field="gmail" type="email" class=""]
 *    {
 *    "required":0,
 *    "validate_format":0,
 *    "default":""
 *    }
 *    [/cred_generic_field]
 *
 * Link:
 *
 *
 * Note:
 *
 *
 **/
    // parse generic input field shortcodes [cred_generic_field]
    public function cred_generic_field_shortcodes($atts, $content='')
    {
        $atts=shortcode_atts( array(
            'field' => '',
            'type' => '',
            'class'=>'',
            'placeholder'=>null,
            'urlparam'=>''
        ), $atts );
        if (empty($atts['field']) || empty($atts['type']) || null==$content || empty($content))
            return ''; // ignore
        
        $field_data=json_decode(preg_replace('/[\r\n]/', '', $content), true); // remove NL (crlf) to prevent json_decode from failing
        // only for php >= 5.3.0
        if (
            (function_exists('json_last_error') && json_last_error() != JSON_ERROR_NONE) ||
            empty($field_data) /* probably JSON decode error */
        )
        {
            return ''; //ignore not valid json
        }
        
        $formHelper=$this->_formHelper;
        
        $field= array ( 
            'id' => $atts['field'], 
            'cred_generic'=>true, 
            'slug' => $atts['field'], 
            'type' => $atts['type'], 
            'name' => $atts['field'], 
            'data' => array ( 
                    'repetitive' => 0, 
                    'validate' => array ( 
                        'required' => array ( 
                            'active' => $field_data['required'], 
                            'value' => $field_data['required'], 
                            'message' => $formHelper->getLocalisedMessage('field_required') 
                        )
                    ), 
                    'validate_format'=>$field_data['validate_format'],
                    'persist'=>isset($field_data['persist'])?$field_data['persist']:0
                ) 
        );
        $default=$field_data['default'];
        switch($atts['type'])
        {
            case 'checkbox':
                $field['data']['set_value']=$field_data['default'];
                if ($field_data['checked']!=1)
                    $default=null;
                break;
            case 'checkboxes':
                $field['data']['options']=array();
                foreach ($field_data['options'] as $ii=>$option)
                {
                    $option_id=$option['value'];
                    //$option_id=$atts['field'].'_option_'.$ii;
                    $field['data']['options'][$option_id]=array(
                        'title' => $option['label'],
                        'set_value' => $option['value']
                    );
                    if (in_array($option['value'],$field_data['default']))
                    {
                        $field['data']['options'][$option_id]['checked']=true;
                    }
                }
                $default=null;
                break;
            case 'date':
                $field['data']['validate']['date']=array(
                    'active' => $field_data['validate_format'],
                    'format' => 'mdy',
                    'message' => $formHelper->getLocalisedMessage('enter_valid_date')
                );
				// allow a default value
                //$default=null;
                break;
            case 'hidden':
                $field['data']['validate']['hidden']=array(
                    'active' => $field_data['validate_format'],
                    'message' => $formHelper->getLocalisedMessage('values_do_not_match')
                );
                break;
            case 'radio':
            case 'select':
                $field['data']['options']=array();
                $default_option='no-default';
                foreach ($field_data['options'] as $ii=>$option)
                {
                    $option_id=$option['value'];
                    //$option_id=$atts['field'].'_option_'.$ii;
                    $field['data']['options'][$option_id]=array(
                        'title' => $option['label'],
                        'value' => $option['value'],
                        'display_value' => $option['value']
                    );
                    if (!empty($field_data['default']) && $field_data['default'][0]==$option['value'])
                        $default_option=$option_id;
                }
                $field['data']['options']['default'] = $default_option;
                $default=null;
                break;
            case 'multiselect':
                $field['data']['options']=array();
                $default_option=array();
                foreach ($field_data['options'] as $ii=>$option)
                {
                    $option_id=$option['value'];
                    //$option_id=$atts['field'].'_option_'.$ii;
                    $field['data']['options'][$option_id]=array(
                        'title' => $option['label'],
                        'value' => $option['value'],
                        'display_value' => $option['value']
                    );
                    if (!empty($field_data['default']) && in_array($option['value'],$field_data['default']))
                        $default_option[]=$option_id;
                }
                $field['data']['options']['default'] = $default_option;
                $field['data']['is_multiselect'] = 1;
                $default=null;
                break;
            case 'email':
                $field['data']['validate']['email']=array(
                    'active' => $field_data['validate_format'],
                    'message' => $formHelper->getLocalisedMessage('enter_valid_email')
                );
                break;
            case 'numeric':
                $field['data']['validate']['number']=array(
                    'active' => $field_data['validate_format'],
                    'message' => $formHelper->getLocalisedMessage('enter_valid_number')
                );
                break;
            case 'integer':
                $field['data']['validate']['integer']=array(
                    'active' => $field_data['validate_format'],
                    'message' => $formHelper->getLocalisedMessage('enter_valid_number')
                );
                break;
            case 'url':
                $field['data']['validate']['url']=array(
                    'active' => $field_data['validate_format'],
                    'message' => $formHelper->getLocalisedMessage('enter_valid_url')
                );
                break;
            default:
                $default=$field_data['default'];
                break;
        }
        
        $name=$field['slug'];
        if ($atts['type']=='image' || $atts['type']=='file')
        {
            if (isset($field_data['max_width']) && is_numeric($field_data['max_width']))
                $max_width=intval($field_data['max_width']);
            else
                $max_width=null;
            if (isset($field_data['max_height']) && is_numeric($field_data['max_height']))
                $max_height=intval($field_data['max_height']);
            else
                $max_height=null;
                
            $ids=$formHelper->translate_field($name, $field, array(
                                                        'preset_value'=>$default,
                                                        'urlparam'=>$atts['urlparam'],
                                                        'is_tax'=>false,
                                                        'max_width'=>$max_width,
                                                        'max_height'=>$max_height));
        }
        else if ($atts['type']=='hidden')
        {
            if (isset($field_data['generic_type']))
                $generic_type=intval($field_data['generic_type']);
            else
                $generic_type=null;
                
            $ids=$formHelper->translate_field($name, $field, array(
					'preset_value'=>$default,
					'urlparam'=>$atts['urlparam'],
					'generic_type'=>$generic_type)
			);
        }
        else
            $ids=$formHelper->translate_field($name, $field, array('preset_value'=>$default, 'placeholder'=>$atts['placeholder'], 'urlparam'=>$atts['urlparam']));
        
        if ($field['data']['persist'])
        {
            // this field is going to be saved as custom field to db
            $this->out_['fields']['post_fields'][$name]=$field;
        }
        // check which fields are actually used in form
        $this->out_['form_fields'][$name]=$ids;
        $this->out_['form_fields_info'][$name]=array(
            'type'=>$field['type'],
            'repetitive'=>(isset($field['data']['repetitive'])&&$field['data']['repetitive']),
            'plugin_type'=>(isset($field['plugin_type']))?$field['plugin_type']:'',
            'name'=>$name
        );
        $this->out_['generic_fields'][$name]=$ids;
        if (!empty($atts['class']))
        {
            $atts['class']=esc_attr($atts['class']);
            foreach ($ids as $id)
                $this->_zebraForm->controls[$id]->set_attributes(array('class'=>$atts['class']),false);
        }
        $out='';
        foreach ($ids as $id)
            $out.= "[render_cred_field field='{$id}']";
        return $out;
    }
        
/**
 * CRED-Shortcode: cred_field
 *
 * Description: Render a form field (using fields defined in wp-types plugin and / or Taxonomies)
 *
 * Parameters:
 * 'field' => Field slug name
 * 'post' => [optional] Post Type where this field is defined 
 * 'value'=> [optional] Preset value (does not apply to all field types, eg taxonomies)
 * 'taxonomy'=> [optional] Used by taxonomy auxilliary fields (eg. "show_popular") to signify to which taxonomy this field belongs
 * 'type'=> [optional] Used by taxonomy auxilliary fields (like show_popular) to signify which type of functionality it provides (eg. "show_popular")
 * 'display'=> [optional] Used by fields for Hierarchical Taxonomies (like Categories) to signify the mode of display (ie. "select" or "checkbox")
 * 'single_select'=> [optional] Used by fields for Hierarchical Taxonomies (like Categories) to signify that select field does not support multi-select mode
 * 'max_width'=>[optional] Max Width for image fields
 * 'max_height'=>[optional] Max Height for image fields
 * 'max_results'=>[optional] Max results in parent select field
 * 'order'=>[optional] Order for parent select field (title or date)
 * 'ordering'=>[optional] Ordering for parent select field (asc, desc)
 * 'required'=>[optional] Whether parent field is required, default 'false'
 * 'no_parent_text'=>[optional] Text for no parent selection in parent field
 * 'select_text'=>[optional] Text for required parent selection
 * 'validate_text'=>[optional] Text for error message when parebt not selected
 * 'placeholder'=>[optional] Text to be used as placeholder (HTML5) for text fields, default none
 * 'readonly'=>[optional] Whether this field is readonly (cannot be edited, applies to text fields), default 'false'
 * 'urlparam'=> [optional] URL parameter to be used to give value to the field
 *
 * Example usage:
 *
 *  Render the wp-types field "Mobile" defined for post type Agent
 * [cred_field field="mobile" post="agent" value="555-1234"]
 *
 * Link:
 *
 *
 * Note:
 *  'value'> translated automatically if WPML translation exists
 *  'taxonomy'> used with "type" option
 *  'type'> used with "taxonomy" option
 *
 **/
    // parse field shortcodes [cred_field]
    public function cred_field_shortcodes($atts)
    {
        $formHelper=$this->_formHelper;
        $form=&$this->_formData;
        $form_type=$form->fields['form_settings']->form['type'];
        $post_type=$form->fields['form_settings']->post['post_type'];
        extract( shortcode_atts( array(
            'post' => '',
            'field' => '',
            'value' => null,
            'urlparam'=>'',
            'placeholder'=>null,
            'escape'=>'false',
            'readonly'=>'false',
            'taxonomy'=>null,
            'single_select'=>null,
            'type'  => null,
            'display'=>null,
            'max_width'=>null,
            'max_height'=>null,
            'max_results'=>null,
            'order'=>null,
            'ordering'=>null,
            'required'=>'false',
            'no_parent_text'=>__('No Parent','wp-cred'),
            'select_text'=>__('-- Please Select --','wp-cred'),
            'validate_text'=>$formHelper->getLocalisedMessage('field_required'),
        ), $atts ) );
        
        // make boolean
        $escape=false; //(bool)(strtoupper($escape)==='TRUE');
        // make boolean
        $readonly=(bool)(strtoupper($readonly)==='TRUE');
        
        if (!$taxonomy)
        {
            if (in_array($field, array_keys($this->out_['fields']['post_fields'])))
            {
                if ($post!=$post_type) return '';
                
                $field=$this->out_['fields']['post_fields'][$field];
                $name=$name_orig=$field['slug'];
                if (isset($field['plugin_type_prefix']))
                    $name = /*'wpcf-'*/$field['plugin_type_prefix'].$name;
                
                if ('image'==$field['type'] || 'file'==$field['type'])
                    $ids=$formHelper->translate_field($name, $field, array(
                                            'preset_value'=>$value,
                                            'urlparam'=>$urlparam,
                                            'is_tax'=>false,
                                            'max_width'=>$max_width,
                                            'max_height'=>$max_height));
                else
                    $ids=$formHelper->translate_field($name, $field, array(
                                            'preset_value'=>$value,
                                            'urlparam'=>$urlparam,
                                            'value_escape'=>$escape,
                                            'make_readonly'=>$readonly,
                                            'placeholder'=>$placeholder));
                    
                // check which fields are actually used in form
                $this->out_['form_fields'][$name_orig]=$ids;
                $this->out_['form_fields_info'][$name_orig]=array(
                    'type'=>$field['type'],
                    'repetitive'=>(isset($field['data']['repetitive'])&&$field['data']['repetitive']),
                    'plugin_type'=>(isset($field['plugin_type']))?$field['plugin_type']:'',
                    'name'=>$name
                );
                $out='';
                foreach ($ids as $id)
                    $out.= "[render_cred_field post='{$post}' field='{$id}']";
                return $out;
            }
            elseif (in_array($field, array_keys($this->out_['fields']['parents'])))
            {
                $name=$name_orig=$field;
                $field=$this->out_['fields']['parents'][$field];
                $potential_parents=CRED_Loader::get('MODEL/Fields')->getPotentialParents($field['data']['post_type'],$this->_post_ID, $max_results, $order, $ordering);
                $field['data']['options']=array();
                
                $default_option='';
                // enable setting parent form url param
                if (array_key_exists('parent_'.$field['data']['post_type'].'_id',$_GET))
                    $default_option=$_GET['parent_'.$field['data']['post_type'].'_id'];
                
                $required=(bool)(strtoupper($required)==='TRUE');
                if (!$required)
                {
                    $field['data']['options']['-1']=array(
                        'title' => $no_parent_text,
                        'value' => '-1',
                        'display_value' => '-1'
                    );
                }
                else
                {
                    $field['data']['options']['-1']=array(
                        'title' => $select_text,
                        'value' => '',
                        'display_value' => '',
                        'dummy'=>true
                    );
                    $field['data']['validate']=array(
                        'required'=>array('message'=>$validate_text,'active'=>1)
                    );
                }
                foreach ($potential_parents as $ii=>$option)
                {
                    $option_id=(string)($option->ID);
                    $field['data']['options'][$option_id]=array(
                        'title' => $option->post_title,
                        'value' => $option_id,
                        'display_value' => $option_id
                    );
                }
                $field['data']['options']['default'] = $default_option;
                $ids=$formHelper->translate_field($name,$field,array('preset_value'=>$value, 'urlparam'=>$urlparam));
                // check which fields are actually used in form
                $this->out_['form_fields'][$name_orig]=$ids;
                $this->out_['form_fields_info'][$name_orig]=array(
                    'type'=>$field['type'],
                    'repetitive'=>(isset($field['data']['repetitive'])&&$field['data']['repetitive']),
                    'plugin_type'=>(isset($field['plugin_type']))?$field['plugin_type']:'',
                    'name'=>$name
                );
                $out='';
                foreach ($ids as $id)
                    $out.= "[render_cred_field field='{$id}']";
                return $out;
            }
            elseif (in_array($field, array_keys($this->out_['fields']['form_fields'])))
            {
                $name=$name_orig=$field;
                $field=$this->out_['fields']['form_fields'][$field];
                $ids=$formHelper->translate_field($name, $field, array('preset_value'=>$value));
                // check which fields are actually used in form
                $this->out_['form_fields'][$name_orig]=$ids;
                $this->out_['form_fields_info'][$name_orig]=array(
                    'type'=>$field['type'],
                    'repetitive'=>(isset($field['data']['repetitive'])&&$field['data']['repetitive']),
                    'plugin_type'=>(isset($field['plugin_type']))?$field['plugin_type']:'',
                    'name'=>$name
                );
                $out='';
                foreach ($ids as $id)
                    $out.= "[render_cred_field field='{$id}']";
                return $out;
            }
            elseif (in_array($field, array_keys($this->out_['fields']['extra_fields'])))
            {
                $field=$this->out_['fields']['extra_fields'][$field];
                $name=$name_orig=$field['slug'];
                $ids=$formHelper->translate_field($name, $field, array('preset_value'=>$value));
                // check which fields are actually used in form
                $this->out_['form_fields'][$name_orig]=$ids;
                $this->out_['form_fields_info'][$name_orig]=array(
                    'type'=>$field['type'],
                    'repetitive'=>(isset($field['data']['repetitive'])&&$field['data']['repetitive']),
                    'plugin_type'=>(isset($field['plugin_type']))?$field['plugin_type']:'',
                    'name'=>$name
                );
                $out='';
                foreach ($ids as $id)
                    $out.= "[render_cred_field field='{$id}']";
                return $out;
            }
            // taxonomy field
            elseif (in_array($field, array_keys($this->out_['fields']['taxonomies'])))
            {
                $field=$this->out_['fields']['taxonomies'][$field];
                $name=$name_orig=$field['name'];
                $single_select=($single_select==='true');
                $ids=$formHelper->translate_field($name, $field, array('preset_value'=>$display,'is_tax'=>true,'single_select'=>$single_select));
                // check which fields are actually used in form
                $this->out_['form_fields'][$name_orig]=$ids;
                $this->out_['form_fields_info'][$name_orig]=array(
                    'type'=>$field['type'],
                    'repetitive'=>(isset($field['data']['repetitive'])&&$field['data']['repetitive']),
                    'plugin_type'=>(isset($field['plugin_type']))?$field['plugin_type']:'',
                    'name'=>$name
                );
                $out='';
                foreach ($ids as $id)
                    $out.= "[render_cred_field field='{$id}']";
                return $out;
            }
        }
        else
        {
            if (in_array($taxonomy, array_keys($this->out_['fields']['taxonomies'])) && in_array($type,array('show_popular','add_new')))
            {
                if ( // auxilliary field type matches taxonomy type
                    ($type=='show_popular' && !$this->out_['fields']['taxonomies'][$taxonomy]['hierarchical']) ||
                    ($type=='add_new' && $this->out_['fields']['taxonomies'][$taxonomy]['hierarchical'])
                )
                {
                    $field=array(
                        'taxonomy'=>$this->out_['fields']['taxonomies'][$taxonomy],
                        'type'=>$type,
                        'master_taxonomy'=>$taxonomy
                    );
                    $name=$name_orig=$taxonomy.'_'.$type;
                    $ids=$formHelper->translate_field($name, $field, array('preset_value'=>$value,'is_tax'=>true));
                    // check which fields are actually used in form
                    //$this->_form_fields[$name_orig]=$ids;
                    $out='';
                    foreach ($ids as $id)
                        $out.= "[render_cred_field field='{$id}']";
                    return $out;
                }
            }
        }
        return '';
    }
        
/**
 * CRED-Shortcode: cred_parent
 *
 * Description: Render data relating to pre-selected parent of the post the form will manipulate
 *
 * Parameters:
 * 'post_type' => [optional] Define a specifc parent type
 * 'get' => Which information to render (title, url) 
 *
 * Example usage:
 *
 *  
 * [cred_parent get="url"]
 *
 * Link:
 *
 *
 * Note:
 *  'post_type'> necessary if there are multiple parent types
 *
 **/
    public function cred_parent($atts)
    {
        extract( shortcode_atts( array(
            'post_type'=>null,
            'get'=>'title'
        ), $atts ) );
        
        $parent_id=null;
        if ($post_type)
        {
            if (isset($this->out_['fields']['parents']['_wpcf_belongs_'.$post_type.'_id']) && isset($_GET['parent_'.$post_type.'_id']))
            {
                $parent_id=intval($_GET['parent_'.$post_type.'_id']);
            }
        }
        else
        {
            foreach ($this->out_['fields']['parents'] as $parentdata)
            {
                if (isset($_GET['parent_'.$parentdata['data']['post_type'].'_id']))
                {
                    $parent_id=intval($_GET['parent_'.$parentdata['data']['post_type'].'_id']);
                    break;
                }
            }
        }
        
        if ($parent_id!==null)
        {
            switch($get)
            {
                case 'title':
                    return get_the_title($parent_id);
                case 'url':
                    return get_permalink($parent_id);
                default:
                    return '';
            }
        }
         return '';
    }
    
    // parse final shortcodes (internal) which render the actual html fields [render_cred_field]
    public function render_cred_shortcodes($atts, $content='')
    {
        extract( shortcode_atts( array(
            'post' => '',
            'field' => '',
        ), $atts ) );
        $out_=&$this->out_;
        //$sync = false;
        $sync = apply_filters('glue_check_sync', false, $this->_zebraForm->controls[$field]->prime_name);      
        
        if (isset($out_['controls'][$field]) && !$sync)            
            return $out_['controls'][$field];
        return '';
    }
        
    // render the whole form (called from Zebra_Form)
    public function render_callback($controls, &$objs)
    {
        $out_=&$this->out_;
        $shortcodeParser=$this->_shortcodeParser;
        $out_['controls']=$controls;
        // render shortcodes, _form_content is being continuously replaced recursively
        $this->_form_content=$shortcodeParser->do_shortcode($this->_form_content);
        return $this->_form_content;
    }
    
    /*
    *   Implement Friendable Interface
    *
    */
    private function friendHash($obj)
    {
        // use __toString, to return friend token
        return sprintf('%s', $obj.'');
    }
    
    private static function friendHashStatic($class)
    {
        return sprintf('%s', (string)$class.'');
    }
    
    private function addFriend($fr, array $shared=array())
    {
        if (!is_array($this->_____friends_____))
            $this->_____friends_____=array();
            
        $hash=$this->friendHash($fr);
        $this->_____friends_____[$hash]=array_merge(
            array(
                'methods'=>array(), 
                'properties'=>array()
            ),
            (array)$shared
        );
    }
    
    private static function addFriendStatic($fr, array $shared=array())
    {
        if (!is_array(self::$_____friendsStatic_____))
            self::$_____friendsStatic_____=array();
            
        $hash=self::friendHashStatic($fr);
        self::$_____friendsStatic_____[$hash]=array_merge(
            array(
                'methods'=>array(), 
                'properties'=>array()
            ),
            (array)$shared
        );
    }
    
    private function sayByeToFriend($fr)
    {
        $hash=$this->friendHash($fr);
        if (isset($this->_____friends_____[$hash]))
            unset($this->_____friends_____[$hash]);
    }
    
    private static function sayByeToFriendStatic($fr)
    {
        $hash=self::friendHashStatic($fr);
        if (isset(self::$_____friendsStatic_____[$hash]))
            unset(self::$_____friendsStatic_____[$hash]);
    }
    
    private function parseFriendCall($the)
    {
        $what=explode('_1_1_1_', $the);
        if (isset($what[0]) && isset($what[1]))
        {
            $hash=$what[0];
            $whatExactly=$what[1];
            $ref=false;
            if($whatExactly && '&'==$whatExactly[0])
            {
                $ref=true;
                $whatExactly=substr( $whatExactly, 1 );
            }
            return array($hash, $whatExactly, $ref);
        }
        return array(false, false, false);
    }
    
    private static function parseFriendCallStatic($the)
    {
        $what=explode('_1_1_1_', $the);
        if (isset($what[0]) && isset($what[1]))
        {
            $hash=$what[0];
            $whatExactly=$what[1];
            $ref=false;
            if($whatExactly && '&'==$whatExactly[0])
            {
                $ref=true;
                $whatExactly=substr( $whatExactly, 1 );
            }
            return array($hash, $whatExactly, $ref);
        }
        return array(false, false, false);
    }
    
    // use these "magic" methods to share with friends
    public function _call_($method)
    {
        list($hash, $method)=$this->parseFriendCall($method);
        if($method && method_exists($this, $method)) 
        {
            if ($hash && isset($this->_____friends_____[$hash]))
            {
                if (isset($this->_____friends_____[$hash]['methods']) && in_array($method, $this->_____friends_____[$hash]['methods']))
                {
                    $args=array_slice(func_get_args(), 1);
                    return call_user_func_array(array(&$this, $method), $args);
                }
            }
        }
        trigger_error("Not available method '$method'", E_USER_WARNING);
        return null;
    }
    
    // use these "magic" methods to share with friends
    public static function _callStatic_($method)
    {
        list($hash, $method)=self::parseFriendCallStatic($method);
        if($method && method_exists(__CLASS__, $method)) 
        {
            if ($hash && isset(self::$_____friendsStatic_____[$hash]))
            {
                if (isset(self::$_____friendsStatic_____[$hash]['methods']) && in_array($method, self::$_____friendsStatic_____[$hash]['methods']))
                {
                    $args = array_slice(func_get_args(), 1);
                    return call_user_func_array(array(__CLASS__, $method), $args);
                }
            }
        }
        trigger_error("Not available static method '$method'", E_USER_WARNING);
        return null;
    }
    
    // use these "magic" methods to share with friends
    public function _set_($prop, $val)
    {
        list($hash, $prop)=$this->parseFriendCall($prop);
        if($prop && property_exists($this, $prop)) 
        {
            if ($hash && isset($this->_____friends_____[$hash]))
            {
                if (isset($this->_____friends_____[$hash]['properties']) && in_array($prop, $this->_____friends_____[$hash]['properties']))
                {
                    return ($this->{$prop}=$val);
                }
            }
        }        
        trigger_error("Not available property '$prop'", E_USER_WARNING);
        return null;
    }
    
    // use these "magic" methods to share with friends
    public static function _setStatic_($prop, $val)
    {
        list($hash, $prop)=self::parseFriendCallStatic($prop);
        if($prop && property_exists(__CLASS__, $prop)) 
        {
            if ($hash && isset(self::$_____friendsStatic_____[$hash]))
            {
                if (isset(self::$_____friendsStatic_____[$hash]['properties']) && in_array($prop, self::$_____friendsStatic_____[$hash]['properties']))
                {
                    // PHP > 5.1
                    //$reflection = new ReflectionClass(self::$_______class_______);
                    //return $reflection->setStaticPropertyValue($prop);
                    // http://stackoverflow.com/questions/1279081/getting-static-property-from-a-class-with-dynamic-class-name-in-php
                    /* Since I cannot trust the value of $val
                    * I am putting it in single quotes (I don't
                    * want its value to be evaled. Now it will
                    * just be parsed as a variable reference).
                    */
                    try{
                        eval(__CLASS__ . '::$'.$prop.'=$val;');
                    } catch (Exception $e){return false;}
                    return true;                  
                }
            }
        }        
        trigger_error("Not available static property '$prop'", E_USER_WARNING);
        return null;
    }
    
    // use these "magic" methods to share with friends
    // http://stackoverflow.com/questions/4527175/working-with-get-by-reference
    // http://stackoverflow.com/questions/4310473/using-set-with-arrays-solved-but-why
    // http://stackoverflow.com/questions/3479036/emulate-public-private-properties-with-get-and-set
    // http://php.net/manual/en/class.arrayaccess.php
    // http://www.php.net/manual/en/language.references.return.php
    // http://stackoverflow.com/questions/5966918/return-null-by-reference-via-get
    public function &_get_($prop)
    {
        list($hash, $prop, $ref)=$this->parseFriendCall($prop);
        $null=null;
        if($prop && property_exists($this, $prop)) 
        {
            if ($hash && isset($this->_____friends_____[$hash]))
            {
                if (isset($this->_____friends_____[$hash]['properties']) && in_array($prop, $this->_____friends_____[$hash]['properties']))
                {
                    if ($ref)
                        $v=&$this->__getPrivRef($prop);
                    else
                        $v=$this->__getPriv($prop);
                    return $v;
                }
            }
        }        
        trigger_error("Not available property '$prop'", E_USER_WARNING);
        return $null;
    }
    
    // use these "magic" methods to share with friends
    public static function &_getStatic_($prop)
    {
        list($hash, $prop, $ref)=self::parseFriendCallStatic($prop);
        $null=null;
        if($prop && property_exists(__CLASS__, $prop)) 
        {
            if ($hash && isset(self::$_____friendsStatic_____[$hash]))
            {
                if (isset(self::$_____friendsStatic_____[$hash]['properties']) && in_array($prop, self::$_____friendsStatic_____[$hash]['properties']))
                {
                    //$_staticVars = get_class_vars(self::$_______class_______);
                    //return $_staticVars[$prop];
                    // PHP > 5.1
                    //$reflection = new ReflectionClass(self::$_______class_______);
                    //return $reflection->getStaticPropertyValue($prop);
                    if ($ref)
                        $v=&self::__getPrivStaticRef($prop);
                    else
                        $v=self::__getPrivStatic($prop);
                    return $v;
                }
            }
        }        
        trigger_error("Not available static property '$prop'", E_USER_WARNING);
        return $null;
    }
    
    // actual get methods
    private function __getPriv($prop)
    {
        return $this->{$prop};
    }
    private function &__getPrivRef($prop)
    {
        return $this->{$prop};
    }
    private static function __getPrivStatic($prop)
    {
        return self::$$prop;
    }
    private static function &__getPrivStaticRef($prop)
    {
        return self::$$prop;
    }
    /*
    *   /END Implement Friendable Interface
    *
    */
}
