<?php
namespace SfxhTaxes\Includes;

use SfxhTaxes\Options\SfxhTaxesOptions;

class SfxhTaxesProductVariationModel {
	private $default_args = [
		'limit' => 20,
		'orderby' => 'date',
		'order' => 'DESC',
	];

	private $product_query = null;

	private $args;

	public function __construct($args = null, $products = null) {
		$this->args = $args ?? $this->default_args;
		$this->product_query = $products ?: wc_get_products($this->args);
	}

	public function get_product_stock_list() {
		$product_stock_list = [];
	
		if (!empty($this->product_query)) {
			foreach ($this->product_query as $product) {
				$image_id = $product->get_image_id();
				$image_url = $image_id ? wp_get_attachment_url($image_id) : wc_placeholder_img_src();
	
				$id = $product->get_id();
				$available_variations = [];
	
				if ($id && $product->is_type('variable')) {
					$variations = $product->get_children();
	
					foreach ($variations as $variation_id) {
						$single_variation = new \WC_Product_Variation($variation_id);
						$attributes = $single_variation->get_attributes();
					
						$state_tax = get_post_meta($variation_id, 'state_tax_rate', true);
						$local_tax = get_post_meta($variation_id, 'local_tax_rate', true);
						$other_tax = get_post_meta($variation_id, 'other_tax_rate', true);
						$total_tax = get_post_meta($variation_id, 'total_tax_rate', true);
					
						if ($total_tax === '') {
							if (is_numeric($state_tax) || is_numeric($local_tax)) {
								$total_tax = floatval($state_tax) + floatval($local_tax) + floatval($other_tax);
							} else {
								$total_tax = 'N/A';
							}
						}
					
						$available_variations[] = [
							'title'               => $single_variation->get_name(),
							'sku'                 => $single_variation->get_sku(),
							'description'         => $single_variation->get_description(),
							'state_tax_rate'      => $state_tax !== '' ? $state_tax : 'N/A',
							'local_tax_rate'      => $local_tax !== '' ? $local_tax : 'N/A',
							'other_tax_rate'      => $other_tax !== '' ? $other_tax : 'N/A',
							'total_tax_rate'      => $total_tax,
							'tax_fixed_per_night' => get_post_meta($variation_id, 'tax_fixed_per_night', true) ?: 'N/A',
							'country_code'        => $attributes['country-code'] ?? 'N/A',
							'state_code'          => $attributes['state-code'] ?? 'N/A',
							'city'                => $attributes['city'] ?? 'N/A',
							'attributes'          => $attributes,
						];
					}
					
	
					usort($available_variations, function($a, $b) {
						return strcmp($a['sku'], $b['sku']);
					});
				}
	
				$product_stock_list[] = [
					'id' => $id,
					'name' => $product->get_name(),
					'edit_link' => get_edit_post_link($id),
					'stock_quantity' => $product->get_stock_quantity(),
					'stock_status' => $product->get_stock_status(),
					'image_url' => $image_url,
					'manage_stock_enabled' => $product->get_manage_stock(),
					'variations' => $available_variations,
				];
			}
		}
	
		return $product_stock_list;
	}
	
	private function parse_nominal_size($size) {
		$size = strtolower(trim($size));
		$size = str_replace(['\"', '"', '”'], '', $size);
		if (preg_match('/([0-9\-\/\.]+)\s*x\s*([0-9\-\/\.]+)/', $size, $matches)) {
			return $this->fraction_to_float($matches[1]) * $this->fraction_to_float($matches[2]);
		}
		return 0;
	}

	private function fraction_to_float($str) {
		$str = trim($str);
		if (strpos($str, '-') !== false) {
			list($whole, $frac) = explode('-', $str);
			list($num, $den) = explode('/', $frac);
			return floatval($whole) + ($num / $den);
		}
		if (strpos($str, '/') !== false) {
			list($num, $den) = explode('/', $str);
			return $num / $den;
		}
		return floatval($str);
	}
}
