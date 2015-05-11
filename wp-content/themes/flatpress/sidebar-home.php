<?php
/**
 * Home Widgets Template
 *
 *
 * @file           sidebar-home.php
 * @package        StrapPress 
 * @author         Brad Williams 
 * @copyright      2011 - 2012 Brag Interactive
 * @license        license.txt
 * @version        Release: 2.3.0
 * @link           http://codex.wordpress.org/Theme_Development#Widgets_.28sidebar.php.29
 * @since          available since Release 1.0
 */
?>
    <div id="tiles" class="home-tiles">
        <div class="row demo-tiles">
        <div class="span3">
        
        <?php responsive_widgets(); // above widgets hook ?>
            
            <?php if (!dynamic_sidebar('home-widget-1')) : ?>
            
            <div class="tile">
                <img class="tile-image big-illustration" alt="" src="<?php echo get_stylesheet_directory_uri() ?>/images/illustrations/compass.png">
                <h3 class="tile-title">Responsive</h3>
                <p>Flat UI integrated into a responsive WordPress theme!</p>
                <a class="btn btn-large btn-block" href="#">Button</a>
            </div>

			<?php endif; //end of home-widget-1 ?>

        <?php responsive_widgets_end(); // responsive after widgets hook ?>
        </div><!-- end of .span3 -->

        <div class="span3">
        <?php responsive_widgets(); // responsive above widgets hook ?>
            
			<?php if (!dynamic_sidebar('home-widget-2')) : ?>
            
            <div class="tile">
            <img class="tile-image" alt="" src="<?php echo get_stylesheet_directory_uri() ?>/images/illustrations/infinity.png">
            <h3 class="tile-title">Easy to Customize</h3>
            <p>Theme Options panel to change content and customize the theme.</p>
            <a class="btn btn-large btn-block" href="#">Button</a>
          </div>

			<?php endif; //end of home-widget-2 ?>
            
            <?php responsive_widgets_end(); // after widgets hook ?>
        </div><!-- end of .span3 -->

        <div class="span3">
        <?php responsive_widgets(); // above widgets hook ?>
            
            <?php if (!dynamic_sidebar('home-widget-3')) : ?>
            
                <div class="tile">
            <img class="tile-image" alt="" src="<?php echo get_stylesheet_directory_uri() ?>/images/illustrations/colors.png">
            <h3 class="tile-title">Flat UI</h3>
            <p>Easy to add or change elements within the WordPress Dashboard. </p>
            <a class="btn btn-large btn-block" href="#">Button</a>
          </div>


		    <?php endif; //end of home-widget-3 ?>
            
        <?php responsive_widgets_end(); // after widgets hook ?>
        </div><!-- end of .span3 -->

            <div class="span3">
        <?php responsive_widgets(); // above widgets hook ?>
            
            <?php if (!dynamic_sidebar('home-widget-4')) : ?>
            
            <div class="tile tile-hot">
            <img class="tile-image big-illustration" alt="" src="<?php echo get_stylesheet_directory_uri() ?>/images/illustrations/share.png">
            <h3 class="tile-title">Social Ready</h3>
            <p>Add social network icons in the header of your website.</p>
            <a class="btn btn-large btn-block" href="#">Button</a>
          </div>
                
            <?php endif; //end of home-widget-4 ?>
            
        <?php responsive_widgets_end(); // after widgets hook ?>
        </div><!-- end of .span3 -->
        </div>
    </div><!-- end of #widgets -->