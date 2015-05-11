<?php
/**
 * Sitemap Template
 *
   Template Name: Sitemap
 *
 * @file           sitemap.php
 * @package        FlatPress 
 * @author         Brad Williams 
 * @copyright      2011 - 2013 Brag Interactive
 * @license        license.txt
 * @version        Release: 0.0.1
 * @link           http://codex.wordpress.org/Templates
 * @since          available since Release 1.0
 */
?>
<?php get_header(); ?>

<div class="row">
        <div class="span12">

        <div id="content-sitemap">
        
<?php if (have_posts()) : ?>

		<?php while (have_posts()) : the_post(); ?>
        
        

        <?php if(of_get_option('breadcrumbs', '1')) {?>
        <?php echo responsive_breadcrumb_lists(); ?>
        <?php } ?>
        
            <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <div class="page-header">
                <h1 class="sitemap-title"><?php the_title(); ?></h1> 
            </div>

        
                
                <div class="post-entry">
                <div id="widgets">
                <div class="row-fluid">
                    <div class="span4">
                        <div class="widget-title"><?php _e('Categories', 'responsive'); ?></div>
                            <ul><?php wp_list_categories('sort_column=name&optioncount=1&hierarchical=0&title_li='); ?></ul>
                    </div><!-- end of .col-300 -->
                    
                    <div class="span4">
                        <div class="widget-title"><?php _e('Latest Posts', 'responsive'); ?></div>
                            <ul><?php $archive_query = new WP_Query('posts_per_page=-1');
                                    while ($archive_query->have_posts()) : $archive_query->the_post(); ?>
                                        <li>
                                            <a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf(__('Permanent Link to %s', 'responsive'), the_title_attribute('echo=0')); ?>"><?php the_title(); ?></a>
                                        </li>
                                    <?php endwhile; ?>
                            </ul>  
                    </div><!-- end of .col-300 -->
                     
                    <div class="span4">
                          <div class="widget-title"><?php _e('Pages', 'responsive'); ?></div>
                            <ul><?php wp_list_pages("title_li=" ); ?></ul>               
                    </div><!-- end of .col-300 fit -->
                
                </div><!-- end of #widgets --> 
            </div>
                   <?php custom_link_pages(array(
                            'before' => '<div class="pagination"><ul>' . __(''),
                            'after' => '</ul></div>',
                            'next_or_number' => 'next_and_number', # activate parameter overloading
                            'nextpagelink' => __('&rarr;'),
                            'previouspagelink' => __('&larr;'),
                            'pagelink' => '%',
                            'echo' => 1 )
                            ); ?>     
                </div><!-- end of .post-entry -->             
            
            <div class="post-edit"><?php edit_post_link(__('Edit', 'responsive')); ?></div>  
            </div><!-- end of #post-<?php the_ID(); ?> -->
            
        <?php endwhile; ?> 
        
        <?php if (  $wp_query->max_num_pages > 1 ) : ?>
        <div class="navigation">
			<div class="previous"><?php next_posts_link( __( '&#8249; Older posts', 'responsive' ) ); ?></div>
            <div class="next"><?php previous_posts_link( __( 'Newer posts &#8250;', 'responsive' ) ); ?></div>
		</div><!-- end of .navigation -->
        <?php endif; ?> 

	    <?php else : ?>

        <h1 class="title-404"><?php _e('404 &#8212; Fancy meeting you here!', 'responsive'); ?></h1>
        <p><?php _e('Don&#39;t panic, we&#39;ll get through this together. Let&#39;s explore our options here.', 'responsive'); ?></p>
        <h6><?php _e( 'You can return', 'responsive' ); ?> <a href="<?php echo home_url(); ?>/" title="<?php esc_attr_e( 'Home', 'responsive' ); ?>"><?php _e( '&#9166; Home', 'responsive' ); ?></a> <?php _e( 'or search for the page you were looking for', 'responsive' ); ?></h6>
        <?php get_search_form(); ?>

<?php endif; ?>  
      
        </div><!-- end of #content-sitemap -->
    </div>
</div>

<?php get_footer(); ?>