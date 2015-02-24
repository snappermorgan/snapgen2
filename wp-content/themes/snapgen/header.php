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
<html <?php language_attributes(); ?> class="no-js">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	 <link rel="shortcut icon" href="<?php echo esc_url( get_template_directory_uri() ); ?>/images/favicon.ico">
	<!--[if lt IE 9]>
	<script src="<?php echo esc_url( get_template_directory_uri() ); ?>/js/vendor/html5shiv.js"></script>
	<script src="<?php echo esc_url( get_template_directory_uri() ); ?>/js/vendor/respond.min.js"></script>
	<![endif]-->
	<script>(function(){document.documentElement.className='js'})();</script>
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<!-- Preloader -->
            <div id="preloader"></div>
            <!-- End preloader -->
<div id="page" class="hfeed site">
	<a class="skip-link screen-reader-text" href="#content"><?php _e( 'Skip to content', 'snapgen' ); ?></a>

	
<!-- Header -->
            <header id="header-4">
                <div class="container">
                	<div class="row">
                		<?php if ( has_nav_menu( 'primary' ) ) : ?>
            			<nav id="site-navigation" class="main-navigation" role="navigation">
            				<?php
            					// Primary navigation menu.
            					wp_nav_menu( array(
            						'menu_class'     => 'nav-menu',
            						'theme_location' => 'primary',
            					) );
            				?>
            			</nav><!-- .main-navigation -->
	                	<?php endif; ?>

                	</div>
                    <div class="row">
                        <div class="col-md-4 col-sm-6">
                            <div class="header-4-logo">
                                <h1><i class="fa fa-cube"></i>Ameriquote</h1>
                            </div>
                        </div>
                        <div class="col-md-8 col-sm-6 text-right">
                            <div class="header-4-social hidden-xs">
                                <ul class="list-inline list-unstyled">
                                    <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                                    <li><a href="#"><i class="fa fa-twitter"></i></a></li>
                                    <li><a href="#"><i class="fa fa-google-plus"></i></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            <!-- End header -->

		<?php get_sidebar(); ?>
	

	<div id="content" class="site-content">
