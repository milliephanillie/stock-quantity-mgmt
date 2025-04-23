<?php

// Tax Rate Fields for Variations (State, Local, Per Night)
add_action('woocommerce_product_after_variable_attributes', 'add_tax_fields_to_variations', 10, 3);
function add_tax_fields_to_variations($loop, $variation_data, $variation) {
    woocommerce_wp_text_input(array(
        'id'          => 'state_tax_rate[' . $variation->ID . ']',
        'label'       => __('State Tax Rate', 'woocommerce'),
        'placeholder' => 'Enter state tax rate',
        'desc_tip'    => true,
        'description' => __('State tax rate as a percentage.', 'woocommerce'),
        'type'        => 'number',
        'custom_attributes' => [
            'step' => '0.01',
            'min'  => '0'
        ],
        'value'       => get_post_meta($variation->ID, 'state_tax_rate', true),
    ));

    woocommerce_wp_text_input(array(
        'id'          => 'local_tax_rate[' . $variation->ID . ']',
        'label'       => __('Local Tax Rate', 'woocommerce'),
        'placeholder' => 'Enter local tax rate',
        'desc_tip'    => true,
        'description' => __('Local tax rate as a percentage.', 'woocommerce'),
        'type'        => 'number',
        'custom_attributes' => [
            'step' => '0.01',
            'min'  => '0'
        ],
        'value'       => get_post_meta($variation->ID, 'local_tax_rate', true),
    ));

    woocommerce_wp_text_input(array(
        'id'          => 'county_tax_rate[' . $variation->ID . ']',
        'label'       => __('County Tax Rate', 'woocommerce'),
        'placeholder' => 'Enter county tax rate',
        'desc_tip'    => true,
        'description' => __('County tax rate as a percentage.', 'woocommerce'),
        'type'        => 'number',
        'custom_attributes' => [
            'step' => '0.01',
            'min'  => '0'
        ],
        'value'       => get_post_meta($variation->ID, 'county_tax_rate', true),
    ));

    woocommerce_wp_text_input(array(
        'id'          => 'other_tax_rate[' . $variation->ID . ']',
        'label'       => __('Other Tax Rate', 'woocommerce'),
        'placeholder' => 'Enter other tax rate',
        'desc_tip'    => true,
        'description' => __('Other tax rate as a percentage.', 'woocommerce'),
        'type'        => 'number',
        'custom_attributes' => [
            'step' => '0.01',
            'min'  => '0'
        ],
        'value'       => get_post_meta($variation->ID, 'other_tax_rate', true),
    ));

    woocommerce_wp_text_input(array(
        'id'          => 'other_tax_rate_info[' . $variation->ID . ']',
        'label'       => __('Other Tax Rate Info', 'woocommerce'),
        'placeholder' => 'Enter other tax rate',
        'desc_tip'    => true,
        'description' => __('Other tax rate info/name.', 'woocommerce'),
        'type'        => 'text',
        'value'       => get_post_meta($variation->ID, 'other_tax_rate_info', true),
    ));

    woocommerce_wp_text_input(array(
        'id'          => 'other_tax_rate_2[' . $variation->ID . ']',
        'label'       => __('Other Tax Rate (2)', 'woocommerce'),
        'placeholder' => 'Enter other tax rate (2)',
        'desc_tip'    => true,
        'description' => __('Other tax rate as a percentage.', 'woocommerce'),
        'type'        => 'number',
        'custom_attributes' => [
            'step' => '0.01',
            'min'  => '0'
        ],
        'value'       => get_post_meta($variation->ID, 'other_tax_rate_2', true),
    ));

    woocommerce_wp_text_input(array(
        'id'          => 'other_tax_rate_info[' . $variation->ID . ']',
        'label'       => __('Other Tax Rate Info (2)', 'woocommerce'),
        'placeholder' => 'Enter other tax rate info (2)',
        'desc_tip'    => true,
        'description' => __('Other tax rate info/name.', 'woocommerce'),
        'type'        => 'text',
        'value'       => get_post_meta($variation->ID, 'other_tax_rate_info_2', true),
    ));

    woocommerce_wp_text_input(array(
        'id'          => 'tax_fixed_per_night[' . $variation->ID . ']',
        'label'       => __('Per Night (tax)', 'woocommerce'),
        'placeholder' => 'Enter per night tax fee',
        'desc_tip'    => true,
        'description' => __('Fixed per night tax fee', 'woocommerce'),
        'type'        => 'number',
        'custom_attributes' => [
            'step' => '0.01',
            'min'  => '0'
        ],
        'value'       => get_post_meta($variation->ID, 'tax_fixed_per_night', true),
    ));
}

add_action('woocommerce_save_product_variation', 'sfxh_save_tax_fields_for_variations', 10, 2);
function sfxh_save_tax_fields_for_variations($variation_id, $i) {
    $fields = ['state_tax_rate', 'local_tax_rate', 'other_tax_rate', 'other_tax_rate_info', 'tax_fixed_per_night'];

    foreach ($fields as $field) {
        if (isset($_POST[$field][$variation_id])) {
            update_post_meta($variation_id, $field, sanitize_text_field($_POST[$field][$variation_id]));
        }
    }
}