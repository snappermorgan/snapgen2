<?php
/**
 * Template for site footer
 * @package themify
 * @since 1.0.0
 */
?>
<?php
/** Themify Default Variables
 @var object */
	global $themify; ?>

	<?php themify_layout_after(); //hook ?>
    </div>
	<!-- /body -->
		
	<div id="footerwrap">
    
    	<?php themify_footer_before(); // hook ?>
		<footer id="footer" class="pagewidth clearfix">
			<?php themify_footer_start(); // hook ?>	

			<?php get_template_part( 'includes/footer-widgets'); ?>
	
			<p class="back-top">
				<a href="#header">
					<img src="<?php echo THEME_URI . '/images/back-top.png'; ?>" alt="<?php _e('Back to Top', 'themify'); ?>">
				</a>
			</p>
	
			<div class="footer-text clearfix">
				<?php themify_the_footer_text(); ?>
				<?php themify_the_footer_text('right'); ?>
			</div>
			<!-- /footer-text --> 
			<?php themify_footer_end(); // hook ?>
		</footer>
		<!-- /#footer --> 
        <?php themify_footer_after(); // hook ?>
	</div>
	<!-- /#footerwrap -->
	
</div>
<!-- /#pagewrap -->

<?php
/**
 *  Stylesheets and Javascript files are enqueued in theme-functions.php
 */
?>

<?php themify_body_end(); // hook ?>
<!-- wp_footer -->
<?php wp_footer(); ?>

</body>
</html>