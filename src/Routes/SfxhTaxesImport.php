<?php
namespace SfxhTaxes\Routes;

use SfxhTaxes\Includes\SfxhTaxesProductVariationModel;

class SfxhTaxesImport extends SfxhTaxesRoutes {
    protected $routes = [
        'product_stock_list' => [
            'methods' => 'POST',
            'callback' => 'import',
            'path' => '/import',
            'permission_callback' => '__return_true',
        ],
    ];

    protected $columns = [];

    public function boot() {
        $this->register_routes();
    }

    public function set_columns($columns) {
        $this->columns = array_map('trim', $columns);
    }

    public function import(\WP_REST_Request $request) {
        add_filter('wp_check_filetype_and_ext', function($data, $file, $filename, $mimes) {
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if ($ext === 'svg') {
                $data['ext']  = 'svg';
                $data['type'] = 'image/svg+xml';
            }
            return $data;
        }, 10, 4);

        if (!function_exists('media_handle_sideload')) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
        }

        $file = $request->get_file_params();
        if (!$file || !isset($file['file']) || !file_exists($file['file']['tmp_name'])) {
            return new \WP_Error('missing_file', 'Valid file not uploaded', ['status' => 400]);
        }

        $rows = array_map('str_getcsv', file($file['file']['tmp_name']));
        if (!$rows || count($rows) < 2) {
            return new \WP_Error('invalid_csv', 'CSV is empty or malformed', ['status' => 400]);
        }

        $headers = array_map('trim', $rows[0]);
        $this->set_columns($headers);

        $csv = array_map(function ($row) use ($headers) {
            return array_combine($headers, $row);
        }, array_slice($rows, 1));

        $results = [];

        foreach ($csv as $row) {
            $sku = trim($row['SKU'] ?? '');
            $link = trim($row['Link'] ?? '');
        
            if (!$sku || !$link || !preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $link, $matches)) {
                continue;
            }
        
            $product_id = wc_get_product_id_by_sku($sku);
            if (!$product_id || get_post_type($product_id) !== 'product_variation') {
                continue;
            }
        
            $file_id = $matches[1];
            $download_url = 'https://drive.google.com/uc?export=download&id=' . $file_id;
        
            $tmp_file = download_url($download_url);
            if (is_wp_error($tmp_file)) {
                continue;
            }
        
            $raw_sku = strtolower($sku);
            $raw_sku = preg_replace('/[^a-z0-9\-]/', '-', $raw_sku);
            $raw_sku = preg_replace('/-+/', '-', $raw_sku);
            $raw_sku = trim($raw_sku, '-');
        
            $filename = sanitize_file_name($raw_sku . '.svg');
            $sanitized_path = dirname($tmp_file) . '/' . $filename;
        
            rename($tmp_file, $sanitized_path); // rename the downloaded temp file
        
            $file_array = [
                'name'     => $filename,
                'tmp_name' => $sanitized_path,
            ];
        
            $attachment_id = media_handle_sideload($file_array, 0, $sku);
            if (is_wp_error($attachment_id)) {
                @unlink($tmp_file);
                continue;
            }
        
            $attachment_url = wp_get_attachment_url($attachment_id);

            update_post_meta($product_id, 'otslr_product_drawings', $attachment_url);
        
            $results[] = [
                'sku'            => $sku,
                'attachment_url' => $attachment_url,
            ];
        }
        

        return rest_ensure_response($results);
    }

    public function get_direct_google_drive_link($shared_url) {
        if (preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $shared_url, $matches)) {
            $file_id = $matches[1];
            return 'https://drive.google.com/uc?export=download&id=' . $file_id;
        }
        return false;
    }
}
