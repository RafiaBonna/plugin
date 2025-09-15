<?php
/*
Plugin Name: Bata Product Bundle
Description: A plugin to create custom product bundles with discounts for WooCommerce.
Version: 1.0
Author: Rafia
Author URI: https://rafiabonna.top
Text Domain: bata-product-bundle
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Check if WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    function bata_product_bundle_notice() {
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php _e('Bata Product Bundle requires WooCommerce to be installed and active.', 'bata-product-bundle'); ?></p>
        </div>
        <?php
    }
    add_action('admin_notices', 'bata_product_bundle_notice');
    return;
}

// Enqueue styles and scripts
function bata_product_bundle_enqueue_assets() {
    wp_enqueue_style('bata-bundle-style', plugin_dir_url(__FILE__) . 'assets/css/style.css', [], '1.0');
    wp_enqueue_script('bata-bundle-script', plugin_dir_url(__FILE__) . 'assets/js/script.js', ['jquery'], '1.0', true);
}
add_action('wp_enqueue_scripts', 'bata_product_bundle_enqueue_assets');

// Add admin menu for creating bundles
function bata_product_bundle_admin_menu() {
    add_menu_page(
        'Product Bundles',
        'Product Bundles',
        'manage_options',
        'bata-product-bundle',
        'bata_product_bundle_admin_page',
        'dashicons-archive',
        30
    );
}
add_action('admin_menu', 'bata_product_bundle_admin_menu');

// Admin page content
function bata_product_bundle_admin_page() {
    ?>
    <div class="wrap">
        <h1>Product Bundles</h1>
        <p>This is where you will manage and create your custom product bundles. You can add a form here to select products, set a price, and create a bundle.</p>
    </div>
    <?php
}

/**
 * Shortcode to display a product bundle on any page.
 * Example usage: [bata_product_bundle bundle_id="1"]
 */
function bata_product_bundle_shortcode($atts) {
    // This is placeholder for bundle logic. You will add code here to fetch the products
    // for the given bundle_id and display them in a structured format.

    $atts = shortcode_atts([
        'bundle_id' => 1,
    ], $atts, 'bata_product_bundle');
    
    ob_start();
    ?>
    <div class="product-bundle">
        <h2>Summer Essentials Bundle</h2>
        <div class="bundle-items">
            <div class="bundle-item">
                <img src="https://via.placeholder.com/200" alt="Product 1">
                <h4>Product 1</h4>
                <p>TK 1200</p>
            </div>
            <div class="bundle-item">
                <img src="https://via.placeholder.com/200" alt="Product 2">
                <h4>Product 2</h4>
                <p>TK 800</p>
            </div>
        </div>
        <div class="bundle-details">
            <p>Original Price: <del>TK 2000</del></p>
            <h3>Bundle Price: TK 1500</h3>
            <button class="add-to-cart-bundle-btn">Add Bundle to Cart</button>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('bata_product_bundle', 'bata_product_bundle_shortcode');