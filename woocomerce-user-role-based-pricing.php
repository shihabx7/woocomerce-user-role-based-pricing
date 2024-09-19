<?php
/*
Plugin Name: WooCommerce Customer Role-Based Pricing
Description: A WooCommerce plugin to product prices based on user roles.
Version: 1.1.1
Author:  Md ziaul Haque 
*/
ob_start();
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
add_action('plugins_loaded', 'rbp_init_plugin');

function rbp_init_plugin() {
    if (class_exists('WooCommerce')) {
//  load js and css
require_once(plugin_dir_path(__FILE__) . 'functions/rbp-enqueue.php');
//  activation and deactivation function rbp_activate + rbp_deactivate
include_once plugin_dir_path(__FILE__) . 'functions/activation.php';


function rbp_get_user_role_options($post_id) {
    global $wp_roles;
    $options = '';
    $all_roles = [];
    
    // Get existing role-based pricing for the product
    $role_based_pricing = get_post_meta($post_id, '_rbp_role_based_pricing', true);
    $used_roles = is_array($role_based_pricing) ? array_keys($role_based_pricing) : [];

    foreach ($wp_roles->roles as $role_key => $role) {
        // Store all roles for checking later
        $all_roles[] = $role_key;
        
        // Remove the "disabled" logic to allow all roles to be selectable
        $options .= '<option value="' . esc_attr($role_key) . '">' . esc_html($role['name']) . '</option>';
    }

    // Check if all roles have pricing set
    $all_roles_have_pricing = count($used_roles) === count($all_roles);

    return [
        'options' => $options,
        'all_roles_have_pricing' => $all_roles_have_pricing,
    ];
}
//  load admin view function
include_once plugin_dir_path(__FILE__) . 'functions/rbp-admin.php';
//  load client view function
include_once plugin_dir_path(__FILE__) . 'functions/rbp-client.php';
    }}







?>