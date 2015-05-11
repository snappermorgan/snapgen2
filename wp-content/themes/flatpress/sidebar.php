<?php
/**
 * Main Widget Template
 *
 *
 * @file           sidebar.php
 * @package        FlatPress 
 * @author         Brad Williams 
 * @copyright      2011 - 2013 Brag Interactive
 * @license        license.txt
 * @version        Release: 0.0.1
 * @link           http://codex.wordpress.org/Theme_Development#Widgets_.28sidebar.php.29
 * @since          available since Release 1.0
 */
?>
        <div class="span5">
        <div id="widgets">
        
        <?php responsive_widgets(); // above widgets hook ?>
            
            

        <?php responsive_widgets_end(); // after widgets hook ?>
    		</div><!-- end of #widgets -->
        </div> <!-- end of .span3 -->
    </div> <!-- end of .span9 -->