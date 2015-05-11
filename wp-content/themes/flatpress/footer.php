<?php
/**
 * Footer Template
 *
 *
 * @file           footer.php
 * @package        FlatPress 
 * @author         Brad Williams 
 * @copyright      2011 - 2013 Brag Interactive
 * @license        license.txt
 * @version        Release: 0.0.1
 * @link           http://codex.wordpress.org/Theme_Development#Footer_.28footer.php.29
 * @since          available since Release 1.0
 */
?>
    </div><!-- end of wrapper-->
    <?php responsive_wrapper_end(); // after wrapper hook ?>
    
   
</div><!-- end of container -->
 <?php responsive_container_end(); // after container hook ?>

<div id="footer" class="clearfix">

  <div class="container">
        <div class="row">
          <div class="span7">
            <?php if (!dynamic_sidebar('left-footer')) : ?>
            
                <h3 class="footer-title"><?php _e('Footer Widget', 'responsive'); ?></h3>
          <p>
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam et sapien orci, quis placerat diam. 
            Suspendisse pretium felis vel purus venenatis tincidunt. Nunc at lacus vitae ligula fermentum commodo. 
            Curabitur orci dui, imperdiet vel <a href="#">tristique congue</a>, gravida sit amet diam. 
            Nulla adipiscing pellentesque est, a ultrices nisi consequat a. 
            Donec placerat gravida iaculis. Aenean molestie est sed orci blandit pretium.
          </p>

            <?php endif; //end of main-sidebar ?>
          </div> <!-- /span7 -->

          <div class="span5">
            <div class="footer-banner">
                <?php if (!dynamic_sidebar('right-footer')) : ?>
            
                <h3 class="footer-title"><?php _e('Right Footer Widget', 'responsive'); ?></h3>
                  <ul>
                    <li>List item here </li>
                    <li>List item here </li>
                    <li>List item with <a href="#">link</a> </li>
                    <li>List item here </li>
                    <li>List item here </li>
                    <li>List item here </li>
                  </ul>

            <?php endif; //end of main-sidebar ?>
            </div>
          </div>
        </div>
      </div>

  </div>  <!-- end #footer -->
</div>

<?php wp_footer(); ?>

</body>
</html>