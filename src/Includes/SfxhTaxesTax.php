<?php
namespace SfxhTaxes\Includes;
use SfxhTaxes\Options\SfxhTaxesOptions;

use HivePress\Models\Booking;
use HivePress\Models\Listing;
use HivePress\Models\User;
use HivePress\Helpers as hp;
use HivePress\Emails;

class SfxhTaxesTax {
    public function __construct() {
        add_filter('woocommerce_calc_taxes', '__return_false', 999999999);

        add_filter( 'woocommerce_product_get_tax_class', [ $this, 'custom_tax_class' ], 10, 2 );
        add_action('woocommerce_cart_calculate_fees', [$this, 'apply_local_tax'], 9999999);

        $this->tax_sheet();
    }

    public function apply_local_tax($cart) {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }
    
        foreach ($cart->get_cart() as $cart_item) {
            $product = $cart_item['data'];
    
            if (!$product instanceof \WC_Product) {
                continue;
            }
    
            $parent_id = $product->get_parent_id();
            $listing   = \HivePress\Models\Listing::query()->get_by_id($parent_id);
    
            if (!$listing) {
                continue;
            }
    
            $listing_id   = $listing->get_id();
            $variation_id = get_post_meta($listing_id, 'hp_tax_name', true);
    
            if (!$variation_id) {
                continue;
            }
    
            $line_total = $cart_item['line_total'];
    
            $taxable_fees_total = 0;
            foreach ($cart->get_fees() as $fee) {
                if (!empty($fee->taxable) && $fee->amount > 0) {
                    $taxable_fees_total += floatval($fee->amount);
                }
            }
    
            $total_taxable = $line_total + $taxable_fees_total;
    
            $tax_fields = [
                'state_tax_rate'      => 'State Tax',
                'local_tax_rate'      => 'Local Tax',
                'county_tax_rate'     => 'County Tax',
                'other_tax_rate'      => get_post_meta($variation_id, 'other_tax_rate_info', true) ?: 'Other Tax',
                'other_tax_rate_2'    => get_post_meta($variation_id, 'other_tax_rate_info_2', true) ?: 'Other Tax (2)',
            ];
    
            foreach ($tax_fields as $meta_key => $label) {
                $rate = (float) get_post_meta($variation_id, $meta_key, true);
                if ($rate > 0) {
                    $tax = $total_taxable * ($rate / 100);
                    $cart->add_fee($label, $tax, false);
                }
            }
    
            $flat_fee = (float) get_post_meta($variation_id, 'tax_fixed_per_night', true);
            if ($flat_fee > 0 && isset($cart_item['quantity'])) {
                $cart->add_fee('Per Night Tax', $flat_fee * $cart_item['quantity'], false);
            }
        }
    }

    public function custom_tax_class( $tax_class, $product ) {
        if ( ! $product instanceof \WC_Product ) {
            return $tax_class;
        }
    
        $this->current_product_id = $product->get_id(); // ✅ store it for later
    
        $parent_id = $product->get_parent_id();
        $listing   = \HivePress\Models\Listing::query()->get_by_id( $parent_id );
    
        if ( ! $listing ) {
            return $tax_class;
        }
    
        $listing_id   = $listing->get_id();
        $variation_id = get_post_meta( $listing_id, 'hp_tax_name', true );
    
        if ( ! $variation_id ) {
            return $tax_class;
        }
    
        $local_tax_rate = get_post_meta( $variation_id, 'local_tax_rate', true );
    
        return $tax_class;
    }

    public function tax_sheet() {
        add_filter(
            'hivepress/v1/meta_boxes',
            function( $meta_boxes ) {
                $variations = [];
        
                $product = wc_get_product( 3026 );
        
                if ( $product && $product->is_type( 'variable' ) ) {
                    foreach ( $product->get_children() as $variation_id ) {
                        $variation = wc_get_product( $variation_id );
        
                        if ( $variation ) {
                            $sku = $variation->get_sku();
                            if ( $sku ) {
                                $variations[ $variation_id ] = $sku;
                            }
                        }
                    }
                }
        
                $meta_boxes['listing_settings']['fields']['tax_name'] = [
                    'label'    => 'Tax Name',
                    'type'     => 'select',
                    'options'  => $variations, 
                    'required' => true,
                    '_order'   => 10,
                ];
        
                return $meta_boxes;
            });
        
    }
}