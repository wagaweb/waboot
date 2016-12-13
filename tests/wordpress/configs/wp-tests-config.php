<?php
define( 'ABSPATH', dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))))."/" );

// Force known bugs: (previously -f)
define( 'WP_TESTS_FORCE_KNOWN_BUGS', true );

// Test with WordPress debug mode on (previously -d)
define( 'WP_DEBUG', true );

// ** MySQL settings ** //
// WARNING WARNING WARNING!
// wp-test will MIGHT DROP ALL TABLES in the database named below.
define( 'DB_NAME', 'waga_waboot_test' );
define( 'DB_USER', 'root' );
define( 'DB_PASSWORD', 'root' );
define( 'DB_HOST', 'localhost' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

$table_prefix = 'wptest_';   // Only numbers, letters, and underscores please!

// ** Site settings ** //
define( 'WP_TESTS_DOMAIN', 'waboot.dev' );
define( 'WP_TESTS_EMAIL', 'dev@waga.it' );
define( 'WP_TESTS_TITLE', 'Waboot' );

define( 'WP_PHP_BINARY', 'php' );

define ( 'WPLANG', '' );