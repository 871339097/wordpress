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
define('DB_NAME', 'wordpress');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'root');

/** MySQL hostname */
define('DB_HOST', 'localhost');

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
define('AUTH_KEY',         'aS-u)a^eKxynyQQ-Z%2k_K`iPfv) @F:yUcobO 1(2O@e_r*Ui#7zW~$H43s-Bk*');
define('SECURE_AUTH_KEY',  '/8B6gU_8MT %B|Bh$1$yI_&Kh3DT(V|^eofxKL/h8NZH#/ilzBK*bheK0q7z= 9R');
define('LOGGED_IN_KEY',    ' Y <v|{_?.IaOPqU#egGek^c,zP6^>(hdL4r04~zU8ajCL}HXAH0dOfR*}MXVK_J');
define('NONCE_KEY',        '7~|K$h2D` YK!0*AkREDHg*jVLQ5wbjy_L1%}BL;2fM-&)#&:}{I#cyMeNyg}3B2');
define('AUTH_SALT',        'yV@<Ka*aP),?M. $y&eU`$gpO2w2_)*PsS3k=m c1NWD(jux+=d@Mp`VWCwU[|<1');
define('SECURE_AUTH_SALT', '}_+7`etHGyv?<u{yxh^ef,D Ee9t4POsLo7=!S00`>KGh0~@rm75|:2IxB`QI_4j');
define('LOGGED_IN_SALT',   '?9tI*~6~Tv[z6:.#f}S@xsx5bU8b[e,B.RZfQ/`.9{A[=L6%a@aTAl@Fk*H|rV.b');
define('NONCE_SALT',       'k+hqA1s^`?q3w ZI!7rbdL@WYJvHmU{:rmco+|1w=z: y=G`Z_hFqE+I#OfyAy=X');

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
