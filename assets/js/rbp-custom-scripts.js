jQuery(document).ready(function ($) {
  // if (rbp_vars.allRolesHavePricing) {
  //   $("#add_customer_pricing_button").prop("disabled", true);
  // }
  // Function to refresh role dropdowns and disable selected ones
  function refreshRoleDropdowns() {
    // Collect all selected roles (simple and variable products)
    $(".role-selector").each(function () {
      var selectedValue = $(this).val();
      $(this)
        .find("option")
        .each(function () {
          // Remove the "disabled" attribute from all options in every dropdown
          $(this).removeAttr("disabled");
        });
    });
  }
  // Add new pricing fields for simple products
  $("#add_customer_pricing_button").on("click", function () {
    var pricingHtml = `
      <div class="customer-pricing">
        <p class="form-field">
          <label>${rbp_vars.addRoleLabel}</label>
          <select class="role-selector" name="rbp_user_roles[]">
            ${rbp_vars.userRoleOptions} <!-- This will insert the options -->
          </select>
        </p>
        <p class="form-field">
          <label>${rbp_vars.regularPriceLabel}</label>
          <input type="text" name="rbp_regular_prices[]" class="wc_input_price" />
        </p>
        <p class="form-field">
          <label>${rbp_vars.salePriceLabel}</label>
          <input type="text" name="rbp_sale_prices[]" class="wc_input_price" />
        </p>
        <button type="button" class="button button-secondary remove-customer-pricing">${rbp_vars.removeButtonLabel}</button>
      </div>`;

    // Append new fields to the simple product container
    $("#customer_pricing_container").append(pricingHtml);
    refreshRoleDropdowns(); // Call function to refresh the dropdowns
  });

  // Add new pricing fields for variable product variations
  $(document).on(
    "click",
    "#add_customer_pricing_button_variation",
    function () {
      var loopIndex = $(this).data("loop");
      var pricingHtml = `
      <div class="customer-pricing">
        <p class="form-field">
          <label>${rbp_vars.addRoleLabel}</label>
          <select class="role-selector" name="variable_rbp_user_roles[${loopIndex}][]">
            ${rbp_vars.userRoleOptions} <!-- This will insert the options -->
          </select>
        </p>
        <p class="form-field">
          <label>${rbp_vars.regularPriceLabel}</label>
          <input type="text" name="variable_rbp_regular_prices[${loopIndex}][]" class="wc_input_price" />
        </p>
        <p class="form-field">
          <label>${rbp_vars.salePriceLabel}</label>
          <input type="text" name="variable_rbp_sale_prices[${loopIndex}][]" class="wc_input_price" />
        </p>
        <button type="button" class="button button-secondary remove-customer-pricing">${rbp_vars.removeButtonLabel}</button>
      </div>`;

      // Append new fields to the variable product container
      $(this)
        .closest(".variable_pricing_container")
        .find("#customer_pricing_container_variation_" + loopIndex)
        .append(pricingHtml);
      refreshRoleDropdowns(); // Refresh dropdowns
    }
  );

  // Remove pricing fields
  $(document).on("click", ".remove-customer-pricing", function () {
    $(this).closest(".customer-pricing").remove();
    refreshRoleDropdowns(); // Refresh the dropdowns after removing a field
  });

  // Refresh dropdowns on page load
  refreshRoleDropdowns();
});
