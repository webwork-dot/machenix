<?php
$order_id = (int)$param2;
$order = $this->common_model->getRowById('sales_order', '*', ['id' => $order_id]);
if (!is_array($order)) {
    $order = [];
}

$logs = $this->db->group_start()
    ->where('parent_id', $order_id)
    ->where('table_name', 'sales_order')
->group_end()
->or_group_start()
    ->where('parent_id', $order_id)
    ->where('module', 'sales')
->group_end()
->or_group_start()
    ->where('ref_id', $order_id)
    ->where('module', 'sales')
->group_end()
->order_by('created_at', 'desc')
->order_by('id', 'desc')
->get('sys_logs')->result_array();

$field_labels = [
    'order_no' => 'Order No',
    'refrence_no' => 'Reference No',
    'date' => 'Order Date',
    'customer_name' => 'Customer Name',
    'warehouse_name' => 'Warehouse',
    'company_name' => 'Company',
    'basic_value' => 'Basic Value',
    'net_sales_value_1' => 'Net Sales Value (Pre-GST)',
    'net_sales_value_2' => 'Net Sales Value (Post-GST)',
    'total_black_amt' => 'Black Amount Total',
    'central_gst' => 'CGST',
    'state_gst' => 'SGST',
    'igst' => 'IGST',
    'gst_type' => 'GST Type',
    'gst_total' => 'Total GST',
    'round_of' => 'Round Off',
    'grand_total' => 'Grand Total',
    'remark' => 'Remark',
    'narration' => 'Narration',
    'is_approved' => 'Approval Status',
    'is_generated' => 'Invoice Generated Status',
    'is_deleted' => 'Deleted Status',
    'other_charges_name' => 'Other Charges Name',
    'other_charges_amount' => 'Other Charges Amount',
    'shipping_address' => 'Shipping Address',
    'billing_address' => 'Billing Address',
];
?>

<style>
  .soh-wrap { font-family: inherit; color: #334155; }
  .soh-hero {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    margin-bottom: 12px;
    background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
    border: 1px solid #e2e8f0;
    border-radius: 12px;
  }
  .soh-thumb {
    width: 46px;
    height: 46px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    color: #6366f1;
    border: 1px solid #e2e8f0;
    flex-shrink: 0;
    font-size: 18px;
  }
  .soh-name { font-size: 14px; font-weight: 700; color: #0f172a; line-height: 1.3; margin-bottom: 4px; }
  .soh-chips { display: flex; flex-wrap: wrap; gap: 6px; }
  .soh-chip {
    font-size: 11px;
    color: #475569;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 999px;
    padding: 2px 8px;
    line-height: 1.5;
  }
  .soh-chip strong { color: #1e293b; font-weight: 600; }
  .soh-scroll { max-height: 420px; overflow-y: auto; padding-right: 4px; }
  .soh-scroll::-webkit-scrollbar { width: 6px; }
  .soh-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
  .soh-timeline { position: relative; padding-left: 18px; }
  .soh-timeline:before {
    content: "";
    position: absolute;
    left: 5px;
    top: 6px;
    bottom: 6px;
    width: 2px;
    background: #e2e8f0;
  }
  .soh-item { position: relative; margin-bottom: 12px; }
  .soh-item:last-child { margin-bottom: 0; }
  .soh-dot {
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
  .soh-item.soh-add .soh-dot { background: #16a34a; box-shadow: 0 0 0 2px #bbf7d0; }
  .soh-item.soh-update .soh-dot { background: #d97706; box-shadow: 0 0 0 2px #fde68a; }
  .soh-item.soh-delete .soh-dot { background: #dc2626; box-shadow: 0 0 0 2px #fecaca; }
  .soh-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 12px; }
  .soh-head { display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 4px; }
  .soh-badge {
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
  .soh-item.soh-add .soh-badge { background: #dcfce7; color: #166534; }
  .soh-item.soh-update .soh-badge { background: #fef3c7; color: #92400e; }
  .soh-item.soh-delete .soh-badge { background: #fee2e2; color: #991b1b; }
  .soh-time { font-size: 11px; color: #94a3b8; white-space: nowrap; }
  .soh-user { font-size: 12px; color: #64748b; margin-bottom: 6px; }
  .soh-user strong { color: #0f172a; }
  .soh-note {
    font-size: 12px;
    color: #334155;
    background: #f8fafc;
    border-left: 3px solid #6366f1;
    border-radius: 6px;
    padding: 6px 8px;
    margin-bottom: 6px;
  }
  .soh-changes {
    margin-top: 6px;
    background: #f8fafc;
    border: 1px solid #eef2f7;
    border-radius: 8px;
    padding: 6px 8px;
  }
  .soh-row {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    padding: 6px 0;
    border-bottom: 1px solid #eef2f7;
    font-size: 12px;
  }
  .soh-row:last-child { border-bottom: none; }
  .soh-field { color: #475569; font-weight: 600; min-width: 110px; }
  .soh-vals { text-align: right; word-break: break-word; }
  .soh-old { color: #ef4444; text-decoration: line-through; margin-right: 4px; }
  .soh-new { color: #059669; font-weight: 700; }
  .soh-sub {
    font-size: 10px;
    text-transform: uppercase;
    font-weight: 700;
    color: #94a3b8;
    letter-spacing: .4px;
    margin: 8px 0 4px;
  }
  .soh-table { width: 100%; font-size: 11px; border-collapse: collapse; }
  .soh-table th {
    background: #f1f5f9;
    color: #475569;
    padding: 5px 6px;
    font-weight: 600;
    text-align: left;
  }
  .soh-table td { padding: 5px 6px; border-top: 1px solid #eef2f7; color: #334155; vertical-align: top; }
  .soh-empty { text-align: center; padding: 28px 12px; color: #94a3b8; }
  .soh-empty i { font-size: 28px; opacity: .45; margin-bottom: 8px; display: block; }
</style>

<div class="soh-wrap">
  <div class="soh-hero">
    <div class="soh-thumb"><i class="fa fa-file-text-o"></i></div>
    <div>
      <div class="soh-name">
        Order #<?php echo htmlspecialchars($order['order_no'] ?? $order_id); ?>
        <?php if (!empty($order['refrence_no'])): ?>
          <span style="font-weight:500;color:#64748b;font-size:12px;">(<?php echo htmlspecialchars($order['refrence_no']); ?>)</span>
        <?php endif; ?>
      </div>
      <div class="soh-chips">
        <span class="soh-chip"><strong>Customer</strong> <?php echo htmlspecialchars($order['customer_name'] ?? '-'); ?></span>
        <?php if (!empty($order['warehouse_name'])): ?>
          <span class="soh-chip"><strong>Warehouse</strong> <?php echo htmlspecialchars($order['warehouse_name']); ?></span>
        <?php endif; ?>
        <?php if (isset($order['grand_total'])): ?>
          <span class="soh-chip"><strong>Total</strong> ₹<?php echo number_format((float)$order['grand_total'], 2); ?></span>
        <?php endif; ?>
        <?php if (!empty($order['date'])): ?>
          <span class="soh-chip"><?php echo date('d M Y', strtotime($order['date'])); ?></span>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <?php if (empty($logs)): ?>
    <div class="soh-empty">
      <i class="fa fa-history"></i>
      <div>No history recorded for this sales order.</div>
    </div>
  <?php else: ?>
    <div class="soh-scroll">
      <div class="soh-timeline">
        <?php foreach ($logs as $log):
          $action = strtolower(trim($log['action'] ?? ''));
          $data_payload = json_decode($log['json'] ?? '', true);
          $item_class = '';
          $badge_icon = 'fa-info-circle';
          $badge_label = ucfirst($action);

          if ($action == 'add' || $action == 'add_salesman_preapproved' || $action == 'add_company_sales') {
            $item_class = 'soh-add';
            $badge_icon = 'fa-plus';
            $badge_label = $action == 'add_salesman_preapproved' ? 'Pre-Approved' : ($action == 'add_company_sales' ? 'Company Sale' : 'Created');
          } elseif ($action == 'add_conversion') {
            $badge_icon = 'fa-random';
            $badge_label = 'Conversion';
          } elseif ($action == 'approve') {
            $item_class = 'soh-add';
            $badge_icon = 'fa-check';
            $badge_label = 'Approved';
          } elseif ($action == 'edit' || $action == 'update') {
            $item_class = 'soh-update';
            $badge_icon = 'fa-pencil';
            $badge_label = 'Updated';
          } elseif ($action == 'delete' || $action == 'cancel') {
            $item_class = 'soh-delete';
            $badge_icon = 'fa-trash';
            $badge_label = 'Cancelled';
          }

          $created_time_str = !empty($log['created_at'])
            ? (function_exists('formatHistoryTime') ? formatHistoryTime($log['created_at']) : date('d M Y, h:i A', strtotime($log['created_at'])))
            : '';
        ?>
          <div class="soh-item <?php echo $item_class; ?>">
            <span class="soh-dot"></span>
            <div class="soh-card">
              <div class="soh-head">
                <span class="soh-badge"><i class="fa <?php echo $badge_icon; ?>"></i> <?php echo htmlspecialchars($badge_label); ?></span>
                <span class="soh-time"><?php echo $created_time_str; ?></span>
              </div>
              <div class="soh-user">
                by <strong><?php echo htmlspecialchars($log['added_by_name'] ?? 'System'); ?></strong>
                <?php if (!empty($log['added_by_type'])): ?>
                  <span>(<?php echo htmlspecialchars($log['added_by_type']); ?>)</span>
                <?php endif; ?>
              </div>

              <?php if (!empty($log['message'])): ?>
                <div class="soh-note"><?php echo htmlspecialchars($log['message']); ?></div>
              <?php endif; ?>

              <?php if (!empty($data_payload) && is_array($data_payload)): ?>
                <?php if (isset($data_payload['old_sale_order']) || isset($data_payload['new_sale_order'])):
                  $old_so = $data_payload['old_sale_order']['sale_order'] ?? [];
                  $new_so = $data_payload['new_sale_order']['sale_order'] ?? [];
                  $new_prods = $data_payload['new_sale_order']['products'] ?? [];
                  $diffs = [];
                  foreach ($new_so as $k => $v) {
                    if (is_array($v) || !isset($field_labels[$k])) {
                      continue;
                    }
                    $old_v = $old_so[$k] ?? null;
                    if (strval($old_v) !== strval($v)) {
                      $diffs[$k] = ['old' => $old_v, 'new' => $v];
                    }
                  }
                ?>
                  <?php if (!empty($diffs) || !empty($new_prods)): ?>
                    <div class="soh-changes">
                      <?php foreach ($diffs as $field_key => $diff):
                        $label = $field_labels[$field_key] ?? ucwords(str_replace('_', ' ', $field_key));
                        $is_price = in_array($field_key, ['grand_total', 'basic_value', 'gst_total', 'total_black_amt', 'net_sales_value_1', 'net_sales_value_2', 'other_charges_amount']);
                        $old_display = $is_price && is_numeric($diff['old']) ? '₹' . number_format((float)$diff['old'], 2) : strval($diff['old'] ?? '—');
                        $new_display = $is_price && is_numeric($diff['new']) ? '₹' . number_format((float)$diff['new'], 2) : strval($diff['new'] ?? '—');
                      ?>
                        <div class="soh-row">
                          <span class="soh-field"><?php echo htmlspecialchars($label); ?></span>
                          <span class="soh-vals">
                            <span class="soh-old"><?php echo htmlspecialchars($old_display); ?></span>
                            <span class="soh-new"><?php echo htmlspecialchars($new_display); ?></span>
                          </span>
                        </div>
                      <?php endforeach; ?>
                      <?php if (!empty($new_prods)): ?>
                        <div class="soh-sub">Products (<?php echo count($new_prods); ?>)</div>
                        <div class="table-responsive">
                          <table class="soh-table">
                            <thead>
                              <tr>
                                <th>Product</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Rate</th>
                                <th class="text-end">Total</th>
                              </tr>
                            </thead>
                            <tbody>
                              <?php foreach ($new_prods as $np):
                                $pname = $np['product_name'] ?? $np['name'] ?? 'Product';
                                $icode = $np['item_code'] ?? '';
                                $pqty = (float)($np['qty'] ?? 0);
                                $prate = (float)($np['amount'] ?? $np['bill_amount'] ?? 0);
                                $ptotal = (float)($np['final_total'] ?? $np['total_amount'] ?? ($pqty * $prate));
                              ?>
                                <tr>
                                  <td>
                                    <strong><?php echo htmlspecialchars($pname); ?></strong>
                                    <?php if (!empty($icode)): ?><br><span class="text-muted"><?php echo htmlspecialchars($icode); ?></span><?php endif; ?>
                                  </td>
                                  <td class="text-center">
                                    <?php echo $pqty; ?>
                                    <?php if (isset($np['white_qty']) || isset($np['black_qty'])): ?>
                                      <br><span class="text-muted">W <?php echo (float)($np['white_qty'] ?? 0); ?> / B <?php echo (float)($np['black_qty'] ?? 0); ?></span>
                                    <?php endif; ?>
                                  </td>
                                  <td class="text-end">₹<?php echo number_format($prate, 2); ?></td>
                                  <td class="text-end"><strong>₹<?php echo number_format($ptotal, 2); ?></strong></td>
                                </tr>
                              <?php endforeach; ?>
                            </tbody>
                          </table>
                        </div>
                      <?php endif; ?>
                    </div>
                  <?php endif; ?>
                <?php elseif (isset($data_payload['sale_order']) || isset($data_payload['products'])):
                  $so_data = $data_payload['sale_order'] ?? [];
                  $so_prods = $data_payload['products'] ?? [];
                  $so_charges = $data_payload['other_charges'] ?? [];
                ?>
                  <div class="soh-changes">
                    <div class="soh-chips" style="margin-bottom:6px;">
                      <?php if (!empty($so_data['grand_total'])): ?>
                        <span class="soh-chip">Total <strong>₹<?php echo number_format((float)$so_data['grand_total'], 2); ?></strong></span>
                      <?php endif; ?>
                      <?php if (!empty($so_data['warehouse_name'])): ?>
                        <span class="soh-chip"><?php echo htmlspecialchars($so_data['warehouse_name']); ?></span>
                      <?php endif; ?>
                      <?php if (!empty($so_data['gst_type'])): ?>
                        <span class="soh-chip">GST <?php echo htmlspecialchars($so_data['gst_type']); ?></span>
                      <?php endif; ?>
                    </div>
                    <?php if (!empty($so_prods)): ?>
                      <div class="soh-sub">Products (<?php echo count($so_prods); ?>)</div>
                      <div class="table-responsive">
                        <table class="soh-table">
                          <thead>
                            <tr>
                              <th>Product</th>
                              <th class="text-center">Qty</th>
                              <th class="text-end">Rate</th>
                              <th class="text-end">Total</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($so_prods as $sp):
                              $pname = $sp['product_name'] ?? $sp['name'] ?? 'Product';
                              $icode = $sp['item_code'] ?? '';
                              $pqty = (float)($sp['qty'] ?? 0);
                              $prate = (float)($sp['amount'] ?? $sp['bill_amount'] ?? 0);
                              $ptotal = (float)($sp['final_total'] ?? $sp['total_amount'] ?? ($pqty * $prate));
                            ?>
                              <tr>
                                <td>
                                  <strong><?php echo htmlspecialchars($pname); ?></strong>
                                  <?php if (!empty($icode)): ?><br><span class="text-muted"><?php echo htmlspecialchars($icode); ?></span><?php endif; ?>
                                </td>
                                <td class="text-center"><?php echo $pqty; ?></td>
                                <td class="text-end">₹<?php echo number_format($prate, 2); ?></td>
                                <td class="text-end"><strong>₹<?php echo number_format($ptotal, 2); ?></strong></td>
                              </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    <?php endif; ?>
                    <?php if (!empty($so_charges)): ?>
                      <div class="soh-sub">Other charges</div>
                      <?php foreach ($so_charges as $charge): ?>
                        <div class="soh-note" style="margin-bottom:4px;">
                          <?php echo htmlspecialchars($charge['type'] ?? 'Charge'); ?>:
                          ₹<?php echo number_format((float)($charge['total_amt'] ?? $charge['amount'] ?? 0), 2); ?>
                        </div>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </div>
                <?php elseif ($action == 'delete' || $action == 'cancel'): ?>
                  <div class="soh-note">Order was cancelled and allocated stock was reverted.</div>
                <?php endif; ?>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</div>
