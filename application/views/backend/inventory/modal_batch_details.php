<?php
$product_id = $param2;
$warehouse_id = $param3;
$batches = $this->inventory_model->get_batches_by_product_warehouse($product_id, $warehouse_id);
?>

<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover mb-0">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Category</th>
                <th>Product Name</th>
                <th>Batch No</th>
                <th>Quantity</th>
                <th>Black Qty</th>
                <th>White Qty</th>
                <th>Cost Without Expense</th>
                <th>Cost With Expense</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($batches)) : ?>
                <?php $sr_no = 1; ?>
                <?php foreach ($batches as $batch) : ?>
                    <?php 
                        $id = $batch['id'];
                        $size_label = '';
                        $category = $this->common_model->getRowById('categories', 'name', ['id' => $batch['categories']]);
                        $size_label = $category['name'] ?? '-';
                        
                        $edit_url = base_url() . 'inventory/my-stock-history/' . $id;
                        $action = '<a href="' . $edit_url . '" target="_blank" data-toggle="tooltip" data-bs-placement="top" title="View History"><button type="button" class="btn btn-sm btn-primary"><i class="fa fa-eye"></i></button></a> ';
                        
                    ?>
                    <tr>
                        <td><?= $sr_no++ ?></td>
                        <td><?= htmlspecialchars($size_label) ?></td>
                        <td><?= htmlspecialchars($batch['product_name'] ?? '-') ?></td>
                        <td><strong><?= htmlspecialchars($batch['batch_no'] != '' ? $batch['batch_no'] : '-') ?></strong></td>
                        <td><?= $batch['quantity'] ?></td>
                        <td><?= $batch['black_qty'] ?></td>
                        <td><?= $batch['official_qty'] ?></td>
                        <td><?= $batch['official_total_rs'] ?></td>
                        <td><?= $batch['total_amt'] ?></td>
                        <td><?= $action ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="10" class="text-center">No batches with quantity > 0 found.</td>
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
