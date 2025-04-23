<?php
/**
 * Plugin Name:     Stay Foxhole Occupancy Tax Calculator
 * Plugin URI:      https://stayfoxhole.com
 * Description:     A simple plugin to calculate US Occupancy Tax Rates
 * Author:          STAY FOXHOLE LLC
 * Author URI:      https://stayfoxhole.com
 * Text Domain:     stay-foxhole
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 */

require_once 'vendor/autoload.php';

require_once 'filters.php';

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

define('SFXH_TAXES_PLUGIN', __FILE__);

if ( ! defined( 'SFXH_TAXES_PLUGIN_PATH' ) ) {
	define( 'SFXH_TAXES_PLUGIN_PATH', plugin_dir_path( SFXH_TAXES_PLUGIN ) );
}

if ( ! defined( 'SFXH_TAXES_PLUGIN_URL' ) ) {
	define( 'SFXH_TAXES_PLUGIN_URL', plugin_dir_url( SFXH_TAXES_PLUGIN ) );
}

if ( ! defined( 'SFXH_TAXES_ASSETS_VERSION' ) ) {
	define( 'SFXH_TAXES_ASSETS_VERSION', '1.0.0' );
}

use SfxhTaxes\Routes\SfxhTaxesRoute;
$SfxhTaxesRoute = new SfxhTaxesRoute(); 
add_action('rest_api_init', [$SfxhTaxesRoute, 'boot']);

use SfxhTaxes\Routes\SfxhTaxesImport;
$SfxhTaxesImport = new SfxhTaxesImport(); 
add_action('rest_api_init', [$SfxhTaxesImport, 'boot']);


use SfxhTaxes\Shortcodes\SfxhTaxesProductTableShortcode;
$SfxhTaxesProductTableShortcode = new SfxhTaxesProductTableShortcode(); 

use SfxhTaxes\Shortcodes\SfxhTaxesProductSelectShortcode;
$SfxhTaxesProductSelectShortcode = new SfxhTaxesProductSelectShortcode(); 

use SfxhTaxes\Import\SfxhTaxesDemoImporter;
use SfxhTaxes\Options\SfxhTaxesOptions;

class SfxhTaxesBase {
	public function __construct() {
		add_action('admin_init', [$this, 'check_required_plugin']);
		add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);

		add_filter('theme_page_templates', [$this, 'add_template']);
		add_filter('template_include', [$this, 'load_template']);
		add_action('add_meta_boxes', [$this, 'sfxh_add_foxhole_taxes_meta']);
	}

	public function add_template($templates) {
		$templates['template-foxhole-taxes.php'] = __('Stay Foxhole Taxes', 'foxhole');
		return $templates;
	}

	public function load_template($template) {
		if (is_page_template('template-foxhole-taxes.php')) {
			$custom_template = SFXH_TAXES_PLUGIN_PATH . 'templates/template-stay-foxhole-taxes.php';
			if (file_exists($custom_template)) {
				return $custom_template;
			}
		}
		return $template;
	}

	public function sfxh_add_foxhole_taxes_meta() {
		add_meta_box(
			'product_id_meta_box',
			'Product ID',
			[$this, 'render_product_id_meta_box'],
			'page',
			'normal',
			'default'
		);
	}

	public function render_product_id_meta_box($post) {
		if (get_page_template_slug($post->ID) !== 'template-foxhole-taxes.php') {
			return;
		}
		$value = get_post_meta($post->ID, 'otslr_product_id', true);
		echo '<input type="text" name="otslr_product_id" value="' . esc_attr($value) . '" style="width:100%;" />';
	}


	public function enqueue_scripts() {
		global $post;
		$post_id = (is_singular() && isset($post->ID)) ? $post->ID : null;
		$version = '1.0.0';
		wp_register_script('sfxh-taxes', SFXH_TAXES_PLUGIN_URL . 'resources/assets/js/sfxh-taxes.js', [], $version, true);
		wp_enqueue_script('sfxh-taxes');
		wp_localize_script('sfxh-taxes', 'sfxhTaxes', [
			'siteUrl' => site_url(),
			'restUrl' => trailingslashit(site_url() . '/' . rest_get_url_prefix()),
			'iconsPath' => SFXH_TAXES_PLUGIN_URL . 'resources/assets/images/icons/',
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'restNonce' => wp_create_nonce('wp_rest'),
			'forgot_password_url' => site_url() . '/login/?action=forgot_password',
			'current_user_id' => get_current_user_id(),
			'current_user_email' => wp_get_current_user()->user_email,
			'current_user_login' => wp_get_current_user()->user_login,
			'post_id' => $post_id,
		]);
	}

	public function check_required_plugin() {
		$admin_notices = SfxhTaxesOptions::get_option('admin_notices', null);
		if(is_array($admin_notices) && isset($admin_notices['required_plugin_missing']) && $admin_notices['required_plugin_missing']) {
			add_action('admin_notices', [$this, 'required_plugins']);

			deactivate_plugins(plugin_basename( SFXH_TAXES_PLUGIN ));

			SfxhTaxesOptions::update_option('admin_notices', ['required_plugin_missing' => false]);

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
}

function activate_sfxh_taxes() {
	if(!class_exists(WooCommerce::class)) {
		error_log(print_r('WooCommerce is required.', true));
		SfxhTaxesOptions::update_option('admin_notices', ['required_plugin_missing' => true]);
	}
}

$SfxhTaxesBase = new SfxhTaxesBase();
register_activation_hook(SFXH_TAXES_PLUGIN, 'activate_sfxh_taxes');

use \SfxhTaxes\Includes\SfxhTaxesTax;
$SfxhTax = new SfxhTaxesTax();

