<?php
$product_id = $param2;
$logs = $this->db->where([
    'ref_id' => $product_id,
    'module' => 'product'
])->order_by('created_at', 'desc')->get('sys_logs')->result_array();

$field_labels = [
    'name' => 'Product Name',
    'alias' => 'Alias Name',
    'item_code' => 'Model No. / SKU',
    'categories' => 'Category ID',
    'unit' => 'Unit',
    'gst' => 'GST Rate (%)',
    'is_gst_applicable' => 'GST Applicable',
    'hsn_code' => 'HSN Code',
    'duty_charge' => 'Duty Charge (%)',
    'cartoon_qty' => 'Cartoon Qty',
    'net_weight' => 'Net Weight',
    'gross_weight' => 'Gross Weight',
    'length' => 'Length',
    'width' => 'Width',
    'height' => 'Height',
    'cbm' => 'CBM',
    'product_mrp' => 'Min Billing Price',
    'costing_price' => 'Min Selling Price',
    'usd_rate' => 'Official USD Rate',
    'actual_usd_rate' => 'Actual USD Rate',
    'rate' => 'Actual RMB',
    'intimation' => 'Stock Intimation',
    'min_stock' => 'Min Stock',
    'opening_stock' => 'Opening Stock',
    'is_deleted' => 'Deleted Status'
];

$suppliers_query = $this->db->get('supplier')->result_array();
$supplier_names = [];
foreach ($suppliers_query as $s) {
    $supplier_names[$s['id']] = $s['name'];
}
?>

<style>
  .history-item {
    margin-bottom: 16px;
  }
  .history-card {
    border: 1px solid #edf0f2;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    overflow: hidden;
    margin: 0 !important;
  }
  .history-card .card-body {
    padding: 16px;
  }
  .history-meta {
    font-size: 11px;
    color: #8898aa;
  }
  .history-title {
    font-size: 13px;
    font-weight: 600;
    color: #32325d;
    margin-bottom: 8px;
  }
  .history-changes {
    font-size: 12px;
    color: #525f7f;
    background: #f6f9fc;
    border-radius: 6px;
    padding: 10px;
    margin-top: 8px;
  }
  .history-change-row {
    padding: 6px 0;
    border-bottom: 1px solid #e9ecef;
  }
  .history-change-row:last-child {
    border-bottom: none;
  }
  .history-pill {
    font-size: 10px;
    padding: 3px 8px;
    border-radius: 4px;
    text-transform: uppercase;
    font-weight: 700;
  }
</style>

<?php if (empty($logs)): ?>
  <div class="text-center py-3 text-muted">No history found for this product.</div>
<?php else: ?>
  <?php foreach ($logs as $log): 
    $data_payload = json_decode($log['json'], true);
    $badge_class = 'primary';
    if ($log['action'] == 'update') {
      $badge_class = 'warning';
    } elseif ($log['action'] == 'delete') {
      $badge_class = 'danger';
    }
  ?>
    <div class="history-item">
      <div class="card history-card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="badge bg-<?php echo $badge_class; ?> history-pill"><?php echo htmlspecialchars($log['action']); ?></span>
            <small class="history-meta"><?php echo date('d M Y, h:i A', strtotime($log['created_at'])); ?></small>
          </div>
          
          <div class="history-title">
            Performed by: <span class="text-primary font-weight-bold"><?php echo htmlspecialchars($log['added_by_name'] ?? 'System'); ?></span>
          </div>

          <?php if ($log['action'] == 'add'): ?>
            <div class="text-muted font-size-12">Product was created with initial parameters.</div>
          <?php elseif ($log['action'] == 'delete'): ?>
            <div class="text-muted font-size-12">Product was marked as deleted.</div>
          <?php elseif ($log['action'] == 'update' && !empty($data_payload)): ?>
            <?php
              $old_data = $data_payload['old_data'] ?? [];
              $new_data = $data_payload['new_data'] ?? [];
              
              $diffs = [];
              foreach ($new_data as $key => $val) {
                if (is_array($val)) {
                  continue;
                }
                $old_val = isset($old_data[$key]) ? $old_data[$key] : null;
                if (strval($old_val) !== strval($val)) {
                  $diffs[$key] = [
                    'old' => $old_val,
                    'new' => $val
                  ];
                }
              }

              // Compute supplier pricing changes
              $old_pricing_map = [];
              if (!empty($old_data['supplier_pricing'])) {
                foreach ($old_data['supplier_pricing'] as $p) {
                  $old_pricing_map[$p['supplier_id']] = $p;
                }
              }

              $new_pricing_map = [];
              if (!empty($new_data['supplier_pricing'])) {
                foreach ($new_data['supplier_pricing'] as $p) {
                  $new_pricing_map[$p['supplier_id']] = $p;
                }
              }

              $supplier_fields = [
                'usd_rate' => 'Official USD Rate',
                'actual_usd_rate' => 'Actual USD Rate',
                'rate' => 'Actual RMB',
                'product_mrp' => 'Min Billing Price',
                'costing_price' => 'Min Selling Price',
                'intimation' => 'Stock Intimation'
              ];

              $supplier_changes = [];
              $all_supplier_ids = array_unique(array_merge(array_keys($old_pricing_map), array_keys($new_pricing_map)));

              foreach ($all_supplier_ids as $s_id) {
                $s_name = isset($supplier_names[$s_id]) ? $supplier_names[$s_id] : "Supplier #$s_id";
                $old_p = $old_pricing_map[$s_id] ?? null;
                $new_p = $new_pricing_map[$s_id] ?? null;
                
                if (!$old_p && $new_p) {
                  $added_details = [];
                  foreach ($supplier_fields as $f_key => $f_lbl) {
                    if (!empty($new_p[$f_key])) {
                      $added_details[] = "$f_lbl: " . $new_p[$f_key];
                    }
                  }
                  $supplier_changes[] = [
                    'type' => 'added',
                    'name' => $s_name,
                    'details' => $added_details
                  ];
                } elseif ($old_p && !$new_p) {
                  $supplier_changes[] = [
                    'type' => 'removed',
                    'name' => $s_name
                  ];
                } elseif ($old_p && $new_p) {
                  $changed_details = [];
                  foreach ($supplier_fields as $f_key => $f_lbl) {
                    $old_val = $old_p[$f_key] ?? 0;
                    $new_val = $new_p[$f_key] ?? 0;
                    if (strval($old_val) !== strval($new_val)) {
                      $changed_details[] = [
                        'label' => $f_lbl,
                        'old' => $old_val,
                        'new' => $new_val
                      ];
                    }
                  }
                  if (!empty($changed_details)) {
                    $supplier_changes[] = [
                      'type' => 'changed',
                      'name' => $s_name,
                      'details' => $changed_details
                    ];
                  }
                }
              }
            ?>
            <?php if (!empty($diffs) || !empty($supplier_changes)): ?>
              <div class="history-changes">
                <?php foreach ($diffs as $field => $change): 
                  $label_text = isset($field_labels[$field]) ? $field_labels[$field] : ucwords(str_replace('_', ' ', $field));
                ?>
                  <div class="history-change-row">
                    <strong><?php echo htmlspecialchars($label_text); ?></strong>:<br/>
                    <span class="text-danger"><del><?php echo htmlspecialchars(strval($change['old'])); ?></del></span>
                    &rarr;
                    <span class="text-success"><strong><?php echo htmlspecialchars(strval($change['new'])); ?></strong></span>
                  </div>
                <?php endforeach; ?>

                <?php if (!empty($supplier_changes)): ?>
                  <div class="mt-2 pt-2" style="border-top: 1px dashed #ced4da;">
                    <h6 style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #8898aa; margin-bottom: 8px;">Supplier Pricing Changes:</h6>
                    <?php foreach ($supplier_changes as $sc): ?>
                      <div class="mb-2">
                        <strong class="text-dark" style="font-size: 12px;"><?php echo htmlspecialchars($sc['name']); ?></strong> 
                        <?php if ($sc['type'] == 'added'): ?>
                          <span class="badge bg-success" style="font-size: 9px; padding: 2px 4px;">Added</span>
                          <div class="text-muted" style="font-size: 11px; margin-top: 2px; padding-left: 8px;">
                            <?php echo implode(', ', $sc['details']); ?>
                          </div>
                        <?php elseif ($sc['type'] == 'removed'): ?>
                          <span class="badge bg-danger" style="font-size: 9px; padding: 2px 4px;">Removed</span>
                        <?php elseif ($sc['type'] == 'changed'): ?>
                          <div style="padding-left: 8px; margin-top: 2px;">
                            <?php foreach ($sc['details'] as $det): ?>
                              <div style="font-size: 11px; padding: 2px 0;">
                                <span class="text-muted"><?php echo htmlspecialchars($det['label']); ?>:</span> 
                                <span class="text-danger"><del><?php echo htmlspecialchars(strval($det['old'])); ?></del></span> &rarr;
                                <span class="text-success"><strong><?php echo htmlspecialchars(strval($det['new'])); ?></strong></span>
                              </div>
                            <?php endforeach; ?>
                          </div>
                        <?php endif; ?>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>
            <?php else: ?>
              <div class="text-muted font-size-12">Product variations or meta details were updated.</div>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
