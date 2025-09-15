<?php
/**
 * Plugin Name: top-level-menu-submenu
 * Description: A responsive navbar with submenu added via shortcode.
 * Versions: 1.0
 * Author: rafiabonna
 */
// create custom plugin settings menu
add_action('admin_menu','prowp_create_menu');
function prowp_create_menu(){
    //create new top-level menu
    add_menu_page('Halloween plugin page','Halloween plugin','manage_options','prowp_main_menu','prowp_main_plugin_page',plugins_url('images/wordpress.png',__FILE__));
    
    //create two sub_menus: settings and support
    add_submenu_page('prowp_main_menu','Halloween settings page','settings','manage_options','Halloween_settings','prowp_settings_page');
    add_submenu_page('prowp_main_menu','Halloween support page','support','manage_options','Halloween_support','prowp_support_page');
}
?>