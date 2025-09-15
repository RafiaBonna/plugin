<?php
/*
Plugin Name: Bata Voucher Plugin
Description: A plugin to create a voucher section with products for a WooCommerce store.
Version: 1.0
Author: Rafia
Author URI: https://rafiabonna.top
Text Domain: bata-voucher-plugin
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Enqueue styles and scripts
function enqueue_bata_voucher_assets() {
    wp_enqueue_style('bata-voucher-style', plugin_dir_url(__FILE__) . 'assets/css/style.css', [], '1.0');
    wp_enqueue_script('bata-voucher-script', plugin_dir_url(__FILE__) . 'assets/js/script.js', ['jquery'], '1.0', true);

    wp_localize_script('bata-voucher-script', 'bataVoucher', [
        'ajax_url' => admin_url('admin-ajax.php')
    ]);
}
add_action('wp_enqueue_scripts', 'enqueue_bata_voucher_assets');

// Register Custom Post Type for Vouchers
function bata_voucher_cpt() {
    $labels = [
        'name'          => 'Vouchers',
        'singular_name' => 'Voucher',
        'add_new_item'  => 'Add New Voucher',
        'edit_item'     => 'Edit Voucher',
        'new_item'      => 'New Voucher',
        'view_item'     => 'View Voucher',
    ];
    $args = [
        'labels'      => $labels,
        'public'      => true,
        'has_archive' => true,
        'menu_icon'   => 'dashicons-tagcloud',
        'supports'    => ['title', 'editor', 'thumbnail'],
    ];
    register_post_type('voucher', $args);
}
add_action('init', 'bata_voucher_cpt');

/**
 * Shortcode to display a voucher banner on any page.
 * Example usage: [bata_voucher_banner link_to_page="voucher-products" image="https://example.com/your-image.jpg"]
 * 'link_to_page' should be the slug of the page where products are displayed.
 */
function bata_voucher_banner_shortcode($atts) {
    $atts = shortcode_atts([
        'title' => '', // এখানে ডিফল্ট মান ফাঁকা করা হয়েছে
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
        // Fetch products based on a specific category slug
        $args = [
            'post_type'      => 'product',
            'posts_per_page' => -1,
            'tax_query'      => [
                [
                    'taxonomy' => 'product_cat',
                    'field'    => 'slug',
                    'terms'    => 'voucher-products', // The dedicated category slug
                ],
            ],
        ];
        
        $products = new WP_Query($args);

        if ($products->have_posts()) :
            while ($products->have_posts()) : $products->the_post();
                global $product;
                ?>
                <div class="product-item">
                    <?php echo $product->get_image(); ?>
                    <h4><?php echo $product->get_name(); ?></h4>
                    <p><?php echo $product->get_price_html(); ?></p>
                    <button class="add-to-cart-btn" data-product-id="<?php echo $product->get_id(); ?>">Add to Cart</button>
                </div>
                <?php
            endwhile;
        else :
            echo '<p>No products found for this voucher.</p>';
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