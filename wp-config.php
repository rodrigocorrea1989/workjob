<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'workjob' );

/** MySQL database username */
define( 'DB_USER', 'dimitri' );

/** MySQL database password */
define( 'DB_PASSWORD', 'dimitri2022' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '3)1xVkf63l2gC[jxDD,PDGg)m8Dp:|Q?tk[^9m7^aK8uxI}>0}:L7-|=O/gNi#(X' );
define( 'SECURE_AUTH_KEY',  'ITp&1A`X-?1>~B]GP0{znkw3V=[7N5BNIF.dm|(=j7; y;{lC`TKbxGtfwrsDW%f' );
define( 'LOGGED_IN_KEY',    '$e[_U..hwU;hb{fFteyueMWl{BMVmQ%RO37r-Y3qeWW=*sX.xnS<z)%O7N0E?A_+' );
define( 'NONCE_KEY',        '6YSB_2_ZA-+pj)N(W]RUB*MHEgMNn/hU#^@#eAaub(O{U)#M>L }HD:^~.#n/p7N' );
define( 'AUTH_SALT',        't]R>q,bViMhesE8*&]F%b.Si%d/]P=D~f@.Ch3GTuxtAsz[;sW^fKgahue(iuckv' );
define( 'SECURE_AUTH_SALT', 'qFG*QRrMI8YmA6G+9vuCFr5Bm^$DEC-iWG7hs)qFAvH|OO%l2n(CsDt+c$%@s%i^' );
define( 'LOGGED_IN_SALT',   'f+44gKJMIeX&>XkZfs#w*b!4]s%?RZXlx~|CC__~f!%PeN3Qo`N6`a>j$Pjzh0?F' );
define( 'NONCE_SALT',       '*6J[,<-(AGz<--S`5VZyjE,WsXDlY=2^vj9&.ST(; :, o!s:#LUkkqaWmbqu)d?' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
