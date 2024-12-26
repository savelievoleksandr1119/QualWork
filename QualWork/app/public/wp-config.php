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
define( 'DB_NAME', 'local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

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
define( 'AUTH_KEY',          '@Zx4O`nRay%|NJKXyMv$MoJw,XJQ-=$B1aH&!tCX=3oxK]Zb/;febsm@SK:=nWG#' );
define( 'SECURE_AUTH_KEY',   '_J6Rm(M*y5eALz}uzd=IBjTHtogL!kqsqc,_FvP&cTx!L:g(TV j8PGaT6aqy7vy' );
define( 'LOGGED_IN_KEY',     'U*{;{<k-1aYkC+(O1vCw|5!69y3CyH7f$}y;`Kf|6!y_paQt870)30:bxZ=nkh?@' );
define( 'NONCE_KEY',         ',]3;s+ 8k}99ZoV$}!q%u$D8r;I2nS#qgz^mw=O]-s]n_VG0M454!XS>,yT`P>ST' );
define( 'AUTH_SALT',         '6cW?]jV3_*Auu:!b^cZY?u@P;D4AW~HXK=FBZ;S}?`B)Z]5H$]NNIu+;1ih%qOX6' );
define( 'SECURE_AUTH_SALT',  'UGcAe?V,L7!ICEi8(8y8u9SUrcdH3z:6a+-d[x@,-9m{G!.`I9z,=_I|_9h|.KL;' );
define( 'LOGGED_IN_SALT',    'a@xL?mJl@}Ov/{~u*Rd4G4rF;B>Hu8};by_t@cfNQB}%%*_{$@Wt(.b?Q(2H2YFK' );
define( 'NONCE_SALT',        '9tGA%HB 9^5RPAaY94.+G6=qu%+xL9:)xylb!,^&-#<sq7_{uO*aVa6 yna<;]s.' );
define( 'WP_CACHE_KEY_SALT', 'EWN/Qku0f*SVBGVbu0km)$&rh{QrT$f`n#8?4S`Ymy$H!&S Jhzvl)0lV@sZ? uk' );


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

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
