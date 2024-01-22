<?php
/**
 * Plugin Name:     WooCommerce Manage Stock
 * Plugin URI:      https://philiparudy.com
 * Description:     A simple plugin to manage the stock quantity changes in a bulk fashion.
 * Author:          Philip Rudy
 * Author URI:      https://philiparudy.com
 * Text Domain:     manage-stock
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         STOCKMGMT
 */

require_once 'vendor/autoload.php';

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

define('STOCKMGMT_PLUGIN', __FILE__);

if ( ! defined( 'STOCKMGMT_PLUGIN_PATH' ) ) {
	define( 'STOCKMGMT_PLUGIN_PATH', plugin_dir_path( STOCKMGMT_PLUGIN ) );
}

if ( ! defined( 'STOCKMGMT_PLUGIN_URL' ) ) {
	define( 'STOCKMGMT_PLUGIN_URL', plugin_dir_url( STOCKMGMT_PLUGIN ) );
}

use StockMGMT\Import\SQMGMT_DemoImporter;
use StockMGMT\Options\SQMGMT_Options;

class SQMGTM_Manage_Stock {
	public function __construct() {
		add_action('admin_init', [$this, 'check_required_plugin']);
		add_action('admin_init', [$this, 'register_install_bg_task']);
	}

	public function register_install_bg_task() {
		$demoImporter = new SQMGMT_DemoImporter();
		add_action('admin_post_create_demo_data', [$demoImporter,'install_demo_data']);
	}

	public function check_required_plugin() {
		$admin_notices = SQMGMT_Options::get_option('admin_notices');
		if($admin_notices['required_plugin_missing']) {
			add_action('admin_notices', [$this, 'required_plugins']);

			deactivate_plugins(plugin_basename( STOCKMGMT_PLUGIN ));

			SQMGMT_Options::update_option('admin_notices', ['required_plugin_missing' => false]);

			if (isset($_GET['activate'])) {
				unset($_GET['activate']);
			}

			return false;
		}

		return true;
	}

	public function required_plugins() {
		$class = 'notice notice-error';
		$message = __( 'WooCommerce must be installed and activated to use this plugin.', 'manage-stock' );

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	}

	public static function create_demo_data() {
		$url = 'http://host.docker.internal:8080';

		$response = wp_remote_post($url, array(
			'body' => array(
				'action' => 'create_demo_data',
				'create_demo_data_nonce' => wp_create_nonce('create-demo-data'),
			),
		));

		if(is_wp_error($response)) {
			error_log(print_r($url, true));
			error_log(print_r($response, true));
		}
	}
}

function activate_manage_stock() {
	if(!class_exists(WooCommerce::class)) {
		error_log(print_r('WooCommerce is required.', true));
		SQMGMT_Options::update_option('admin_notices', ['required_plugin_missing' => true]);
	} else {
		error_log(print_r("plugin activated"));
	}
}

$sqmgmt = new SQMGTM_Manage_Stock();

register_activation_hook(STOCKMGMT_PLUGIN, 'activate_manage_stock');

use \StockMGMT\Admin\SQMGMT_BaseAdmin;

$baseAdmin = new SQMGMT_BaseAdmin();

