<?php
$product_id = (int)$param2;
$product = $this->common_model->getRowById('raw_products', '*', ['id' => $product_id]);
if (!is_array($product)) {
    $product = [];
}

$image_row = $this->db->query(
    "SELECT image FROM product_images WHERE product_id = ? ORDER BY is_main DESC, id ASC LIMIT 1",
    [$product_id]
)->row_array();
$product_image = !empty($image_row['image']) ? $image_row['image'] : (!empty($product['image']) ? $product['image'] : '');

$category_name = '-';
if (!empty($product['categories'])) {
    $category = $this->common_model->getRowById('categories', 'name', ['id' => $product['categories']]);
    $category_name = is_array($category) ? ($category['name'] ?? '-') : '-';
}

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

$supplier_fields = [
    'usd_rate' => 'Official USD Rate',
    'actual_usd_rate' => 'Actual USD Rate',
    'rate' => 'Actual RMB',
    'product_mrp' => 'Min Billing Price',
    'costing_price' => 'Min Selling Price',
    'intimation' => 'Stock Intimation'
];

$suppliers_query = $this->db->get('supplier')->result_array();
$supplier_names = [];
foreach ($suppliers_query as $s) {
    $supplier_names[$s['id']] = $s['name'];
}

$action_meta = [
    'add' => ['label' => 'Created', 'class' => 'rph-add', 'icon' => 'fa-plus'],
    'update' => ['label' => 'Updated', 'class' => 'rph-update', 'icon' => 'fa-pencil'],
    'delete' => ['label' => 'Deleted', 'class' => 'rph-delete', 'icon' => 'fa-trash'],
];

$display = function ($value) {
    $text = trim(strval($value));
    return $text === '' ? '—' : htmlspecialchars($text);
};
?>

<style>
  .rph-wrap { font-family: inherit; color: #334155; }
  .rph-hero {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    margin-bottom: 12px;
    background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
    border: 1px solid #e2e8f0;
    border-radius: 12px;
  }
  .rph-thumb {
    width: 46px;
    height: 46px;
    border-radius: 10px;
    object-fit: cover;
    background: #fff;
    border: 1px solid #e2e8f0;
    flex-shrink: 0;
  }
  .rph-thumb-placeholder {
    width: 46px;
    height: 46px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    color: #94a3b8;
    border: 1px solid #e2e8f0;
    flex-shrink: 0;
  }
  .rph-name {
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.3;
    margin-bottom: 4px;
  }
  .rph-chips { display: flex; flex-wrap: wrap; gap: 6px; }
  .rph-chip {
    font-size: 11px;
    color: #475569;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 999px;
    padding: 2px 8px;
    line-height: 1.5;
  }
  .rph-chip strong { color: #1e293b; font-weight: 600; }
  .rph-scroll {
    max-height: 420px;
    overflow-y: auto;
    padding-right: 4px;
  }
  .rph-scroll::-webkit-scrollbar { width: 6px; }
  .rph-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
  .rph-timeline { position: relative; padding-left: 18px; }
  .rph-timeline:before {
    content: "";
    position: absolute;
    left: 5px;
    top: 6px;
    bottom: 6px;
    width: 2px;
    background: #e2e8f0;
  }
  .rph-item { position: relative; margin-bottom: 12px; }
  .rph-item:last-child { margin-bottom: 0; }
  .rph-dot {
    position: absolute;
    left: -16px;
    top: 14px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #6366f1;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #c7d2fe;
  }
  .rph-item.rph-add .rph-dot { background: #16a34a; box-shadow: 0 0 0 2px #bbf7d0; }
  .rph-item.rph-update .rph-dot { background: #d97706; box-shadow: 0 0 0 2px #fde68a; }
  .rph-item.rph-delete .rph-dot { background: #dc2626; box-shadow: 0 0 0 2px #fecaca; }
  .rph-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 10px 12px;
  }
  .rph-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
  }
  .rph-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .3px;
    text-transform: uppercase;
    padding: 3px 8px;
    border-radius: 999px;
    background: #eef2ff;
    color: #4338ca;
  }
  .rph-item.rph-add .rph-badge { background: #dcfce7; color: #166534; }
  .rph-item.rph-update .rph-badge { background: #fef3c7; color: #92400e; }
  .rph-item.rph-delete .rph-badge { background: #fee2e2; color: #991b1b; }
  .rph-time { font-size: 11px; color: #94a3b8; white-space: nowrap; }
  .rph-user { font-size: 12px; color: #64748b; margin-bottom: 6px; }
  .rph-user strong { color: #0f172a; }
  .rph-note { font-size: 12px; color: #64748b; }
  .rph-changes {
    margin-top: 6px;
    background: #f8fafc;
    border: 1px solid #eef2f7;
    border-radius: 8px;
    padding: 6px 8px;
  }
  .rph-row {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    padding: 6px 0;
    border-bottom: 1px solid #eef2f7;
    font-size: 12px;
  }
  .rph-row:last-child { border-bottom: none; }
  .rph-field { color: #475569; font-weight: 600; min-width: 110px; }
  .rph-vals { text-align: right; word-break: break-word; }
  .rph-old { color: #ef4444; text-decoration: line-through; margin-right: 4px; }
  .rph-new { color: #059669; font-weight: 700; }
  .rph-supplier {
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px dashed #e2e8f0;
  }
  .rph-supplier-title {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .4px;
    text-transform: uppercase;
    color: #94a3b8;
    margin-bottom: 6px;
  }
  .rph-supplier-name { font-size: 12px; font-weight: 700; color: #1e293b; }
  .rph-mini {
    display: inline-block;
    font-size: 9px;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 999px;
    margin-left: 4px;
  }
  .rph-mini-add { background: #dcfce7; color: #166534; }
  .rph-mini-remove { background: #fee2e2; color: #991b1b; }
  .rph-empty {
    text-align: center;
    padding: 28px 12px;
    color: #94a3b8;
  }
  .rph-empty i { font-size: 28px; opacity: .45; margin-bottom: 8px; display: block; }
</style>

<div class="rph-wrap">
  <div class="rph-hero">
    <?php if (!empty($product_image)): ?>
      <img class="rph-thumb" src="<?php echo base_url() . htmlspecialchars($product_image); ?>" alt="">
    <?php else: ?>
      <div class="rph-thumb-placeholder"><i class="fa fa-cube"></i></div>
    <?php endif; ?>
    <div>
      <div class="rph-name"><?php echo htmlspecialchars($product['name'] ?? 'Product'); ?></div>
      <div class="rph-chips">
        <span class="rph-chip"><strong>SKU</strong> <?php echo htmlspecialchars($product['item_code'] ?? '-'); ?></span>
        <?php if (!empty($product['alias'])): ?>
          <span class="rph-chip"><strong>Alias</strong> <?php echo htmlspecialchars($product['alias']); ?></span>
        <?php endif; ?>
        <span class="rph-chip"><strong>Category</strong> <?php echo htmlspecialchars($category_name); ?></span>
        <?php if (!empty($product['hsn_code'])): ?>
          <span class="rph-chip"><strong>HSN</strong> <?php echo htmlspecialchars($product['hsn_code']); ?></span>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <?php if (empty($logs)): ?>
    <div class="rph-empty">
      <i class="fa fa-history"></i>
      <div>No history found for this product.</div>
    </div>
  <?php else: ?>
    <div class="rph-scroll">
      <div class="rph-timeline">
        <?php foreach ($logs as $log):
          $data_payload = json_decode($log['json'], true);
          $action = strtolower($log['action'] ?? '');
          $meta = $action_meta[$action] ?? ['label' => ucfirst($action ?: 'Event'), 'class' => '', 'icon' => 'fa-circle'];
        ?>
          <div class="rph-item <?php echo $meta['class']; ?>">
            <span class="rph-dot"></span>
            <div class="rph-card">
              <div class="rph-head">
                <span class="rph-badge"><i class="fa <?php echo $meta['icon']; ?>"></i> <?php echo htmlspecialchars($meta['label']); ?></span>
                <span class="rph-time"><?php echo !empty($log['created_at']) ? date('d M Y, h:i A', strtotime($log['created_at'])) : ''; ?></span>
              </div>
              <div class="rph-user">by <strong><?php echo htmlspecialchars($log['added_by_name'] ?? 'System'); ?></strong></div>

              <?php if ($action == 'add'): ?>
                <div class="rph-note">Product was created with initial parameters.</div>
              <?php elseif ($action == 'delete'): ?>
                <div class="rph-note">Product was marked as deleted.</div>
              <?php elseif ($action == 'update' && !empty($data_payload)): ?>
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
                      $diffs[$key] = ['old' => $old_val, 'new' => $val];
                    }
                  }

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
                          $added_details[] = $f_lbl . ': ' . $new_p[$f_key];
                        }
                      }
                      $supplier_changes[] = ['type' => 'added', 'name' => $s_name, 'details' => $added_details];
                    } elseif ($old_p && !$new_p) {
                      $supplier_changes[] = ['type' => 'removed', 'name' => $s_name];
                    } elseif ($old_p && $new_p) {
                      $changed_details = [];
                      foreach ($supplier_fields as $f_key => $f_lbl) {
                        $old_val = $old_p[$f_key] ?? 0;
                        $new_val = $new_p[$f_key] ?? 0;
                        if (strval($old_val) !== strval($new_val)) {
                          $changed_details[] = ['label' => $f_lbl, 'old' => $old_val, 'new' => $new_val];
                        }
                      }
                      if (!empty($changed_details)) {
                        $supplier_changes[] = ['type' => 'changed', 'name' => $s_name, 'details' => $changed_details];
                      }
                    }
                  }
                ?>
                <?php if (!empty($diffs) || !empty($supplier_changes)): ?>
                  <div class="rph-changes">
                    <?php foreach ($diffs as $field => $change):
                      $label_text = isset($field_labels[$field]) ? $field_labels[$field] : ucwords(str_replace('_', ' ', $field));
                    ?>
                      <div class="rph-row">
                        <span class="rph-field"><?php echo htmlspecialchars($label_text); ?></span>
                        <span class="rph-vals">
                          <span class="rph-old"><?php echo $display($change['old']); ?></span>
                          <span class="rph-new"><?php echo $display($change['new']); ?></span>
                        </span>
                      </div>
                    <?php endforeach; ?>

                    <?php if (!empty($supplier_changes)): ?>
                      <div class="rph-supplier">
                        <div class="rph-supplier-title">Supplier pricing</div>
                        <?php foreach ($supplier_changes as $sc): ?>
                          <div class="mb-1">
                            <span class="rph-supplier-name"><?php echo htmlspecialchars($sc['name']); ?></span>
                            <?php if ($sc['type'] == 'added'): ?>
                              <span class="rph-mini rph-mini-add">Added</span>
                              <?php if (!empty($sc['details'])): ?>
                                <div class="rph-note mt-25"><?php echo htmlspecialchars(implode(', ', $sc['details'])); ?></div>
                              <?php endif; ?>
                            <?php elseif ($sc['type'] == 'removed'): ?>
                              <span class="rph-mini rph-mini-remove">Removed</span>
                            <?php elseif ($sc['type'] == 'changed'): ?>
                              <?php foreach ($sc['details'] as $det): ?>
                                <div class="rph-row">
                                  <span class="rph-field"><?php echo htmlspecialchars($det['label']); ?></span>
                                  <span class="rph-vals">
                                    <span class="rph-old"><?php echo $display($det['old']); ?></span>
                                    <span class="rph-new"><?php echo $display($det['new']); ?></span>
                                  </span>
                                </div>
                              <?php endforeach; ?>
                            <?php endif; ?>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    <?php endif; ?>
                  </div>
                <?php else: ?>
                  <div class="rph-note">Product variations or meta details were updated.</div>
                <?php endif; ?>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</div>
