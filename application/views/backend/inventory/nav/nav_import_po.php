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

<div class="col-12 d-flex">
    <a href="<?php echo site_url('inventory/imp-purchase-order'); ?>" class="sub-link <?php echo ($page_name == 'purchase_order') ? 'active' : ''; ?>">Purchase Order</a>
    <a href="<?php echo site_url('inventory/priority-po'); ?>" class="sub-link <?php echo ($page_name == 'priority_po') ? 'active' : ''; ?>">Priority List</a>
    <a href="<?php echo site_url('inventory/loading-list-po'); ?>" class="sub-link <?php echo ($page_name == 'loading_list_po') ? 'active' : ''; ?>">Loading List</a>
    <a href="<?php echo site_url('inventory/po-purchase-in'); ?>" class="sub-link <?php echo ($page_name == 'po_purchase_in') ? 'active' : ''; ?>">Purchase In</a>
    <a href="<?php echo site_url('inventory/po-expense'); ?>" class="sub-link <?php echo ($page_name == 'po_expense') ? 'active' : ''; ?>">Expense</a>
</div>
