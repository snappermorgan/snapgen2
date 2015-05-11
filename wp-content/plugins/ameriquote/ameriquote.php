<?php
/*
Plugin Name: Ameriquote
Plugin URI: https://radaralley.com
Description: Integrates the Compulife quote engine into Snapgen
Author: Snapper Morgan
Version: 1.0
Author URI: http://radaralley.com
Changelog:
1.0 - Initial Release.
*/


	add_filter('init', 'generateJson');
	
	
	function generateJson(){
		
		if(isset($_REQUEST['widget']) && $_REQUEST['widget']=='quotes'){
			
			if(!isset($_REQUEST['callback'])){
				ob_start();
				echo "You must include a callback parameter in the querystring";
				
			ob_end_flush();
			exit(0);
			}else{
			    $html = file_get_contents(__DIR__.'/webapp.inc',false);
			    
			    $json = json_encode(array("html"=>$html));
			    
			    $callback = $_REQUEST['callback'];
			    
			    $txt = $callback."(".$json.");";
			    Header("content-type: text/javascript");
			    ob_start();
			    
					echo $txt;
					
				ob_end_flush();
				exit(0);
			}
		}else{
			return true;
		}
	}
