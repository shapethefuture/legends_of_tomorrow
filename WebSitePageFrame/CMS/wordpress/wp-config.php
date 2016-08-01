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
define('DB_NAME', 'my_wordpress');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '123');

/** MySQL hostname */
define('DB_HOST', '127.0.0.1');

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
define('AUTH_KEY',         '8+K%(fxN0hx>r>CIc;5#;--dW_bQBn68osv3))[%,grsq&dR4$Y58nbJ~q!>C70p');
define('SECURE_AUTH_KEY',  '#0h e-=D`)ADXtPnMK{u8 {Rff)8`62e@yCah{YUg/$59=$Ni[2AHaBHAw&f]%-n');
define('LOGGED_IN_KEY',    'kfpR*zk5&vmy+B+p+O?y!g@9h`#$*V/sMR,2qArv1:P)U#<d>KDi:71QGgfW/<RR');
define('NONCE_KEY',        '+zPR[i*DCyuX1?ydRT)BP>h-K2y%58fAq,_X$f5zG_9tI;Q3:!|;S$-f<.J800#+');
define('AUTH_SALT',        ':RR7PjDV./lJ],:)8<bjv6y;RS2fAk)&LWC7g7ggDTZhNzRoSRaH<3!#8C#e@o<4');
define('SECURE_AUTH_SALT', 'I1@=8R zrV3M4_}NsJ1qqRgp!vv|hb:4KB/]~y&})]X64JB{Ck8~Zd5HN<5(Y4b9');
define('LOGGED_IN_SALT',   ' JgBh/g{5wg1[zxAPn>dod,) -O>H&zHc$Gp[qw!D`LdD8Cq_smFO&x%h#yQXAz?');
define('NONCE_SALT',       ' ?zgHh/hxq#;F+bSERo^yl?I*gE~]^<#y]:A1M.>R?YX9IEa*G5r=ys+1VZLg>TE');

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
