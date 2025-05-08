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
define( 'DB_NAME', 'Enerschool' );

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

if ( !defined('WP_CLI') ) {
    define( 'WP_SITEURL', $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] );
    define( 'WP_HOME',    $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] );
}



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
define( 'AUTH_KEY',         'HAbYJP1rO7iyFl2pdCPQn5vMmNDH3Xokhntr7KWr7XTUJegtWfODE7zf15V5apjr' );
define( 'SECURE_AUTH_KEY',  'eN1mXxjp4CIFPT27cg2fUHs16iWIRAiLPXokijC0QzfzpKCUYtX4PlTXCvghQbLr' );
define( 'LOGGED_IN_KEY',    'CS8oaZok3S0O0kRGWTsNIYtWZ8lQpcQAi7jCwakfApYn1qAJjrR6rLMTZNvy6lji' );
define( 'NONCE_KEY',        '4BVzcKaG8qn91dauzkZ6OZFAddk9SeKRBeCXxUrBjXLkrDkOx756SU4yRWZpHXCp' );
define( 'AUTH_SALT',        'DE62O7jRqCbQWXpO2nJRuvcF7QpJ05j3yiKm0pjq5VDyxUymvaSYYv6CIOWp5st3' );
define( 'SECURE_AUTH_SALT', 'zEmkcdAzmFGI8lsCRk75WvO119ShRkSUsbkXubuIQcKEDmjlJrsz1rmVvSkUFDXc' );
define( 'LOGGED_IN_SALT',   'GOkYBy5lz1ay0PFxXig97SmDJ6eJQCMpqvV9OEEBDfsAODrVEWhxAc62w8OlHqnv' );
define( 'NONCE_SALT',       '3B3bb5hMcBM0l4JsFxP0seB8O9QOrw3HHI0riDlrr0l7k3zt5fiitRkWvNg8d1TP' );

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
