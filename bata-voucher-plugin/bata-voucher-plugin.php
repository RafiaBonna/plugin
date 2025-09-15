<?php
/*
Plugin Name: Bata Voucher Plugin
Description: A plugin to create a voucher section with products for a WooCommerce store.
Version: 1.1
Author: Rafia
Author URI: https://rafiabonna.top
Text Domain: bata-voucher-plugin
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Enqueue styles and scripts
function enqueue_bata_voucher_assets() {
    wp_enqueue_style('bata-voucher-style', plugin_dir_url(__FILE__) . 'assets/css/style.css', [], '1.1');
    wp_enqueue_script('bata-voucher-script', plugin_dir_url(__FILE__) . 'assets/js/script.js', ['jquery'], '1.1', true);

    wp_localize_script('bata-voucher-script', 'bataVoucher', [
        'ajax_url' => admin_url('admin-ajax.php')
    ]);
}
add_action('wp_enqueue_scripts', 'enqueue_bata_voucher_assets');

// Add custom field to product page
function add_voucher_value_field() {
    $args = [
        'id'            => 'bata_voucher_value',
        'label'         => __('Voucher Value (TK)', 'bata-voucher-plugin'),
        'class'         => 'bata-voucher-value-class',
        'desc_tip'      => true,
        'description'   => __('Enter the value of the voucher to be displayed on the product.', 'bata-voucher-plugin'),
    ];
    woocommerce_wp_text_input($args);
}
add_action('woocommerce_product_options_general_product_data', 'add_voucher_value_field');

// Save the custom field
function save_voucher_value_field($post_id) {
    $voucher_value = isset($_POST['bata_voucher_value']) ? sanitize_text_field($_POST['bata_voucher_value']) : '';
    update_post_meta($post_id, 'bata_voucher_value', $voucher_value);
}
add_action('woocommerce_process_product_meta', 'save_voucher_value_field');

/**
 * Shortcode to display a voucher banner on any page.
 * Example usage: [bata_voucher_banner link_to_page="voucher-products" image="https://example.com/your-image.jpg"]
 */
function bata_voucher_banner_shortcode($atts) {
    $atts = shortcode_atts([
        'title' => '',
        'link_to_page' => '',
        'image' => ''
    ], $atts, 'bata_voucher_banner');

    $page_url = get_permalink(get_page_by_path($atts['link_to_page']));
    $image_url = $atts['image'] ? esc_url($atts['image']) : plugin_dir_url(__FILE__) . 'assets/images/default-banner.jpg';
    
    ob_start();
    ?>
  <div class="voucher-banner">
    <a href="<?php echo $page_url; ?>">
        <img src="<?php echo $image_url; ?>" alt="<?php echo esc_attr($atts['title']); ?>">
        <?php if (!empty($atts['title'])) : ?>
            <div class="banner-overlay">
                <h3><?php echo esc_html($atts['title']); ?></h3>
            </div>
        <?php endif; ?>
    </a>
</div>
    <?php
    return ob_get_clean();
}
add_shortcode('bata_voucher_banner', 'bata_voucher_banner_shortcode');

/**
 * Shortcode to display all voucher products on a specific page.
 * Example usage: [bata_voucher_products]
 */
function bata_voucher_products_shortcode() {
    ob_start();
    ?>
    <div class="products-container">
        <?php
        $args = [
            'post_type'      => 'product',
            'posts_per_page' => -1,
            'meta_query'     => [
                [
                    'key'     => 'bata_voucher_value',
                    'value'   => '',
                    'compare' => '!=',
                ],
            ],
        ];
        
        $products = new WP_Query($args);

        if ($products->have_posts()) :
            while ($products->have_posts()) : $products->the_post();
                global $product;
                $voucher_value = get_post_meta($product->get_id(), 'bata_voucher_value', true);
                ?>
                <div class="product-item">
                    <div class="voucher-product-image">
                        <?php echo $product->get_image(); ?>
                        <?php if (!empty($voucher_value)) : ?>
                            <div class="voucher-overlay">
                                <h3><?php echo esc_html($voucher_value); ?> TK.</h3>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h4><?php echo $product->get_name(); ?></h4>
                    <p><?php echo $product->get_price_html(); ?></p>
                    <button class="add-to-cart-btn" data-product-id="<?php echo $product->get_id(); ?>">Add to Cart</button>
                </div>
                <?php
            endwhile;
        else :
            echo '<p>No products with voucher offers found.</p>';
        endif;
        wp_reset_postdata();
        ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('bata_voucher_products', 'bata_voucher_products_shortcode');

// Ajax handler to add product to cart
function bata_add_to_cart_ajax() {
    if (isset($_POST['product_id']) && function_exists('WC')) {
        $product_id = intval($_POST['product_id']);
        WC()->cart->add_to_cart($product_id);
        
        wp_send_json_success([
            'fragments' => WC()->cart->get_refreshed_fragments(),
            'cart_hash' => WC()->cart->get_cart_hash(),
        ]);
    }
    wp_send_json_error('Invalid product ID.');
}
add_action('wp_ajax_bata_add_to_cart', 'bata_add_to_cart_ajax');
add_action('wp_ajax_nopriv_bata_add_to_cart', 'bata_add_to_cart_ajax');