<?php if (!defined('ABSPATH')) exit; // Prevent direct access ?>

<?php if (!empty($product_stock_list)) : ?>
    <?php foreach ($product_stock_list as $product) : ?>
        <?php if (!empty($product['variations'])) : ?>
            <?php 
                $total_variations = count($product['variations']);
                $total_pages = ceil($total_variations / $per_page);
                $offset = ($page - 1) * $per_page;
                $variations = array_slice($product['variations'], $offset, $per_page);
            ?>
            <?php foreach ($variations as $variation) : ?>
                <tr class="otslr-<?php echo esc_attr($variation['sku']); ?>">
                    <td class="left-cell">
                        <span class="product-name"><strong><?php echo esc_html($variation['sku']); ?></strong></span>
                    </td>
                    <td class="left-cell">
                        <span><?php echo esc_html($variation['description'] ?? 'N/A'); ?></span>
                    </td>
                    <td class="center-cell">
                        <span><?php echo esc_html($variation['carton_qty'] ?? 'N/A'); ?></span>
                    </td>
                    <td class="center-cell">
                        <span><?php echo esc_html($variation['skid_qty'] ?? 'N/A'); ?></span>
                    </td>
                    <td class="center-cell">
                        <span><?php echo esc_html($variation['order_qty'] ?? 'N/A'); ?></span>
                    </td>
                    <td class="center-cell">
                        <?php if (!empty($variation['otslr_product_drawings'])) : ?>
                            <a href="<?php echo esc_url($variation['otslr_product_drawings']); ?>" target="_blank">Link</a>
                        <?php else : ?>
                            N/A
                        <?php endif; ?>
                    </td>
                    <td class="center-cell">
                        <?php if (!empty($variation['otslr_pdf'])) : ?>
                            <a href="<?php echo esc_url($variation['otslr_pdf']); ?>" target="_blank">Download PDF</a>
                        <?php else : ?>
                            N/A
                        <?php endif; ?>
                    </td>
                    <td class="center-cell">
                        <a href="<?php echo esc_url($variation['add_to_cart_link'] ?? '#'); ?>" class="sqmgmt-add-to-cart">+</a>
                    </td>
                    <td class="center-cell">
                        <a href="<?php echo esc_url($variation['remove_from_cart_link'] ?? '#'); ?>" class="sqmgmt-remove-from-cart">-</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <tr style="visibility: hidden; display:none;">
                <td colspan="9" data-total-pages="<?php echo esc_attr($total_pages); ?>" id="sqmgmt-pagination-info"></td>
            </tr>
        <?php endif; ?>
    <?php endforeach; ?>
<?php else : ?>
    <tr>
        <td colspan="9">No variations.</td>
    </tr>
<?php endif; ?>
