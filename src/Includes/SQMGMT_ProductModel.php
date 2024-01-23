<?php
namespace StockMGMT\Includes;

use StockMGMT\Options\SQMGMT_Options;

class SQMGMT_ProductModel {
	const INITIAL_DEMO_DATA = [
		[
			"id" => 15,
			"name" => "WooCommerce Tote Bag",
			"stock_quantity" => 5,
			"image_url" => "WordPress-Totebag.webp",
			"stock_enabled" => null
		],
		[
			"id" => 14,
			"name" => "Air Pet Filters",
			"stock_quantity" => 3,
			"image_url" => "air-pet-filters.webp",
			"stock_enabled" => null
		],
		[
			"id" => 13,
			"name" => "Health Gummies",
			"stock_quantity" => 6,
			"image_url" => "premium-gummies.jpeg",
			"stock_enabled" => null
		]
	];

	private $default_args = [
		'limit' => 10,
		'orderby' => 'date',
		'order' => 'DESC',
	];

	private $product_query = null;

	private $args;

	public function __construct($args = null, \WC_Product_Query $product_query = null) {
		$this->args = $args ?? $this->default_args;
		if(class_exists(\WC_Product_Query::class)) {
			$this->product_query = $product_query ?: new \WC_Product_Query($this->args);
		}
	}

	public function get_product_stock_list() {
		$product_stock_list = null;

		if($this->product_query) {
			$products = $this->product_query->get_products();

			if(!empty($products)) {
				$product_stock_list = [];

				foreach ($products as $product) {
					$image_id = $product->get_image_id();
					$image_url = null;
					if($image_id) {
						$image_url = wp_get_attachment_url($image_id);
					}

					$id = $product->get_id();

					$product_stock_list[] = array(
						'id' => $id,
						'name' => $product->get_name(),
						'edit_link' => get_edit_post_link($id),
						'stock_quantity' => $product->get_stock_quantity(),
						'stock_status' => $product->get_stock_status(),
						'image_url' => $image_url ?? wc_placeholder_img_src(),
						"manage_stock_enabled" => $product->get_manage_stock(),
					);
				}
			}
		}

		return $product_stock_list;
	}
}
