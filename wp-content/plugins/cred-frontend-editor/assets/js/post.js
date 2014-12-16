(function(window, $, settings, utils, gui, undefined) {
    // uses WordPress 3.3+ features of including jquery-ui effects
    
    // oonstants
    var KEYCODE_ENTER = 13, KEYCODE_ESC = 27, PREFIX = '_cred_cred_prefix_',
        PAD = '\t', NL = '\r\n';

    // private properties
    var form_id = 0,
        settingsPage = null,
        form_name = '',
        field_data = null,
        CodeMirrorEditors = {},
        // used for MV framework, bindings and interaction
        _credModel, credView;
    
    var cred_media_buttons,
        cred_popup_boxes,
        checkButtonTimer
        ;
    
    // auxilliary functions
    var aux = {
        
        checkButton : function()
        {
            var butt=$('#cred-insert-shortcode');
            var disable=false;
            var tip=false;
            var _vv=null;
            var mode=$('#cred-form-shortcodes-box-inner input.cred-shortcode-container-radio:checked');

            switch(mode.attr('id'))
            {
                case 'cred-post-creation-container':
                    _vv=$('#cred_form-new-shortcode-select').val();
                    if (!_vv || ''==_vv)
                    {
                        disable=true;
                        tip=settings.locale.select_form;
                    }
                break;

                case 'cred-post-edit-container':
                    if ($('#cred-post-edit-container-advanced input[name="cred-edit-how-to-display"]:checked').val()=='insert-link')
                    {
                        $('#cred-edit-link-text-container').show();
                    }
                    else
                    {
                        $('#cred-edit-link-text-container').hide();
                    }
                    if ($('#cred-post-edit-container-advanced input[name="cred-edit-what-to-edit"]:checked').val()=='edit-other-post')
                    {
                        $('#cred-edit-other-post-more').show();
                    }
                    else
                    {
                        $('#cred-edit-other-post-more').hide();
                    }

                    _vv=$('#cred_form-edit-shortcode-select').val();
                    if (!_vv || ''==_vv)
                    {
                        disable=true;
                        tip=settings.locale.select_form;
                    }
                    else
                    {
                        _vv=$('#cred-edit-post-select').val();
                        if (
                        $('#cred-post-edit-container-advanced input[name="cred-edit-what-to-edit"]:checked').val()=='edit-other-post'
                        &&
                        (!_vv || ''==_vv)
                        )
                        {
                            disable=true;
                            tip=settings.locale.select_post;
                        }
                    }
                break;

                case 'cred-post-child-link-container':
                    _vv=$('#cred-child-form-page').val();
                    if (!_vv || ''==_vv)
                    {
                        disable=true;
                        tip='Select a page which has child form';
                    }

                    _vv=$('#cred_post_child_parent_id').val();
                    if ($('#_cred-post-child-link-container input[name="cred-post-child-parent-action"]:checked').val()=='other'
                        && (!_vv || ''==_vv)
                    )
                    {
                        disable=true;
                        tip='Select Parent Post';
                    }

                break;

                case 'cred-post-delete-link-container':
                    _vv=$('#cred_post_delete_id').val();
                    if (
                        $('#cred-post-delete-link-container-advanced input[name="cred-delete-what-to-delete"]:checked').val()=='delete-other-post'
                        &&
                            (
                                !_vv || ''==_vv
                                || !utils.isNumber(_vv)
                            )
                        )
                        {
                            disable=true;
                            tip=settings.locale.insert_post_id;
                        }
                break;

                default:
                    disable=true;
                    tip=settings.locale.select_shortcode;
                break;
            }
            // add a tip as title to insert link to notify about potential errors
            if (tip!==false)
                butt.attr('title',tip);
            else
                butt.attr('title',settings.locale.insert_shortcode);

            if (disable)
                butt.attr('disabled','disabled');
            else
                butt.removeAttr('disabled'); // if all ok enable it
				
			aux.checkClassButton(butt);			
        },

        checkButton2 : function($parent)
        {
            var butt=$('.cred-insert-shortcode2',$parent);
            var disable=false;
            var tip=false;
            var _vv;
            var mode=$('input.cred-shortcode-container-radio:checked',$parent);

                if (mode.hasClass('cred-post-creation-container2'))
                {
                    _vv=$('.cred_form-new-shortcode-select2',$parent).val();
                    if (''==_vv || !_vv)
                    {
                        disable=true;
                        tip=settings.locale.select_form;
                    }
                }

                else if (mode.hasClass('cred-post-edit-container2'))
                {
                    _vv=$('.cred-post-edit-container-advanced2 input[name^="cred-edit-how-to-display"]:checked',$parent).val();
                    if ('insert-link'==_vv)
                    {
                        $('.cred-edit-link-text-container2',$parent).show();
                    }
                    else
                    {
                        $('.cred-edit-link-text-container2',$parent).hide();
                    }
                    _vv=$('.cred-post-edit-container-advanced2 input[name^="cred-edit-what-to-edit"]:checked',$parent).val();
                    if ('edit-other-post'==_vv)
                    {
                        $('.cred-edit-other-post-more2',$parent).show();
                    }
                    else
                    {
                        $('.cred-edit-other-post-more2',$parent).hide();
                    }

                    _vv=$('.cred_form-edit-shortcode-select2',$parent).val();
                    if (''==_vv || !_vv)
                    {
                        disable=true;
                        tip=settings.locale.select_form;
                    }
                    else 
                    {
                        _vv=$('.cred-edit-post-select2',$parent).val();
                        if (
                            'edit-other-post'==$('.cred-post-edit-container-advanced2 input[name^="cred-edit-what-to-edit"]:checked',$parent).val()
                            &&
                            (''==_vv || !_vv)
                            )
                            {
                                disable=true;
                                tip=settings.locale.select_post;
                            }
                    }
                }
                else if (mode.hasClass('cred-post-child-link-container2'))
                {
                    _vv=$('.cred-child-form-page2',$parent).val();
                    if (''==_vv || !_vv)
                    {
                        disable=true;
                        tip='Select a page which has child form';
                    }

                    _vv=$('.cred_post_child_parent_id2',$parent).val();
                    if ($('._cred-post-child-link-container2 input[name^="cred-post-child-parent-action"]:checked',$parent).val()=='other'
                        && (''==_vv || !_vv)
                    )
                    {
                        disable=true;
                        tip='Select Parent Post';
                    }

                }

                else if (mode.hasClass('cred-post-delete-link-container2'))
                {
                    _vv=$('.cred_post_delete_id2',$parent).val();
                    if (
                        $('.cred-post-delete-link-container-advanced2 input[name^="cred-delete-what-to-delete"]:checked',$parent).val()=='delete-other-post'
                        &&
                            (
                                ''==_vv || !_vv
                                ||
                                !utils.isNumber(_vv)
                            )
                        )
                        {
                            disable=true;
                            tip=settings.locale.insert_post_id;
                        }
                }
                else
                {
                    disable=true;
                    tip=settings.locale.select_shortcode;
                }

            // add a tip as title to insert link to notify about potential errors
            if (tip!==false)
                butt.attr('title',tip);
            else
                butt.attr('title',settings.locale.insert_shortcode);

            if (disable)
                butt.attr('disabled','disabled');
            else
                butt.removeAttr('disabled'); // if all ok enable it
				
			aux.checkClassButton(butt);
        },

        checkClassButton : function($button)
        {
			if ('disabled' == $button.attr('disabled'))
			{
				if ($button.hasClass('button-primary'))
				{
					$button.removeClass('button-primary').addClass('button-secondary');
				}
			}
			else
			{
				if ($button.hasClass('button-secondary'))
				{
					$button.removeClass('button-secondary').addClass('button-primary');
				}
			}
		},
        
        popupHandler : function(event)
        {
            event.stopPropagation();
            event.preventDefault();

            var form_id, form_name, post_id, shortcode, form_page_id, parent_id;

            var el=$(this);
            if (el.is(':disabled') || el.attr('disabled'))
                return false;

            var mode=$('#cred-form-shortcodes-box-inner input.cred-shortcode-container-radio:checked');

            switch(mode.attr('id'))
            {
                case 'cred-post-creation-container':
                    form_id=$('#cred_form-new-shortcode-select').val();
                    form_name=$("option:selected",$('#cred_form-new-shortcode-select')).text();
                    if (!form_id) return false;
                    shortcode='[cred_form form="'+form_name+'"]';
                break;

                case 'cred-post-edit-container':
                    form_id=$('#cred_form-edit-shortcode-select').val();
                    form_name=$("#cred_form-edit-shortcode-select option:selected").text();
                    if (!form_id) return false;

                    //post_id=null;
                    switch($('#cred-post-edit-container-advanced input[name="cred-edit-what-to-edit"]:checked').val())
                    {
                        case 'edit-current-post':
                            post_id=null;
                        break;
                        case 'edit-other-post':
                            post_id=$('#cred-edit-post-select').val();
                            if (!post_id) return false;
                        break;
                        default: return false;
                    }
                    switch($('#cred-post-edit-container-advanced input[name="cred-edit-how-to-display"]:checked').val())
                    {
                        case 'insert-link':
                            var _class='',_target='_self',_style='', _text='', _more_atts='', _atts=[];
                            _class=$('#cred-edit-html-class').val();
                            _style=$('#cred-edit-html-style').val();
                            _text=$('#cred-edit-html-text').val();
                            _more_atts=$('#cred-edit-html-attributes').val();
                            _target=$('#cred-edit-html-target').val();
                            if (_class!='')
                                _atts.push('class="'+_class+'"');
                            if (_style!='')
                                _atts.push('style="'+_style+'"');
                            if (_text!='')
                                _atts.push('text="'+_text+'"');
                            if (_target!='')
                                _atts.push('target="'+_target+'"');
                            if (_more_atts!='')
                                _atts.push('attributes="'+_more_atts.split('"').join("%dbquo%").split("'").join("%quot%").split('=').join('%eq%')+'"');
                            if (_atts.length>0)
                                _atts=' '+_atts.join(' ');
                            else
                                _atts='';
                            if (post_id==null)
                                shortcode='[cred_link_form form="'+form_name+'"'+_atts+']';
                            else
                                shortcode='[cred_link_form form="'+form_name+'" post="'+post_id+'"'+_atts+']';
                        break;
                        case 'insert-form':
                            if (post_id==null)
                                shortcode='[cred_form form="'+form_name+'"]';
                            else
                                shortcode='[cred_form form="'+form_name+'" post="'+post_id+'"]';
                        break;
                        default: return false;
                    }
                break;

                case 'cred-post-child-link-container':
                    form_page_id=$('#cred-child-form-page').val();
                    if (form_page_id=='' || isNaN(new Number(form_page_id))) return false;

                    //post_id=null;
                    switch($('#_cred-post-child-link-container input[name="cred-post-child-parent-action"]:checked').val())
                    {
                        case 'current':
                            parent_id=-1;
                        break;
                        case 'form':
                            parent_id=null;
                        break;
                        case 'other':
                            parent_id=$('#cred_post_child_parent_id').val();
                            if (!parent_id || isNaN(new Number(parent_id))) return false;
                        break;
                        default: return false;
                    }
                    var _class='',_target='_self',_style='', _text='', _more_atts='', _atts=[], _post_type;
                    _class=$('#cred-child-html-class').val();
                    _style=$('#cred-child-html-style').val();
                    _text=$('#cred-child-link-text').val();
                    _more_atts=$('#cred-child-html-attributes').val();
                    _target=$('#cred-child-html-target').val();
                    //_post_type=$('#post_type').val(); // parent (current) post type
                    //_atts.push('parent_type="'+_post_type+'"');
                    if (_class!='')
                        _atts.push('class="'+_class+'"');
                    if (_style!='')
                        _atts.push('style="'+_style+'"');
                    if (_text!='')
                        _atts.push('text="'+_text+'"');
                    if (_target!='')
                        _atts.push('target="'+_target+'"');
                    if (_more_atts!='')
                        _atts.push('attributes="'+_more_atts.split('"').join("%dbquo%").split("'").join("%quot%").split('=').join('%eq%')+'"');
                    if (_atts.length>0)
                        _atts=' '+_atts.join(' ');
                    else
                        _atts='';
                    if (parent_id==null)
                        shortcode='[cred_child_link_form form="'+form_page_id+'"'+_atts+']';
                    else
                        shortcode='[cred_child_link_form form="'+form_page_id+'" parent_id="'+parent_id+'"'+_atts+']';
                break;

                case 'cred-post-delete-link-container':
                    var _class='',_style='', _text='', _refresh=true, _atts=[];
                    var _action='';
					var _message='';
                    _class=$('#cred-delete-html-class').val();
                    _style=$('#cred-delete-html-style').val();
                    _text=$('#cred-delete-html-text').val();
					_message=$('#cred-delete-html-message').val();
                    _refresh=$('#cred-refresh-after-action').is(':checked');
                    if (_refresh)
                        _class+=(''==_class)?'cred-refresh-after-delete':' cred-refresh-after-delete';
                    _action=$('#cred-post-delete-link-container-advanced input[name="cred-delete-delete-action"]:checked').val();
                    if (_class!='')
                        _atts.push('class="'+_class+'"');
                    if (_style!='')
                        _atts.push('style="'+_style+'"');
                    if (_text!='')
                        _atts.push('text="'+_text+'"');
                    if (_action!='')
                        _atts.push('action="'+_action+'"');
                    if (_message!='')
                        _atts.push('message="'+_message+'"');
                    if (_atts.length>0)
                        _atts=' '+_atts.join(' ');
                    else
                        _atts='';
                    if ($('#cred-post-delete-link-container-advanced input[name="cred-delete-what-to-delete"]:checked').val()=='delete-other-post')
                    {
                        post_id=$('#cred_post_delete_id').val();
                        shortcode='[cred_delete_post_link post="'+post_id+'"'+_atts+']';
                    }
                    else
                    {
                        shortcode='[cred_delete_post_link'+_atts+']';
                    }
                break;

                default: return false; break;
            }
            if (shortcode && shortcode!='')
            {
                utils.InsertAtCursor($('#content'),shortcode);
                utils.doDelayed(function(){
                    clearInterval(checkButtonTimer);
                    cred_media_buttons.css('z-index',1);
                    cred_popup_boxes.hide();
                });
            }
        },
        
        popupHandler2 : function(event)
        {
            event.stopPropagation();
            event.preventDefault();

            var form_id, form_name, post_id, shortcode, form_page_id, parent_id, error=false;

            var el=$(this);
            if (el.is(':disabled') || el.attr('disabled'))
                return false;

            var content=$(el.attr('data-content'));
            var $parent=el.closest('.cred-popup-box');
            var mode=$('input.cred-shortcode-container-radio:checked',$parent);

            //alert($parent.attr('class'));
            if (mode.hasClass('cred-post-creation-container2'))
            {
                form_id=$('.cred_form-new-shortcode-select2',$parent).val();
                form_name=$(".cred_form-new-shortcode-select2 option:selected",$parent).text();
                if (!form_id)
                    error='No Form';
                else
                    shortcode='[cred_form form="'+form_name+'"]';
            }
            else if (mode.hasClass('cred-post-edit-container2'))
            {
                form_id=$('.cred_form-edit-shortcode-select2',$parent).val();
                form_name=$(".cred_form-edit-shortcode-select2 option:selected",$parent).text();
                if (!form_id)                                
                    error='No Form';
                else
                {
                    //post_id=null;
                    switch($('.cred-post-edit-container-advanced2 input[name^="cred-edit-what-to-edit"]:checked',$parent).val())
                    {
                        case 'edit-current-post':
                            post_id=null;
                        break;
                        case 'edit-other-post':
                            post_id=$('.cred-edit-post-select2',$parent).val();
                            if (!post_id)
                            {
                                error='No Post';
                                break;
                            }

                        break;
                        default: 
                            error='No Option';
                        break;
                    }
                    if (!error)
                    {
                        switch($('.cred-post-edit-container-advanced2 input[name^="cred-edit-how-to-display"]:checked',$parent).val())
                        {
                            case 'insert-link':
                                var _class='',_target='_self',_style='', _text='', _more_atts='', _atts=[];
                                _class=$('.cred-edit-html-class2',$parent).val();
                                _style=$('.cred-edit-html-style2',$parent).val();
                                _text=$('.cred-edit-html-text2',$parent).val();
                                _more_atts=$('.cred-edit-html-attributes2',$parent).val();
                                _target=$('.cred-edit-html-target2',$parent).val();
                                if (_class!='')
                                    _atts.push('class="'+_class+'"');
                                if (_style!='')
                                    _atts.push('style="'+_style+'"');
                                if (_text!='')
                                    _atts.push('text="'+_text+'"');
                                if (_target!='')
                                    _atts.push('target="'+_target+'"');
                                if (_more_atts!='')
                                    _atts.push('attributes="'+_more_atts.split('"').join("%dbquo%").split("'").join("%quot%").split('=').join('%eq%')+'"');
                                if (_atts.length>0)
                                    _atts=' '+_atts.join(' ');
                                else
                                    _atts='';
                                if (null==post_id)
                                    shortcode='[cred_link_form form="'+form_name+'"'+_atts+']';
                                else
                                    shortcode='[cred_link_form form="'+form_name+'" post="'+post_id+'"'+_atts+']';
                            break;
                            case 'insert-form':
                                if (null==post_id)
                                    shortcode='[cred_form form="'+form_name+'"]';
                                else
                                    shortcode='[cred_form form="'+form_name+'" post="'+post_id+'"]';
                            break;
                            default: 
                                error='No Option';
                            break;
                        }
                    }
                }
            }
            else if (mode.hasClass('cred-post-child-link-container2'))
            {
                form_page_id=$('.cred-child-form-page2',$parent).val();
                if (form_page_id=='' || isNaN(new Number(form_page_id)))
                    error='No Form Page';
                else
                {
                    //post_id=null;
                    switch($('._cred-post-child-link-container2 input[name^="cred-post-child-parent-action"]:checked',$parent).val())
                    {
                        case 'current':
                            parent_id=-1;
                        break;
                        case 'form':
                            parent_id=null;
                        break;
                        case 'other':
                            parent_id=$('.cred_post_child_parent_id2',$parent).val();
                            if (!parent_id || isNaN(new Number(parent_id)))
                            {
                                error='No Parent';
                                break;
                            }
                        break;
                        default:
                            error='No Option';
                        break;
                    }
                    if (!error)
                    {
                        var _class='',_target='_self',_style='', _text='', _more_atts='', _atts=[], _post_type;
                        _class=$('.cred-child-html-class2',$parent).val();
                        _style=$('.cred-child-html-style2',$parent).val();
                        _text=$('.cred-child-link-text2',$parent).val();
                        _more_atts=$('.cred-child-html-attributes2',$parent).val();
                        _target=$('.cred-child-html-target2',$parent).val();
                        //_post_type=$('#post_type').val(); // parent (current) post type
                        //_atts.push('parent_type="'+_post_type+'"');
                        if (_class!='')
                            _atts.push('class="'+_class+'"');
                        if (_style!='')
                            _atts.push('style="'+_style+'"');
                        if (_text!='')
                            _atts.push('text="'+_text+'"');
                        if (_target!='')
                            _atts.push('target="'+_target+'"');
                        if (_more_atts!='')
                            _atts.push('attributes="'+_more_atts.split('"').join("%dbquo%").split("'").join("%quot%").split('=').join('%eq%')+'"');
                        if (_atts.length>0)
                            _atts=' '+_atts.join(' ');
                        else
                            _atts='';
                        if (parent_id==null)
                            shortcode='[cred_child_link_form form="'+form_page_id+'"'+_atts+']';
                        else
                            shortcode='[cred_child_link_form form="'+form_page_id+'" parent_id="'+parent_id+'"'+_atts+']';
                    }
                }
            }
            else if (mode.hasClass('cred-post-delete-link-container2'))
            {
                var _class='',_style='', _text='', _refresh=true, _atts=[];
                var _action='';
                _class=$('.cred-delete-html-class2',$parent).val();
                _style=$('.cred-delete-html-style2',$parent).val();
                _text=$('.cred-delete-html-text2',$parent).val();
                _refresh=$('.cred-refresh-after-action',$parent).is(':checked');
                if (_refresh)
                    _class+=(''==_class)?'cred-refresh-after-delete':' cred-refresh-after-delete';
                _action=$('.cred-post-delete-link-container-advanced2 input[name^="cred-delete-delete-action"]:checked',$parent).val();
                if (_class!='')
                    _atts.push('class="'+_class+'"');
                if (_style!='')
                    _atts.push('style="'+_style+'"');
                if (_text!='')
                    _atts.push('text="'+_text+'"');
                if (_action!='')
                    _atts.push('action="'+_action+'"');
                if (_atts.length>0)
                    _atts=' '+_atts.join(' ');
                else
                    _atts='';

                if ($('.cred-post-delete-link-container-advanced2 input[name^="cred-delete-what-to-delete"]:checked',$parent).val()=='delete-other-post')
                {
                    post_id=$('.cred_post_delete_id',$parent).val();
                    shortcode='[cred_delete_post_link post="'+post_id+'"'+_atts+']';
                }
                else
                {
                    shortcode='[cred_delete_post_link'+_atts+']';
                }
            }
            else
            {
                error='No Option';
            }
            if (error)
            {
                //alert(error);
                //console.log(error);
                return false;
            }
            if (shortcode && ''!=shortcode)
            {
                utils.InsertAtCursor(content, shortcode);
                utils.doDelayed(function(){
                    clearInterval(checkButtonTimer);
                    cred_media_buttons.css('z-index',1);
                    cred_popup_boxes.hide();
                });
                return false;
            }
        }
    };
	
    // public methods / properties
    var self = {
        
        // add the extra Modules as part of main CRED Module
        app : utils,
        gui : gui,
        settings : settings,
        
        route : function(path, params, raw)
        {
            return utils.route('cred', settings.ajaxurl, path, params, raw);
        },
        
        getContents : function()
        {
            return {
                'content' : utils.getContent($('#content')),
                'cred-extra-css-editor' : utils.getContent($('#cred-extra-css-editor')),
                'cred-extra-js-editor' : utils.getContent($('#cred-extra-js-editor'))
            };
        },

        posts : function()
        {
                cred_media_buttons=$('.cred-media-button');
                cred_popup_boxes=$('.cred-popup-box');
                var new_select_options=$('#cred_form-new-shortcode-select').find('option'),
                    edit_select_options=$('#cred_form-edit-shortcode-select').find('option'),
                    advanced_options=$('.cred-shortcodes-container-advanced');

            // show / hide advanced options and links
            advanced_options.each(function(){
                $(this).hide();
                $('.cred-show-hide-advanced',$(this).parent()).text(settings.locale.show_advanced_options);
            });

            // hide loaders
            $('.cred_ajax_loader_small').hide();

            advanced_options.filter(function(){
                if ($(this).hasClass('cred-show'))
                    return true;
                return false;
            }).each(function(){
                $(this).show();
                $('.cred-show-hide-advanced',$(this).parent()).text(settings.locale.hide_advanced_options);
            });

            cred_popup_boxes.on('click', '.cred-show-hide-advanced', function(){
                var adv_option=$('.cred-shortcodes-container-advanced',$(this).parent());

                if (adv_option.hasClass('cred-show'))
                {
                    adv_option.removeClass('cred-show');
                    adv_option.stop().slideFadeUp('slow','quintEaseIn');
                    $(this).text(settings.locale.show_advanced_options);
                }
                else
                {
                    adv_option.addClass('cred-show');
                    adv_option.stop().slideFadeDown('slow','quintEaseOut');
                    $(this).text(settings.locale.hide_advanced_options);
                }

            });

            $('#cred-form-shortcodes-box').on('change','#cred_form-edit-shortcode-select',function(event){
                event.stopPropagation();
                var form_id=$(this).val();
                var form_name=$("option:selected",$(this)).text();
                var loader=$('#cred-form-addtional-loader').show();
                $.ajax({
                    url: self.route('/Posts/getPosts?form_id='+form_id),
                    timeout: 10000,
                    type: 'GET',
                    data: '',
                    dataType: 'html',
                    success: function(result)
                    {
                        $('#cred-edit-post-select').html(result);
                        loader.hide();
                    },
                    error: function()
                    {
                        loader.hide();
                    }
                });
            });

            $('.cred-form-shortcodes-box2').on('change','.cred_form-edit-shortcode-select2',function(event){
                event.stopPropagation();
                var $parent=$(this).closest('.cred-form-shortcodes-box2');
                var form_id=$(this).val();
                var form_name=$("option:selected",$(this)).text();
                $('.cred-form-addtional-loader2',$parent).show();
                $.ajax({
                    url: self.route('/Posts/getPosts?form_id='+form_id),
                    timeout: 10000,
                    type: 'GET',
                    data: '',
                    dataType: 'html',
                    success: function(result)
                    {
                        $('.cred-edit-post-select2',$parent).html(result);
                        $('.cred-form-addtional-loader2',$parent).hide();
                    },
                    error: function()
                    {
                        $('.cred-form-addtional-loader2',$parent).hide();
                    }
                });
            });

            $('#cred-child-form-page, .cred-child-form-page2, #cred_post_child_parent_id, .cred_post_child_parent_id2')
            .cred_suggest(self.route('/Posts/suggestPostsByTitle'), {
                delay: 200,
                minchars: 3,
                multiple: false,
                multipleSep: '',
                resultsClass : 'ac_results',
                selectClass : 'ac_over',
                matchClass : 'ac_match',
                onStart : function(){$('#cred-form-suggest-child-form-loader').show();},
                onComplete : function() {$('#cred-form-suggest-child-form-loader').hide();}
            });

            // preselect options if only one of them
            if (new_select_options.length==2)
            {
                new_select_options.eq(0).removeAttr('selected');
                new_select_options.eq(1).attr('selected','selected');
            }
            if (edit_select_options.length==2)
            {
                edit_select_options.eq(0).removeAttr('selected');
                edit_select_options.eq(1).attr('selected','selected');
                edit_select_options.eq(1).closest('select').trigger('change');
            }
            // no new form exists
            if (new_select_options.length==1)
            {
                var rel=$('#cred-form-shortcode-types-select-container #cred-post-creation-container');
                rel.attr('disabled','disabled');
                rel.closest('td').append('<span class="cred-warn">'+settings.locale.create_new_content_form+'</span>');
            }
            // no edit form exist
            if (edit_select_options.length==1)
            {
                var rel=$('#cred-form-shortcode-types-select-container #cred-post-edit-container');
                rel.attr('disabled','disabled');
                rel.closest('td').append('<span class="cred-warn">'+settings.locale.create_edit_content_form+'</span>');
            }

            // hide shortcode details areas
            $('.cred-shortcodes-container').hide();
            $('#cred-form-addtional-loader').hide();
            $('.cred-form-addtional-loader2').hide();
            $('#cred-form-shortcode-types-select-container .cred-shortcode-container-radio').each(function(){
                this.checked = false;
            });


            // hide/show areas according to
            $('#cred-form-shortcode-types-select-container').on('change','.cred-shortcode-container-radio',function(event){
                   var el=$(this);
                   if (el.is(':disabled'))
                   {
                        return false;
                   }
                   if (el.is(':checked'))
                   {
                        $('.cred-shortcodes-container').hide();
                        $('#_'+el.attr('id')).stop().slideFadeDown('slow','quintEaseOut');
                   }
            });

            // hide/show areas according to
            $('.cred-form-shortcode-types-select-container2').on('change','.cred-shortcode-container-radio',function(event){
                   var el=$(this);
                   if (el.is(':disabled'))
                   {
                        return false;
                   }
                   if (el.is(':checked'))
                   {
                        var el_class=el.attr('class');
                        el_class=el_class.replace('cred-shortcode-container-radio','').replace('cred-radio-10','').replace(/\s+/g,'');
                        $('.cred-shortcodes-container').hide();
                        $('._'+el_class).stop().slideFadeDown('slow','quintEaseOut');
                   }
            });

            $('#cred-post-edit-container-advanced input[name="cred-edit-how-to-display"]').change(function(){
                if ($(this).is(':checked') && $(this).val()=='insert-link')
                {
                    $('#cred-edit-html-fieldset').show();
                    $('#cred-edit-html-single-fieldset').show();
                }
                else
                {
                    $('#cred-edit-html-fieldset').hide();
                    $('#cred-edit-html-single-fieldset').hide();
                }
            });

            $('.cred-post-edit-container-advanced2 input[name="cred-edit-how-to-display"]').change(function(){
                var $parent=$(this).closest('.cred-form-shortcode-button2');

                if ($(this).is(':checked') && $(this).val()=='insert-link')
                {
                    $('.cred-edit-html-fieldset2',$parent).show();
                    $('.cred-edit-html-single-fieldset2',$parent).show();
                }
                else
                {
                    $('.cred-edit-html-fieldset2',$parent).hide();
                    $('.cred-edit-html-single-fieldset2',$parent).hide();
                }
            });

            // insert shortcode button handler
            $('#cred-insert-shortcode').click(aux.popupHandler);

            $('.cred-insert-shortcode2').click(aux.popupHandler2);

            $(document).on('click','#cred-form-shortcode-button-button',function(event){
                event.stopPropagation();
                event.preventDefault();
                cred_media_buttons.css('z-index',1);
                cred_popup_boxes.hide();

                $(this).closest('.cred-media-button').css('z-index',100);
                $('#cred-form-shortcodes-box').__show();

                aux.checkButton();
                checkButtonTimer=setInterval(function(){
                    aux.checkButton();
                },500);
            });

            $(document).on('click','.cred-form-shortcode-button-button2,.js-code-editor-toolbar-button-cred-icon',function(event){
                event.stopPropagation();
                event.preventDefault();
                cred_media_buttons.css('z-index',1);
                cred_popup_boxes.hide();

                $(this).closest('.cred-media-button').css('z-index',100);
                $('.cred-form-shortcodes-box2',$(this).closest('.cred-media-button')).__show();

                var $parent=$(this).closest('.cred-form-shortcode-button2');
                aux.checkButton2($parent);
                checkButtonTimer=setInterval(function(){
                    aux.checkButton2($parent);
                },500);
            });

            $('#cred-form-shortcodes-box').on('click','#cred-popup-cancel',function(event){
                event.preventDefault();
                utils.doDelayed(function(){
                    clearInterval(checkButtonTimer);
                    cred_media_buttons.css('z-index',1);
                    cred_popup_boxes.hide();
                });
            });

            $('.cred-form-shortcodes-box2').on('click','.cred-popup-cancel2',function(event){
                event.preventDefault();
                utils.doDelayed(function(){
                    clearInterval(checkButtonTimer);
                    cred_media_buttons.css('z-index',1);
                    cred_popup_boxes.hide();
                });
            });

            //$('html').click(function(){
            $(document).click/*mouseup*/(function (e){
                if (
                    !e._cred_specific &&
                    cred_popup_boxes.filter(function(){
                        return $(this).is(':visible');
                        }).has(e.target).length === 0
                    )
                {
                    utils.doDelayed(function(){
                        clearInterval(checkButtonTimer);
                        cred_media_buttons.css('z-index',1);
                        cred_popup_boxes.hide();
                    }, true);
                }
            });

            $(document).keyup(function(e) {
                if (e.keyCode == KEYCODE_ESC)
                {
                    utils.doDelayed(function(){
                        clearInterval(checkButtonTimer);
                        cred_media_buttons.css('z-index',1);
                        cred_popup_boxes.hide();
                    });
                }
            });

            // cancel buttons
            $(document).on('click','.cred-cred-cancel-close',function(event){
                utils.doDelayed(function(){
                    clearInterval(checkButtonTimer);
                    cred_media_buttons.css('z-index',1);
                    cred_popup_boxes.hide();
                });
            });
        }
    };
    
    // make public methods/properties available
    window.cred_cred=self;
    
    
})(window, jQuery, cred_settings, cred_utils, cred_gui);