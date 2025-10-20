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
 * This has been slightly modified (to read environment variables) for use in Docker.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// a helper function to lookup "env_FILE", "env", then fallback
if (!function_exists('getenv_docker')) {
	// https://github.com/docker-library/wordpress/issues/588 (WP-CLI will load this file 2x)
	function getenv_docker($env, $default) {
		if ($fileEnv = getenv($env . '_FILE')) {
			return rtrim(file_get_contents($fileEnv), "\r\n");
		}
		else if (($val = getenv($env)) !== false) {
			return $val;
		}
		else {
			return $default;
		}
	}
}

/** The name of the database for WordPress */
define( 'DB_NAME', getenv_docker('WORDPRESS_DB_NAME', 'wordpress') );
define( 'DB_USER', getenv_docker('WORDPRESS_DB_USER', 'example username') );
define( 'DB_PASSWORD', getenv_docker('WORDPRESS_DB_PASSWORD', 'example password') );
define( 'DB_HOST', getenv_docker('WORDPRESS_DB_HOST', 'mysql') );
define( 'DB_CHARSET', getenv_docker('WORDPRESS_DB_CHARSET', 'utf8mb4') );
define( 'DB_COLLATE', getenv_docker('WORDPRESS_DB_COLLATE', '') );


define( 'AUTH_KEY',         getenv_docker('WORDPRESS_AUTH_KEY',         'f552951087bbd9c490f81a4ddf843b6c3fed254a') );
define( 'SECURE_AUTH_KEY',  getenv_docker('WORDPRESS_SECURE_AUTH_KEY',  '16a50b905be71745c42fefb94fd9fbd79c04ddc1') );
define( 'LOGGED_IN_KEY',    getenv_docker('WORDPRESS_LOGGED_IN_KEY',    '3ae8fb9d8037e6cceb98584ee1c0230b8fa5dc92') );
define( 'NONCE_KEY',        getenv_docker('WORDPRESS_NONCE_KEY',        '4779cb4dfe8acdc48ce6ce8e373ca86d9b3ea68f') );
define( 'AUTH_SALT',        getenv_docker('WORDPRESS_AUTH_SALT',        'd27ce2a6db5741be4fdeb44895d6765b90e84004') );
define( 'SECURE_AUTH_SALT', getenv_docker('WORDPRESS_SECURE_AUTH_SALT', '97fbdf4808e24cd52e67d4dea9370c867a1da2fa') );
define( 'LOGGED_IN_SALT',   getenv_docker('WORDPRESS_LOGGED_IN_SALT',   'f60df96da4162f837264f320e1b5652e029d10a7') );
define( 'NONCE_SALT',       getenv_docker('WORDPRESS_NONCE_SALT',       '912b089f99d64cb60876047b79f2f1be467cc8eb') );

$table_prefix = getenv_docker('WORDPRESS_TABLE_PREFIX', 'wp_');
define( 'WP_DEBUG', !!getenv_docker('WORDPRESS_DEBUG', '') );
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strpos($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') !== false) {
	$_SERVER['HTTPS'] = 'on';
}
// (we include this by default because reverse proxying is extremely common in container environments)

if ($configExtra = getenv_docker('WORDPRESS_CONFIG_EXTRA', '')) {
	eval($configExtra);
}

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
