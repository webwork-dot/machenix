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
    <?php if (is_access('531')) { ?>
      <a href="<?php echo site_url('inventory/leads/all'); ?>" class="sub-link <?php echo ($page_name == 'leads_all') ? 'active' : ''; ?>">All Leads</a>
    <?php } ?>
    <?php if (is_access('532')) { ?>
    <a href="<?php echo site_url('inventory/leads/new'); ?>" class="sub-link <?php echo ($page_name == 'leads_new') ? 'active' : ''; ?>">New Leads</a>
    <?php } ?>
    <?php if (is_access('533')) { ?>
    <a href="<?php echo site_url('inventory/customer_calls'); ?>" class="sub-link <?php echo ($page_name == 'customer_calls_data') ? 'active' : ''; ?>">Customers/Leads Calls</a>
    <?php } ?>
    <?php if (is_access('534')) { ?>
    <a href="<?php echo site_url('inventory/leads/today'); ?>" class="sub-link <?php echo ($page_name == 'leads_today') ? 'active' : ''; ?>">Todays Follow-up</a>
    <?php } ?>
    <?php if (is_access('535')) { ?>
    <a href="<?php echo site_url('inventory/leads/upcoming'); ?>" class="sub-link <?php echo ($page_name == 'leads_upcoming') ? 'active' : ''; ?>">Upcoming Follow-up</a>
    <?php } ?>
    <?php if (is_access('536')) { ?>
    <a href="<?php echo site_url('inventory/leads/missed'); ?>" class="sub-link <?php echo ($page_name == 'leads_missed') ? 'active' : ''; ?>">Missed Leads</a>
    <?php } ?>
    <?php if (is_access('537')) { ?>
    <a href="<?php echo site_url('inventory/leads/lost'); ?>" class="sub-link <?php echo ($page_name == 'leads_lost') ? 'active' : ''; ?>">Lost Leads</a>
    <?php } ?>
    <?php if (is_access('538')) { ?>
    <a href="<?php echo site_url('inventory/leads/moved'); ?>" class="sub-link <?php echo ($page_name == 'leads_moved') ? 'active' : ''; ?>">Move To Customer</a>
    <?php } ?>
</div>
