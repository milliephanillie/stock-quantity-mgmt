<?php
use StockMGMT\Includes\SQMGMT_ProductModel;

class Test_ProductModel extends WP_UnitTestCase {
	private $products;

	public function setUp(): void
	{
		$this->products = [
			15 => new \WC_Product_Simple([
				'name' => 'Test Health Gummies',
				'slug' => 'test-health-gummies',
				'status' => 'publish',
				'manage_stock' => true,
				'stock_quantity' => 5,
				'stock_status' => 'instock',
			]),
			16 => new \WC_Product_Simple([
				'name' => 'Test Woo Tote Bag',
				'slug' => 'test-woo-tote-bag',
				'status' => 'publish',
				'manage_stock' => true,
				'stock_quantity' => 7,
				'stock_status' => 'instock',
			]),
			17 => new \WC_Product_Simple([
				'name' => 'Test Air Pet Filter',
				'slug' => 'test-air-pet-filter',
				'status' => 'publish',
				'manage_stock' => false,
				'stock_quantity' => null,
				'stock_status' => 'outofstock'
			])
		];
	}

	public function test_instance_of_productmodel() {
		$productMock = $this->createMock(\WC_Product_Query::class);
		$this->assertInstanceOf(SQMGMT_ProductModel::class, new SQMGMT_ProductModel(null, $productMock));
	}

	public function test_product_stock_list_returns_array_of_products() {
		$product = new \WC_Product_Simple();
		$product->set_id(26);
		$product->set_name('Test Health Gummies');
		$product->set_status('publish');
		$product->set_manage_stock(true);
		$product->set_stock_quantity(5);
		$product->set_stock_status('instock');
		$edit_link = admin_url('post.php?post=26&action=edit');
		$product->edit_link = $edit_link;

		$this->products =[
			$product
		];

		$productMock = $this->createMock(\WC_Product_Query::class);
		$productMock->method('get_products')->willReturn($this->products);

		$productModel = new SQMGMT_ProductModel(null, $productMock);

		$stock_list = $productModel->get_product_stock_list();

		$this->assertEquals('26', $stock_list[0]['id']);
		$this->assertEquals('Test Health Gummies', $stock_list[0]['name']);
		$this->assertEquals(5, $stock_list[0]['stock_quantity']);
		$this->assertEquals('instock', $stock_list[0]['stock_status']);
		$this->assertEquals(true, $stock_list[0]['manage_stock_enabled']);
	}
}
