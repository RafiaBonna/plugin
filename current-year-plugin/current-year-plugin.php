<?php
/**
 * Plugin Name: Current Year Shortcode
 * Description: A simple plugin to display the current year using a shortcode.
 * Version: 1.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 */


function display_current_year_shortcode() {
    return date('Y');
}
add_shortcode('current_year', 'display_current_year_shortcode');
?>