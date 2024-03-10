<?php
define( 'WP_CACHE', true ); // Added by WP Rocket

/**

 * The base configuration for WordPress

 *

 * The wp-config.php creation script uses this file during the installation.

 * You don't have to use the web site, you can copy this file to "wp-config.php"

 * and fill in the values.

 *

 * This file contains the following configurations:

 *

 * * MySQL settings

 * * Secret keys

 * * Database table prefix

 * * ABSPATH

 *

 * @link https://wordpress.org/support/article/editing-wp-config-php/

 *

 * @package WordPress

 */


// ** MySQL settings - You can get this info from your web host ** //

/** The name of the database for WordPress */

define( 'DB_NAME', 'stagingtravelgift' );


/** MySQL database username */

define( 'DB_USER', 'stagingtravelgift' );


/** MySQL database password */

define( 'DB_PASSWORD', 'it7fXTTFJYGINF' );


/** MySQL hostname */

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

define( 'AUTH_KEY',         'Ek,Lz/6$uUEjftCbP!lno6KKVQL`,-Pru~;!fpOr9ecw<v=R_ma4:A,S$3 80nY0' );

define( 'SECURE_AUTH_KEY',  '(793zZB|d<12iXC04ni[s@Le+![[4pa=`[pMWw1Px{Vsj( |.e0Zr0&PmlZ[|aCk' );

define( 'LOGGED_IN_KEY',    'aS*3R?R2;hE|:{7*ughOYz@gKvJOIp, %E`lcdNa_2;:q!{oEVpxON>.h}){X_QZ' );

define( 'NONCE_KEY',        'OjGT-lX,m4T%qfdZJe4pzH5Kp[K+?gyIZ1aPCMW]$bWrKh%-++u`%V}A> ^_ *>N' );

define( 'AUTH_SALT',        'KF6eBq/>bPUhptj|fVUFh0GP@>!YM+V_*3IH=(8DNr=+bTy.23@`p4?VV{L>f<Y-' );

define( 'SECURE_AUTH_SALT', 'e7Su9&WK^_;6@Yu>4:&}XIfrj?!G6/RevA3M|)@>;%mUw]%Ljm]]e#vIt2?b,#I*' );

define( 'LOGGED_IN_SALT',   '|Lk3*EEb=nf*)/H$`19x;9XZh|:%(}|BjXW[Sge3vdBFbEqEt[iM[2!4K(Nn+}C<' );

define( 'NONCE_SALT',       '=_u%9?IUrN .}A:#1l0.M.{;nPz|Z1B{*k/UsQ(._(y&R3[K>jGw#I}{4QxXo)CK' );


/**#@-*/


/**

 * WordPress database table prefix.

 *

 * You can have multiple installations in one database if you give each

 * a unique prefix. Only numbers, letters, and underscores please!

 */

$table_prefix = '5EZ2QbOm_';

/**

 * For developers: WordPress debugging mode.

 *

 * Change this to true to enable the display of notices during development.

 * It is strongly recommended that plugin and theme developers use WP_DEBUG

 * in their development environments.

 *

 * For information on other constants that can be used for debugging,

 * visit the documentatio

 *

 * @link https://wordpress.org/support/article/debugging-in-wordpress/

 */

define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', true );

ini_set('display_errors', true );
ini_set('error_reporting', E_ALL );

define( 'WP_MEMORY_LIMIT', '1024M' );

define( 'WP_MAX_MEMORY_LIMIT', '1024M' );

define( 'SAVEQUERIES', true );

/* Add any custom values between this line and the "stop editing" line. */


define( 'CONCATENATE_SCRIPTS', false );
define( 'DISALLOW_FILE_EDIT', true );
/* That's all, stop editing! Happy publishing. */


/** Absolute path to the WordPress directory. */

if ( ! defined( 'ABSPATH' ) ) {

	define( 'ABSPATH', __DIR__ . '/' );

}


/** Sets up WordPress vars and included files. */

require_once ABSPATH . 'wp-settings.php';