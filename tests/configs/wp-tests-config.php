<?php
define( 'ABSPATH', WBTEST_WORDPRESS_PATH."/" );
// Force known bugs: (previously -f)
define( 'WP_TESTS_FORCE_KNOWN_BUGS', true );
// Test with WordPress debug mode on (previously -d)
define( 'WP_DEBUG', true );

// ** MySQL settings ** //
// WARNING WARNING WARNING!
// wp-test will MIGHT DROP ALL TABLES in the database named below.
define( 'DB_NAME', 'throwaway' ); 
define( 'DB_USER', 'root' );
define( 'DB_PASSWORD', 'root' );
define( 'DB_HOST', 'localhost' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

// ** Site settings ** //
define( 'WP_TESTS_DOMAIN', 'localhost' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Blog' );
define( 'WP_PHP_BINARY', 'php' );

define ( 'WPLANG', '' );

$table_prefix  = 'wp_';   // Only numbers, letters, and underscores please!