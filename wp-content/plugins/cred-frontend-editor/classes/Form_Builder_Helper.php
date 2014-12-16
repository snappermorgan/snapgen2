<?php
/**
 * Form Builder Helper Class
 * Friend Classes (quasi-)Pattern
 */
class CRED_Form_Builder_Helper implements CRED_Friendly, CRED_FriendlyStatic
{
    // CONSTANTS
    const METHOD='POST';                                         // form method POST
    const PREFIX='_cred_cred_prefix_';                           // prefix for various hidden auxiliary fields
    const MSG_PREFIX='Message_';                                 // Message prefix for WPML localization
    const NONCE='_cred_cred_wpnonce';                            // nonce field name
    const POST_CONTENT_TAG='%__CRED__CRED__POST__CONTENT__%';    // placeholder for post content
    const FORM_TAG='%__CRED__CRED__FORM___FORM__%';              // 
    const DELAY=0;                                               // seconds delay before redirection

    // PRIVATE INSTANCE
    // form builder instance associated with current helper (quasi-Dependency Injection)
    private $_formBuilder =null;
    // for delayed redirection, if needed
    private $_uri_='';
    private $_delay_=0;
       
       
    /*
    *   Implement Friendly Interface
    *
    */
    private $____friend_token____=null;
    //private static $_______class_______='CRED_Form_Builder_Helper';
    /*
    *   /END Implement Friendly Interface
    *
    */
    
    /*=============================== STATIC METHODS ========================================*/

    public static function getCurrentUserData()
    {
        global $current_user;
        
        get_currentuserinfo();

        $user_data=new stdClass;
        
        $user_data->ID=isset($current_user->ID)?$current_user->ID:0;
        $user_data->roles=isset($current_user->roles)?$current_user->roles:array();
        $user_data->role=isset($current_user->roles[0])?$current_user->roles[0]:'';
        $user_data->login=isset($current_user->data->user_login)?$current_user->data->user_login:'';
        $user_data->display_name=isset($current_user->data->display_name)?$current_user->data->display_name:'';
     
        //print_r($user_data);
        return $user_data;
    }
    
    // load frontend assets on init
    public static function loadFrontendAssets() 
    {
        if (!is_admin())
        {
            // register assets and assign them to footer
            if (defined('CRED_DEV')&&CRED_DEV)
            {
                wp_register_script( 'cred_myzebra_parser', CRED_PLUGIN_URL.'/third-party/zebra_form/public/javascript/myzebra_parser.js',
                    array(), CRED_FE_VERSION, 1);
                wp_register_script( 'cred_myzebra_form', CRED_PLUGIN_URL.'/third-party/zebra_form/public/javascript/myzebra_form.js',
                    array('jquery','suggest','thickbox', 'cred_myzebra_parser'), CRED_FE_VERSION, 1);
            }
            else
            {
                wp_register_script( 'cred_myzebra_form', CRED_PLUGIN_URL.'/third-party/zebra_form/public/javascript/myzebra_form.min.js',
                    array('jquery','suggest','thickbox'), CRED_FE_VERSION, 1);
            }
            wp_register_script( 're_captcha_ajax', 'http://www.google.com/recaptcha/api/js/recaptcha_ajax.js',
                array('cred_myzebra_form'), CRED_FE_VERSION, 1);
            
            wp_enqueue_script('cred_myzebra_form');
        }
    }
     

    // unload frontend assets if no form rendered on page
    public static function unloadFrontendAssets() 
    {
        if (!is_admin())
        {
            // get ref here
            $globals=&self::friendGetStatic('CRED_Form_Builder', '&_staticGlobal');
            
            //wp_deregister_script('jquery');
            // unload them when not needed
            if (0>=$globals['COUNT'])
            {
                wp_dequeue_script('cred_myzebra_form');
                wp_deregister_script('cred_myzebra_form');
            }
            else
            {
                // load css first 
                wp_enqueue_style('thickbox');
                foreach ($globals['CACHE'] as $form_data)
                {
                    // if this css is not already loaded, load it
                    if (isset($form_data['css_to_use']) && !in_array($form_data['css_to_use'], $globals['CSS_LOADED']))
                    {            
                        $globals['CSS_LOADED'][]=$form_data['css_to_use'];
                        wp_enqueue_style('cred_form_custom_css_'.$form_data['count'], $form_data['css_to_use'], null, CRED_FE_VERSION);
                        wp_print_styles('cred_form_custom_css_'.$form_data['count']);
                    }
                }
                
                // include client side assets (just in time)
                $myzebra_js_settings=array(
                        'add_new_repeatable_field' =>  $globals['LOCALES']['add_new_repeatable_field'],
                        'remove_repeatable_field'   =>  $globals['LOCALES']['remove_repeatable_field'],
                        'cancel_upload_text' => $globals['LOCALES']['cancel_upload_text'],
                        'days' => $globals['LOCALES']['days'],
                        'months' => $globals['LOCALES']['months'],
                        'insertMediaIconURL' => admin_url().'/images/media-button.png',
                        'insertMediaPopupURL' => admin_url().'/media-upload.php',
                        'PREFIX'=>self::PREFIX,
                        'mimes'=>$globals['MIMES'],
                        'parser_info'=>array('user'=>$globals['CURRENT_USER'])
                );
                
                
                // check jquery dependency
                $doing_jquery = wp_script_is('jquery', 'registered');
                if (!$doing_jquery)
                    wp_enqueue_script('jquery', admin_url().'/wp-includes/js/jquery/jquery.js', null, CRED_FE_VERSION, 1);
                
                wp_localize_script('cred_myzebra_form', 'myzebra', $myzebra_js_settings );
                wp_print_scripts('cred_myzebra_form');
                /*if (defined('CRED_DEV')&&CRED_DEV)
                    wp_print_scripts('cred_myzebra_parser');*/
                
                // add additional only if it is rendered
                foreach ($globals['CACHE'] as $form_data)
                {
                    if (!$globals['RECAPTCHA_LOADED'] && isset($form_data['has_recaptcha']) && $form_data['has_recaptcha'])
                    {
                        wp_print_scripts('re_captcha_ajax');
                        $globals['RECAPTCHA_LOADED']=true;
                    }
                    
                    // echo specific inline javascript for each form
                    if (isset($form_data['js']))
                        echo $form_data['js'];
                    
                    if (isset($form_data['extra']))
                    {
                        if (isset($form_data['extra']->css) && !empty($form_data['extra']->css))
                        {
                            echo "\n<style type='text/css'>\n";
                            echo $form_data['extra']->css."\n";
                            echo "</style>\n";
                        }
                        if (isset($form_data['extra']->js) && !empty($form_data['extra']->js))
                        {
                            echo "\n<script type='text/javascript'>\n";
                            echo $form_data['extra']->js."\n";
                            echo "</script>\n";
                        }
                    }
                }
                
                // echo specific inline javascript for each form
                /*foreach ($globals['CACHE'] as $form_data)
                    if (isset($form_data['js']))
                        echo $form_data['js'];*/
            }
        }
    }
    
    // initialize some vars that are used by all instances
    public static function initVars()
    {
        static $setts=null;
        static $user_setts=null;
        
        // get ref here
        $globals=&self::friendGetStatic('CRED_Form_Builder', '&_staticGlobal');
        if (null===$setts)
        {
            $setts=true;
            
            $globals['LOCALES']=array(
                'clear_date'    => __('Clear','wp-cred'),
                'csrf_detected' => __('There was a problem with your submission!<br>Possible causes may be that the submission has taken too long, or it represents a duplicate request.<br>Please try again.','wp-cred'),
                'days'          => array(__('Sunday','wp-cred'),__('Monday','wp-cred'),__('Tuesday','wp-cred'),__('Wednesday','wp-cred'),__('Thursday','wp-cred'),__('Friday','wp-cred'),__('Saturday','wp-cred')),
                'months'        => array(__('January','wp-cred'),__('February','wp-cred'),__('March','wp-cred'),__('April','wp-cred'),__('May','wp-cred'),__('June','wp-cred'),__('July','wp-cred'),__('August','wp-cred'),__('September','wp-cred'),__('October','wp-cred'),__('November','wp-cred'),__('December','wp-cred')),
                'other'         => __('Other...','wp-cred'),
                'select'        => __('- select -','wp-cred'),
                'add_new_repeatable_field' =>  __('Add Another','wp-cred'),
                'remove_repeatable_field'   =>  __('Remove','wp-cred'),
                'cancel_upload_text' => __('Retry Upload','wp-cred'),
                'spam_detected' => __('Possible spam attempt detected. The posted form data was rejected.','wp-cred'),
                '_days' => array('Sunday'=>__('Sunday','wp-cred'),'Monday'=>__('Monday','wp-cred'),'Tuesday'=>__('Tuesday','wp-cred'),'Wednesday'=>__('Wednesday','wp-cred'),'Thursday'=>__('Thursday','wp-cred'),'Friday'=>__('Friday','wp-cred'),'Saturday'=>__('Saturday','wp-cred')),
                '_months' => array('January'=>__('January','wp-cred'),'February'=>__('February','wp-cred'),'March'=>__('March','wp-cred'),'April'=>__('April','wp-cred'),'May'=>__('May','wp-cred'),'June'=>__('June','wp-cred'),'July'=>__('July','wp-cred'),'August'=>__('August','wp-cred'),'September'=>__('September','wp-cred'),'October'=>__('October','wp-cred'),'November'=>__('November','wp-cred'),'December'=>__('December','wp-cred'))     
            );
            $globals['ASSETS_PATH']=CRED_PLUGIN_PATH.DIRECTORY_SEPARATOR.'third-party'.DIRECTORY_SEPARATOR.'zebra_form'.DIRECTORY_SEPARATOR;
            $globals['ASSETS_URL']=plugins_url().'/'.CRED_PLUGIN_FOLDER.'/third-party/zebra_form/';
        }
        if (null===$user_setts)
        {
            $user_setts=true;
            
            $globals['CURRENT_USER']=self::getCurrentUserData();
        }
    }
    
    public static function makeCommentsClosed($open, $post_id)
    {
        return false;
    }
        
    public static function noComments($comments,$post_id)
    {
        return array();
    }

    public static function hideComments()
    {
        global $post, $wp_query;
        // hide comments
        if (isset($post))
        {
            //global $_wp_post_type_features;
            remove_post_type_support($post->post_type,'comments');
            remove_post_type_support($post->post_type,'trackbacks');
            $post->comment_status="closed";
            $post->ping_status="closed";
            $post->comment_count=0;
            $wp_query->comment_count=0;
            $wp_query->comments=array();
            add_filter('comments_open', array('CRED_Form_Builder_Helper', 'makeCommentsClosed'), 1000, 2);
            add_filter('pings_open', array('CRED_Form_Builder_Helper', 'makeCommentsClosed'), 1000, 2);
            add_filter('comments_array', array('CRED_Form_Builder_Helper', 'noComments'), 1000, 2);
            // as a last resort, use the template hook
            //add_filter('comments_template', STYLESHEETPATH . $file );
        }
    }
    
    /*=============================== INSTANCE METHODS ========================================*/

    public function __construct($formBuilder)
    {
        $this->_formBuilder=$formBuilder;
        $this->makeFriendToken();
    }
    
    // get current url under which this is executed
    public function currentURI($replace_get=array(), $remove_get=array()) 
    {
        $request_uri=$_SERVER["REQUEST_URI"];
        if (!empty($replace_get))
        {
            $request_uri=explode('?',$request_uri,2);
            $request_uri=$request_uri[0];
            
            parse_str($_SERVER['QUERY_STRING'], $get_params);
            if (empty($get_params)) $get_params=array();
            
            foreach ($replace_get as $key=>$value)
            {
                $get_params[$key]=$value;
            }
            if (!empty($remove_get))
            {
                foreach ($get_params as $key=>$value)
                {
                    if (isset($remove_get[$key]))
                        unset($get_params[$key]);
                }
            }
            if (!empty($get_params))
                $request_uri.='?'.http_build_query($get_params, '', '&');
        }
        return $request_uri;
    }
    
    public function getLocalisedPermalink($id, $type=null)
    {
        static $_cache=array();
        
        if (!isset($_cache[$id]))
        {    
            /*
                WPML localised ID
            function icl_object_id($element_id, $element_type='post',
                    $return_original_if_missing=false, $ulanguage_code=null)
            */
            if (function_exists('icl_object_id'))
            {
                if (null===$type) $type=get_post_type($id);
                $loc_id=icl_object_id($id, $type, true);
            }
            else
            {
                $loc_id=$id;
            }
            $_cache[$id]=get_permalink($loc_id);
        }
        return $_cache[$id];
    }
    
    public function checkFormAccess($form_type, $form_id, $post=false)
    {
        global $current_user;
        get_currentuserinfo();
        
        switch ($form_type)
        {
            case 'edit':
                if (!$post) return false;
                if (!current_user_can('edit_own_posts_with_cred_'.$form_id) && $current_user->ID == $post->post->post_author)
                    return false;
                
                if (!current_user_can('edit_other_posts_with_cred_'.$form_id) && $current_user->ID != $post->post->post_author)
                    return false;
                break;
            case 'translation':
                $return = false;
                return apply_filters('cred_wpml_glue_check_user_privileges',$return);            
                break;    
            case 'new':
                if (!current_user_can('create_posts_with_cred_'.$form_id))
                    return false;
                break;
            default:
                return false;
                break;
        }
        return true;
    }
    
    public function error($msg='')
    {
        return new WP_Error($msg);
    }
    
    public function isError($obj)
    {
        return is_wp_error($obj);
    }
    
    public function getError($obj)
    {
        if (is_wp_error($obj))
            return $obj->get_error_message($obj->get_error_code());
        return '';
    }
    
    public function getAllowedExtensions()
    {
        static $extensions=null;
        
        if (null==$extensions)
        {
            $extensions=array();
            $wp_mimes=get_allowed_mime_types(); // calls the upload_mimes filter itself, wp-includes/functions.php
            foreach ($wp_mimes as $exts=>$mime)
            {
                $exts_a=explode('|',$exts);
                foreach ($exts_a as $single_ext)
                {
                    $extensions[]=$single_ext;
                }
            }
            $extensions=implode(',',$extensions);
            unset($wp_mimes);
        }
        return $extensions;
    }
    
    public function getAllowedMimeTypes()
    {
        static $mimes=null;
        
        if (null==$mimes)
        {
            $mimes=array();
            $wp_mimes=get_allowed_mime_types();
            foreach ($wp_mimes as $exts=>$mime)
            {
                $exts_a=explode('|',$exts);
                foreach ($exts_a as $single_ext)
                {
                    $mimes[$single_ext]=$mime;
                }
            }
            //$mimes=implode(',',$mimes);
            unset($wp_mimes);
        }
        return $mimes;
    }
    
    public function getPostData($post_id)
    {
        if ($post_id)
        {
            $fm = CRED_Loader::get('MODEL/Forms');
            $data = $fm->getPost($post_id);
            if ($data && isset($data[0]))
            {
                $mypost=$data[0];
                $myfields=isset($data[1])?$data[1]:array();
                $mytaxs=isset($data[2])?$data[2]:array();
                $myextra=isset($data[3])?$data[3]:array();
                if (isset($mypost->post_title))
                    $myfields['post_title']=array($mypost->post_title);
                if (isset($mypost->post_content))
                    $myfields['post_content']=array($mypost->post_content);
                if (isset($mypost->post_excerpt))
                    $myfields['post_excerpt']=array($mypost->post_excerpt);
                
                return (object) array(
                    'post'=>$mypost,
                    'fields'=>$myfields,
                    'taxonomies'=>$mytaxs,
                    'extra'=>$myextra
                );
            }
            return $this->error(__('Post does not exist', 'wp-cred'));
        }
        return null;
    }
    
    public function getFieldSettings($post_type)
    {
        static $fields=null;
        static $_post_type=null;
        
        if (null===$fields || $_post_type!=$post_type)
        {
            $_post_type=$post_type;
            $ffm=CRED_Loader::get('MODEL/Fields');
            $fields= $ffm->getFields($post_type);
            // in CRED 1.1 post_fields and custom_fields are different keys, merge them together to keep consistency
            $fields['_post_fields']=$fields['post_fields'];
            $fields['post_fields']=array_merge($fields['post_fields'], $fields['custom_fields']);
        }
        return $fields;
    }
    
    public function createFormID($id, $count)
    {
        return 'cred_form_'.$id.'_'.$count;
    }
    
    public function createPrgID($id, $count)
    {
        return $id.'_'.$count;
    }
    
    public function redirect($uri, $headers=array())
    {
        if (!headers_sent())
        {
            // additional headers
            if (!empty($headers))
            {
                foreach ($headers as $header)
                    header("$header");
            }
            // redirect
            header("Location: $uri");
            exit();
        }
        else
        {
            echo sprintf("<script type='text/javascript'>document.location='%s';</script>", $uri);
            exit();
        }
    }
    
    public function redirectDelayed($uri, $delay)
    {
        $delay=intval($delay);
        if ($delay<=0)
        {
            $this->redirect($uri);
            return;
        }
        if (!headers_sent())
        {
            $this->_uri_=$uri;
            $this->_delay_=$delay;
            add_action('wp_head', array(&$this, 'doDelayedRedirect'), 1000);
        }
        else
        {
            echo sprintf("<script type='text/javascript'>setTimeout(function(){document.location='%s';},%d);</script>", $uri, $delay*1000);
        }
    }
    
    // hook to add html head meta tag for delayed redirect
    public function doDelayedRedirect()
    {
        echo sprintf("<meta http-equiv='refresh' content='%d;url=%s'>", $this->_delay_, $this->_uri_);
    }
    
    public function displayMessage($form)
    {
        // apply some rich filters
        return CRED_Helper::renderWithBasicFilters(
                    cred_translate(
                        'Display Message: '.$form->form->post_title, 
                        $form->fields['form_settings']->form['action_message'], 
                        'cred-form-'.$form->form->post_title.'-'.$form->form->ID
                    ) 
                );
        
        /*return  do_shortcode(
                    cred_translate(
                        'Display Message: '.$form->form->post_title, 
                        $form->fields['form_settings']->form['action_message'], 
                        'cred-form-'.$form->form->post_title.'-'.$form->form->ID
                    )
                );*/
    }
    
    public function getZebraForm($form_id, $actionUri, $preview=false)
    {
        // load dependencies
        //CRED_Loader::load('THIRDPARTY/MyZebra_Parser');
        CRED_Loader::load('THIRDPARTY/MyZebra_Form');
        
        // instantiate form
        $zebraForm=new MyZebra_Form( $form_id, self::METHOD, $actionUri, '', array() );
        
        if (!$zebraForm)
            return $this->error(__('Zebra Form failed', 'wp-cred'));
        
        if ($preview)
            $zebraForm->preview=true;
        else
            $zebraForm->preview=false;
        
        // form properties
        $zebraForm->doctype('xhtml');            
        // disables client-side validation messages sometimes, so add full settings here
        $zebraForm->client_side_validation(/*true*/ array(
            'scroll_to_error'       =>  true,
            'tips_position'         =>  'left',
            'close_tips'            =>  true,
            'validate_on_the_fly'   =>  true,
            'validate_all'          =>  false,
        ));
        $zebraForm->show_all_error_messages(true);
        // get globals ref
        $globals=&self::friendGetStatic('CRED_Form_Builder', '&_staticGlobal');
        $zebraForm->assets_path($globals['ASSETS_PATH'], $globals['ASSETS_URL']);
        $zebraForm->language($globals['LOCALES']);
        
        return $zebraForm;
    }
    
    public function getRecaptchaSettings($settings)
    {
        if (!$settings)
        {
            $sm=CRED_Loader::get('MODEL/Settings');
            $gen_setts=$sm->getSettings();
            if (
                isset($gen_setts['recaptcha']['public_key']) && 
                isset($gen_setts['recaptcha']['private_key']) && 
                !empty($gen_setts['recaptcha']['public_key']) &&
                !empty($gen_setts['recaptcha']['private_key'])
                )
            $settings=$gen_setts['recaptcha'];
        }
        return $settings;
    }
    
    public function loadForm($formID, $preview=false)
    {
        global $post, $current_user;
        
        // reference to the form submission method
        global ${'_' . self::METHOD};
        $method = & ${'_' . self::METHOD};
       
       // load form data
        $fm=CRED_Loader::get('MODEL/Forms');
        $form= $fm->getForm($formID);
        if (/*false===*/!$form)
        {
            return $this->error(__('Form does not exist!','wp-cred'));
        }
        
        // preview when form is saved only partially
        if ( !isset($form->fields) || !is_array($form->fields) || empty($form->fields) )
        {
            $form->fields=array();
            if ($preview)
            {
                unset($form);
                return $this->error(__('Form preview does not exist. Try saving your form first','wp-cred'));
            }
        }
        
        $form->fields=array_merge(
            array(
                'form_settings'=>(object)array('form'=>array(),'post'=>array()),
                'extra'=>(object)array('css'=>'','js'=>''),
                'notification'=>(object)array('enable'=>0,'notifications'=>array())
            ), 
            $form->fields
        );
        
        if (!isset($form->fields['extra']->css))
            $form->fields['extra']->css='';
        if (!isset($form->fields['extra']->js))
            $form->fields['extra']->js='';
            
        $redirect_delay=isset($form->fields['form_settings']->form['redirect_delay'])?intval($form->fields['form_settings']->form['redirect_delay']):self::DELAY;
        $hide_comments=(isset($form->fields['form_settings']->form['hide_comments'])&&$form->fields['form_settings']->form['hide_comments'])?true:false;
        $form->fields['form_settings']->form['redirect_delay']=$redirect_delay;
        $form->fields['form_settings']->form['hide_comments']=$hide_comments;
        
        $cred_css_themes=array(
            'minimal'=>CRED_PLUGIN_URL.'/third-party/zebra_form/public/css/minimal.css',
            'styled'=>CRED_PLUGIN_URL.'/third-party/zebra_form/public/css/styled.css'
        );
        
        if ($preview)
        {
            if (array_key_exists(self::PREFIX.'form_preview_post_type', $method))
                $form->fields['form_settings']->post['post_type']=stripslashes($method[self::PREFIX.'form_preview_post_type']);
            else
            {
                unset($form);
                return $this->error(__('Preview post type not provided','wp-cred'));
            }
            
            if (array_key_exists(self::PREFIX.'form_preview_form_type',$method))
                $form->fields['form_settings']->form['type']=stripslashes($method[self::PREFIX.'form_preview_form_type']);
            else
            {
                unset($form);
                $this->error=__('Preview form type not provided','wp-cred');
            }
            if (array_key_exists(self::PREFIX.'form_preview_content',$method))
            {
                $form->form->post_content=stripslashes($method[self::PREFIX.'form_preview_content']);
            }
            else
            {
                unset($form);
                return $this->error(__('No preview form content provided','wp-cred'));
            }
            if (array_key_exists(self::PREFIX.'form_css_to_use',$method))
            {
                $css_to_use=trim(stripslashes($method[self::PREFIX.'form_css_to_use']));
                if (in_array($css_to_use, array_keys($cred_css_themes)))
                    $form->fields['form_settings']->form['css_to_use']=$cred_css_themes[$css_to_use];
                else
                    $form->fields['form_settings']->form['css_to_use']=$cred_css_themes['minimal'];
            }
            else
            {
                $form->fields['form_settings']->form['css_to_use']=$cred_css_themes['minimal'];
            }
            if (array_key_exists(self::PREFIX.'extra_css_to_use',$method))
            {
                $form->fields['extra']->css=trim(stripslashes($method[self::PREFIX.'extra_css_to_use']));
            }
            if (array_key_exists(self::PREFIX.'extra_js_to_use',$method))
            {
                $form->fields['extra']->js=trim(stripslashes($method[self::PREFIX.'extra_js_to_use']));
            }
        }
        else
        {
            if (isset($form->fields['form_settings']->form['theme']) && 
                    in_array($form->fields['form_settings']->form['theme'], array_keys($cred_css_themes)))
                $form->fields['form_settings']->form['css_to_use']=$cred_css_themes[$form->fields['form_settings']->form['theme']];
            else
                $form->fields['form_settings']->form['css_to_use']=$cred_css_themes['minimal'];
        }
        
        if (!isset($form->fields['extra']->messages))
        {
            $form->fields['extra']->messages=$fm->getDefaultMessages();
        }
        
        //return it
        return $form;
    }
    
    public function getLocalisedMessage($id)
    {
        static $messages=null;
        static $formData=null;
        if (null==$formData)
        {
            $formData=$this->friendGet($this->_formBuilder, '_formData');
            $messages=$formData->fields['extra']->messages;
        }
        
        $id='cred_message_'.$id;
        return cred_translate(
            self::MSG_PREFIX.$id, 
            $messages[$id], 
            'cred-form-'.$formData->form->post_title.'-'.$formData->form->ID
        );
    }
    
    // extra sanitization methods to be used by form framework
    /*public function esc_js($data) {return esc_js($data);}
    public function esc_attr($data) {return esc_attr($data);}
    public function esc_textarea($data) {return esc_textarea($data);}
    public function esc_html($data) {return esc_html($data);}
    public function esc_url($data) {return esc_url($data);}
    public function esc_url_raw($data) {return esc_url_raw($data);}
    public function esc_sql($data) {return esc_sql($data);}*/
        
    // utility methods
    public function getUserRolesByID( $user_id ) 
    {
        $user = get_userdata( $user_id );
        return empty( $user ) ? array() : $user->roles;
    }
       
    //** Notification Manager takes care of sending notifications now 
    //**
    // translate codes in notification fields of cred form (like %%POST_ID%% to post id etc..)
    /*public function translateNotificationField($field, $data)
    {
        return str_replace(array_keys($data), array_values($data), $field);
    }*/
       
    //** Notification Manager takes care of sending notifications now 
    //**
    // TODO: use a template(user defined) to render notifications for mail
    // render notification data for this form and send them through wp_mail
    /*public function sendNotifications($post_id, $renderedForm)
    {
        $formBuilder=$this->_formBuilder;
        // get ref here
        $form=&$this->friendGet($formBuilder, '&_formData');
        
        $n_failed=array();
        $n_sent=array();
        
        // send notification
        if (
            isset($form->fields['notification']->enable) && 
            $form->fields['notification']->enable &&
            !empty($form->fields['notification']->notifications)
        )
        {
            $globals=&self::friendGetStatic('CRED_Form_Builder', '&_staticGlobal');
            // get Zebra form instance
            $zebraForm=$this->friendGet($formBuilder, '_zebraForm');
            // get Mailer
            $mh=CRED_Loader::get('CLASS/Mail_Handler');
            
            $form_id=$form->form->ID;
            $form_type=$form->fields['form_settings']->form['type'];
            $post_type=$form->fields['form_settings']->post['post_type'];
            $thisform=array(
                'id' => $form_id,
                'post_type' => $post_type,
                'form_type' => $form_type
            );
            
            $notification_data=isset($renderedForm['notification_data'])?$renderedForm['notification_data']:'';
            $parent_link=$formBuilder->cred_parent(array('get'=>'url'));
            $parent_title=$formBuilder->cred_parent(array('get'=>'title'));
            $link=get_permalink( $post_id );
            $title=get_the_title( $post_id );
            $date=date('d/m/Y H:i:s');
            $admin_edit_link=admin_url('post.php').'?action=edit&post='.$post_id;
            $data_all=array(
                '%%USER_LOGIN_NAME%%'=>$globals['CURRENT_USER']->login,
                '%%USER_DISPLAY_NAME%%'=>$globals['CURRENT_USER']->display_name,
                '%%POST_PARENT_TITLE%%'=>$parent_title,
                '%%POST_PARENT_LINK%%'=>$parent_link,
                '%%POST_ID%%'=>$post_id,
                '%%POST_TITLE%%'=>$title,
                '%%POST_LINK%%'=>$link,
                '%%FORM_NAME%%'=>$form->form->post_title,
                //'%%FORM_DATA%%'=>$notification_data,
                '%%DATE_TIME%%'=>$date,
                '%%POST_ADMIN_LINK%%'=>$admin_edit_link
            );
            $data_restricted=array(
                '%%USER_LOGIN_NAME%%'=>$globals['CURRENT_USER']->login,
                '%%USER_DISPLAY_NAME%%'=>$globals['CURRENT_USER']->display_name,
                '%%POST_PARENT_TITLE%%'=>$parent_title,
                '%%POST_ID%%'=>$post_id,
                '%%POST_TITLE%%'=>$title,
                '%%FORM_NAME%%'=>$form->form->post_title,
                '%%DATE_TIME%%'=>$date
            );
            
            // allow to bypass notifications by returning false here in these filters
            $notifications=apply_filters('cred_filter_notification_'.$form_id, $form->fields['notification']->notifications, $post_id, $thisform);
            $notifications=apply_filters('cred_filter_notification', $notifications, $post_id, $thisform);
            if ($notifications && !empty($notifications))
            {
                // send notifications
                foreach ($notifications as $ii=>$_notification)
                {
                    if ($_notification && !empty($_notification) && isset($_notification['to']['type']))
                    {
                        
                        if (isset($_notification['event']['type']) && 'form_submit'!=$_notification['event']['type'])
                            continue;  // not send this notification on form submit
                            
                        // custom actions to integrate 3rd-party (eg CRED Commerce)
                        do_action('cred_before_send_notification_'.$form_id, $ii, $_notification, $post_id, $thisform);
                        do_action('cred_before_send_notification', $ii, $_notification, $post_id, $thisform);
                        
                        // reset mail handler
                        $mh->reset();
                        $mh->setHTML(true);
                        $_addr=false;
                        $_addr_name=false;
                        $_addr_lastname=false;
                        
                        // parse Notification Fields
                        // provide WPML translations for notification fields also
                        if (
                            'mail_field'==$_notification['to']['type'] && 
                            isset($_notification['to']['address_field']) && !empty($_notification['to']['address_field']) &&
                            isset($renderedForm['form_fields'][$_notification['to']['address_field']])
                        )
                        {
                            $mailcontrol=$zebraForm->controls[$renderedForm['form_fields'][$_notification['to']['address_field']][0]];
                            if (isset($mailcontrol->controls)) // repetitive control
                            {
                                // take 1st field
                                $_addr=$mailcontrol->controls[0]->attributes['value'];
                            }
                            else
                            {
                                $_addr=$mailcontrol->attributes['value'];;
                            }
                            if (
                                isset($_notification['to']['name_field']) && !empty($_notification['to']['name_field']) &&
                                isset($renderedForm['form_fields'][$_notification['to']['name_field']]) &&
                                '###none###'!=$_notification['to']['name_field']
                            )
                            {
                                $mailcontrol=$zebraForm->controls[$renderedForm['form_fields'][$_notification['to']['name_field']][0]];
                                if (isset($mailcontrol->controls)) // repetitive control
                                {
                                    // take 1st field
                                    $_addr_name=$mailcontrol->controls[0]->attributes['value'];
                                }
                                else
                                {
                                    $_addr_name=$mailcontrol->attributes['value'];;
                                }
                            }
                            if (
                                isset($_notification['to']['lastname_field']) && !empty($_notification['to']['lastname_field']) &&
                                isset($renderedForm['form_fields'][$_notification['to']['lastname_field']]) &&
                                '###none###'!=$_notification['to']['lastname_field']
                            )
                            {
                                $mailcontrol=$zebraForm->controls[$renderedForm['form_fields'][$_notification['to']['lastname_field']][0]];
                                if (isset($mailcontrol->controls)) // repetitive control
                                {
                                    // take 1st field
                                    $_addr_lastname=$mailcontrol->controls[0]->attributes['value'];
                                }
                                else
                                {
                                    $_addr_lastname=$mailcontrol->attributes['value'];;
                                }
                            }
                        }
                        elseif ('wp_user'==$_notification['to']['type'])
                        {
                            $_addr=cred_translate('CRED Notification '.$ii.' Mail To', $_notification['to']['user'], 'cred-form-'.$form->form->post_title.'-'.$form->form->ID);
                            $user_id = email_exists($_addr);
                            if ($user_id)
                            {
                                $user_info = get_userdata($user_id);
                                $_addr_name = (isset($user_info->user_firstname)&&!empty($user_info->user_firstname))?$user_info->user_firstname:false;
                                $_addr_lastname = (isset($user_info->user_lasttname)&&!empty($user_info->user_lasttname))?$user_info->user_lastname:false;
                            }
                            else
                                $_addr=false;
                        }
                        elseif ('specific_mail'==$_notification['to']['type'] && isset($_notification['to']['address']))
                        {
                            $_addr=cred_translate('CRED Notification '.$ii.' Mail To', $_notification['to']['address'], 'cred-form-'.$form->form->post_title.'-'.$form->form->ID);
                            if (
                                isset($_notification['to']['name'])
                            )
                            {
                                $_addr_name=cred_translate('CRED Notification '.$ii.' Mail To Name', $_notification['to']['name'], 'cred-form-'.$form->form->post_title.'-'.$form->form->ID);
                            }
                            if (
                                isset($_notification['to']['lastname']) 
                            )
                            {
                                $_addr_lastname=cred_translate('CRED Notification '.$ii.' Mail To LastName', $_notification['to']['lastname'], 'cred-form-'.$form->form->post_title.'-'.$form->form->ID);
                            }
                        }
                        else  continue;
                        if (!$_addr)    continue;
                        
                        // build TO address
                        $_to=array();
                        if ($_addr_name)
                            $_to[]=$_addr_name;
                        if ($_addr_lastname)
                            $_to[]=$_addr_lastname;
                        if (!empty($_to))
                            $_to[]='<'.$_addr.'>';
                        else
                            $_to[]=$_addr;
                        $_to=implode(' ', $_to);
                        $mh->addAddress($_to);
                        
                        // build SUBJECT
                        $_subj='';
                        if (isset($_notification['mail']['subject']))
                            $_subj=$_notification['mail']['subject'];
                        $_subj=$this->translateNotificationField(cred_translate('CRED Notification '.$ii.' Subject', $_subj, 'cred-form-'.$form->form->post_title.'-'.$form->form->ID), $data_restricted);
                        $mh->setSubject($_subj);
                        
                        // build BODY
                        $_bod='';
                        if (isset($_notification['mail']['body']))
                            $_bod=$_notification['mail']['body'];
                        // allow shortcodes in body message
                        $_bod=do_shortcode($this->translateNotificationField(cred_translate('CRED Notification '.$ii.' Body', $_bod, 'cred-form-'.$form->form->post_title.'-'.$form->form->ID), $data_all));
                        // add notification data after shortcodes are parsed, to avoid any complications with data having raw shortcodes
                        $_bod=$this->translateNotificationField($_bod, array('%%FORM_DATA%%'=>$notification_data));
                        $mh->setBody($_bod);
                        
                        // build FROM address
                        $_from_addr=isset($_notification['from']['address'])?$_notification['from']['address']:false;
                        if ($_from_addr)
                        {
                            if (isset($_notification['from']['name']))
                            {
                                $mh->setFrom($_from_addr, $_notification['from']['name'], false);
                            }
                            else
                            {
                                $mh->setFrom($_from_addr, '');
                            }
                        }
                        
                        // send it
                        if (($_send_result=$mh->send()))
                        {
                            $zebraForm->add_form_message('notification_'.$ii, $this->getLocalisedMessage('notification_was_sent'));
                            // save them to be used later as messages if PRG
                            $n_sent[]=$ii;
                        }
                        else
                        {
                            $zebraForm->add_form_error('notification_'.$ii, $this->getLocalisedMessage('notification_failed'));
                            // save them to be used later as messages if PRG
                            $n_failed[]=$ii;
                        }
                        
                        // custom actions to integrate 3rd-party (eg CRED Commerce)
                        do_action('cred_after_send_notification_'.$form_id, $_send_result, $ii, $_notification, $post_id, $thisform);
                        do_action('cred_after_send_notification', $_send_result, $ii, $_notification, $post_id, $thisform);
                    }
                }
            }
        }
        return array($n_sent, $n_failed);
    }*/
       
    public function extractPostFields($post_id, $track=false)
    {
        global $user_ID;
        // reference to the form submission method
        global ${'_' . self::METHOD};
        $method = & ${'_' . self::METHOD};
       
        // get refs here
        $form=&$this->friendGet($this->_formBuilder, '&_formData');
        $out_=&$this->friendGet($this->_formBuilder, '&out_');
        $form_id=$form->form->ID;
        $zebraForm=$this->friendGet($this->_formBuilder, '_zebraForm');
        $form_type=$form->fields['form_settings']->form['type'];
        $post_type=$form->fields['form_settings']->post['post_type'];
        $fields=$out_['fields'];
        $form_fields=$out_['form_fields'];
        
        // extract main post fields
        $post=new stdClass;
        // ID
        $post->ID=$post_id;
        // author
        if ('new'==$form_type)
            $post->post_author=$user_ID;
        // title
        if (
            array_key_exists('post_title', $form_fields) && 
            array_key_exists('post_title', $method) &&
            !$zebraForm->controls[$form_fields['post_title'][0]]->isDiscarded()
        )
        {
            $post->post_title=stripslashes($method['post_title']);
            unset($method['post_title']);
        }
        // content
        if (
            array_key_exists('post_content', $form_fields) && 
            array_key_exists('post_content', $method) &&
            !$zebraForm->controls[$form_fields['post_content'][0]]->isDiscarded()
        )
        {
            $post->post_content=stripslashes($method['post_content']);
            unset($method['post_content']);
        }
        // excerpt
        if (
            array_key_exists('post_excerpt', $form_fields) && 
            array_key_exists('post_excerpt', $method) &&
            !$zebraForm->controls[$form_fields['post_excerpt'][0]]->isDiscarded()
        )
        {
            $post->post_excerpt=stripslashes($method['post_excerpt']);
            unset($method['post_excerpt']);
        }
        // parent
        if (
            array_key_exists('post_parent', $form_fields) && 
            array_key_exists('post_parent', $method) &&
            !$zebraForm->controls[$form_fields['post_parent'][0]]->isDiscarded() &&
            isset($fields['parents']) && isset($fields['parents']['post_parent']) &&
            intval($method['post_parent'])>=0
        )
        {
            $post->post_parent=intval($method['post_parent']);
            unset($method['post_parent']);
        }
        // type
        $post->post_type=$post_type;
        // status
        if (
            !isset($form->fields['form_settings']->post['post_status']) ||
            !in_array($form->fields['form_settings']->post['post_status'], array('draft','private','pending','publish','original'))
        )
            $form->fields['form_settings']->post['post_status']='draft';
        
        if (
            isset($form->fields['form_settings']->post['post_status']) && 
            'original'==$form->fields['form_settings']->post['post_status'] && 
            'edit'!=$form_type
        )
            $form->fields['form_settings']->post['post_status']='draft';
        
        if (
            'original'!=$form->fields['form_settings']->post['post_status']
        )
            $post->post_status=(isset($form->fields['form_settings']->post['post_status']))?$form->fields['form_settings']->post['post_status']:'draft';
            
        if ($track)
        {
            // track the data, eg for notifications
            if (isset($post->post_title))
                $this->trackData(array('Post Title'=>$post->post_title));
            if (isset($post->post_content))
                $this->trackData(array('Post Content'=>$post->post_content));
            if (isset($post->post_excerpt))
                $this->trackData(array('Post Excerpt'=>$post->post_excerpt));
        }
        
        // return them
        return $post;
    }
    
    public function extractCustomFields($post_id, $track=false)
    {
        global $user_ID;
        // reference to the form submission method
        global ${'_' . self::METHOD};
        $method = & ${'_' . self::METHOD};
       
        // get refs here
        $globals=&self::friendGetStatic('CRED_Form_Builder', '&_staticGlobal');
        $form=&$this->friendGet($this->_formBuilder, '&_formData');
        $out_=&$this->friendGet($this->_formBuilder, '&out_');
        $form_id=$form->form->ID;
        $form_type=$form->fields['form_settings']->form['type'];
        $post_type=$form->fields['form_settings']->post['post_type'];
        $supported_date_formats=$this->friendGet($this->_formBuilder, '_supportedDateFormats');
        $_fields=$out_['fields'];
        $_form_fields=$out_['form_fields'];
        $_form_fields_info=$out_['form_fields_info'];
        $zebraForm=$this->friendGet($this->_formBuilder, '_zebraForm');
        
        // custom fields
        $fields=array();
        $removed_fields=array();
        // taxonomies
        $taxonomies=array('flat'=>array(),'hierarchical'=>array());
        $fieldsInfo=array();
        // files, require extra care to upload correctly
        $files=array();
        foreach ($_fields['post_fields'] as $key=>$field)
        {
            $field_label=$field['name'];
            $done_data=false;
            
            // use the key as was rendered (with potential prefix)
            $key11=$key;
            if (isset($field['plugin_type_prefix']))
                $key=$field['plugin_type_prefix'].$key;
            
            // if this field was not rendered in this specific form, bypass it
            if (!array_key_exists($key11, $_form_fields)) continue;
            
            // if this field was discarded due to some conditional logic, bypass it
            if ($zebraForm->controls[$_form_fields[$key11][0]]->isDiscarded())  continue;
            
            $fieldsInfo[$key]=array('save_single'=>false);
            
            if (
                ('file'==$field['type'] || 'image'==$field['type'])
            )
            {
                if (
                    !array_key_exists($key, $method)
                )
                {
                    // remove the fields
                    $removed_fields[]=$key;
                    unset($fieldsInfo[$key]);
                }
                else/*if (is_array($method[$key]))*/
                {
                    /*$_hasFile=false;
                    foreach ($method[$key] as $_file_)
                    {
                        if (''!=trim($method[$key]))
                            $_hasFile=true;
                    }
                    // repetitive field and all values are empty, remove
                    if (!$_hasFile)
                    {
                        // remove the fields
                        $removed_fields[]=$key;
                        unset($fieldsInfo[$key]);
                    }*/
                    $fields[$key]=$method[$key];
                }
            }
            if (
                'checkboxes'==$field['type'] && 
                isset($field['data']['save_empty']) && 
                'yes'==$field['data']['save_empty'] && 
                !array_key_exists($key, $method)
            )
            {
                $values=array();
                foreach ($field['data']['options'] as $optionkey=>$optiondata)
                {
                    $values[$optionkey]='0';
                }

                // let model serialize once, fix Types-CRED mapping issue with checkboxes
                $fieldsInfo[$key]['save_single']=true;
                $fields[$key]=$values;
            }
            elseif (
                'checkboxes'==$field['type'] && 
                (!isset($field['data']['save_empty']) || 
                'yes'!=$field['data']['save_empty']) && 
                !array_key_exists($key, $method)
            )
            {
                // remove the fields
                $removed_fields[]=$key;
                unset($fieldsInfo[$key]);
            }
            elseif (
                'checkbox'==$field['type'] && 
                isset($field['data']['save_empty']) && 
                'yes'==$field['data']['save_empty'] && 
                !array_key_exists($key, $method)
            )
            {
                $fields[$key]='0';
            }
            elseif (
                'checkbox'==$field['type'] && 
                (!isset($field['data']['save_empty']) || 
                'yes'!=$field['data']['save_empty']) && 
                !array_key_exists($key, $method)
            )
            {
                // remove the fields
                $removed_fields[]=$key;
                unset($fieldsInfo[$key]);
            }
            elseif (array_key_exists($key, $method))
            {
                // normalize repetitive values out  of sequence
                if ($_form_fields_info[$key11]['repetitive'])
                    $values=array_values($method[$key]);
                else
                    $values=$method[$key];
                    
                if ('file'==$field['type'] || 'image'==$field['type'])
                {
                    $files[$key]=$zebraForm->controls[$_form_fields[$key11][0]]->get_values();
                    //cred_log($files[$key]);
                    $files[$key]['name_orig']=$key11;
                    $files[$key]['label']=$field['name'];
                    $files[$key]['repetitive']=$_form_fields_info[$key11]['repetitive'];
                }
                elseif ('textarea'==$field['type'] || 'wysiwyg'==$field['type'])
                {
                    // stripslashes for textarea, wysiwyg fields
                    if (is_array($values))
                        $values=array_map('stripslashes',$values);
                    else
                        $values=stripslashes($values);
                }
                elseif ('textfield'==$field['type'] || 'text'==$field['type'] || 'date'==$field['type'])
                {
                    // stripslashes for text fields
                    if (is_array($values))
                        $values=array_map('stripslashes',$values);
                    else
                        $values=stripslashes($values);
                }
                
                // track form data for notification mail
                if ($track)
                {
                    $tmp_data=null;
                    if ('checkbox'==$field['type'])
                    {
                        if ('db'==$field['data']['display'])
                            $tmp_data=$values;
                        else
                            $tmp_data=$field['data']['display_value_selected'];
                    }
                    elseif ('radio'==$field['type'] || 'select'==$field['type'])
                    {
                        $tmp_data=$field['data']['options'][$values]['title'];
                    }
                    elseif ('checkboxes'==$field['type'] || 'multiselect'==$field['type'])
                    {
                        $tmp_data=array();
                        foreach ($values as $tmp_val)
                            $tmp_data[]=$field['data']['options'][$tmp_val]['title'];
                        //$tmp_data=implode(', ',$tmp_data);
                        unset($tmp_val);
                    }
                    if (null!==$tmp_data)
                    {
                        $this->trackData(array($field_label=>$tmp_data));
                        $done_data=true;
                    }
                }
                if ('checkboxes'==$field['type'] || 'multiselect'==$field['type'])
                {
                    $result=array();
                    foreach ($field['data']['options'] as $optionkey=>$optiondata)
                    {
                        /*if (
                            isset($field['data']['save_empty']) && 
                            'yes'==$field['data']['save_empty'] && 
                            !in_array($optionkey, $values)
                        )
                            $result[$optionkey]='0';
                        else*/if (in_array($optionkey, $values))
                            $result[$optionkey]=$optiondata['set_value'];
                    }

                    $values=$result;
                    $fieldsInfo[$key]['save_single']=true;
                }
                elseif ('radio'==$field['type'] || 'select'==$field['type'])
                {
                    $values=$field['data']['options'][$values]['value'];
                }
                elseif ('date'==$field['type'])
                {
                    $date_format=null;
                    if (isset($field['data']) && isset($field['data']['validate']))
                        $date_format=$field['data']['validate']['date']['format'];
                    if (!in_array($date_format, $supported_date_formats))
                        $date_format='F j, Y';
                    if (!is_array($values))  
                        $tmp=array($values);
                    else    
                        $tmp=$values;
                    
                    // track form data for notification mail
                    if ($track) 
                    {
                        $this->trackData(array($field_label=>$tmp));
                        $done_data=true;
                    }
                    
                    MyZebra_DateParser::setDateLocaleStrings($globals['LOCALES']['days'], $globals['LOCALES']['months']);
                    foreach ($tmp as $ii=>$val)
                    {
                        $val = MyZebra_DateParser::parseDate($val, $date_format);
                        if (false !== $val)  // succesfull
                            $val=$val->getNormalizedTimestamp();
                        else continue;    
                        
                        $tmp[$ii]=$val;
                    }
                    
                    if (!is_array($values))  
                        $values=$tmp[0];
                    else 
                        $values=$tmp;
                }
                elseif ('skype'==$field['type'])
                {
                    if (
                        array_key_exists('skypename', $values) && 
                        array_key_exists('style', $values)
                    )
                    {
                        $new_values=array();
                        $values['skypename']=(array)$values['skypename'];
                        $values['style']=(array)$values['style'];
                        foreach ($values['skypename'] as $ii=>$val)
                        {
                            $new_values[]=array(
                                'skypename'=>$values['skypename'][$ii],
                                'style'=>$values['style'][$ii]
                            );
                            
                        }
                        $values=$new_values;
                        unset($new_values);
                        if ($track)
                        {
                            $this->trackData(array($field_label=>$values));
                            $done_data=true;
                        }
                    }
                }
                // dont track file/image data now but after we upload them..
                if (
                    $track && !$done_data && 
                    'file'!=$field['type'] && 
                    'image'!=$field['type']
                )
                {
                    $this->trackData(array($field_label=>$values));
                }
                $fields[$key]=$values;
            }
        }
        // custom parents (Types feature)
        foreach ($_fields['parents'] as $key=>$field)
        {
            $field_label=$field['name'];
            
            // overwrite parent setting by url, even though no fields might be set
            if (
                !array_key_exists($key, $_form_fields) && 
                array_key_exists('parent_'.$field['data']['post_type'].'_id',$_GET) && 
                is_numeric($_GET['parent_'.$field['data']['post_type'].'_id'])
            )
            {
                $fieldsInfo[$key]=array('save_single'=>false);
                $fields[$key]=intval($_GET['parent_'.$field['data']['post_type'].'_id']);
                continue;
            }
            // if this field was not rendered in this specific form, bypass it
            if (!array_key_exists($key, $_form_fields)) continue;
            
            // if this field was discarded due to some conditional logic, bypass it
            if ($zebraForm->controls[$_form_fields[$key][0]]->isDiscarded())  continue;
            
            if (
                array_key_exists($key, $method) && 
                intval($method[$key])>=0
            )
            {
                $fieldsInfo[$key]=array('save_single'=>false);
                $fields[$key]=intval($method[$key]);
            }
        }
        // taxonomies
        foreach ($_fields['taxonomies'] as $key=>$field)
        {
            // if this field was not rendered in this specific form, bypass it
            if (!array_key_exists($key, $_form_fields)) continue;
            
            // if this field was discarded due to some conditional logic, bypass it
            if ($zebraForm->controls[$_form_fields[$key][0]]->isDiscarded())  continue;
            
            if (
                array_key_exists($key, $method) || 
                ($field['hierarchical'] && isset($method[$key.'_hierarchy'])) 
            )
            {
                if ($field['hierarchical'] /*&& is_array($method[$key])*/)
                {
                    $values=isset($method[$key])?$method[$key]:array();
                    if (isset($method[$key.'_hierarchy']))
                    {
                        $add_new=array();
                        preg_match_all("/\{([^\{\}]+?),([^\{\}]+?)\}/", $method[$key.'_hierarchy'], $tmp_a_n);
                        for ($ii=0; $ii<count($tmp_a_n[1]); $ii++)
                        {
                            $add_new[]=array(
                                'parent'=>$tmp_a_n[1][$ii],
                                'term'=>$tmp_a_n[2][$ii]
                            );
                        }
                        unset($tmp_a_n);
                    }
                    else
                        $add_new=array();
                    
                    $taxonomies['hierarchical'][]=array(
                        'name'=>$key,
                        'terms'=>$values, 
                        'add_new'=>$add_new
                    );
                    // track form data for notification mail
                    if ($track)
                    {
                        $tmp_data=array();
                        foreach ($field['all'] as $tmp_tax)
                        {
                            if (in_array($tmp_tax['term_taxonomy_id'],$values))
                                $tmp_data[]=$tmp_tax['name'];
                        }
                        // add also new terms created
                        foreach ($values as $val)
                        {
                            if (is_string($val) && !is_numeric($val))
                                $tmp_data[]=$val;
                        }
                        $this->trackData(array($field['label']=>$tmp_data));
                        unset($tmp_data);
                    }
                }
                elseif (!$field['hierarchical'])
                {
                    $values=$method[$key];
                    // find which to add and which to remove
                    //$sanit=create_function('$a', 'return preg_replace("/[\*\(\)\{\}\[\]\+\,\.]|\s+/g","",$a);');
                    preg_match("/^add\{(.*?)\}remove\{(.*?)\}$/i", $values, $matches);
                    // allow white space in tax terms
                    $tax_add=(!empty($matches[1]))?preg_replace("/[\*\(\)\{\}\[\]\+\,\.]|[\f\n\r\t\v\x{00A0}\x{2028}\x{2029}]+/u","",explode(',',$matches[1])):array();
                    $tax_remove=(!empty($matches[2]))?preg_replace("/[\*\(\)\{\}\[\]\+\,\.]|[\f\n\r\t\v\x{00A0}\x{2028}\x{2029}]+/u","",explode(',',$matches[2])):array();
                    $taxonomies['flat'][]=array('name'=>$key,'add'=>$tax_add,'remove'=>$tax_remove);
                    // track form data for notification mail
                    if ($track)
                        $this->trackData(array($field['label']=>array('added'=>$tax_add, 'removed'=>$tax_remove)));
                }
            }
        }
        return array($fields, $fieldsInfo, $taxonomies, $files, $removed_fields);
    }
    
    public function uploadAttachments($post_id, &$fields, &$files, &$extra_files, $track=false)
    {
        // dependencies
        require_once(ABSPATH.'/wp-admin/includes/file.php');
        //CRED_Loader::loadThe('wp_handle_upload');
        
        // get ref here
        $form=&$this->friendGet($this->_formBuilder, '&_formData');
        // get ref here
        $out_=&$this->friendGet($this->_formBuilder, '&out_');
        $_form_fields=$out_['form_fields'];
        $zebraForm=$this->friendGet($this->_formBuilder, '_zebraForm');
        
        // upload data
        $all_ok=true;
        // set featured image only if uploaded
        $fkey='_featured_image';
        $extra_files=array();
        if (
            array_key_exists($fkey, $_form_fields) && 
            array_key_exists($fkey, $_FILES) && 
            isset($_FILES[$fkey]['name']) && 
            ''!=$_FILES[$fkey]['name']
        )
        {
            $upload = wp_handle_upload($_FILES[$fkey], array('test_form'=>false, 'test_upload'=>false));
            if(!isset($upload['error']) && isset($upload['file'])) 
            {
                $extra_files[$fkey]['wp_upload']=$upload;
                if ($track) $tmp_data=$upload['url'];
                $zebraForm->controls[$_form_fields[$fkey][0]]->set_values(array('value'=>''));
            }
            else
            {
                $all_ok=false;
                if ($track) $tmp_data=$this->getLocalisedMessage('upload_failed');
                $fields[$fkey]='';
                $extra_files[$fkey]['upload_fail']=true;
                $zebraForm->controls[$_form_fields[$fkey][0]]->set_values(array('value'=>''));
                $zebraForm->controls[$_form_fields[$fkey][0]]->addError($upload['error']);
            }
            if ($track)
            {
                $this->trackData(array(__('Featured Image')=>$tmp_data));
                unset($tmp_data);
            }
        }
        
        foreach ($files as $fkey=>$fdata)
        {
            if ($fdata['repetitive'])
            {
                if ($track) $tmp_data=array();
                //cred_log($fdata);
                foreach ($fdata as $ii=>$fdata2)
                {
                    if (!isset($fdata2['file_data'][$fkey]) || !is_array($fdata2['file_data'][$fkey])) continue;
                    $file_data=$fdata2['file_data'][$fkey];
                    $upload = wp_handle_upload($file_data, array('test_form' => false,'test_upload'=>false));
                    if(!isset($upload['error']) && isset($upload['file'])) 
                    {
                        $files[$fkey][$ii]['wp_upload']=$upload;
                        $fields[$fkey][$ii]=$upload['url'];
                        if ($track) $tmp_data[]=$upload['url'];
                        $zebraForm->controls[$_form_fields[$files[$fkey]['name_orig']][0]]->set_values(array($ii=>array('value'=>$upload['url'])));
                    }
                    else
                    {
                        $all_ok=false;
                        $files[$fkey]['upload_fail']=true;
                        if ($track) $tmp_data[]=$this->getLocalisedMessage('upload_failed');
                        $fields[$fkey][$ii]='';
                        $files[$fkey][$ii]['upload_fail']=true;
                        $zebraForm->controls[$_form_fields[$files[$fkey]['name_orig']][0]]->set_values(array($ii=>array('value'=>'')));
                        $zebraForm->controls[$_form_fields[$files[$fkey]['name_orig']][0]]->addError(array($ii=>$upload['error']));
                    }
                }
                if ($track)
                {
                    $this->trackData(array($files[$fkey]['label']=>$tmp_data));
                    unset($tmp_data);
                }
            }
            else
            {
                if (!isset($fdata['file_data'][$fkey]) || !is_array($fdata['file_data'][$fkey])) continue;
                
                $file_data=$fdata['file_data'][$fkey];
                $upload = wp_handle_upload($file_data, array('test_form' => false,'test_upload'=>false));
                if(!isset($upload['error']) && isset($upload['file'])) 
                {
                    $files[$fkey]['wp_upload']=$upload;
                    $fields[$fkey]=$upload['url'];
                    if ($track) $tmp_data=$upload['url'];
                    $zebraForm->controls[$_form_fields[$files[$fkey]['name_orig']][0]]->set_values(array('value'=>$upload['url']));
                }
                else
                {
                    $all_ok=false;
                    if ($track) $tmp_data=$this->getLocalisedMessage('upload_failed');
                    $fields[$fkey]='';
                    $files[$fkey]['upload_fail']=true;
                    $zebraForm->controls[$_form_fields[$files[$fkey]['name_orig']][0]]->set_values(array('value'=>''));
                    $zebraForm->controls[$_form_fields[$files[$fkey]['name_orig']][0]]->addError($upload['error']);
                }
                if ($track)
                {
                    $this->trackData(array($files[$fkey]['label']=>$tmp_data));
                    unset($tmp_data);
                }
            }
        }
        return $all_ok;
    }
    
    public function attachUploads($result, &$fields, &$files, &$extra_files)
    {
        // you must first include the image.php file
        // for the function wp_generate_attachment_metadata() to work
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        //CRED_Loader::loadThe('wp_generate_attachment_metadata');
        
        // get ref here
        $form=&$this->friendGet($this->_formBuilder, '&_formData');
        // get ref here
        $out_=&$this->friendGet($this->_formBuilder, '&out_');
        $_form_fields=$out_['form_fields'];
        $zebraForm=$this->friendGet($this->_formBuilder, '_zebraForm');
        
        foreach ($files as $fkey=>$fdata)
        {
            if ($files[$fkey]['repetitive'])
            {
                //cred_log($fdata);
                foreach ($fdata as $ii=>$fdata2)
                {
                    if (!isset($fdata2['file_data'][$fkey]) || !is_array($fdata2['file_data'][$fkey])) continue;
                    
                    if (!isset($files[$fkey][$ii]['upload_fail']) || !$files[$fkey][$ii]['upload_fail'])
                    {
                        $filetype   = wp_check_filetype(basename($files[$fkey][$ii]['wp_upload']['file']), null);
                        $title      = $files[$fkey][$ii]['file_data'][$fkey]['name'];
                        $ext        = strrchr($title, '.');
                        $title      = ($ext !== false) ? substr($title, 0, -strlen($ext)) : $title;
                        $attachment = array(
                            'post_mime_type'    => $filetype['type'],
                            'post_title'        => addslashes($title),
                            'post_content'      => '',
                            'post_status'       => 'inherit',
                            'post_parent'       => $result,
                            'post_type' => 'attachment',
                            'guid' => $files[$fkey][$ii]['wp_upload']['url']                                
                        );            
                        $attach_id  = wp_insert_attachment($attachment, $files[$fkey][$ii]['wp_upload']['file']);
                        $attach_data = wp_generate_attachment_metadata( $attach_id, $files[$fkey][$ii]['wp_upload']['file'] );
                        wp_update_attachment_metadata( $attach_id, $attach_data );                       
                    }
                }
            }
            else
            {
                if (!isset($fdata['file_data'][$fkey]) || !is_array($fdata['file_data'][$fkey])) continue;
                
                if (!isset($files[$fkey]['upload_fail']) || !$files[$fkey]['upload_fail'])
                {
                    $filetype   = wp_check_filetype(basename($files[$fkey]['wp_upload']['file']), null);
                    $title      = $files[$fkey]['file_data'][$fkey]['name'];
                    $ext        = strrchr($title, '.');
                    $title      = ($ext !== false) ? substr($title, 0, -strlen($ext)) : $title;
                    $attachment = array(
                            'post_mime_type'    => $filetype['type'],
                            'post_title'        => addslashes($title),
                            'post_content'      => '',
                            'post_status'       => 'inherit',
                            'post_parent'       => $result,
                            'post_type' => 'attachment',
                            'guid' => $files[$fkey]['wp_upload']['url']                                
                    );            
                    $attach_id  = wp_insert_attachment($attachment, $files[$fkey]['wp_upload']['file']);
                    $attach_data = wp_generate_attachment_metadata( $attach_id, $files[$fkey]['wp_upload']['file'] );
                    wp_update_attachment_metadata( $attach_id, $attach_data ); 
                }
            }
        }
        
        foreach ($extra_files as $fkey=>$fdata)
        {
            if (!isset($extra_files[$fkey]['upload_fail']) || !$extra_files[$fkey]['upload_fail'])
            {
                $filetype   = wp_check_filetype(basename($extra_files[$fkey]['wp_upload']['file']), null);
                $title      = $_FILES[$fkey]['name'];
                $ext        = strrchr($title, '.');
                $title      = ($ext !== false) ? substr($title, 0, -strlen($ext)) : $title;
                $attachment = array(
                        'post_mime_type'    => $filetype['type'],
                        'post_title'        => addslashes($title),
                        'post_content'      => '',
                        'post_status'       => 'inherit',
                        'post_parent'       => $result,
                        'post_type' => 'attachment',
                        'guid' => $extra_files[$fkey]['wp_upload']['url']                                
                );            
                $attach_id  = wp_insert_attachment($attachment, $extra_files[$fkey]['wp_upload']['file']);
                $attach_data = wp_generate_attachment_metadata( $attach_id, $extra_files[$fkey]['wp_upload']['file'] );
                wp_update_attachment_metadata( $attach_id, $attach_data ); 
                
                if ($fkey=='_featured_image')
                {
                    // set current thumbnail
                    update_post_meta( $result, '_thumbnail_id', $attach_id );
                    // get current thumbnail
                    $zebraForm->controls[$_form_fields['_featured_image'][0]]->set_attributes(array('display_featured_html'=>get_the_post_thumbnail( $result, 'thumbnail' /*, $attr*/ )));
                }
            }
        }
    }
    
    public function setCookie($name, $data)
    {
        $result=false;
        if (!headers_sent())
        {
            $result=setcookie($name, urlencode(serialize($data)));
        }
        return $result;
    }
    
    public function readCookie($name)
    {
        $data=false;
        if (isset($_COOKIE[$name]))
        {
            $data=maybe_unserialize(urldecode($_COOKIE[$name]));
        }
        return $data;
    }
    
    public function clearCookie($name)
    {
        if (isset($_COOKIE[$name]))
            unset($_COOKIE[$name]);
        if (!headers_sent())
            $result=setcookie($name, ' ', time()-5832000);
            //setcookie($cookieName, ' ', time() - YEAR_IN_SECONDS, SITECOOKIEPATH);
    }
    
    public function trackData($data, $return=false)
    {
        static $track=array();
        if ($return)
        {
            // format data for output
            $trackRet=$this->formatData($track);
            // reset track data
            $track=array();
            return $trackRet;
        }
        $track=array_merge($track, $data);
    }
    
    public function formatData($data, $level=0)
    {
        // tabular output format ;)
        $keystyle=' style="background:#676767;font-weight:bold;color:#e1e1e1"';
        $valuestyle=' style="background:#ddd;font-weight:normal;color:#121212"';
        $output='';
        $data=(array)$data;
        foreach ($data as $k=>$v)
        {
            $output.='<tr>';
            if (!is_numeric($k))
                $output.='<td'.$keystyle.'>' . $k . '</td><td'.$valuestyle.'>';
            else
                $output.='<td colspan=2'.$valuestyle.'>';
            
            if (is_array($v) || is_object($v))
                $output.=$this->formatData((array)$v, $level+1);
            else
                $output.=$v;
            
            $output.= '</td></tr>';
        }
        if (0==$level)
            $output='<table style="position:relative;width:100%;"><tbody>'.$output.'</tbody></table>';
        else
            $output='<table><tbody>'.$output.'</tbody></table>';
        return $output;
    }
    
    // parse and check conditional expressions to be used in javascript
    public function parseConditionalExpressions(&$out_)
    {
        global $user_ID;
        
        // get ref here
        $globals=&self::friendGetStatic('CRED_Form_Builder', '&_staticGlobal');
        $zebraForm=$this->friendGet($this->_formBuilder, '_zebraForm');
        $formfields=array_keys($out_['form_fields']);
        $roles=$this->getUserRolesByID($user_ID);
        $conditional_js_data=array();
        $affected_fields=array();
        $kk=0;
        
        // check expression is valid
        foreach ($out_['conditionals'] as $key=>$cond)
        {
            ++$kk;
            $out_['conditionals'][$key]['valid']=true;
            $replace=array(
                'original'=>array(),
                'original_name'=>array(),
                'field_reference'=>array(),
                'field_name'=>array(),
                'values_map'=>array(),
                'replace'=>array()
            );
            
            if (preg_match_all('/\$\(([a-z_][a-z_\-\d]*:?)\)/si', $cond['condition'], $matches))
            {
                foreach ($matches[1] as $k=>$m)
                {
                    if (!in_array($m, $formfields))
                    {
                        if (in_array('administrator', $roles) && $zebraForm->preview)
                            $zebraForm->add_form_error('condition'.$key.$k, 
                                sprintf(__('Variable {%1$s} in Expression {%2$s} does not refer to an existing form field','wp-cred'),htmlspecialchars($m),htmlspecialchars($cond['condition'])));
                        $out_['conditionals'][$key]['valid']=false;
                    }
                    else if (
                        $out_['form_fields_info'][$m]['type']=='file' ||
                        $out_['form_fields_info'][$m]['type']=='image' ||
                        $out_['form_fields_info'][$m]['type']=='recaptcha' ||
                        $out_['form_fields_info'][$m]['type']=='skype' ||
                        $out_['form_fields_info'][$m]['type']=='form_messages' ||
                        $out_['form_fields_info'][$m]['repetitive'] 
                        )
                    {
                        if (in_array('administrator', $roles) && $zebraForm->preview)
                            $zebraForm->add_form_error('condition'.$key.$k, 
                                sprintf(__('Variable {%1$s} in Expression {%2$s} refers to a field that cannot be used in conditional expressions','wp-cred'),htmlspecialchars($m),htmlspecialchars($cond['condition'])));
                        $out_['conditionals'][$key]['valid']=false;
                    }
                    else
                    {
                        if (!in_array($m, $replace['original_name']))
                        {
                            $name=$out_['form_fields_info'][$m]['name'];
                            $replace['original'][]=$matches[0][$k];
                            $replace['original_name'][]=$m;
                            $replace['field_reference'][]=$out_['form_fields'][$m][0]; // field id this var references
                            $replace['field_name'][]=$name; // field name this var references
                            $replace['replace'][]='__var__'.$kk.$k;
                            if (isset($out_['field_values_map'][$m]))
                                $replace['values_map'][]=$out_['field_values_map'][$m];
                            else
                                $replace['values_map'][]=false;
                        }
                    }
                }
            }
            if ($out_['conditionals'][$key]['valid'])
            {
                if (!empty($replace['replace']))
                    $out_['conditionals'][$key]['replaced_condition']=str_replace($replace['original'],$replace['replace'],$out_['conditionals'][$key]['condition']);
                else
                {
                    $out_['conditionals'][$key]['replaced_condition']=$out_['conditionals'][$key]['condition'];
                    if (in_array('administrator', $roles) && $zebraForm->preview)
                        $zebraForm->add_form_error('condition'.$key, 
                            sprintf(__('Expression {%1$s} has no variables that refer to form fields, the evaluated result is constant','wp-cred'),htmlspecialchars($cond['condition'])));
                }
                $out_['conditionals'][$key]['var_field_map']=$replace;
                // format for js
                $tmp=array(
                    'condition' => $out_['conditionals'][$key]['replaced_condition'],
                    'group' => $out_['conditionals'][$key]['container_id'],
                    'mode' => $out_['conditionals'][$key]['mode'],
                    'affected_fields' => $replace['field_reference'],
                    'affected_fields_names' => $replace['field_name'],
                    'map' => array()
                );
                foreach ($replace['replace'] as $ii=>$var)
                    $tmp['map'][]=array(
                        'variable'=>$replace['replace'][$ii],
                        'field'=>$replace['field_reference'][$ii],
                        'field_name'=>$replace['field_name'][$ii],
                        'values_map'=>$replace['values_map'][$ii]
                    );
                $conditional_js_data[]=$tmp;
                // group all affected fields in one place for easy reference
                $affected_fields=array_merge($affected_fields, array_diff($replace['field_reference'], $affected_fields));
            }
            if (isset($zebraForm->controls[$key]) && $zebraForm->controls[$key]->isContainer())
                $zebraForm->controls[$key]->setConditionData($out_['conditionals'][$key]);
        }
        $extra_parameters=array(
            'parser_info'=>array(
                'user'=>(array)$globals['CURRENT_USER']
            )
        );
        $zebraForm->set_extra_parameters($extra_parameters);
        if (!empty($conditional_js_data))
        {
            $zebraForm->add_conditional_settings($conditional_js_data, $affected_fields);
        }
    }
       
    // get all form field values to be used in validation hooks
    public function get_form_field_values()
    {
        // get ref here
        $out_=&$this->friendGet($this->_formBuilder, '&out_');
        $zebraForm=$this->friendGet($this->_formBuilder, '_zebraForm');
        $fields=array();
        foreach ($out_['form_fields'] as $name=>$id)
        {
            $fields[$name]=array(
                'value'=>$this->convertFromTypes($name, $zebraForm->controls[$id[0]]->get_values(), $out_),
                'name'=>$name,
                'type'=>$out_['form_fields_info'][$name]['type'],
                'repetitive'=>$out_['form_fields_info'][$name]['repetitive']);
        }
        return $fields;
    }
   
   // set form fields values to new values (after validation)
    public function set_form_field_values($fields)
    {
        // get ref here
        $out_=&$this->friendGet($this->_formBuilder, '&out_');
        $zebraForm=$this->friendGet($this->_formBuilder, '_zebraForm');
        foreach ($fields as $name=>$data)
        {
            if (isset($out_['form_fields'][$name]))
            {
                $zebraForm->controls[$out_['form_fields'][$name][0]]->set_values($this->convertToTypes($name, $data['value'], $out_));
            }
        }
    }
       
    // checkboxes,radios and select must be converted to Types format
    public function convertToTypes($field, $vals, &$out_)
    {
        if (isset($out_['fields']['post_fields'][$field]))
        {
            // types field
            $field=$out_['fields']['post_fields'][$field];
            switch ($field['type'])
            {
                case 'select':
                        foreach ($field['data']['options'] as $key=>$option)
                        {
                            if ($vals==$option['value'])
                            {
                                return $key;
                            }
                        }
                        return '';
                        break;
                case 'radio':
                        foreach ($field['data']['options'] as $key=>$option)
                        {
                            if ($vals==$option['value'])
                            {
                                return $key;
                            }
                        }
                        return '';
                        break;
                case 'multiselect':
                case 'checkboxes':
                    $vvals='';
                    $avals=array();
                    foreach ($field['data']['options'] as $key=>$option)
                    {
                       if (is_array($vals))
                       {
                            if (in_array($option['set_value'],$vals))
                                $avals[]=$key;
                       }
                       else
                       {
                            if ($option['value']==$vals)
                                $vvals=$key;
                       }
                   }
                   if (is_array($vals)) return $avals;
                   else return $vvals;
                   break;
                default:
                    return $vals;
                    break;
            }
        }
        else
        {
            // generic
            return $vals;
        }
    }
       
    // checkboxes, radios and select must be transformed from Types format
    public function convertFromTypes($field, $vals, &$out_)
    {
        if (isset($out_['fields']['post_fields'][$field]))
        {
            // types field
            $field=$out_['fields']['post_fields'][$field];
            if ('radio'==$field['type'])
            {
                return isset($field['data']['options'][$vals])?$field['data']['options'][$vals]['value']:'';
            }
            elseif ('select'==$field['type'])
            {
                return isset($field['data']['options'][$vals])?$field['data']['options'][$vals]['value']:'';
            }
            elseif ('checkboxes'==$field['type'] || 'multiselect'==$field['type'])
            {
                $tmp_data=array();
                foreach ($vals as $tmp_val)
                    if (isset($field['data']['options'][$tmp_val]))
                        $tmp_data[]=$field['data']['options'][$tmp_val]['set_value'];
                return $tmp_data;
            }
            else
            {
                return $vals;
            }
        }
        else
        {
            // generic
            return $vals;
        }
    }
       
    // translate each cred field to a customized Zebra_Form field
    public function translate_field($name, &$field, $additional_options=array())
    {
        // allow multiple submit buttons
        static $_count_=array(
            'submit'=>0
        );
        static $wpExtensions=false;
        // get refs here
        $globals=&self::friendGetStatic('CRED_Form_Builder', '&_staticGlobal');
        if (false===$wpExtensions)
        {
            $wpMimes=$globals['MIMES'];
            $wpExtensions=implode(',', array_keys($wpMimes));
        }
        // get refs here
        $form=&$this->friendGet($this->_formBuilder, '&_formData');
        $supported_date_formats=&$this->friendGet($this->_formBuilder, '&_supportedDateFormats');
        $out_=&$this->friendGet($this->_formBuilder, '&out_');
        $postData=&$this->friendGet($this->_formBuilder, '&_postData');
        $zebraForm=$this->friendGet($this->_formBuilder, '_zebraForm');
        
        // extend additional_options with defaults
        extract(array_merge(
            array(
                'preset_value'=>null,
                'placeholder'=>null,
                'value_escape'=>false,
                'make_readonly'=>false,
                'is_tax'=>false,
                'max_width'=>null,
                'max_height'=>null,
                'single_select'=>false,
                'generic_type'=>null,
                'urlparam'=>''
            ),
            $additional_options
        ));
        
        // add the "name" element
        // the "&" symbol is there so that $obj will be a reference to the object in PHP 4
        // for PHP 5+ there is no need for it
        $type='text';
        $attributes=array();
        $value='';
        
        $name_orig=$name;
        if (!$is_tax) // if not taxonomy field
        {
            if ($placeholder && $placeholder!==null && !empty($placeholder) && is_string($placeholder))
            {
                // use translated value by WPML if exists
                $placeholder=cred_translate(
                    'Value: '.$placeholder, 
                    $placeholder, 
                    'cred-form-'.$form->form->post_title.'-'.$form->form->ID
                );
            }    
            
            if ($preset_value && null!==$preset_value && is_string($preset_value) && !empty($preset_value))
            {
                // use translated value by WPML if exists
                $data_value=cred_translate(
                    'Value: '.$preset_value, 
                    $preset_value, 
                    'cred-form-'.$form->form->post_title.'-'.$form->form->ID
                );
            }
            // allow field to get value through url parameter
            elseif (is_string($urlparam) && !empty($urlparam) && isset($_GET[$urlparam]))
            {
                // use translated value by WPML if exists
                $data_value=urldecode($_GET[$urlparam]);
            }
            // allow persisted generic fields to display values
            elseif ($postData && isset($postData->fields[$name_orig]))
            {
                $data_value=$postData->fields[$name_orig][0];
            }
            else
            {
                $data_value=null;
            }
            
            $value='';
            // save a map between options / actual values for these types to be used later
            if (in_array($field['type'], array('checkboxes', 'radio', 'select', 'multiselect')))
            {
                //cred_log($field);
                $tmp=array();
                foreach ($field['data']['options'] as $optionKey=>$optionData)
                {
                    if ($optionKey!='default' && is_array($optionData))
                        $tmp[$optionKey]=('checkboxes'==$field['type'])?$optionData['set_value']:$optionData['value'];
                }
                $out_['field_values_map'][$field['slug']]=$tmp;
                unset($tmp);
                unset($optionKey);
                unset($optionData);
            }
            switch ($field['type'])
            {
                case 'form_messages' :   $type='messages';
                                    break;
                                    
                case 'form_submit': $type='submit'; if (null!==$data_value) $value=$data_value;
                                    // allow multiple submit buttons
                                    $name.='_'.++$_count_['submit'];
                                    break;
                                    
                case 'recaptcha':   $type='recaptcha'; 
                                    $value='';
                                    $attributes=array(
                                        'error_message'=>$this->getLocalisedMessage('enter_valid_captcha'),
                                        'show_link'=>$this->getLocalisedMessage('show_captcha'),
                                        'no_keys'=>__('Enter your ReCaptcha keys at the CRED Settings page in order for ReCaptcha API to work','wp-cred')
                                        ); 
                                    if (false!==$globals['RECAPTCHA'])
                                    {
                                        $attributes['public_key']=$globals['RECAPTCHA']['public_key'];
                                        $attributes['private_key']=$globals['RECAPTCHA']['private_key'];
                                    }
                                    if (1==$out_['count'])
                                        $attributes['open']=true;
                                    // used to load additional js script
                                    $out_['has_recaptcha']=true;
                                    break;
                                    
                case 'file':        $type='file';  if ($data_value!==null) $value=$data_value; 
                                    break;
                                    
                case 'image':       $type='file';  if ($data_value!==null) $value=$data_value;
                                    // show previous post featured image thumbnail
                                    if ('_featured_image'==$name)
                                    {
                                        $value='';
                                        if (isset($postData->extra['featured_img_html']))
                                        {
                                            $attributes['display_featured_html']=$postData->extra['featured_img_html'];
                                        }
                                    }
                                    break;
                                    
                case 'date':        $type='date'; 
                                    $format='';
                                    if (isset($field['data']) && isset($field['data']['validate']))
                                        $format=$field['data']['validate']['date']['format'];
                                    if (!in_array($format,$supported_date_formats))
                                        $format='F j, Y';
                                    $attributes['format']=$format;
                                    $attributes['readonly_element']=false;
                                    if (
                                        null!==$data_value && 
                                        !empty($data_value) && 
                                        (is_numeric($data_value) || is_int($data_value) || is_long($data_value))
                                    )
                                    {
                                        MyZebra_DateParser::setDateLocaleStrings($globals['LOCALES']['days'], $globals['LOCALES']['months']);
                                        // format localized date form timestamp 
                                        $value = MyZebra_DateParser::formatDate($data_value, $format, true);
                                    }
                                    break;
                                    
                case 'select':      $type='select';
                                    $value=array();
                                    $attributes=array();
                                    $attributes['options']=array();
                                    $default=array();
                                    foreach ($field['data']['options'] as $key=>$option)
                                    {
                                        $index=$key; //$option['value']; 
                                        if ('default'==$key)
                                        {
                                            $default[]=$option;
                                        }
                                        else
                                        {
											if (is_admin()) {
												//register strings on form save
												cred_translate_register_string('cred-form-'.$form->form->post_title.'-'.$form->form->ID, $option['title'], $option['title'], false);
											}
											$option['title'] = cred_translate(
												$option['title'], 
												$option['title'], 
												'cred-form-'.$form->form->post_title.'-'.$form->form->ID
											);
                                            $attributes['options'][$index]=$option['title'];
                                            if ((null!==$data_value) && $data_value==$option['value'])
                                            {
                                                $value[]=$key;
                                            }
                                            if (isset($option['dummy']) && $option['dummy'])
                                                $attributes['dummy']=$key;
                                        }
                                    }
                                    if (empty($value) && !empty($default))
                                        $value=$default;
                                    if (isset($out_['field_values_map'][$field['slug']]))
                                        $attributes['actual_options']=$out_['field_values_map'][$field['slug']];

                                    break;
                                    
                case 'multiselect': $type='select'; $name.='[]'; 
                                    $value=array();
                                    $attributes=array();
                                    $attributes['options']=array();
                                    $attributes['multiple']='multiple';
                                    $default=array();
                                    foreach ($field['data']['options'] as $key=>$option)
                                    {
                                        $index=$key; //$option['value']; 
                                        if ('default'==$key)
                                        {
                                            $default=(array)$option;
                                        }
                                        else
                                        {
											if (is_admin()) {
												//register strings on form save
												cred_translate_register_string('cred-form-'.$form->form->post_title.'-'.$form->form->ID, $option['title'], $option['title'], false);
											}
											$option['title'] = cred_translate(
												$option['title'], 
												$option['title'], 
												'cred-form-'.$form->form->post_title.'-'.$form->form->ID
											);
                                            $attributes['options'][$index]=$option['title'];
                                            if ((null!==$data_value) && $data_value==$option['value'])
                                            {
                                                $value[]=$key;
                                            }
                                            if (isset($option['dummy']) && $option['dummy'])
                                                $attributes['dummy']=$key;
                                        }
                                    }
                                    if (empty($value) && !empty($default))
                                        $value=$default;
                                    if (isset($out_['field_values_map'][$field['slug']]))
                                        $attributes['actual_options']=$out_['field_values_map'][$field['slug']];
                                    break;
                                    
                case 'radio':       $type='radios'; 
                                    $value=array();
                                    $attributes='';
                                    $default='';
                                    foreach ($field['data']['options'] as $key=>$option)
                                    {
                                        $index=$key; //$option['display_value'];
                                        if ('default'==$key)
                                        {
                                            $default=$option;
                                        }
                                        else
                                        {
											if (is_admin()) {
												//register strings on form save
												cred_translate_register_string('cred-form-'.$form->form->post_title.'-'.$form->form->ID, $option['title'], $option['title'], false);
											}
											$option['title'] = cred_translate(
												$option['title'], 
												$option['title'], 
												'cred-form-'.$form->form->post_title.'-'.$form->form->ID
											);
                                            $value[$index]=$option['title'];
                                            if (($data_value!==null) && $data_value==$option['value'])
                                            {
                                                $attributes=$key;
                                            }
                                        }
                                    }
                                    if (($data_value===null) && !empty($default))
                                    {
                                        $attributes=$default;
                                    }
                                    $def=$attributes;
                                    $attributes=array('default'=>$def);
                                   if (isset($out_['field_values_map'][$field['slug']]))
                                        $attributes['actual_values']=$out_['field_values_map'][$field['slug']];

                                    break;
                                    
                case 'checkboxes':  $type='checkboxes'; $name.='[]'; 
                                    $value=array();
                                    $attributes=array();
                                    /*if (is_array($data_value))
                                        $data_value=array_keys($data_value);
                                    else*/if (!is_array($data_value) && null!==$data_value) $data_value=array($data_value);
                                    foreach ($field['data']['options'] as $key=>$option)
                                    {
										if (is_admin()) {
											//register strings on form save
											cred_translate_register_string('cred-form-'.$form->form->post_title.'-'.$form->form->ID, $option['title'], $option['title'], false);
										}
										$option['title'] = cred_translate(
											$option['title'], 
											$option['title'], 
											'cred-form-'.$form->form->post_title.'-'.$form->form->ID
										);
                                        $index=$key;
                                        $value[$index]=$option['title'];
                                        if (isset($option['checked']) && $option['checked'] && null===$data_value)
                                        {
                                            $attributes[]=$index;
                                        }
                                        elseif ((null!==$data_value) && isset($data_value[$index]) /*&& in_array($index,$data_value)*/)
                                        {
                                            if (
                                                !('yes'==$field['data']['save_empty'] && (0===$data_value[$index] || '0'===$data_value[$index]))
                                            )
                                                $attributes[]=$index;
                                        }
                                    }
                                    $def=$attributes;
                                    $attributes=array('default'=>$def);
                                    if (isset($out_['field_values_map'][$field['slug']]))
                                        $attributes['actual_values']=$out_['field_values_map'][$field['slug']];
                                    break;
                                    
                case 'checkbox':    $type='checkbox'; $value=$field['data']['set_value']; 
                                    if ((null!==$data_value) && $data_value==$value) $attributes=array('checked'=>'checked');
                                    break;
                                    
                case 'textarea':    $type='textarea'; if (null!==$data_value) $value=$data_value; 
                                    if ($placeholder && null!==$placeholder && !empty($placeholder))
                                        $attributes['placeholder']=$placeholder;
                                    break;
                                    
                case 'wysiwyg':     $type='wysiwyg'; if (null!==$data_value) $value=$data_value; 
                                    $attributes=array('disable_xss_filters'=>true);
                                    //cred_log($form->fields);
                                    if ('post_content'==$name && isset($form->fields['form_settings']->form['has_media_button']) && $form->fields['form_settings']->form['has_media_button'])
                                        $attributes['has_media_button']=true;
                                    break;
                                    
                case 'numeric':     $type='text'; if (null!==$data_value) $value=$data_value; 
                                    break;
                                    
                case 'phone':       $type='text'; if (null!==$data_value) $value=$data_value; 
                                    break;
                                    
                case 'url':         $type='text'; if (null!==$data_value) $value=$data_value; 
                                    break;
                                    
                case 'email':       $type='text'; if (null!==$data_value) $value=$data_value; 
                                    break;
                                    
                case 'textfield':   $type='text'; if (null!==$data_value) $value=$data_value; 
                                    if ($placeholder && null!==$placeholder && !empty($placeholder))
                                        $attributes['placeholder']=$placeholder;
                                    break;
                                    
                case 'password':   $type='password'; if (null!==$data_value) $value=$data_value; 
                                    if ($placeholder && null!==$placeholder && !empty($placeholder))
                                        $attributes['placeholder']=$placeholder;
                                    break;
                                    
                case 'hidden':      $type='hidden'; if (null!==$data_value) $value=$data_value;
                                    break;
                                    
                case 'skype':       $type='skype'; 
                                    if ((null!==$data_value) && is_string($data_value))
                                        $data_value=array('skypename'=>$data_value,'style'=>'');
                                    if (null!==$data_value) $value=$data_value;
                                    else $value=array('skypename'=>'','style'=>'');
                                    $attributes=array(
                                        'ajax_url'=>admin_url('admin-ajax.php'),
                                        'edit_skype_text'=>$this->getLocalisedMessage('edit_skype_button'),
                                        'value' => $data_value,
                                        '_nonce'=>wp_create_nonce('insert_skype_button')
                                        );
                                    break;
                                    
                // everything else defaults to a simple text field
                default:            $type='text'; if (null!==$data_value) $value=$data_value; 
                                    break;
            }
            if ($make_readonly)
            {
                if (!is_array($attributes))
                    $attributes=array();
                $attributes['readonly']='readonly';
            }
            // repetitive field (special care)
            if (isset($field['data']['repetitive']) && $field['data']['repetitive'])
            {
                $name.='[]';
                $objs = & $zebraForm->add_repeatable($type, $name, $value, $attributes);
                
                if (isset($postData->fields[$name_orig]) && count($postData->fields[$name_orig])>1)
                for ($ii=1; $ii<count($postData->fields[$name_orig]) && count($postData->fields[$name_orig]); $ii++)
                {
                    $data_value=$postData->fields[$name_orig][$ii];
                    $atts=array();
                    switch ($type)
                    {
                        case 'skype':
                                $atts=array(
                                    'value' => $data_value,
                                    );
                            break;
                            
                        case 'date':
                                    $format='';
                                    if (isset($field['data']) && isset($field['data']['validate']))
                                        $format=$field['data']['validate']['date']['format'];
                                    if (!in_array($format, $supported_date_formats))
                                        $format='F j, Y';
                                    $atts['format']=$format;
                                    $atts['readonly_element']=false;
                                    if (!empty($data_value))
                                    {
                                        MyZebra_DateParser::setDateLocaleStrings($globals['LOCALES']['days'], $globals['LOCALES']['months']);
                                        // format localized date form timestamp 
                                        $atts['value'] = MyZebra_DateParser::formatDate($data_value, $format, true);
                                    }
                                    break;

                        case 'file':
                                    $atts['value']=$data_value;
                                    break;
                                    
                        case 'textfield':
                        case 'text':
                                    $atts['value']=$data_value;
                                    break;
                                    
                        case 'wysiwyg':
                        case 'textarea':
                                    $atts['value']=$data_value;
                                    break;
                                    
                        case 'checkbox':
                                $value=$field['data']['set_value']; 
                                if ($data_value==$value) $atts=array('checked'=>'checked');
                                break;
                                
                        case 'select':
                                    $value=array();
                                    foreach ($field['data']['options'] as $key=>$option)
                                    {
                                        $index=$option['value'];//$option['set_value'];
                                        if ('default'==$key && ''==$data_value)
                                        {
                                            $value[]=$field['data']['options'][$option]['value'];
                                        }
                                        elseif (''!=$data_value)
                                        {
                                            $value[]=$data_value;
                                        }
                                    }
                                    $atts['value']=$value;
                                    break;
                                    
                        default:
                                    $atts['value']=$data_value;
                                    break;
                    }
                    $objs->addControl($atts);
                }
            }
            else
            {
                $objs = & $zebraForm->add($type, $name, $value, $attributes);
            }
            if (!is_array($objs)) $oob=array($objs);
            else    $oob=$objs;
            $ids=array();
            // add validation rules if needed
            foreach ($oob as &$obj)
            {
                $obj->setPrimeName($name_orig);
                
                if ('hidden'==$type)
                {
                    $obj->attributes['user_defined']=true;
                }
                
                // field belongs to a container?
                if (null!==$out_['current_group'])
                {
                    $out_['current_group']->addControl($obj);
                }
                    
                $atts = $obj->get_attributes(array('id','type'));
                $ids[]=$atts['id'];
                if ('label'==$atts['type']) continue;
                switch($type)
                {
                    case 'file':
                        $upload=wp_upload_dir();
                        // set rules
                        $obj->set_rule(array(
                            // error messages will be sent to a variable called "error", usable in custom templates
                            'upload' => array($upload['path'], $upload['url'], true, 'error', $this->getLocalisedMessage('upload_failed')),
                        ));
                        $obj->set_attributes(array('external_upload'=>true)); // we will handle actual upload
                        
                        if ('image'==$field['type'])
                        {
                            // set rules
                            $obj->set_rule(array(
                                // error messages will be sent to a variable called "error", usable in custom templates
                                'image' => array('error', $this->getLocalisedMessage('not_valid_image')),
                            ));
                        }
                        else
                        {
                            // if general file upload, restrict to Wordpress allowed file types
                            $obj->set_rule(array(
                                // error messages will be sent to a variable called "error", usable in custom templates
                                'filetype'=>array($wpExtensions, 'error', $this->getLocalisedMessage('file_type_not_allowed'))
                            ));
                        }
                        if (null!==$max_width && is_numeric($max_width))
                        {
                            $max_width=intval($max_width);
                            $obj->set_rule(array(
                                // error messages will be sent to a variable called "error", usable in custom templates
                                'image_max_width' => array($max_width, sprintf($this->getLocalisedMessage('image_width_larger'),$max_width))
                            ));
                        }
                        if (null!==$max_height && is_numeric($max_height))
                        {
                            $max_height=intval($max_height);
                            $obj->set_rule(array(
                                // error messages will be sent to a variable called "error", usable in custom templates
                                'image_max_height' => array($max_height, sprintf($this->getLocalisedMessage('image_height_larger'),$max_height))
                            ));
                        }
                        break;
                }
                
                if (isset($field['data']) && isset($field['data']['validate']))
                {
                    foreach ($field['data']['validate'] as $method=>$validation)
                    {
                        if ($validation['active'])
                        {
                            switch ($method)
                            {
                                case 'required':
                                    // set rules
                                    $obj->set_rule(array(
                                        // error messages will be sent to a variable called "error", usable in custom templates
                                        'required' => array('error', $this->getLocalisedMessage('field_required'))
                                    ));
                                    break;
                                    
                                case 'hidden':
                                    // set rules
                                    $obj->set_rule(array(
                                        // error messages will be sent to a variable called "error", usable in custom templates
                                        'hidden' => array('error', $this->getLocalisedMessage('values_do_not_match'))
                                    ));
                                    // default attribute to check against submitted value
                                    $obj->set_attributes(array('default'=>$obj->attributes['value']));
                                    break;
                                    
                                case 'date':
                                    // set rules
                                    $obj->set_rule(array(
                                        // error messages will be sent to a variable called "error", usable in custom templates
                                        'date' => array('error', $this->getLocalisedMessage('enter_valid_date'))
                                    ));
                                    break;
                                    
                                case 'email':
                                    // set rules
                                    $obj->set_rule(array(
                                        // error messages will be sent to a variable called "error", usable in custom templates
                                        'email' => array('error', $this->getLocalisedMessage('enter_valid_email'))
                                    ));
                                    break;
                                    
                                // change number to a float type (same as Types) instead of integer
                                case 'number':
                                    // set rules
                                    $obj->set_rule(array(
                                        // error messages will be sent to a variable called "error", usable in custom templates
                                        'float' => array('','error', $this->getLocalisedMessage('enter_valid_number'))
                                    ));
                                    break;
                                    
                                case 'integer':
                                    // set rules
                                    $obj->set_rule(array(
                                        // error messages will be sent to a variable called "error", usable in custom templates
                                        'number' => array('','error', $this->getLocalisedMessage('enter_valid_number'))
                                    ));
                                    break;
                                
                                case 'image':
                                case 'file':
                                    break;
                                    
                                case 'url':
                                    // set rules
                                    $obj->set_rule(array(
                                        // error messages will be sent to a variable called "error", usable in custom templates
                                        'url' => array('error', $this->getLocalisedMessage('enter_valid_url'))
                                    ));
                                    break;
                            }
                        }
                    }
                }
            }
        }
        else // taxonomy field or auxilliary taxonomy field (eg popular terms etc..)
        {
            if (!array_key_exists('master_taxonomy', $field)) // taxonomy field
            {
                if ($field['hierarchical'])
                {
                    if (in_array($preset_value,array('checkbox', 'select')))
                        $tax_display=$preset_value;
                    else
                        $tax_display='checkbox';
                }
                
                if ($postData && isset($postData->taxonomies[$name_orig]))
                {
                    if (!$field['hierarchical'])
                    {
                        $data_value=array(
                            'terms'=>$postData->taxonomies[$name_orig]['terms'],
                            'add_text'=>$this->getLocalisedMessage('add_taxonomy'),
                            'remove_text'=>$this->getLocalisedMessage('remove_taxonomy'),
                            'ajax_url'=>admin_url('admin-ajax.php'),
                            'auto_suggest'=>true
                        );
                    }
                    else
                    {
                        $data_value=array(
                            'terms'=>$postData->taxonomies[$name_orig]['terms'],
                            'all'=>$field['all'],
                            'type'=>$tax_display,
                            'single_select'=>$single_select
                        );
                    }
                }
                else
                {
                    if (!$field['hierarchical'])
                    {
                        $data_value=array(
                            //'terms'=>array(),
                            'add_text'=>$this->getLocalisedMessage('add_taxonomy'),
                            'remove_text'=>$this->getLocalisedMessage('remove_taxonomy'),
                            'ajax_url'=>admin_url('admin-ajax.php'),
                            'auto_suggest'=>true
                        );
                    }
                    else
                    {
                        $data_value=array(
                            'all'=>$field['all'],
                            'type'=>$tax_display,
                            'single_select'=>$single_select
                        );
                    }
                }
                
                // if not hierarchical taxonomy
                if (!$field['hierarchical'])
                {
                    $objs = & $zebraForm->add('taxonomy', $name, $value, $data_value);
                }
                else
                {
                    $objs = & $zebraForm->add('taxonomyhierarchical', $name, $value, $data_value);
                }
                
                // register this taxonomy field for later use by auxilliary taxonomy fields
                $out_['taxonomy_map']['taxonomy'][$name_orig]=&$objs;
                // if a taxonomy auxiliary field exists attached to this taxonomy, add this taxonomy id to it
                if (isset($out_['taxonomy_map']['aux'][$name_orig]))
                {
                    $out_['taxonomy_map']['aux'][$name_orig]->set_attributes(array('master_taxonomy_id'=>$objs->attributes['id']));
                }
                
                if (!is_array($objs)) $oob=array($objs);
                else    $oob=$objs;
                $ids=array();
                foreach ($oob as &$obj)
                {
                    $obj->setPrimeName($name_orig);
                    
                    // field belongs to a container?
                    if (null!==$out_['current_group'])
                    {
                        $out_['current_group']->addControl($obj);
                    }
                    
                    $atts = $obj->get_attributes(array('id','type'));
                    $ids[]=$atts['id'];
                }
            }
            else // taxonomy auxilliary field (eg most popular etc..)
            {
                if ($preset_value && null!==$preset_value)
                    // use translated value by WPML if exists
                    $data_value=cred_translate(
                        'Value: '.$preset_value, 
                        $preset_value, 
                        'cred-form-'.$form->form->post_title.'-'.$form->form->ID
                    );
                else
                    $data_value=null;
                
                $ids=array();
                if (in_array($field['type'], array('show_popular', 'add_new'))) // these auxilliaries are implemented
                {
                    if ('show_popular'==$field['type'])
                    {
                        $objs = & $zebraForm->add('taxonomypopular', $name, $value, array(
                                    'popular'=>$field['taxonomy']['most_popular'],
                                    'show_popular_text'=>$this->getLocalisedMessage('show_popular'),
                                    'hide_popular_text'=>$this->getLocalisedMessage('hide_popular')));
                    }
                    elseif ('add_new'==$field['type'])
                    {
                        $objs = & $zebraForm->add('taxonomyhierarchicaladdnew', $name, $value, array(
                                            'add_new_text'=>$this->getLocalisedMessage('add_new_taxonomy'),
                                            'add_text'=>$this->getLocalisedMessage('add_taxonomy'),
                                            'parent_text'=>__('-- Parent --','wp-cred')

                        ));
                    }
                    
                    // register this taxonomy auxilliary field for later use by taxonomy fields
                    $out_['taxonomy_map']['aux'][$field['master_taxonomy']]=&$objs;
                    // if a taxonomy field exists that this field is attached, link to its id here 
                    if (isset($out_['taxonomy_map']['taxonomy'][$field['master_taxonomy']]))
                    {
                        $objs->set_attributes(array('master_taxonomy_id'=>$out_['taxonomy_map']['taxonomy'][$field['master_taxonomy']]->attributes['id']));
                    }
                    
                    if (!is_array($objs)) $oob=array($objs);
                    else    $oob=$objs;
                    foreach ($oob as &$obj)
                    {
                        $atts = $obj->get_attributes(array('id', 'type'));
                        $ids[]=$atts['id'];
                    }
                }
            }
        }
        return $ids; // return the ids of the created fields
    }
    
    /*
    *   Implement Friendly Interface
    *
    */
    // use this "magic" method to pass friend token to friendable
    public function __toString()
    {
        return (string)($this->____friend_token____);
    }
    
    private function makeFriendToken($id=null)
    {
        if (null===$id)
        {
            $id='foo123'.time().'r'.rand(0,9);
        }
        $this->____friend_token____=(string)$id;
    }
    
    private function friendHash($what)
    {
        return (string)($this->____friend_token____.'_1_1_1_'.$what);
    }
    
    private static function friendHashStatic($what)
    {
        return (string)(__CLASS__ . '_1_1_1_' . $what);
    }
    
    private function friendCall(&$fr, $method)
    {
        $method=$this->friendHash($method);
        $args = array_slice(func_get_args(), 1); // Get pure arguments with method also
        return call_user_func_array( array(&$fr, '_call_'), $args );
    }
    
    private static function friendCallStatic($fr, $method)
    {
        $method=self::friendHashStatic($method);
        $args = array_slice(func_get_args(), 1); // Get pure arguments, add method
        return call_user_func_array( array($fr, '_callStatic_'), $args );
    }
    
    // http://stackoverflow.com/questions/9798134/pass-variable-number-of-params-without-call-user-func-array
    private function &friendGet(&$fr, $prop)
    {
        $ref=false;
        $prop1=$prop;
        if ('&'==$prop[0])
        {
            $ref=true;
            $prop1=substr($prop, 1);
        }
        // if this is public anyway (PHP 5 >= 5.1.0)
        /*if (property_exists($fr, $prop1))
        {
            if ($ref)
                $v=&$fr->{$prop1};
            else
                $v=$fr->{$prop1};
            return $v;
        }*/
        $prop=$this->friendHash($prop);
        if ($ref)
            $v=&$fr->_get_($prop);
        else
            $v=$fr->_get_($prop);
        return $v;
    }
    
    private static function &friendGetStatic($fr, $prop)
    {
        $ref=false;
        $prop1=$prop;
        if ('&'==$prop[0])
        {
            $ref=true;
            $prop1=substr($prop, 1);
        }
        // if this is public anyway (PHP 5 >= 5.1.0)
        /*if (property_exists($fr, $prop1))
        {
            if ($ref)
                eval('$v=&'.$fr.'::$'.$prop1.';');
            else
                eval('$v='.$fr.'::$'.$prop1.';');
            return $v;
        }*/
        $prop=self::friendHashStatic($prop);
        if ($ref)
            eval('$v=&'.$fr."::_getStatic_('$prop');");
        else
            $v=call_user_func_array( array($fr, '_getStatic_'), array($prop) );
        return $v;
    }
    
    private function friendSet(&$fr, $prop, $val)
    {
        $prop=$this->friendHash($prop);
        return $fr->_set_($prop, $val);
    }
    
    private static function friendSetStatic($fr, $prop, $val)
    {
        $prop=self::friendHashStatic($prop);
        return call_user_func_array( array($fr, '_setStatic_'), array($prop, $val) );
    }
    /*
    *   /END Implement Friendly Interface
    *
    */
}
