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
    <a href="<?php echo site_url('inventory/category'); ?>" class="sub-link <?php echo ($navigation == 'categories') ? 'active' : ''; ?>">Categories</a>
    <a href="<?php echo site_url('inventory/expense-type'); ?>" class="sub-link <?php echo ($navigation == 'expense_type') ? 'active' : ''; ?>">Expense Type</a>
    <a href="<?php echo site_url('inventory/bank-accounts'); ?>" class="sub-link <?php echo ($navigation == 'bank_accounts') ? 'active' : ''; ?>">Bank Accounts</a>
    <a href="<?php echo site_url('inventory/my-company'); ?>" class="sub-link <?php echo ($navigation == 'my_company') ? 'active' : ''; ?>">My Vendors</a>
    <!-- <a href="<?php echo site_url('inventory/po-expense'); ?>" class="sub-link <?php echo ($navigation == 'po_expense') ? 'active' : ''; ?>">Expense</a> -->
</div>
