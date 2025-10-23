<?php

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'unitylab_db' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '123456789' );

/** Database hostname */
define( 'DB_HOST', 'localhost:3306' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

define( 'AUTH_KEY',         'dbxwgrfzvvugl2lhyhcbdfjz0mx78npx2sjmuhn1l6pmdpelga7ep0jv3gzgbnpv' );
define( 'SECURE_AUTH_KEY',  'uq3y3q0snfpfuozihqgcxkaehk4alc4pkz4nplr7r9luykxfgvihtafeiqlcrkdj' );
define( 'LOGGED_IN_KEY',    'mnveso1xfmzow4skel8md4kbnwtlchkgglqwbsq8kipgjtypl7zc7f2r3o4b7hfc' );
define( 'NONCE_KEY',        '7lv8bzpxndyxd9jp5etwbhpuk1kj4arjb4lrwtafhw20a3bnqoutwjgudliubdlc' );
define( 'AUTH_SALT',        'tzyezsdnmzy8mqa3pe84x6o9eddbx0zmsxzrsocpn8qelfvfxxwuf6j6p2ojzesx' );
define( 'SECURE_AUTH_SALT', 'kuknpi5jqtsu65hcxx1rhco1qchnw3mznndpwcv1qguqe3vgdjleghe7a1xrithr' );
define( 'LOGGED_IN_SALT',   'rhetksrlsgw8ktmlu6ponhiqrgfqey6oqprjkduq5i8h7ixtsyhob34om1a0mwgx' );
define( 'NONCE_SALT',       'moabwexfwofmkgjyw2xepdxprqhmqbjjrbb9veavyjuqkrlnifqr7di1z6a2rxqq' );

/**#@-*/

$table_prefix = 'wplx_';

define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
