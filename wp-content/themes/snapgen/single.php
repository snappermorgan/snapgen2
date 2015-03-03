<?php
/**
 * The template for displaying all single posts and attachments
 *
 * @package WordPress
 * @subpackage Twenty_Fifteen
 * @since Twenty Fifteen 1.0
 */

get_header(); ?>

<section id="hero-4">
            	<?php
				if ( is_active_sidebar( 'hero' ) ) : ?>
				<div class="container">
                	<div class="row">
						<div id="widget-area" class="widget-area" role="complementary">
							<?php dynamic_sidebar( 'hero' ); ?>
						</div><!-- .widget-area -->
						</div></div>
					<?php endif; ?>
            </section>
	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<?php
		// Start the loop.
		while ( have_posts() ) : the_post();

			/*
			 * Include the post format-specific template for the content. If you want to
			 * use this in a child theme, then include a file called called content-___.php
			 * (where ___ is the post format) and that will be used instead.
			 */
			get_template_part( 'content', get_post_format() );

		

		// End the loop.
		endwhile;
		?>

		</main><!-- .site-main -->
	</div><!-- .content-area -->

<?php get_footer(); ?>
