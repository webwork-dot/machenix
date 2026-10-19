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
    <?php if (is_access('210')) { ?>
    <a href="<?php echo site_url('inventory/sales-order'); ?>" class="sub-link <?php echo ($page_name == 'sales_order') ? 'active' : ''; ?>">Pending</a>
    <?php } ?>
    <?php if (is_access('211')) { ?>
    <a href="<?php echo site_url('inventory/sales-order-invoice'); ?>" class="sub-link <?php echo ($page_name == 'sales_order_invoice') ? 'active' : ''; ?>">Generate Invoice</a>
    <?php } ?>
    <?php if (is_access('212')) { ?>
    <a href="<?php echo site_url('inventory/sales-order-complete'); ?>" class="sub-link <?php echo ($page_name == 'sales_order_complete') ? 'active' : ''; ?>">Complete</a>
    <?php } ?>
    <?php if (is_access('213')) { ?>
    <a href="<?php echo site_url('inventory/sales-order-all'); ?>" class="sub-link <?php echo ($page_name == 'sales_order_all') ? 'active' : ''; ?>">All Orders</a>
    <?php } ?>
    <?php if (is_access('214')) { ?>
    <a href="<?php echo site_url('inventory/sales-order-cancelled'); ?>" class="sub-link <?php echo ($page_name == 'sales_order_cancelled') ? 'active' : ''; ?>">Cancelled</a>
    <?php } ?>
</div>
