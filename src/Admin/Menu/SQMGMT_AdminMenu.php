<?php
namespace StockMGMT\Admin\Menu;

use StockMGMT\Options\SQMGMT_Options;
use StockMGMT\Includes\SQMGMT_ProductModel;

class SQMGMT_AdminMenu
{
    const PAGE_IDENTIFIER       = 'stock_quantity_mgmt';
    const PAGE_TEMPLATE         = 'dashboard';
    const SETTINGS_PAGE         = 'sqmgmt-settings';

    private $option_group = 'products';

    private $option_name = 'products';

    private $prefix;

    private $settings;

	private $version;

    /**
     * Schema, such as max character count, for our options
     *
     * @var array[]
     */
    private $fields = [];

    public function __construct() {
        $this->prefix = SQMGMT_Options::PREFIX;
		$this->version = SQMGMT_Options::VERSION;

        $this->option_group = $this->prefix . $this->option_group;
        $this->option_name  = $this->prefix . $this->option_name;
        $this->settings = SQMGMT_Options::get_option($this->option_name) ?? [];
    }

    public function register_hooks()
    {
        add_action('admin_menu', [$this, 'register_pages']);
        add_action('admin_init', [$this, 'register_stock_quantity_list']);
		add_filter( 'custom_menu_order', [$this, 'reorder_woocommerce_submenu'] );
    }

    public function register_pages()
    {
        $manage_capability = $this->get_manage_capability();
        $page_identifier = $this->get_page_identifier();

        $submenu_css = add_submenu_page(
			'woocommerce',
            'Manage Stock: ' . __('Manage Stock', 'stock-quantity-mgmt'),
            __('Manage Stock', 'stock-quantity-mgmt'),
            $manage_capability,
            self::SETTINGS_PAGE,
            [$this, 'show_page'],
            1
        );

		global $submenu;
		if (isset($submenu['woocommerce'])) {
			foreach ($submenu['woocommerce'] as $key => $menu_item) {
				if ($menu_item[2] === self::SETTINGS_PAGE) {
					$submenu['woocommerce'][$key][1] = 'manage_options'; // Ensure correct capability
					$submenu['woocommerce'][$key][4] = 'menu-order-56'; // Custom CSS class to manipulate order
					break;
				}
			}
		}

        add_action( 'load-' . $submenu_css, [$this, 'do_admin_enqueue'] );
    }

    public function do_admin_enqueue() {
        add_action( 'admin_enqueue_scripts', [$this, 'enqueue_admin_css'] );
    }

    public function enqueue_admin_css() {
        wp_enqueue_style( 'stock-mgmt-css', STOCKMGMT_PLUGIN_URL . 'resources/css/stock-mgmt.css', [], $this->version, 'all' );
    }

    public function register_stock_quantity_list() {
        add_settings_section(
            'stock-quantities-table',
            '',
            [$this, 'stock_quantities_callback'],
            self::SETTINGS_PAGE,
            []
        );

        register_setting('stock-quantities-table-options', $this->option_name, [$this, 'save_post']);
    }

    /**
     * Save the post meta
     *
     * @param $input
     * @return mixed
     */
	function save_post(array $input) {
		$products = $input['products'] ?? null;

		if($products) {
			foreach ($products as $product_id => $product_meta) {
				$product_id = intval($product_id);
				$current_stock_quantity = intval($product_meta['current_stock_quantity']);
				$stock_quantity = intval($product_meta['stock_quantity']);
				$manage_stock = $product_meta['manage_stock'];

				$product = wc_get_product($product_id);


				$error_message = null;
				if ($product && $current_stock_quantity != $stock_quantity) {
					$result = wc_update_product_stock($product, $stock_quantity);

					$manage_stock = $product->get_manage_stock();

					if(false === $manage_stock) {
						$error_message = sprintf('You must enable manage stock management for product %s', $product_id);
					}


					if ($result || $result === 0) {
						add_settings_error(
							'woocommerce_stock',
							esc_attr('settings_updated'),
							'Stock updated for product ID ' . $product_id,
							'updated'
						);
					} else {
						if(empty($error_message)) {
							$error_message = sprintf('Failed to update stock for product ID %s', $product_id);
						}

						add_settings_error(
							'woocommerce_stock',
							esc_attr('settings_error'),
							$error_message,
							'error'
						);
					}

					$product->save();
				}
			}
		}

		return $input;
	}

    public function get_page_identifier()
    {
        return self::PAGE_IDENTIFIER;
    }

    public function get_manage_capability()
    {
        return 'manage_options';
    }

    public function show_page()
    {
        require_once STOCKMGMT_PLUGIN_PATH . 'pages/' . self::PAGE_TEMPLATE . '.php';
    }

    /*
     * The table output
     *
     *
     *
     */

    public function stock_quantities_callback($args) {
		$cols = "
			<th class='thumb column-thumb left-cell'>Image</th>
			<th class='left-cell'>Product Name</th>
			<th class='center-cell'>Current Stock</th>
			<th class='enable-stock center-cell'>Manage Stock</th>
			<th class='right-cell'>Stock Change (-/+)</th>
		";

		echo "
		<table class='pscaff-table pscaff-table--rounded widefat'>
			<thead>
				<tr class='header-row'>
					".$cols."
				<tr>
			</thead>
		";

		$this->render_table_body();

		echo "
			<tfoot>
				<tr class='footer-row'>
					".$cols."
				<tr>
			</tfoot>
		</table>
		";
    }

	public function render_table_body() {
		$productModel = new SQMGMT_ProductModel(null, null);
		$product_stock_list = $productModel->get_product_stock_list();

		echo '<tbody>';

		if($product_stock_list) {
			array_walk($product_stock_list, [$this, 'add_text_inputs'], $this->option_name);
		} elseif(null === $product_stock_list) {
			$add_product_url = admin_url('post-new.php?post_type=product');
			echo '
				<tr>
					<td colspan="4">
						<span>Start adding products to see data.  <a href="' . esc_url($add_product_url) . '">Add a new product</a>.</span>
					</td>
				</tr>
			';
		} else {
			echo '
				<tr>
					<td colspan="4">
						<span>Add products.</a>.</span>
					</td>
				</tr>
			';
		}

		echo '</tbody>';
	}

	/**
	 * Output all the input fields for the client variables
	 *
	 * @param $item
	 * @param $key
	 * @param $option_name
	 * @return void
	 */
	public function add_text_inputs($product, $key, $option_name) {
		$option_key = 'products';
		$stock_quantity_string_value = null;
		$enable_stock_string_value = null;
		$disabled = '';

		if (null === $product['stock_quantity'] ) {
			$stock_quantity_string_value = '<span>N/A<span>';
		} else {
			$stock_quantity_string_value = '<span>'.$product['stock_quantity'].'</span>';
		}

		if(!$product['manage_stock_enabled']) {
			$enable_stock_string_value = 'Enable Manage Stock.';

			$disabled = 'disabled';
		} else {
			$enable_stock_string_value = '<span style="color: green">enabled</span>';
		}

		echo '
            <tr>
				<td class="thumb column-thumb" data-colname="Image">
					<img style="display:block;" alt="' . esc_attr($product['name']) . '" width="40" height="40" src="'.esc_attr($product['image_url']).'" class="woocommerce-placeholder wp-post-image">
				</td>

				<td class=\'left-cell\'>
					<span class=\"product-name\"><strong>'.esc_attr($product['name']).'</strong></span>
					<div class="row-actions">
						<span class="id">ID: '.esc_attr($product['id']).'</span>
						<span class="edit">
						<a href="'.esc_attr($product['edit_link']).'">Edit</a>
						</span>
					</div>

				</td>

				<td class=\'center-cell\'>
					<span class=\"product-q\">'.$stock_quantity_string_value.'</span>
					<input type="hidden" name="'.$this->option_name.'['.$option_key.'][' . esc_attr($product['id']) . '][current_stock_quantity]" value="' . $product['stock_quantity'] . '">
				</td>

				<td class="enable-stock center-cell">
					<div>
						'.$enable_stock_string_value.'
					</div>
				</td>

			   <td class="right-cell" style="min-width: 175px;">
				   <input style="min-width: 105px;" id="" min="0" type="number" name="'.$this->option_name.'['.$option_key.'][' . esc_attr($product['id']) . '][stock_quantity]" placeholder="" value="' . $product['stock_quantity'] . '" '.$disabled.'>
			   </td>
            </tr>
    	';
	}

	public function reorder_woocommerce_submenu( $menu_order ) {
		if ( is_admin() ) {
			global $submenu;
			if ( isset( $submenu['woocommerce'] ) && is_array( $submenu['woocommerce'] ) ) {
				$home_menu = null;
				$orders_menu = null;
				$custom_menu = null;
				$remaining_menus = [];

				foreach ( $submenu['woocommerce'] as $submenu_item ) {
					if ( isset( $submenu_item[2] ) ) {
						if ( $submenu_item[2] == 'wc-admin' ) {
							$home_menu = $submenu_item;
						} elseif ( $submenu_item[2] == 'edit.php?post_type=shop_order' ) {
							$orders_menu = $submenu_item;
						} elseif ( $submenu_item[2] == 'sqmgmt-settings' ) {
							$custom_menu = $submenu_item;
						} else {
							$remaining_menus[] = $submenu_item;
						}
					}
				}

				$submenu['woocommerce'] = [];
				if ( $home_menu ) {
					$submenu['woocommerce'][] = $home_menu;
				}
				if ( $orders_menu ) {
					$submenu['woocommerce'][] = $orders_menu;
				}
				if ( $custom_menu ) {
					$submenu['woocommerce'][] = $custom_menu;
				}
				$submenu['woocommerce'] = array_merge( $submenu['woocommerce'], $remaining_menus );
			}
		}
		return $menu_order;
	}
}
