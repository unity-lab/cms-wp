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
define( 'DB_NAME', 'sigesa_db' );

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
define( 'AUTH_KEY',         'Wl,mmVCVg|P7.` MT0SWi9/fN~ W&JSCd,9bnK KvB}O@,`}g3KWjM@`,jP^@>))' );
define( 'SECURE_AUTH_KEY',  'SwO?_ (!!zVQO)1^A,B=s34c]lFR G>k_-70$d:s-^zMv+ZYtUPn9=HZ[Av&Uw6l' );
define( 'LOGGED_IN_KEY',    '3N!$#CS7ko*z<%?Yt@ hW /<x5m.^Ak]nj^cHU<+4:r[M6JiJ))P<!$zx-LPq?>F' );
define( 'NONCE_KEY',        'qsM%!OTzJ:Ig+<w+[6HyW}bfp/]=^Q l^<<bLI9cIQOhn!?),(4~+*]b[gKKrFZi' );
define( 'AUTH_SALT',        '@q6L`v);=c5UY nv-RIhG{<sMN@a?K.907#]X4tIyQ~2%]jYY?Aq8_&>U6pEq ;l' );
define( 'SECURE_AUTH_SALT', '^eUcS_YkOUqVH<sb<`ias8kNV:yUTXcQ?t,i+z`ErPu[OS5,@dH)m=[Q(ef2e6X.' );
define( 'LOGGED_IN_SALT',   'bMclRLDl(Z%r~_]vICq{Hi-s:@lp)]m<0qbL]HecpfgiyCj-gq,Ny~89.>[h@<tS' );
define( 'NONCE_SALT',       '6&:S_>x<sef5:)1BiDWU9?J25`zOyQ]JBv:C-K]LrI-!CT9a%N0J-^Dt_08[qc-L' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
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
