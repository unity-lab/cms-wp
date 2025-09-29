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
define('WP_CACHE', true);
define( 'WPCACHEHOME', 'C:\xampp\htdocs\unitylab\wp-content\plugins\wp-super-cache/' );
define( 'DB_NAME', 'unitylab_db' );

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
define( 'AUTH_KEY',         'dbxwgrfzvvugl2lhyhcbdfjz0mx78npx2sjmuhn1l6pmdpelga7ep0jv3gzgbnpv' );
define( 'SECURE_AUTH_KEY',  'uq3y3q0snfpfuozihqgcxkaehk4alc4pkz4nplr7r9luykxfgvihtafeiqlcrkdj' );
define( 'LOGGED_IN_KEY',    'mnveso1xfmzow4skel8md4kbnwtlchkgglqwbsq8kipgjtypl7zc7f2r3o4b7hfc' );
define( 'NONCE_KEY',        '7lv8bzpxndyxd9jp5etwbhpuk1kj4arjb4lrwtafhw20a3bnqoutwjgudliubdlc' );
define( 'AUTH_SALT',        'tzyezsdnmzy8mqa3pe84x6o9eddbx0zmsxzrsocpn8qelfvfxxwuf6j6p2ojzesx' );
define( 'SECURE_AUTH_SALT', 'kuknpi5jqtsu65hcxx1rhco1qchnw3mznndpwcv1qguqe3vgdjleghe7a1xrithr' );
define( 'LOGGED_IN_SALT',   'rhetksrlsgw8ktmlu6ponhiqrgfqey6oqprjkduq5i8h7ixtsyhob34om1a0mwgx' );
define( 'NONCE_SALT',       'moabwexfwofmkgjyw2xepdxprqhmqbjjrbb9veavyjuqkrlnifqr7di1z6a2rxqq' );

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
$table_prefix = 'wplx_';

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
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );

/* Add any custom values between this line and the "stop editing" line. */

define('WP_HOME', 'http://localhost/unitylab');
define('WP_SITEURL', 'http://localhost/unitylab');

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
