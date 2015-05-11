<?php
/**
 * Front Page
 *
 * Note: You can overwrite home.php as well as any other Template in Child Theme.
 * Create the same file (name) include in /child-theme/ and you're all set to go!
 * @see            http://codex.wordpress.org/Child_Themes
 *
 * @file           home.php
 * @package        FlatPress 
 * @author         Brad Williams 
 * @copyright      2011 - 2013 Brag Interactive
 * @license        license.txt
 * @version        Release: 0.0.1
 * @link           N/A
 * @since          available since Release 1.0
 */
?>
<?php get_header(); ?>

        <div class="hero-unit">
            <div class="row-fluid">

                <?php 
            // First let's check if content is in place
                if(of_get_option('home_hero_area', 'no entry')) {
                    echo '<p>'; 
                    echo of_get_option('home_hero_area', 'no entry');
                    echo '</p>'; 
            // If not let's show dummy content for demo purposes
                  } else { 
                    echo '<h1>Hello, world!</h1>';
                    echo '<p>This is a simple hero unit, a simple jumbotron-style component for calling extra attention to featured content or information.</p>';
                    echo '<p><a class="btn btn-primary btn-large">Learn more</a></p>';
                  }
            ?>
        </div>
        </div><!-- end of .hero-unit -->   

        

<?php get_sidebar('home'); ?>
<?php get_footer(); ?>