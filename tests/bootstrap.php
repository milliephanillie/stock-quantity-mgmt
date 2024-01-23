<?php
/**
 * PHPUnit bootstrap file
 */

require_once '/app/wp-tests-config.php';

require_once dirname(__DIR__) . '/vendor/autoload.php';

$WP_PHPUNIT_DIR = getenv('WP_PHPUNIT__DIR');

if (!$WP_PHPUNIT_DIR) {
	$WP_PHPUNIT_DIR = '/app/wp-content/plugins/stock-quantity-mgmt/vendor/wp-phpunit/wp-phpunit';
}

// Give access to tests_add_filter() function
require_once $WP_PHPUNIT_DIR . '/includes/functions.php';

tests_add_filter('muplugins_loaded', function() {
	require dirname(__DIR__) . '/stock-quantity-mgmt.php';
	require dirname(__DIR__, 2) . '/woocommerce/woocommerce.php';
});

require $WP_PHPUNIT_DIR . '/includes/bootstrap.php';

WP_Mock::setUsePatchwork( false );
WP_Mock::bootstrap();
