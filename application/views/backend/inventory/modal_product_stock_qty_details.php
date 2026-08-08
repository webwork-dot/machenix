<?php
$product_id = intval($param2 ?? 0);
$scope = ($param3 ?? 'all') === 'company' ? 'company' : 'all';
$company_id = ($scope === 'company') ? $this->session->userdata('company_id') : null;

$product = $this->db->select('name, item_code')->where('id', $product_id)->get('raw_products')->row_array();
$rows = $this->inventory_model->get_product_stock_qty_details($product_id, $company_id);
$scope_label = ($scope === 'company') ? 'Company Stock' : 'In Stock';
$total_qty = 0;
foreach ($rows as $row) {
  $total_qty += intval($row['quantity'] ?? 0);
}
?>

<div class="mb-1">
  <strong><?php echo htmlspecialchars($product['name'] ?? 'Product'); ?></strong>
  <?php if (!empty($product['item_code'])): ?>
    <span class="text-muted">(<?php echo htmlspecialchars($product['item_code']); ?>)</span>
  <?php endif; ?>
  <div class="small text-muted"><?php echo $scope_label; ?> bifurcation (Batch / Supplier / Company)</div>
</div>

<div class="table-responsive">
  <table class="table table-bordered table-striped table-sm mb-0">
    <thead class="table-light">
      <tr>
        <th style="width: 50px;">#</th>
        <th>Batch No</th>
        <th>Supplier</th>
        <th>Company</th>
        <th>Warehouse</th>
        <th class="text-right">Qty</th>
        <th class="text-right">White Qty</th>
        <th class="text-right">Black Qty</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($rows)): ?>
        <?php $sr = 1; foreach ($rows as $row): ?>
          <tr>
            <td><?php echo $sr++; ?></td>
            <td><strong><?php echo htmlspecialchars(($row['batch_no'] ?? '') !== '' ? $row['batch_no'] : '-'); ?></strong></td>
            <td><?php echo htmlspecialchars($row['supplier_name'] ?? '-'); ?></td>
            <td><?php echo htmlspecialchars($row['company_name'] ?? '-'); ?></td>
            <td><?php echo htmlspecialchars($row['warehouse_name'] ?? '-'); ?></td>
            <td class="text-right"><?php echo intval($row['quantity'] ?? 0); ?></td>
            <td class="text-right"><?php echo intval($row['official_qty'] ?? 0); ?></td>
            <td class="text-right"><?php echo intval($row['black_qty'] ?? 0); ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="8" class="text-center text-muted">No stock records found.</td>
        </tr>
      <?php endif; ?>
    </tbody>
    <?php if (!empty($rows)): ?>
    <tfoot>
      <tr class="font-weight-bold">
        <td colspan="5" class="text-right">Total</td>
        <td class="text-right"><?php echo $total_qty; ?></td>
        <td colspan="2"></td>
      </tr>
    </tfoot>
    <?php endif; ?>
  </table>
</div>
