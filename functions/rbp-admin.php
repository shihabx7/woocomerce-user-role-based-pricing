<?php


// Add user based pricing field box product edit page, simple product
add_action('woocommerce_product_options_pricing', 'add_rbp_pricing_fields_simple_products');

function add_rbp_pricing_fields_simple_products() {
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
                            <?php _e('Remove', 'woocommerce'); ?>
                        </button>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
        <div class="wc_rpb_add_btn_container"> 
		 <button type="button" class="button button-secondary" id="add_customer_pricing_button">
            <?php _e('Add New', 'woocommerce'); ?>
        </button>
        </div>
    </div>

   
    <?php
}

// Save the simple page rbp pricing fields
add_action('woocommerce_process_product_meta', 'save_rbp_simple_product_pricing_fields');

function save_rbp_simple_product_pricing_fields($post_id) {
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

add_action('woocommerce_variation_options_pricing', 'add_rbp_pricing_fields_to_variations', 10, 3);

// Add user based pricing field boxs to each product variation
function add_rbp_pricing_fields_to_variations($loop, $variation_data, $variation) {
    ?>
    <div class="options_group rbp-var-pricing-box">
        <div class="wc_rpb_heading">Add user role based pricing</div>
        <div id="customer_pricing_container_variation_<?php echo $loop; ?>">
            <?php
            // Load if rbp pricing for this variation
            $role_based_pricing = get_post_meta($variation->ID, '_rbp_role_based_pricing', true);
            $selected_roles = [];

            if ($role_based_pricing && is_array($role_based_pricing)) {
                foreach ($role_based_pricing as $role_key => $prices) {
                    $selected_roles[] = $role_key; // check selected roles
                    ?>
                    <div class="customer-pricing">
                        <p class="form-field">
                            <label><?php _e('User Role', 'woocommerce'); ?></label>
                            <select class="role-selector" name="rbp_user_roles_variation[<?php echo $loop; ?>][]">
                                <?php
                                global $wp_roles;
                                foreach ($wp_roles->roles as $wp_role_key => $role) {
                                    // mute roles already selected
                                    $disabled = in_array($wp_role_key, $selected_roles) ? 'disabled' : '';
                                    echo '<option value="' . esc_attr($wp_role_key) . '" ' . selected($role_key, $wp_role_key, false) . ' ' . $disabled . '>' . esc_html($role['name']) . '</option>';
                                }
                                ?>
                            </select>
                        </p>
                        <p class="form-field">
                            <label><?php _e('Regular Price', 'woocommerce'); ?></label>
                            <input type="text" name="rbp_regular_prices_variation[<?php echo $loop; ?>][]" class="wc_input_price" value="<?php echo esc_attr($prices['regular_price']); ?>" placeholder="<?php _e('Regular Price', 'woocommerce'); ?>" />
                        </p>
                        <p class="form-field">
                            <label><?php _e('Sale Price', 'woocommerce'); ?></label>
                            <input type="text" name="rbp_sale_prices_variation[<?php echo $loop; ?>][]" class="wc_input_price" value="<?php echo esc_attr($prices['sale_price']); ?>" placeholder="<?php _e('Sale Price', 'woocommerce'); ?>" />
                        </p>
                        <button type="button" class="button button-secondary remove-customer-pricing">
                            <?php _e('Remove', 'woocommerce'); ?>
                        </button>
                        
                    </div>
                    <?php
                }
            }
            ?>
        </div>
        <div class="wc_rpb_add_btn_container"> 
            <button type="button" id="add_customer_pricing_button_variation" class="button button-secondary add_customer_pricing_button_variation" data-loop="<?php echo $loop; ?>">
                <?php _e('Add New', 'woocommerce'); ?>
            </button>
        

        </div>
    </div>
    <?php
}
// Save role-based pricing for each variation
add_action('woocommerce_save_product_variation', 'save_rbp_pricing_fields_for_variations', 10, 2);

function save_rbp_pricing_fields_for_variations($variation_id, $i) {
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