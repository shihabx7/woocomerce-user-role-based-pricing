
<?php
// Display the role-based price on the product page and product cards
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

function rbp_modify_variation_price($price, $variation) {
    // Get the role-based price
    $role_price = rbp_get_role_based_price($variation);
    if ($role_price) {
        if (!empty($role_price['sale_price'])) {
            return $role_price['sale_price'];
        } else {
            return $role_price['regular_price'];
        }
    }
    return $price;
}

add_filter('woocommerce_product_variation_get_price', 'rbp_modify_variation_price', 10, 2);
add_filter('woocommerce_product_variation_get_regular_price', 'rbp_modify_variation_price', 10, 2);
add_filter('woocommerce_product_variation_get_sale_price', 'rbp_modify_variation_price', 10, 2);

// Add role-based pricing to available variations data
add_filter('woocommerce_available_variation', 'rbp_add_role_based_price_to_variation', 10, 3);

function rbp_add_role_based_price_to_variation($variation_data, $product, $variation) {
    // Get the current user's roles
    $user = wp_get_current_user();
    $user_roles = $user->roles;

    // Retrieve role-based pricing for the variation
    $role_based_pricing = get_post_meta($variation->get_id(), '_rbp_role_based_pricing', true);

    // Loop through user roles to find matching role-based pricing
    if (is_array($user_roles)) {
        foreach ($user_roles as $role) {
            if (isset($role_based_pricing[$role])) {
                $variation_data['rbp_role_based_price'] = $role_based_pricing[$role];
                break;
            }
        }
    }

    return $variation_data;
}

// Save role-based pricing for variable products
add_action('woocommerce_save_product_variation', 'rbp_save_variation_pricing_fields', 10, 2);

function rbp_save_variation_pricing_fields($variation_id, $i) {
    $user_roles = isset($_POST['rbp_user_roles_variation'][$i]) ? $_POST['rbp_user_roles_variation'][$i] : [];
    $regular_prices = isset($_POST['rbp_regular_prices_variation'][$i]) ? $_POST['rbp_regular_prices_variation'][$i] : [];
    $sale_prices = isset($_POST['rbp_sale_prices_variation'][$i]) ? $_POST['rbp_sale_prices_variation'][$i] : [];

    $role_based_pricing = [];

    foreach ($user_roles as $index => $role) {
        if (!empty($role)) {
            $role_based_pricing[$role] = [
                'regular_price' => isset($regular_prices[$index]) ? $regular_prices[$index] : '',
                'sale_price' => isset($sale_prices[$index]) ? $sale_prices[$index] : '',
            ];
        }
    }

    update_post_meta($variation_id, '_rbp_role_based_pricing', $role_based_pricing);
}