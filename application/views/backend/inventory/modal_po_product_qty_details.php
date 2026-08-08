<?php
$product_id = intval($param2 ?? 0);
$supplier_id = intval($param3 ?? 0);
$status = $param4 ?? 'pending';
if (!in_array($status, array('pending', 'priority', 'loading'), true)) {
  $status = 'pending';
}
$method = $param5 ?? '';

$product = $this->db->select('name, item_code')->where('id', $product_id)->get('raw_products')->row_array();
$rows = $this->inventory_model->get_po_product_qty_details($product_id, $supplier_id, $status, $method);
if ($status === 'loading') {
  $status_label = 'Loading List';
} elseif ($status === 'priority') {
  $status_label = 'Priority';
} else {
  $status_label = 'Pending PO';
}
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
  <div class="small text-muted"><?php echo $status_label; ?> bifurcation</div>
</div>

<div class="table-responsive">
  <table class="table table-bordered table-striped table-sm mb-0">
    <thead class="table-light">
      <tr>
        <th style="width: 50px;">#</th>
        <th>PO Number</th>
        <th>Date</th>
        <th>Supplier</th>
        <th class="text-right">Quantity</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($rows)): ?>
        <?php $sr = 1; foreach ($rows as $row): ?>
          <tr>
            <td><?php echo $sr++; ?></td>
            <td><strong><?php echo htmlspecialchars($row['voucher_no'] ?? '-'); ?></strong></td>
            <td><?php echo !empty($row['date']) ? date('d-M-Y', strtotime($row['date'])) : '-'; ?></td>
            <td><?php echo htmlspecialchars($row['supplier_name'] ?? '-'); ?></td>
            <td class="text-right"><?php echo intval($row['quantity'] ?? 0); ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="5" class="text-center text-muted">No <?php echo strtolower($status_label); ?> records found.</td>
        </tr>
      <?php endif; ?>
    </tbody>
    <?php if (!empty($rows)): ?>
    <tfoot>
      <tr class="font-weight-bold">
        <td colspan="4" class="text-right">Total</td>
        <td class="text-right"><?php echo $total_qty; ?></td>
      </tr>
    </tfoot>
    <?php endif; ?>
  </table>
</div>
