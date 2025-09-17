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
define( 'DB_PASSWORD', '123456789' );

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
define( 'AUTH_KEY',         '_S N#Z:G@%>P-DQMy^#|oAGa50Zn07WNL}ae.UXB1+vHI;UE8Gn!<Jb%bD8 p+_I' );
define( 'SECURE_AUTH_KEY',  '(-r)H?iPq1?+No8Bp_-2iHI;HSid?NCi2LS0OQaIbgfpu7iJ+I+D`~f(U[U=T~Xw' );
define( 'LOGGED_IN_KEY',    '.#GiB_{ynGuQ%c4s3u.A9jF`32KG,epDbSJsO@=b_HzbGR+X8kX,yKo*X839kS$j' );
define( 'NONCE_KEY',        'k6+/vS:wdv]xMRNU(ZZ/b~Umv]w #/2?&pLg-_k9T*bLHdJQ]Ma+9!rH[;YpTwzf' );
define( 'AUTH_SALT',        'X@e.,k0DWkUMG6jhL%4F3VYZzOzffE2BL#hqupY82~D&X:@NTkSrPtT}vmZ~+XAa' );
define( 'SECURE_AUTH_SALT', 'V9xGBCEQ+4W@MAlc_bAg2E!{Ox&+7r)*aA5H@`#EKl<p+^*E4Jw3*LQ<Z1o8e!r}' );
define( 'LOGGED_IN_SALT',   'l)/E8K=[d}2L.utDiSM8JH6>eIn{e7:srAxZw&=$T?A)~BNChXeL|^w! :@&(sN`' );
define( 'NONCE_SALT',       '@H$3NIW+-eAlnlnK2ucK{>Qpy*hFTk)*A?&5zz3HmI]<@B:z3wF_${)p?C01D$Rg' );

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
