<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'setuko_school' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
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
define( 'AUTH_KEY',         'yp^]y)jd%8`T|>$t$dFqW:iV_!s^Ei^$GErw7Ljzufgy!@)8H}[=BE)ouyJCzKBa' );
define( 'SECURE_AUTH_KEY',  '!UWg}g{H^.MzXi<0XZ/aS;3f|VGy5bIe_4Sri?0Kpt!rpY6*uNu~,&z;)6AUW+C>' );
define( 'LOGGED_IN_KEY',    '!J1|`&gvGKAx`M@[y#d*Z4&x+8JE1T6Rc:Y_lZcQ;[pY.qkb5ZtY5dlkO=(1c6n(' );
define( 'NONCE_KEY',        'L(i|D08Y+C/*EQB{lrh?N)Gk]yWIB{830,5yR:p{tY9_EU@2&CD!f|lU7*IIK- {' );
define( 'AUTH_SALT',        '!:Ed_LGub@e@s0{7<tHL 4(g/SzUHyuB;($YkhW|zkSIA[+I%f,?Rxm[F3dmPuNG' );
define( 'SECURE_AUTH_SALT', '1px5!=OI@A74pxv9s_C6ooER.Bq}>]*9::&bj^@v;DlPrEEFunVRa06p%viW+Xnq' );
define( 'LOGGED_IN_SALT',   ' 7<LG_YgfP]Aemw4EaTGlxsi|LL]<<,>YfpZ5YY:f7NLULs!WpS8/y} }T|)EpYR' );
define( 'NONCE_SALT',       '`9]s_hfsl`*m:3Bx^fQAJ&mxJ*ax9&Zc}]0JbtyYM_`8g-v$r;?`;,m{nL_K)N|q' );

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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
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
