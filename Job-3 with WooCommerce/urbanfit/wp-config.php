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
define( 'DB_NAME', 'urbanfit_db' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

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
define( 'AUTH_KEY',          '$A.?ct=YU-y;#!L0`l.#A.m!b91z=^EiQP6s{KeHo>yyX@8:PMId1@K?-qoO1Hl8' );
define( 'SECURE_AUTH_KEY',   'Cn)6 %994[F_hO-TqUm<$5Wfs2jQ gmN2;%`#Y#NW`$5:wGwR`?<xL,m_|PreQ@#' );
define( 'LOGGED_IN_KEY',     ',.S*$%_Jri}3|14|%aDU77BgwxWmEV8)h,yOX9%ft]~!P,i#1kl<87hO~qWj~m3+' );
define( 'NONCE_KEY',         'En=5$K;wB:*l;SjiKSp)}k}/4(a?,IjgQhBt+fQx,E4;%B+s%ih,~9;s`hg/nXS2' );
define( 'AUTH_SALT',         'TQP``x`o}3e|h1FkyA05~,e>q2m$KK?rx#Rg&&M},b[It!Jo(@UE!jsrk(41Lr>H' );
define( 'SECURE_AUTH_SALT',  '1r3>j~YiH6rts&msAw{aHtG=cA/NE;^C{p,~hgWAUW?uS%o3 v !2.[zbW ^UNy{' );
define( 'LOGGED_IN_SALT',    'l/ZemA$#q88d25m_*]aR0uCKPL[P8a[+*.T|~I%`RBYFHpD0hNW,SR#q$tcAro3*' );
define( 'NONCE_SALT',        'A:PqB3-_V1JJ;{qmdK%o+VXxX)4HNb]*qn$xJp2~ELGA^d$2};K$&#r=X~KG6 /K' );
define( 'WP_CACHE_KEY_SALT', '0XM?Z_;~Ia@/DMK?T:hW6 ;P$7?^]30&1O7lY G=;!;E`RB]$Dls|C&4qs(.+QB{' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


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
	define( 'WP_DEBUG', false );
}

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
