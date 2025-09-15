<?php
/*
Plugin Name: Bata Product Bundle
Description: A plugin to create custom product bundles with discounts for WooCommerce.
Version: 1.0
Author: Your Name
Author URI: https://yourwebsite.com
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

// Register the settings
function bata_product_bundle_settings_init() {
    register_setting('bata_product_bundle_options', 'bata_product_bundles');
}
add_action('admin_init', 'bata_product_bundle_settings_init');

// Admin page content
function bata_product_bundle_admin_page() {
    ?>
    <div class="wrap">
        <h1>Product Bundles</h1>

        <style>
            .bata-bundle-form {
                background-color: #fff;
                border: 1px solid #c3c4c7;
                padding: 20px;
                border-radius: 5px;
                box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
            }
            .bata-bundle-form h2 {
                margin-top: 0;
            }
            .bata-bundle-form .form-table th {
                width: 150px;
                padding: 10px 0;
            }
            .bata-bundle-form .form-table input[type="text"],
            .bata-bundle-form .form-table input[type="number"] {
                width: 100%;
                max-width: 400px;
            }
            .bata-bundle-form .form-table td {
                padding: 10px 0;
            }
        </style>

        <div class="bata-bundle-form">
            <h2>Create New Bundle</h2>
            <form method="post" action="options.php">
                <?php settings_fields('bata_product_bundle_options'); ?>
                <?php do_settings_sections('bata_product_bundle_options'); ?>
                
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><label for="bundle_name">Bundle Name</label></th>
                        <td><input type="text" id="bundle_name" name="bata_product_bundles[bundle_name]" value="" required /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="product_ids">Product IDs</label></th>
                        <td><input type="text" id="product_ids" name="bata_product_bundles[product_ids]" value="" placeholder="e.g., 1,2,3" required />
                        <p class="description">Enter product IDs separated by commas.</p></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="bundle_price">Bundle Price</label></th>
                        <td><input type="number" step="0.01" id="bundle_price" name="bata_product_bundles[bundle_price]" value="" required />
                        <p class="description">Enter the final discounted price for the bundle.</p></td>
                    </tr>
                </table>
                
                <?php submit_button('Save Bundle'); ?>
            </form>
        </div>

        <h2>Existing Bundles</h2>
        <?php
        $bundles = get_option('bata_product_bundles');
        if (!empty($bundles)) {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr><th>Bundle Name</th><th>Product IDs</th><th>Bundle Price</th><th>Shortcode</th></tr></thead>';
            echo '<tbody>';
            echo '<tr>';
            echo '<td>' . esc_html($bundles['bundle_name']) . '</td>';
            echo '<td>' . esc_html($bundles['product_ids']) . '</td>';
            echo '<td>' . esc_html($bundles['bundle_price']) . '</td>';
            echo '<td><code>[bata_product_bundle bundle_id="1"]</code></td>';
            echo '</tr>';
            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p>No bundles created yet.</p>';
        }
        ?>
    </div>
    <?php
}

/**
 * Shortcode to display a product bundle on any page.
 * Example usage: [bata_product_bundle bundle_id="1"]
 */
function bata_product_bundle_shortcode($atts) {
    $atts = shortcode_atts([
        'bundle_id' => 1,
    ], $atts, 'bata_product_bundle');
    
    $bundles_data = get_option('bata_product_bundles');

    if (empty($bundles_data)) {
        return '<p>No product bundle found.</p>';
    }

    $bundle_name = esc_html($bundles_data['bundle_name']);
    $product_ids = array_map('intval', explode(',', $bundles_data['product_ids']));
    $bundle_price = esc_html($bundles_data['bundle_price']);
    
    ob_start();
    ?>
    <div class="product-bundle">
        <h2><?php echo $bundle_name; ?></h2>
        <div class="bundle-items">
            <?php
            foreach ($product_ids as $product_id) {
                $product = wc_get_product($product_id);
                if ($product) {
                    ?>
                    <div class="bundle-item">
                        <?php echo $product->get_image(); ?>
                        <h4><?php echo $product->get_name(); ?></h4>
                        <p><?php echo $product->get_price_html(); ?></p>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
        <div class="bundle-details">
            <p>Original Price:
                <del><?php echo wc_price(array_sum(array_map(function($id) {
                    $product = wc_get_product($id);
                    return $product ? $product->get_price() : 0;
                }, $product_ids))); ?></del>
            </p>
            <h3>Bundle Price: <?php echo wc_price($bundle_price); ?></h3>
            <button class="add-to-cart-bundle-btn" data-bundle-id="<?php echo esc_attr($atts['bundle_id']); ?>">Add Bundle to Cart</button>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('bata_product_bundle', 'bata_product_bundle_shortcode');

// Ajax handler to add product bundle to cart
function bata_add_bundle_to_cart_ajax() {
    if (isset($_POST['bundle_id']) && function_exists('WC')) {
        $bundle_id = intval($_POST['bundle_id']);
        $bundles_data = get_option('bata_product_bundles');

        if (isset($bundles_data)) {
            $product_ids = array_map('intval', explode(',', $bundles_data['product_ids']));
            $bundle_price = floatval($bundles_data['bundle_price']);

            // Remove existing items from cart to avoid conflicts
            WC()->cart->empty_cart();
            
            // Add products to cart
            foreach ($product_ids as $product_id) {
                WC()->cart->add_to_cart($product_id);
            }
            
            // Apply a custom discount based on the bundle price
            WC()->cart->add_fee('Bundle Discount', -(array_sum(array_map(function($id) {
                $product = wc_get_product($id);
                return $product ? $product->get_price() : 0;
            }, $product_ids)) - $bundle_price));

            wp_send_json_success([
                'fragments' => WC()->cart->get_refreshed_fragments(),
                'cart_hash' => WC()->cart->get_cart_hash(),
                'message' => 'Product bundle added to cart successfully!'
            ]);
        }
    }
    wp_send_json_error('Invalid bundle ID or products not found.');
}
add_action('wp_ajax_bata_add_to_cart_bundle', 'bata_add_bundle_to_cart_ajax');
add_action('wp_ajax_nopriv_bata_add_to_cart_bundle', 'bata_add_to_cart_bundle_ajax');