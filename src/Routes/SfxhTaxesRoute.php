<?php
namespace SfxhTaxes\Routes;

use SfxhTaxes\Includes\SfxhTaxesProductVariationModel;

class SfxhTaxesRoute extends SfxhTaxesRoutes {
    protected $routes = [
        'product_stock_list' => [
            'methods' => 'GET',
            'callback' => 'product_stock_list',
            'path' => '/product-stock-list',
            'permission_callback' => '__return_true',
        ],
    ];

    public function boot() {
        $this->register_routes();
    }

    public function product_stock_list(\WP_REST_Request $request) {
        $params = $request->get_params();
        $page = isset($params['page']) ? max(1, intval($params['page'])) : 1;
        $post_id = isset($params['post_id']) ? max(1, intval($params['post_id'])) : 1;

        if(!$post_id) {
            return new \WP_REST_Response([
                'status' => 'error',
                'message' => 'Missing post ID param.'
            ], 404);
        }

        $per_page = 3; 

        // Fetch only variable products
        $args = [
            'limit'     => -1, // Get all products (pagination is handled in variations)
            'orderby'   => 'date',
            'order'     => 'DESC',
            'type'      => 'variable', // Ensures we only get products with variations
            'include' => [$post_id]
        ];

        $products = wc_get_products($args);
        $total_products = count($products);
        $total_pages = ceil($total_products / $per_page);

        // Instantiate the variation model and get variations
        $product_model = new SfxhTaxesProductVariationModel(null, $products);
        $product_stock_list = $product_model->get_product_stock_list();

        $template_path = SFXH_TAXES_PLUGIN_PATH . 'templates/product-stock-list.php';

        if (file_exists($template_path)) {
            ob_start();
            include $template_path;
            $html_output = ob_get_clean();
            return new \WP_REST_Response([
                'status' => 'success',
                'data' => $html_output,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $total_pages,
                    'per_page' => $per_page,
                    'total_products' => $total_products
                ]
            ], 200);
        }

        return new \WP_REST_Response([
            'status' => 'error',
            'message' => 'Template not found.'
        ], 404);
    }
}
