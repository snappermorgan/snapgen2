<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

//let's include a php file for server values so wp-cli works and doesn't rely on APACHE vars.

if (file_exists(dirname(__FILE__) . '/../snapgen.inc')) {
  require(dirname(__FILE__) . '/../snapgen.inc');
}



// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', SNAPGEN_WP_DB_NAME);

/** MySQL database username */
define('DB_USER', SNAPGEN_WP_DB_USER);

/** MySQL database password */
define('DB_PASSWORD', SNAPGEN_WP_DB_PASSWORD);

/** MySQL hostname */
define('DB_HOST', SNAPGEN_WP_DB_HOST);

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');
define('FS_METHOD', 'direct');
/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '^1%?j?<#jUV-,zb>J2eoZ]Cdjj%F.Sq1:OV7g$aSvEK<hY_6jl*:H+|`ScFq(v2+');
define('SECURE_AUTH_KEY',  '&(bA <M7JOcAFrNy<&?BH;tvRjYkJ,,(qMBUxV9RiM,Fk&hNQqh?,a%7c:6hg-#j');
define('LOGGED_IN_KEY',    'KxBp8e~5KHw^DHZ?YOvfZA;P}aut9&3OyECf#1=,`Ejs4`<@8<}?R[n,;vG9sLjW');
define('NONCE_KEY',        'Bctp*o-*IA_FS7H8hBE&us;R~ _GM}g(1.,DS6<2qjraNfeUznkx2F;2=XsbBtxu');
define('AUTH_SALT',        '3]}oo2PppZW9C!kBI[.<Tqk%@KpH7RY&VJ;]r!;z,FkGy|M=K;0^$}?IcauT#<F2');
define('SECURE_AUTH_SALT', '`jE>[&fu?ET,okjs~Z$]Qot:B/=tjd>EbUNvRu+pVqZP9BjF$7=p+%A;JrmZi95c');
define('LOGGED_IN_SALT',   'z7DE%YC^{+bl[drKJv46fsWH-#:J]UX>r2EvWK.2E!eNR[wG1B]3Td27XXFRnFw ');
define('NONCE_SALT',       'G}&uyI8Z^^BsH <G0t~Nq~z$M=lJ.s`L<w&tNX!J+,P<tZKD?jES#*RoLXP.h1Ys');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', true);
define('WP_DEBUG_DISPLAY', false); // Turn forced display OFF
define('WP_DEBUG_LOG',     true);  // Turn logging to wp-content/debug.log ON

define( 'SUNRISE', 'on' );
//define( 'DOMAINMAPPING_ALLOWMULTI', 1 );
/* Multisite */
define( 'WP_ALLOW_MULTISITE', true );
define('MULTISITE', true);
define('SUBDOMAIN_INSTALL', true);
define('DOMAIN_CURRENT_SITE',SNAPGEN_WP_DOMAIN); 
define('PATH_CURRENT_SITE', '/');
define('SITE_ID_CURRENT_SITE', 1);
define('BLOG_ID_CURRENT_SITE', 1);
/* That's all, stop editing! Happy blogging. */

error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
