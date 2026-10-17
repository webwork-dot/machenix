<style>
  .sub-link {
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
    margin-right: 4px;
    background: white;
    padding: 8px 10px;
    min-width: 100px;
    text-align: center;
  }

  .sub-link.active {
    background: #5a79c0 !important;
    color: white;
  }
</style>

<div class="col-12 d-flex flex-wrap">
    <?php if($this->session->userdata('super_type') == 'Inventory'){ ?>
      <a href="<?php echo site_url('inventory/leads/all'); ?>" class="sub-link <?php echo ($page_name == 'leads_all') ? 'active' : ''; ?>">All Leads</a>
    <?php } ?>
    <a href="<?php echo site_url('inventory/leads/new'); ?>" class="sub-link <?php echo ($page_name == 'leads_new') ? 'active' : ''; ?>">New Leads</a>
    <a href="<?php echo site_url('inventory/customer_calls'); ?>" class="sub-link <?php echo ($page_name == 'customer_calls_data') ? 'active' : ''; ?>">Customers/Leads Calls</a>
    <a href="<?php echo site_url('inventory/leads/today'); ?>" class="sub-link <?php echo ($page_name == 'leads_today') ? 'active' : ''; ?>">Todays Follow-up</a>
    <a href="<?php echo site_url('inventory/leads/upcoming'); ?>" class="sub-link <?php echo ($page_name == 'leads_upcoming') ? 'active' : ''; ?>">Upcoming Follow-up</a>
    <a href="<?php echo site_url('inventory/leads/missed'); ?>" class="sub-link <?php echo ($page_name == 'leads_missed') ? 'active' : ''; ?>">Missed Leads</a>
    <a href="<?php echo site_url('inventory/leads/lost'); ?>" class="sub-link <?php echo ($page_name == 'leads_lost') ? 'active' : ''; ?>">Lost Leads</a>
    <a href="<?php echo site_url('inventory/leads/moved'); ?>" class="sub-link <?php echo ($page_name == 'leads_moved') ? 'active' : ''; ?>">Move To Customer</a>
</div>
