<?php
namespace SfxhTaxes\Shortcodes;

use SfxhTaxes\Shortcodes\SfxhTaxesShortcodes;
use SfxhTaxes\Includes\SfxhTaxesProductVariationModel;

class SfxhTaxesProductTableShortcode extends SfxhTaxesShortcodes {
	public $option_name = 'sfxh_taxes_product_table';

    public function set_sc_settings() {
        $this->sc_settings = [
            'name' => 'sfxh_taxes_product_table',
			'handle' => 'sfxh-taxes-product-table',
			'css_handle' => 'sfxh-taxes-product-table',
        ];
    }

    public function render_shortcode($atts, $content = null) {
        $user = wp_get_current_user();
        
        if(isset($this->sc_settings['handle']) && $this->sc_settings['handle'] ) {
            wp_enqueue_script($this->sc_settings['handle']);
        }

		if(isset($this->sc_settings['css_handle']) && $this->sc_settings['css_handle'] ) {
            wp_enqueue_style($this->sc_settings['css_handle']);
        }

        ob_start();
            $this->stock_quantities_callback();
        return ob_get_clean();
    }

    /*
     * The table output
     *
     *
     *
     */

	 public function stock_quantities_callback() {
		$sfxh_product_id = get_post_meta(get_the_ID(), 'sfxh_taxes_product_id', true);
		$current_product_id = $sfxh_product_id ?? get_the_ID();

		$args = [
			'post_type'   => 'product',
			'post_status' => 'publish',
			'limit'       => -1,
			'orderby'     => 'date',
			'order'       => 'DESC',
			'include'     => [$current_product_id],
		];

		$products = wc_get_products($args);
		$productModel = new SfxhTaxesProductVariationModel(null, $products);
		$product_stock_list = $productModel->get_product_stock_list();
	
		echo '<div class="otslr-product-stock-list" id="otslr-product-stock-list">';
	
		$attribute_keys = [];
	
		foreach ($product_stock_list as $product) {
			if (!empty($product['variations'])) {
				foreach ($product['variations'] as $variation) {
					if (!empty($variation['attributes'])) {
						foreach ($variation['attributes'] as $key => $val) {
							$normalized_key = strtolower(str_replace(['-', '_'], ' ', trim($key)));
							if ($normalized_key !== 'nominal size' && !in_array($key, $attribute_keys)) {
								$attribute_keys[] = $key;
							}
						}
					}
				}
			}
		}		
	
		$cols = "
			<div class='otslr-frozen left-cell'><div class='otslr-table-header'>TAX NAME</div></div>
			<div class='center-cell'><div class='otslr-table-header'>COUNTRY<br />CODE</div></div>
			<div class='center-cell'><div class='otslr-table-header'>STATE<br />CODE</div></div>
			<div class='center-cell'><div class='otslr-table-header'>CITY</div></div>
			<div class='center-cell'><div class='otslr-table-header'>STATE<br />TAX (%)</div></div>
			<div class='center-cell'><div class='otslr-table-header'>LOCAL<br />TAX (%)</div></div>
			<div class='center-cell'><div class='otslr-table-header'>OTHER<br />TAX (%)</div></div>
			<div class='center-cell'><div class='otslr-table-header'>TOTAL<br />TAX (%)</div></div>
			<div class='center-cell'><div class='otslr-table-header'>PER NIGHT<br />FEE ($)</div></div>
		";

		
	
		echo "
		<div class='pscaff-table pscaff-table--rounded widefat' cellpadding='0' cellspacing='0'>
			<div class='otslr-thead'>
				<div class='header-row'>
					" . $cols . "
				</div>
			</div>
		";
	
		$this->render_table_body(1, 20, $attribute_keys);
	
		echo "</div>";
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
    	$productModel = new SfxhTaxesProductVariationModel(null, $products);
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
	}

	public function render_table_body($page = 1, $per_page = 20, $attribute_keys = []) {
		$otslr_product_id = get_post_meta(get_the_ID(), '_otslr_product_id', true);
		$current_product_id = !empty($otslr_product_id) ? $otslr_product_id : get_the_ID();
		
		$args = [
			'post_type'   => 'product',
			'post_status' => 'publish',
			'limit'       => -1, // Get all products, but we'll paginate variations manually
			'orderby'     => 'date',
			'order'       => 'DESC',
			'include'     => [3026]
		];
	
		$products = wc_get_products($args);
		$productModel = new SfxhTaxesProductVariationModel(null, $products);
		$product_stock_list = $productModel->get_product_stock_list();
	
		echo '<div id="sqmgmt-product-stock-list">';
	
		if ($product_stock_list) {
			foreach ($product_stock_list as $product) {
				if (!empty($product['variations'])) {
					$total_variations = count($product['variations']);
					$total_pages = ceil($total_variations / $per_page);
					$offset = ($page - 1) * $per_page;
					$variations = array_slice($product['variations'], $offset, $per_page);
	
					foreach ($variations as $variation) {
						$this->add_text_inputs($variation, $product['id'], $this->option_name, $attribute_keys);
					}
	
					// Output pagination data as a data attribute
					echo '<div style="visibility: hidden; display:none;">
						<div data-total-pages="' . esc_attr($total_pages) . '" id="sqmgmt-pagination-info"></div>
					</div>';
				}
			}
		} else {
			echo '<div><div>No variations found.</div></div>';
		}
	
		echo '</div>';
	}

	public function add_nice_select_options($variation, $product_id, $option_name) {
		if (!$variation) {
			return;
		}
	
		echo '<option value="' . esc_attr($variation['sku']) . '">' 
			. esc_html($variation['sku'])
			. '</option>';
	}

	public function add_text_inputs($variation, $product_id, $option_name) {
		if (!$variation) {
			return;
		}
	
		$attribute_cells = '';
	
		if (!empty($variation['attributes']) && is_array($variation['attributes'])) {
			foreach ($variation['attributes'] as $attr_name => $attr_value) {
				$attribute_cells .= '<div class="attr-cell center-cell">' . esc_html($attr_value) . '</div>';
			}
		}

		$other_tax_info = isset($variation['other_tax_info']) && $variation['other_tax_info'] ? $variation['other_tax_info'] : '';
		
		if($other_tax_info) {
			$other_tax_info_html = '<span class="speech-bubble">' . $other_tax_info  . '</span>';
		}

		echo '
			<div class="otslr-table-row otslr-' . esc_attr($variation['sku']) . '">
				
				<!-- SKU / TAX NAME -->
				<div class="otslr-frozen left-cell">
					<span class="product-name">' . esc_attr($variation['sku']) . '</span>
				</div>

				<!-- COUNTRY CODE -->
				<div class="center-cell">
					<span>' . esc_attr($variation['country_code'] ?? 'N/A') . '</span>
				</div>
	
				<!-- STATE CODE -->
				<div class=" center-cell">
					<span>' . esc_attr($variation['state_code'] ?? 'N/A') . '</span>
				</div>
	
				<!-- CITY -->
				<div class="center-cell">
					<span>' . esc_attr($variation['city'] ?? 'N/A') . '</span>
				</div>
	
				<!-- STATE TAX RATE -->
				<div class="center-cell">
					<span>' . esc_attr($variation['state_tax_rate'] ?? 'N/A') . '</span>
				</div>
	
				<!-- LOCAL TAX RATE -->
				<div class="center-cell">
					<span>' . esc_attr($variation['local_tax_rate'] ?? 'N/A') . '</span>
				</div>

				<!-- OTHER TAX RATE -->
				<div class="center-cell speech-bubble-wrapper">
					<span>' . esc_attr($variation['other_tax_rate'] ?? 'N/A') . '</span>
					'.$other_tax_info_html.'
					
				</div>
	
				<!-- TOTAL TAX RATE -->
				<div class="center-cell">
					<span>' . esc_attr($variation['total_tax_rate'] ?? 'N/A') . '</span>
				</div>
	
				<!-- FIXED PER NIGHT TAX FEE -->
				<div class="center-cell">
					<span>' . esc_attr($variation['tax_fixed_per_night'] ?? 'N/A') . '</span>
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