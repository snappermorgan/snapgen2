<?php
/**
 * The template used for displaying page content
 *
 * @package WordPress
 * @subpackage Twenty_Fifteen
 * @since Twenty Fifteen 1.0
 */
?>
<section id="features">
    <div class="container">
        <div class="row">
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			
			
				<div class="entry-content">
					<?php the_content(); ?>
				
				</div><!-- .entry-content -->
			
				<?php edit_post_link( __( 'Edit', 'snapgen' ), '<footer class="entry-footer"><span class="edit-link">', '</span></footer><!-- .entry-footer -->' ); ?>
			
			</article><!-- #post-## -->
			        		
			        	
        	
        	
        </div>
    </div>
</section>

