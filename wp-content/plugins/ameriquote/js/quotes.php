<?php
Header("content-type: text/javascript");
?>

    
    
(function() {
    document.write('<div id="ameriquote_wrapper" style="display:none;"></div>');
        var script_tag = document.createElement('script');
        script_tag.setAttribute("type","text/javascript");
        script_tag.setAttribute("src",
            "http://quotes.ameriquote.com/wp-content/plugins/ameriquote/js/head.min.js");
        if (script_tag.readyState) {
          script_tag.onreadystatechange = function () { // For old versions of IE
              if (this.readyState == 'complete' || this.readyState == 'loaded') {
                  scriptLoadHandler();
              }
          };
        } else {
          script_tag.onload = scriptLoadHandler;
        }
        // Try to find the head, otherwise default to the documentElement
        (document.getElementsByTagName("body")[0] || document.documentElement).appendChild(script_tag);
    
        main();
    

    /******** Called once jQuery has loaded ******/
    function scriptLoadHandler() {
        // Restore $ and window.jQuery to their previous values and store the
        // new jQuery in our local jQuery variable
        jQuery = window.jQuery.noConflict(true);
        // Call our main function
        main(); 
    }

    /******** Our main function ********/
    function main() { 
        jQuery(document).ready(function($) { 
            /******* Load CSS *******/
            var css_link = $("<link>", { 
                rel: "stylesheet", 
                type: "text/css", 
                href: "http://quotes.ameriquote.com/wp-content/plugins/ameriquote/css/styles.css" 
            });
                var fontawesome_link = $("<link>", { 
                rel: "stylesheet", 
                type: "text/css", 
                href: "//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" 
            });
            
           
            css_link.appendTo('head');          
            fontawesome_link.appendTo('head'); 
            /******* Load HTML *******/
            var jsonp_url = "http://quotes.ameriquote.com/?widget=quotes&callback=?";
            $.getJSON(jsonp_url, function(data) {
             $('#ameriquote_wrapper').html(data.html);
             
             head.ready(document,function(){
                  head.load("http://quotes.ameriquote.com/wp-content/plugins/ameriquote/js/jquery.js",function(){
                      head.load("http://quotes.ameriquote.com/wp-content/plugins/ameriquote/js/flat-ui.js","http://quotes.ameriquote.com/wp-content/plugins/ameriquote/js/application.js","http://quotes.ameriquote.com/wp-content/plugins/ameriquote/js/knockout/knockout.js","http://quotes.ameriquote.com/wp-content/plugins/ameriquote/js/jStorage/jstorage.js","http://quotes.ameriquote.com/wp-content/plugins/ameriquote/js/isotope/isotope.js","http://quotes.ameriquote.com/wp-content/plugins/ameriquote/js/underscore/underscore.js","http://quotes.ameriquote.com/wp-content/plugins/ameriquote/js/query-string.js","http://quotes.ameriquote.com/wp-content/plugins/ameriquote/js/loading-overlay.js","http://quotes.ameriquote.com/wp-content/plugins/ameriquote/js/main.js",function(){
                        parsed = queryString.parse(location.search);
                     
        
       
        
        if(parsed.cpa_phone){
            ameriquote_phone = format_telephone(parsed.cpa_phone)[0];
            
        }
        
        if(parsed.aff_id){
            ameriquote_aff_id = parsed.aff_id;
        }
        
        if(parsed.ameriquote_transaction_id){
            ameriquote_transaction_id = parsed.ameriquote_transaction_id;
        }
                      });
                  });
             
             });
             
              if(head.mobile){
                  head.feature("mobile",true);   
              }
            if(head.desktop){
              head.feature("desktop",true);
            }
            
               ameriquote_phone = format_telephone("<?php echo ($_GET['cpa_phone']?$_GET['cpa_phone']:'8883994129');?>")[0];
    	                ameriquote_aff_id = <?php echo ($_GET['aff_id']?$_GET['aff_id']:'');?>;
    	                ameriquote_transaction_id = <?php echo ($_GET['transaction_id']?$_GET['transaction_id']:'8883994129');?>;
       
    	
    	
            });
        });
    }

    })(); // We call our anonymous function immediately

    
    var ameriquote_phone="";
    var ameriquote_transaction_id="";
    var ameriquote_aff_id = "";
    	format_telephone = function (phone_number)
{
    var cleaned = phone_number.replace('/[^[:digit:]]/g', '');
   matches = cleaned.match('([0-9]{3})([0-9]{3})([0-9]{4})');
    return matches;
}