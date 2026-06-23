<?php
$product_id = $param2;
$stocks = $this->inventory_model->get_company_wise_product_stock($product_id);
?>

<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover mb-0">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Company</th>
                <th>Warehouse</th>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>White Qty</th>
                <th>Black Qty</th>
                <th>PO Qty</th>
                <th>Priority Qty</th>
                <th>Loading Qty</th>
                <th>Cost</th>
                <th>Cost with Expense</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($stocks)) : ?>
                <?php $sr_no = 1; ?>
                <?php foreach ($stocks as $stock) : ?>
                    <?php 
                        $pid = $stock['product_id'];
                        $cid = $stock['company_id'];
                        $wid = $stock['warehouse_id'];
                        $inv_id = $stock['inventory_id'];
                        
                        $action = '<button type="button" class="btn btn-sm btn-primary" onclick="showProductBatches(' . $pid . ', ' . $wid . ', \'' . htmlspecialchars($stock['product_name'] ?? '', ENT_QUOTES) . '\')" data-toggle="tooltip" data-bs-placement="top" title="View Batches"><i class="fa fa-eye"></i></button>';
                        
                        $po_qty_arr = $this->inventory_model->get_product_po_list($pid, $cid, 'po', $wid);
                        $po_qty = array_sum(array_column($po_qty_arr, 'quantity'));
                        $po_qty_btn = "<a href='javascript:void(0)' onclick='showProductPOList(" . $pid. "," . $cid. ",\"po\")'>" . $po_qty . "</a>";
                        
                        $priority_qty_arr = $this->inventory_model->get_product_po_list($pid, $cid, 'priority', $wid);
                        $priority_qty = array_sum(array_column($priority_qty_arr, 'quantity'));
                        $priority_qty_btn = "<a href='javascript:void(0)' onclick='showProductPOList(" . $pid. "," . $cid. ",\"priority\")'>" . $priority_qty . "</a>";

                        $loading_qty_arr = $this->inventory_model->get_product_po_list($pid, $cid, 'loading', $wid);
                        $loading_qty = array_sum(array_column($loading_qty_arr, 'quantity'));
                        $loading_qty_btn = "<a href='javascript:void(0)' onclick='showProductPOList(" . $pid. "," . $cid. ",\"loading\")'>" . $loading_qty . "</a>";

                        $no_expense_amt_arr = $this->inventory_model->get_product_po_list($pid, $cid, 'no_expense', $wid);
                        $no_expense_amt = array_sum(array_column($no_expense_amt_arr, 'amount'));
                        $no_expense_amt_btn = "<a href='javascript:void(0)' onclick='showProductPOList(" . $pid. "," . $cid. ",\"no_expense\",\"" . $wid . "\")'>" . $no_expense_amt . "</a>";

                        $expense_amt_arr = $this->inventory_model->get_product_po_list($pid, $cid, 'expense', $wid);
                        $expense_amt = array_sum(array_column($expense_amt_arr, 'amount'));
                        $expense_qty_btn = "<a href='javascript:void(0)' onclick='showProductPOList(" . $pid. "," . $cid. ",\"expense\",\"" . $wid . "\")'>" . $expense_amt . "</a>";
                    ?>
                    <tr>
                        <td><?= $sr_no++ ?></td>
                        <td><?= htmlspecialchars($stock['company_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($stock['warehouse_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($stock['product_name'] ?? '-') ?></td>
                        <td><strong><?= $stock['current_qty'] ?></strong></td>
                        <td><?= $stock['white_qty'] ?? 0 ?></td>
                        <td><?= $stock['black_qty'] ?? 0 ?></td>
                        <td><?= $po_qty_btn ?></td>
                        <td><?= $priority_qty_btn ?></td>
                        <td><?= $loading_qty_btn ?></td>
                        <td><?= $no_expense_amt_btn ?></td>
                        <td><?= $expense_qty_btn ?></td>
                        <td><?= $action ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="13" class="text-center">No company stock found for this product.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
$(document).ready(function() {
    // Initialize tooltips inside modal
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('#scrollable-modal [data-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
