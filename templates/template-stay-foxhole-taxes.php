<?php
/**
 * Template Name: Stay Foxhole Taxes
 * Description: A custom page template for displaying the Foxhole Taxes layout.
 */


get_header() ?>

<div class="otslr-single-product">
    <div class="otslr-full-width otslr-single-product__hero-bg" style="background-image: url(<?php echo get_stylesheet_directory_uri() . '/images/single-product-hero-bg.png'; ?>);">
        <div class="otslr-single-product-container">
            <div class="otslr-single-product--hero">
                <div class="otslr-single-product--hero-inner">
                    <div>
                        <div class="otslr-product-single--thumbnail-wrapper otslr-middle" style="padding: 72px;">
                            <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'full'); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
                        </div>
                        <?php
                            $product = wc_get_product(get_the_ID());
                            if ( $product instanceof WC_Product ) {
                                $attachment_ids = $product->get_gallery_image_ids();

                                if ( $attachment_ids && is_array($attachment_ids) ) : ?>
                                    <div class="otslr-spacer no-border small"></div>
                                    <div class="otslr-product-gallery-wrapper">
                                        <?php foreach ( $attachment_ids as $attachment_id ) :
                                            $img_url = wp_get_attachment_url( $attachment_id ); ?>
                                            <div class="otslr-product-gallery-image-wrapper">
                                                <img src="<?php echo esc_url( $img_url ); ?>" alt="">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif;
                            }
                            ?>

                    </div>
                    <div>
                        <h1><?php echo get_the_title(); ?></h1>

                        <div class="otslr-single-product__description">
                            <p><?php echo get_the_content(); ?>
                        </div>

                        <div class="otslr-spacer"></div>

                        <div class="otslr-single-product__destails">
                            <h4>What types of occupancy taxes and fees apply?</h4>
                            <p>Occupancy tax rates in the United States vary widely depending on the state and city where lodging is provided. These taxes are typically added to the cost of short-term stays in hotels, motels, vacation rentals, and other accommodations. Each state sets its own base rate, and many local governments impose additional taxes on top of that. As a result, the total tax a guest pays can differ significantly from one location to another.

                            <ul>
  <li><strong>STATE TAX (%)</strong>: The percentage of occupancy tax imposed by the state government on short-term lodging.</li>
  <li><strong>LOCAL TAX (%)</strong>: The additional percentage charged by local jurisdictions such as counties or cities.</li>
  <li><strong>TOTAL TAX (%)</strong>: The combined percentage of both state and local taxes applied to the cost of accommodation.</li>
  <li><strong>PER NIGHT FEE ($)</strong>: A flat dollar amount added per night, often set by local ordinances, regardless of the accommodation price.</li>
</ul>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="otslr-full-width otslr-single-product__table">
        <div class="otslr-single-product-container">
            <div class="otslr-single-product__table_table">
                <?php echo do_shortcode('[sfxh_taxes_product_table]'); ?>    
            </div>
            <div class="otslr-spacer no-border"></div>
            <div class="otslr-single-product__actions" style="display: flex; justify-content: flex-end;">
                <div class="otslr-single-product__actions otslr-start">
                    <button style="color: black !important;" id="otslr-prev" class="otslr-prev otslr-button-primary"><</button>
                    <button style="color: black !important;" id="otslr-next" class="otslr-next otslr-button-primary">></button>
                </div>
            </div>  
        </div>
             
    </div>
</div>

<?php get_footer() ?>