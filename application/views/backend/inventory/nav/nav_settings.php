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
    <?php if (is_access('947')) { ?>
    <a href="<?php echo site_url('inventory/category'); ?>" class="sub-link <?php echo ($navigation == 'categories') ? 'active' : ''; ?>">Categories</a>
    <?php } ?>
    <?php if (is_access('948')) { ?>
    <a href="<?php echo site_url('inventory/product-unit'); ?>" class="sub-link <?php echo ($navigation == 'product_unit') ? 'active' : ''; ?>">Product Unit</a>
    <?php } ?>
    <?php if (is_access('949')) { ?>
    <a href="<?php echo site_url('inventory/expense-type'); ?>" class="sub-link <?php echo ($navigation == 'expense_type') ? 'active' : ''; ?>">Expense Type</a>
    <?php } ?>
    <?php if (is_access('950')) { ?>
    <a href="<?php echo site_url('inventory/charges'); ?>" class="sub-link <?php echo ($navigation == 'other_charges') ? 'active' : ''; ?>">Charges</a>
    <?php } ?>
    <?php if (is_access('951')) { ?>
    <a href="<?php echo site_url('inventory/commission-slab'); ?>" class="sub-link <?php echo ($navigation == 'commission_slab') ? 'active' : ''; ?>">Commissions</a>
    <?php } ?>
    <?php if (is_access('952')) { ?>
    <a href="<?php echo site_url('inventory/profit-commission-slab'); ?>" class="sub-link <?php echo ($navigation == 'profit_commission_slab') ? 'active' : ''; ?>">Profit Commission Slab</a>
    <?php } ?>
    <?php if (is_access('953')) { ?>
    <a href="<?php echo site_url('inventory/bank-accounts'); ?>" class="sub-link <?php echo ($navigation == 'bank_accounts') ? 'active' : ''; ?>">Bank Accounts</a>
    <?php } ?>
    <?php if (is_access('954')) { ?>
    <a href="<?php echo site_url('inventory/settings'); ?>" class="sub-link <?php echo ($navigation == 'settings') ? 'active' : ''; ?>">Settings</a>
    <?php } ?>
</div>
