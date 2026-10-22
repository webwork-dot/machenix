<?php
  // Dashboards Switcher Partial
  $ci =& get_instance();
  $curr_page = isset($page_name) ? $page_name : '';
  $uri_seg1  = $ci->uri->segment(1);
  $uri_seg2  = $ci->uri->segment(2);

  $is_sales    = ($curr_page === 'sales_dashboard' || $uri_seg1 === 'sales-dashboard' || ($uri_seg1 === 'inventory' && $uri_seg2 === 'sales-dashboard'));
  $is_purchase = ($curr_page === 'purchase_dashboard' || $uri_seg1 === 'purchase-dashboard' || ($uri_seg1 === 'inventory' && $uri_seg2 === 'purchase-dashboard'));
  $is_leads    = (!$is_sales && !$is_purchase); // default to Leads & Customers
?>
<div class="dashboard-switcher-container d-flex align-items-center" style="gap: 8px;">
  <label for="dashboardSelectSwitcher" class="form-label mb-0 fw-bolder text-muted small text-uppercase d-none d-sm-inline-flex align-items-center" style="letter-spacing: 0.5px; font-size: 11px; white-space: nowrap;">
    <i class="feather icon-layers text-primary me-50"></i> Dashboards:
  </label>
  <div class="input-group input-group-sm" style="min-width: 220px; max-width: 260px;">
    <select id="dashboardSelectSwitcher" 
            class="form-control form-control-sm form-select form-select-sm fw-bold  text-dark" 
            style="height: 38px; font-size: 13px; font-weight: 600; cursor: pointer; border-color: #d8d6de; box-shadow: none;" 
            onchange="if(this.value){ window.location.href = this.value; }">
      <option value="<?php echo site_url('inventory/dashboard'); ?>" <?php echo $is_leads ? 'selected="selected"' : ''; ?>>1. Leads & Customers</option>
      <option value="<?php echo site_url('sales-dashboard'); ?>" <?php echo $is_sales ? 'selected="selected"' : ''; ?>>2. Sales</option>
      <option value="<?php echo site_url('purchase-dashboard'); ?>" <?php echo $is_purchase ? 'selected="selected"' : ''; ?>>3. Purchase</option>
    </select>
  </div>
</div>
