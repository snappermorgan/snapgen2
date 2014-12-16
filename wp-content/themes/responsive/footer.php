<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Footer Template
 *
 *
 * @file           footer.php
 * @package        Responsive
 * @author         Emil Uzelac
 * @copyright      2003 - 2014 CyberChimps
 * @license        license.txt
 * @version        Release: 1.2
 * @filesource     wp-content/themes/responsive/footer.php
 * @link           http://codex.wordpress.org/Theme_Development#Footer_.28footer.php.29
 * @since          available since Release 1.0
 */

/*
 * Globalize Theme options
 */
global $responsive_options;
$responsive_options = responsive_get_options();
?>
<?php responsive_wrapper_bottom(); // after wrapper content hook ?>
<?php if(!is_singular('landing_page')): //if is not landing page?>
</div><!-- end of #wrapper -->
<?php endif;?>
<?php responsive_wrapper_end(); // after wrapper hook ?>
<?php if(!is_singular('landing_page')): //if is not landing page?>
</div><!-- end of #container -->
<?php endif;?>
<?php responsive_container_end(); // after container hook ?>
<?php if(!is_singular('landing_page')): //if is not landing page?>
<div id="footer" class="clearfix">
<?php endif;?>
	<?php responsive_footer_top(); ?>
<?php if(!is_singular('landing_page')): //if is not landing page?>
	<div id="footer-wrapper">
<?php endif;?>
		<?php get_sidebar( 'footer' ); ?>

		<div class="grid col-940">

			<div class="grid col-540">
				<?php if ( has_nav_menu( 'footer-menu', 'responsive' ) ) {
					wp_nav_menu( array(
						'container'      => '',
						'fallback_cb'    => false,
						'menu_class'     => 'footer-menu',
						'theme_location' => 'footer-menu'
					) );
				} ?>
			</div><!-- end of col-540 -->

			<div class="grid col-380 fit">
				<?php echo responsive_get_social_icons() ?>
			</div><!-- end of col-380 fit -->

		</div><!-- end of col-940 -->
		<?php get_sidebar( 'colophon' ); ?>

		<div class="grid col-300 copyright">
			
		</div><!-- end of .copyright -->

		<div class="grid col-300 scroll-top"><a href="#scroll-top" title="<?php esc_attr_e( 'scroll to top', 'responsive' ); ?>"><?php _e( '&uarr;', 'responsive' ); ?></a></div>

		<div class="grid col-300 fit powered">
			
		</div><!-- end .powered -->

	</div><!-- end #footer-wrapper -->

	<?php responsive_footer_bottom(); ?>
<?php if(!is_singular('landing_page')): //if is not landing page?>
</div><!-- end #footer -->
<?php endif;?>
<?php responsive_footer_after(); ?>

<?php wp_footer(); ?>
</body>
</html>
