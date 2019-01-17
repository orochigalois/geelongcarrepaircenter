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
define('DB_NAME', 'geelongcarrepaircentre_database');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'root');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

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
define('AUTH_KEY',         'uXyVgbiOq8fuqnnoSYxKY9T4jMtFBSauJjQeKn7Iw04sDW6uVkJHRsaMAuUNyInG');
define('SECURE_AUTH_KEY',  'cRSAzDGhDuIlgVS2Rqv2MRaCmEi5IEcFSMxyybmbQldjTfEon9UAp7WSXJTgDSaj');
define('LOGGED_IN_KEY',    'FTmjOLbBWGdyMrwUx6kRDmjSuKp1hiIH7fwXxFJX6fqRfVXPlDGsE2rfRsoXpGiG');
define('NONCE_KEY',        'WuzhRX9ovhHGTIPQF7S2aXkfsVz5Hwz2lhUxsJEPUmEMv9YYCg40wY5AkkEXO28p');
define('AUTH_SALT',        'mbxy9BM6IR1ofLg48xcPO1tELXqTgubwXzCijA1vI5ODZ87gyruStzx521LQjgT1');
define('SECURE_AUTH_SALT', '5BTEFFElDk3Gtw0yKRW4U3qkkWTh1y0mcqCArgwWS7etfGPz4wsb8qcvcDJcvF36');
define('LOGGED_IN_SALT',   'DywFKDlGiseFU2JCPFvKyJXBDQ744FMXN1Y7y5QcInhaRohAqnwKHcHCoo4p4ZK6');
define('NONCE_SALT',       'SaGz0324m3S9EkCcMV9APO4p5pxLeV6sKqJDI5ut98fYPjKmNb5PfgPoSu4OjnVW');

/**
 * Other customizations.
 */
define('FS_METHOD','direct');define('FS_CHMOD_DIR',0755);define('FS_CHMOD_FILE',0644);
define('WP_TEMP_DIR',dirname(__FILE__).'/wp-content/uploads');

/**
 * Turn off automatic updates since these are managed upstream.
 */
define('AUTOMATIC_UPDATER_DISABLED', true);


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
