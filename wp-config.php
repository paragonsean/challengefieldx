<?php

// BEGIN iThemes Security - Do not modify or remove this line
// iThemes Security Config Details: 2
define( 'DISALLOW_FILE_EDIT', true ); // Disable File Editor - Security > Settings > WordPress Tweaks > File Editor
// END iThemes Security - Do not modify or remove this line

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wwwchallengess' );

/** Database username */
define( 'DB_USER', 'wwwchallengess' );

/** Database password */
define( 'DB_PASSWORD', '4wOwO08R8qQqLsgItWZPvk9W' );

/** Database hostname */
define( 'DB_HOST', 'ls-96c030164f6fe533ab584965faa5449280eac2d2.c15l9m3qhl8e.us-east-1.rds.amazonaws.com:3306' );

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
define( 'AUTH_KEY',          'V<vAc]x&0Ej4_;ib*1 ,78_C#pQ)0y$hQfvjcdpjXJL4D$-f %:y BpJDo|[Of6v' );
define( 'SECURE_AUTH_KEY',   '<0)W%pz86:P?Lr5>!<deHcucO(|vXt(nSI$-^>dKykZP}N(A,hR+S<R!N[PV)XLW' );
define( 'LOGGED_IN_KEY',     ')1Nj&04l*F]mIay2U,eZ&JH(F4y>CNz,/tML&3mb6HtCBTnR?L3yM!v:`X8bM8*]' );
define( 'NONCE_KEY',         'NZM1/$v7YWD83< GX0S[h;|$#KzvFjBS-?6m]lU<G6ZP2B]5Y>y{F||aXUID94eo' );
define( 'AUTH_SALT',         'bfam$<~^FUKiyjcJ#R;_g!+ W;5Yc$ifUe^l:_GhPX1.Md?a(hEbENiUzziM@@R!' );
define( 'SECURE_AUTH_SALT',  '.^lJ4Q56Hv0rgTVa8Y!GdNdiiv,5[g%;9vO^u_/BoK, actp]s-n(F82}ALt;3t1' );
define( 'LOGGED_IN_SALT',    'VynyxZh=n2cJ1f{Gw#1Hiw?(22-,j[Be;E4afBm{?7||?IaX#G&A-RR7t<9&2I7&' );
define( 'NONCE_SALT',        'q<T; DAq$iUe4A`9C!1N~oS$!;sav3<vX@8q[:$#|?[GZ kHaG=YbT(D_Z:7|F{7' );
define( 'WP_CACHE_KEY_SALT', '/.d^?S:f<tlh%LKFP;pvk0n/Sdv|jW`F?TUH#C2ee;8/z#J!Em&LOfTw*(A:YT/L' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'ue1_';


/* Add any custom values between this line and the "stop editing" line. */



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
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', true );
}

define( 'WP_DEBUG_DISPLAY', false );
define( 'WP_DEBUG_LOG', true );
define( 'DISALLOW_FILE_EDIT', true );
define( 'DISABLE_WP_CRON', true );
define( 'WP_MEMORY_LIMIT', '256M' );
set_time_limit(30000);
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';