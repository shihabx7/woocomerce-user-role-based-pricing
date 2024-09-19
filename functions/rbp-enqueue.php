<?php
function rbp_enqueue_scripts() {
    global $pagenow;

    if ($pagenow === 'post.php' && isset($_GET['post']) && 'product' === get_post_type($_GET['post'])) {
        // add custom JavaScript 
        wp_enqueue_script(
            'rbp-custom-script',
            plugin_dir_url(__FILE__) . '../assets/js/rbp-custom-scripts.js', 
            array('jquery'), 
            '1.0', 
            true
        );

        // Get role options and whether all roles have pricing from rbp_get_user_role_options functiuon
        $role_data = rbp_get_user_role_options($_GET['post']);
        
        // Localize data for rbp-custom-script 
        wp_localize_script('rbp-custom-script', 'rbp_vars', array(
            'addRoleLabel' => __('User Role', 'woocommerce'),
            'regularPriceLabel' => __('Regular Price', 'woocommerce'),
            'salePriceLabel' => __('Sale Price', 'woocommerce'),
            'removeButtonLabel' => __('Remove Pricing', 'woocommerce'),
            'userRoleOptions' => $role_data['options'],
            'allRolesHavePricing' => $role_data['all_roles_have_pricing'],
          
        ));

        // add custom CSS
        wp_enqueue_style(
            'rbp-custom-style',
            plugin_dir_url(__FILE__) . '../assets/css/rbp-custom-style.css',
            array(),
            '1.0'
        );
    }
}
add_action('admin_enqueue_scripts', 'rbp_enqueue_scripts');

