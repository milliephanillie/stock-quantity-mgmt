<?php
namespace StockMGMT\Includes;

use StockMGMT\Options\SQMGMT_Options;

class SQMGMT_ProductModel {
	const INITIAL_DEMO_DATA = [
		[
			"id" => 15,
			"name" => "WooCommerce Tote Bag",
			"stock_quantity" => 5,
			"image_url" => "WordPress-Totebag.webp"
		],
		[
			"id" => 14,
			"name" => "Air Pet Filters",
			"stock_quantity" => 3,
			"image_url" => "air-pet-filters.webp"
		],
		[
			"id" => 13,
			"name" => "Health Gummies",
			"stock_quantity" => 6,
			"image_url" => "premium-gummies.jpeg"
		]
	];

	private $demo_data = null;

	public function __construct() {
		$this->settings = SQMGMT_Options::get_option('settings') ?? [];

		$this->set_use_demo_data();
		$this->set_demo_data();
	}

	public function set_use_demo_data() {
		$this->use_demo_data = $this->settings['use_demo_data'] ?? null;
	}

	public function set_demo_data() {
		if(!$this->settings['products']) {
			$this->settings['products'] = self::INITIAL_DEMO_DATA;
			SQMGMT_Options::update_option('settings', $this->settings);
		}

		$this->demo_data = array_intersect_key(
			array_merge(self::INITIAL_DEMO_DATA, $this->settings['products']),
			self::INITIAL_DEMO_DATA
		);
	}

	public function get_product_stock_list() {
		$products_per_page = 10;

		$args = [
			'limit' => $products_per_page,
			'orderby' => 'date',
			'order' => 'DESC',
		];

		$product_stock_list = null;

		if(class_exists(\WC_Product_Query::class) && !$this->use_demo_data) {
			$query = new \WC_Product_Query($args);
			$products = $query->get_products();

			$product_stock_list = [];

			foreach ($products as $product) {
				$image_id = $product->get_image_id();
				$image_url = null;
				if($image_id) {
					$image_url = wp_get_attachment_url($image_id);
				}


				$product_stock_list[] = array(
					'id' => $product->get_id(),
					'name' => $product->get_name(),
					'stock_quantity' => $product->get_stock_quantity(),
					'image_url' => $image_url ?? wc_placeholder_img_src(),
				);
			}
		}

		return $product_stock_list;
	}
}
