<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'ipesports_wordpress');

/** MySQL database username */
define('DB_USER', 'ipesports_wordpress');

/** MySQL database password */
define('DB_PASSWORD', 'bdwp@@Ipe--1954-Feliz-@@@2019');

/** MySQL hostname */
define('DB_HOST', 'www.crosier.com.br');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'hu=<ZP{;e<?LeL1k6&m)J3$2CE89m]e]*IP8j0rmag+Q(h|Z{+d8r (__JUZ;GB_');
define('SECURE_AUTH_KEY',  '}z0Opu9!w_b_xq;!Q[J^u`fd1Xe(^(W3nO-lo{#L^SV`q#AJU}?DHwT7:XF*!7`)');
define('LOGGED_IN_KEY',    'zxQ9I:AIYbB1I,pSi(K$A$wKrRL{*K><6ta57]yqSf+*Yqk)=8s1cPsw4g/P=C t');
define('NONCE_KEY',        '/Z4GpzlxAs=dt &7BN4YNjj*&)D%(jC6}XmyjCX?`ieu~G<JN20HB=Y__y0ggsGv');
define('AUTH_SALT',        '0}!^Dr95;=r8 <.:T~L-xQsD|?vZu^Vr>]drR.gu2%,a>/G}=N+r6q?42!)cuc2$');
define('SECURE_AUTH_SALT', 'h,TzogC^};*k!n8k||eFF@L3M3}D#jAz`jyH9E{g^}2%u5Uk^x,&V)R:u*[SFw+E');
define('LOGGED_IN_SALT',   'dM{*r))0Lbm0itd4&Mr8%4@N}}5%!T{n()mKBK*rLH(u-PQi.Au@S J3S7puD;?&');
define('NONCE_SALT',       '2JS`i4/E$oJ#:(eSO-A3#rN[-DNvutupQGNd$p(Fg!i%FFcZ}}nkk><f-{7@potb');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
