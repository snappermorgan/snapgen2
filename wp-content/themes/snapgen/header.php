<?php
/**
 * The template for displaying the header
 *
 * Displays all of the head element and everything up until the "site-content" div.
 *
 * @package WordPress
 * @subpackage Twenty_Fifteen
 * @since Twenty Fifteen 1.0
 */
?><!DOCTYPE html>
<html <?php language_attributes();?> class="no-js">
<head>
	<meta charset="<?php bloginfo('charset');?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable = no" >
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo('pingback_url');?>">
	 <link rel="shortcut icon" href="<?php echo esc_url(get_template_directory_uri());?>/images/favicon.ico">
	<!--[if lt IE 9]>
	<script src="<?php echo esc_url(get_template_directory_uri());?>/js/vendor/html5shiv.js"></script>
	<script src="<?php echo esc_url(get_template_directory_uri());?>/js/vendor/respond.min.js"></script>
	<![endif]-->
	<script>(function(){document.documentElement.className='js'})();</script>
	<?php wp_head();?>
</head>

<body <?php body_class();?>>
<!-- Preloader -->
            
            <!-- End preloader -->
<div id="page" class="hfeed site">
	<a class="skip-link screen-reader-text" href="#content"><?php _e('Skip to content', 'snapgen');?></a>


<!-- Header -->
            <header id="header-4">
                <div class="container">
                	<div class="row">
                		<?php if (has_nav_menu('primary')): ?>
            			<nav id="site-navigation" class="main-navigation" role="navigation">
            				<?php
// Primary navigation menu.
wp_nav_menu(array(
	'menu_class' => 'nav-menu',
	'theme_location' => 'primary',
));
?>
            			</nav><!-- .main-navigation -->
	                	<?php endif;?>

                	</div>
                    <div class="row">
                    <?php
if (is_active_sidebar('header')): ?>
                        <div id="widget-area" class="widget-area" role="complementary">
                            <?php dynamic_sidebar('header');?>
                        </div><!-- .widget-area -->
                    <?php endif;?>

                    </div>
                </div>
            </header>
            <!-- End header -->

		<?php get_sidebar();?>


	<div id="content" class="site-content">
