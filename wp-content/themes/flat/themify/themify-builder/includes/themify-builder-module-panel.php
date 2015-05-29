<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Builder Frontend Panel HTML
 */
?>

<div class="themify_builder themify_builder_front_panel">	
	<div id="themify_builder_module_panel" class="themify_builder_module_panel clearfix">
		
		<a class="slide_builder_module_panel" href="#"><?php _e('Slide', 'themify') ?></a>

		<div class="slide_builder_module_wrapper">
		<?php foreach( $this->modules as $module_key => $module_settings ): ?>
		<?php $class = "themify_builder_module module-{$module_key}"; ?>

		<div class="<?php echo esc_attr($class); ?>" data-module-name="<?php echo esc_attr( $module_key ); ?>">
			<strong class="module_name"><?php echo esc_html( $module_settings['name'] ); ?></strong>
			<a href="#" title="<?php _e('Add module', 'themify') ?>" class="add_module" data-module-name="<?php echo esc_attr( $module_key ); ?>"><?php _e('Add', 'themify') ?></a>
		</div>
		<!-- /module -->
		<?php endforeach; ?>
		</div>
		<!-- /slide_builder_module_wrapper -->

		<div class="builder_save_front_panel">
			<a href="#" class="themify-builder-front-save"><?php _e('Save', 'themify') ?></a>
			<a href="#" class="themify-builder-front-close"><?php _e('Close', 'themify') ?></a>
		</div>

	</div>
	<!-- /themify_builder_module_panel -->
</div>

<div style="display: none;">
	<?php
		wp_editor( ' ', 'tfb_lb_hidden_editor' );
	?>
</div>