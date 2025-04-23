<?php
namespace SfxhTaxes\Shortcodes;

use SfxhTaxes\Shortcodes\SfxhTaxesShortcodes;
use SfxhTaxes\Includes\SfxhTaxesProductVariationModel;

class SfxhTaxesProductSelectShortcode extends SfxhTaxesShortcodes {
	public $option_name = 'sfxh_taxes_product_table';

    public function set_sc_settings() {
        $this->sc_settings = [
            'name' => 'sfxh_taxes_select',
            'handle' => 'sfxh-taxes-select',
            'css_handle' => 'sfxh-taxes-select',
        ];
    }

    public function render_shortcode($atts, $content = null) {
        $user = wp_get_current_user();

        if( $this->sc_settings['css_handle'] ) {
            wp_enqueue_style($this->sc_settings['css_handle']);
        }
        
        if( $this->sc_settings['handle'] ) {
            wp_enqueue_script($this->sc_settings['handle']);
        }

        ob_start();
            $this->stock_quantities_callback();
        return ob_get_clean();
    }

    /*
     * The select output
     *
     *
     *
     */

     public function stock_quantities_callback() {
		$this->render_select();
    }

	public function render_select() {
		$current_product_id = get_the_ID();
		$args = [
			'post_type'   => 'product',
			'post_status' => 'publish',
			'limit'       => -1,
			'orderby'     => 'date',
			'order'       => 'DESC',
			'include'     => [$current_product_id],
		];

    	$products = wc_get_products($args);
    	$productModel = new SQMGMT_ProductVariationModel(null, $products);
		$product_stock_list = $productModel->get_product_stock_list();
		
		echo '<div class="otslr-nice-select-wrapper">';
		echo '<select class="otslr-nice-select">';

		foreach ($product_stock_list as $product) {
			if (!empty($product['variations'])) {
				foreach ($product['variations'] as $variation) {
					$this->add_nice_select_options($variation, $product['id'], $this->option_name);
				}
			}
		}

		echo '</select>';
		echo '</div>';

        foreach ($product_stock_list as $product) {
			if (!empty($product['variations'])) {
                $index = 1;
				foreach ($product['variations'] as $variation) {
					$this->add_text_inputs($variation, $product['id'], $this->option_name, $index);
                    $index++;
				}
			}
		}
	}

	public function add_nice_select_options($variation, $product_id, $option_name) {
		if (!$variation) {
			return;
		}
	
		echo '<option value="'. esc_attr(strtolower(str_replace(' ', '-', $variation['sku']))) .'">' 
			. esc_html($variation['description'])
			. '</option>';
	}
	
    public function add_text_inputs($variation, $product_id, $option_name, $index) {
        if (!$variation) {
            return;
        }

        $display = $index == 1 ? 'block' : 'none';
    
        echo '
            <div class="otslr-variation-details otslr-' . esc_attr(strtolower(str_replace(' ', '-', $variation['sku']))) . '" style="display: '.$display.';">

                <div class="variation-row">
                    <span class="variation-label">Item:</span>
                    <span class="variation-value"><strong>' . esc_attr($variation['sku']) . '</strong></span>
                </div>
    
                <div class="variation-row">
                    <span class="variation-label">Description:</span>
                    <span class="variation-value">' . esc_attr($variation['description'] ?? 'N/A') . '</span>
                </div>
    
                <div class="variation-row">
                    <span class="variation-label">Carton Qty:</span>
                    <span class="variation-value">' . esc_attr($variation['carton_qty'] ?? 'N/A') . '</span>
                </div>
    
                <div class="variation-row">
                    <span class="variation-label">Skid Qty:</span>
                    <span class="variation-value">' . esc_attr($variation['skid_qty'] ?? 'N/A') . '</span>
                </div>
    
                <div class="variation-row">
                    <span class="variation-label">Order Qty:</span>
                    <span class="variation-value">' . esc_attr($variation['order_qty'] ?? 'N/A') . '</span>
                </div>
    
                <div class="variation-row">
                    <span class="variation-label">Product Drawing:</span>
                    <span class="variation-value">
                        ' . (!empty($variation['otslr_product_drawings']) ? '<a href="' . esc_url($variation['otslr_product_drawings']) . '" target="_blank">View Drawing</a>' : 'N/A') . '
                    </span>
                </div>
    
                <div class="variation-row">
                    <span class="variation-label">BIM File:</span>
                    <span class="variation-value">
                        ' . (!empty($variation['otslr_pdf']) ? '<a href="' . esc_url($variation['otslr_pdf']) . '" target="_blank">Download PDF</a>' : 'N/A') . '
                    </span>
                </div>
    
                <div class="variation-row">
                    <span class="variation-label">Actions:</span>
                    <span class="variation-value">
                        <a href="' . esc_url($variation['add_to_cart_link'] ?? '#') . '" class="sqmgmt-add-to-cart">+</a>
                        <a href="' . esc_url($variation['remove_from_cart_link'] ?? '#') . '" class="sqmgmt-remove-from-cart">-</a>
                    </span>
                </div>
            </div>
        ';
    }
    
	
	

    public function determine_disable_attr($product_id) {
        if ($product_id) {
            $product = wc_get_product($product_id);
    
            if ($product && $product->managing_stock()) {
                return $product->is_in_stock() && $product->get_stock_quantity() > 0;
            }
        }
    
        return false; 
    } 
}