<?php
/*
Plugin Name: WooCommerce Customer Role-Based Pricing
Description: A WooCommerce plugin to set product prices based on user roles.
Version: 1.0.0
Author:  Md ziaul Haque
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
add_action('plugins_loaded', 'rbp_init_plugin');

function rbp_init_plugin() {
    if (class_exists('WooCommerce')) {

        require_once(plugin_dir_path(__FILE__) . 'functions/rbp-enqueue.php');
include_once plugin_dir_path(__FILE__) . 'functions/activation.php';
//  activation and deactivation hook rbp_activate + rbp_deactivate
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
        
        // Disable roles that already have pricing set
        $disabled = in_array($role_key, $used_roles) ? 'disabled' : '';
        $options .= '<option value="' . esc_attr($role_key) . '" ' . $disabled . '>' . esc_html($role['name']) . '</option>';
    }
    
    // Check if all roles have pricing set
    $all_roles_have_pricing = count($used_roles) === count($all_roles);

    return [
        'options' => $options,
        'all_roles_have_pricing' => $all_roles_have_pricing,
    ];
}

// Add custom pricing fields to the product edit page
add_action('woocommerce_product_options_pricing', 'rbp_add_dynamic_pricing_fields');

function rbp_add_dynamic_pricing_fields() {
    ?>
    <div class="options_group">
       <div class="wc_rpb_heading">Add user role based pricing</div>
        <div id="customer_pricing_container">
            <?php
            // Load existing role-based pricing if available
            global $post;
            $role_based_pricing = get_post_meta($post->ID, '_rbp_role_based_pricing', true);
            $selected_roles = [];

            if ($role_based_pricing && is_array($role_based_pricing)) {
                foreach ($role_based_pricing as $role_key => $prices) {
                    $selected_roles[] = $role_key; // Track selected roles
                    ?>
                    <div class="customer-pricing">
                        <p class="form-field">
                            <label><?php _e('User Role', 'woocommerce'); ?></label>
                            <select class="role-selector" name="rbp_user_roles[]">
                                <?php
                                global $wp_roles;
                                foreach ($wp_roles->roles as $wp_role_key => $role) {
                                    // Disable roles already selected
                                    $disabled = in_array($wp_role_key, $selected_roles) ? 'disabled' : '';
                                    echo '<option value="' . esc_attr($wp_role_key) . '" ' . selected($role_key, $wp_role_key, false) . ' ' . $disabled . '>' . esc_html($role['name']) . '</option>';
                                }
                                ?>
                            </select>
                        </p>
                        <p class="form-field">
                            <label><?php _e('Regular Price', 'woocommerce'); ?></label>
                            <input type="text" name="rbp_regular_prices[]" class="wc_input_price" value="<?php echo esc_attr($prices['regular_price']); ?>" placeholder="<?php _e('Regular Price', 'woocommerce'); ?>" />
                        </p>
                        <p class="form-field">
                            <label><?php _e('Sale Price', 'woocommerce'); ?></label>
                            <input type="text" name="rbp_sale_prices[]" class="wc_input_price" value="<?php echo esc_attr($prices['sale_price']); ?>" placeholder="<?php _e('Sale Price', 'woocommerce'); ?>" />
                        </p>
                        <button type="button" class="button button-secondary remove-customer-pricing">
                            <?php _e('Remove Pricing', 'woocommerce'); ?>
                        </button>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
        <div class="wc_rpb_add_btn_container"> 
		 <button type="button" class="button button-secondary" id="add_customer_pricing_button">
            <?php _e('Add user role based Pricing', 'woocommerce'); ?>
        </button>
        </div>
    </div>

   
    <?php
}

// Save the dynamic pricing fields
add_action('woocommerce_process_product_meta', 'rbp_save_dynamic_pricing_fields');

function rbp_save_dynamic_pricing_fields($post_id) {
    $user_roles = isset($_POST['rbp_user_roles']) ? $_POST['rbp_user_roles'] : [];
    $regular_prices = isset($_POST['rbp_regular_prices']) ? $_POST['rbp_regular_prices'] : [];
    $sale_prices = isset($_POST['rbp_sale_prices']) ? $_POST['rbp_sale_prices'] : [];

    $role_based_pricing = [];

    foreach ($user_roles as $index => $role) {
        if (!empty($role)) {
            $role_based_pricing[$role] = [
                'regular_price' => isset($regular_prices[$index]) ? $regular_prices[$index] : '',
                'sale_price' => isset($sale_prices[$index]) ? $sale_prices[$index] : '',
            ];
        }
    }

    update_post_meta($post_id, '_rbp_role_based_pricing', $role_based_pricing);
}

    

    add_filter('woocommerce_get_price_html', 'rbp_modify_price_display', 10, 2);
    add_filter('woocommerce_product_get_price', 'rbp_modify_product_price', 10, 2);
    add_filter('woocommerce_product_get_regular_price', 'rbp_modify_product_price', 10, 2);
    add_filter('woocommerce_product_get_sale_price', 'rbp_modify_product_price', 10, 2);
    
    function rbp_modify_price_display($price_html, $product) {
        // Get the role-based price
        $role_price = rbp_get_role_based_price($product);
        if ($role_price) {
            // If a sale price is set, show it
            if (!empty($role_price['sale_price'])) {
                $price_html = '<del>' . wc_price($role_price['regular_price']) . '</del> <ins>' . wc_price($role_price['sale_price']) . '</ins>';
            } else {
                $price_html = wc_price($role_price['regular_price']);
            }
        }
        return $price_html;
    }
    
    function rbp_modify_product_price($price, $product) {
        // Get the role-based price
        $role_price = rbp_get_role_based_price($product);
        if ($role_price) {
            // Return sale price if available, otherwise return the regular price
            if (!empty($role_price['sale_price'])) {
                return $role_price['sale_price'];
            } else {
                return $role_price['regular_price'];
            }
        }
        return $price;
    }
    
    // Ensure the function is not declared again
    if (!function_exists('rbp_get_role_based_price')) {
        function rbp_get_role_based_price($product) {
            // Get the current user's roles
            $user = wp_get_current_user();
            $user_roles = $user->roles;
    
            // Retrieve the product's role-based pricing
            $role_based_pricing = get_post_meta($product->get_id(), '_rbp_role_based_pricing', true);
    
            // Loop through user roles to find matching role-based pricing
            if (is_array($user_roles)) {
                foreach ($user_roles as $role) {
                    if (isset($role_based_pricing[$role])) {
                        return $role_based_pricing[$role];
                    }
                }
            }
    
            return null;
        }
    }

}}