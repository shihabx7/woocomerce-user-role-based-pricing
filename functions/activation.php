<?php
function rbp_activate() {
    // active plugin from dashboard 
    if (!get_option('rbp_enable_role_based_pricing')) {
        update_option('rbp_enable_role_based_pricing', 'yes');
    }
}

function rbp_deactivate() {
    // deactive active plugin from dashboard 
    delete_option('rbp_enable_role_based_pricing');
}

register_activation_hook(__FILE__, 'rbp_activate');
register_deactivation_hook(__FILE__, 'rbp_deactivate');