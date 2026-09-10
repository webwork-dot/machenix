<?php
$order_id = (int)$param2;
$order = $this->common_model->getRowById('sales_order', '*', ['id' => $order_id]);

// Retrieve logs from sys_logs for this sales order
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

// Also retrieve records from sales_history table
$sales_history_records = $this->db->where('parent_id', $order_id)->order_by('id', 'desc')->get('sales_history')->result_array();

$field_labels = [
    'order_no'              => 'Order No',
    'refrence_no'           => 'Reference No',
    'date'                  => 'Order Date',
    'customer_name'         => 'Customer Name',
    'warehouse_name'        => 'Warehouse',
    'company_name'          => 'Company',
    'basic_value'           => 'Basic Value',
    'net_sales_value_1'     => 'Net Sales Value (Pre-GST)',
    'net_sales_value_2'     => 'Net Sales Value (Post-GST)',
    'total_black_amt'       => 'Black Amount Total',
    'central_gst'           => 'CGST',
    'state_gst'             => 'SGST',
    'igst'                  => 'IGST',
    'gst_type'              => 'GST Type',
    'gst_total'             => 'Total GST',
    'round_of'              => 'Round Off',
    'grand_total'           => 'Grand Total',
    'remark'                => 'Remark',
    'narration'             => 'Narration',
    'is_approved'           => 'Approval Status',
    'is_generated'          => 'Invoice Generated Status',
    'is_deleted'            => 'Deleted Status',
    'other_charges_name'    => 'Other Charges Name',
    'other_charges_amount'  => 'Other Charges Amount',
    'shipping_address'      => 'Shipping Address',
    'billing_address'       => 'Billing Address',
];
?>

<style>
  .so-history-container {
    font-family: inherit;
  }
  .so-order-summary {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 14px;
    margin-bottom: 16px;
  }
  .so-order-summary .so-meta-title {
    font-size: 13px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 4px;
  }
  .so-order-summary .so-meta-sub {
    font-size: 12px;
    color: #64748b;
  }
  .so-timeline {
    position: relative;
    padding-left: 8px;
  }
  .so-history-item {
    position: relative;
    margin-bottom: 16px;
  }
  .so-history-card {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #fff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    overflow: hidden;
    transition: all 0.2s ease;
  }
  .so-history-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  }
  .so-history-card .card-body {
    padding: 14px 16px;
  }
  .so-history-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    flex-wrap: wrap;
  }
  .so-action-badge {
    font-size: 11px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 20px;
    letter-spacing: 0.3px;
    text-transform: uppercase;
    display: inline-flex;
    align-items: center;
    gap: 4px;
  }
  .so-action-badge.badge-add {
    background-color: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
  }
  .so-action-badge.badge-approve {
    background-color: #e0e7ff;
    color: #3730a3;
    border: 1px solid #c7d2fe;
  }
  .so-action-badge.badge-edit {
    background-color: #fef3c7;
    color: #92400e;
    border: 1px solid #fde68a;
  }
  .so-action-badge.badge-delete {
    background-color: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
  }
  .so-action-badge.badge-conversion {
    background-color: #f3e8ff;
    color: #6b21a8;
    border: 1px solid #e9d5ff;
  }
  .so-action-badge.badge-other {
    background-color: #f1f5f9;
    color: #334155;
    border: 1px solid #e2e8f0;
  }
  .so-history-time {
    font-size: 11px;
    color: #94a3b8;
    font-weight: 500;
  }
  .so-user-info {
    font-size: 12px;
    color: #475569;
    margin-bottom: 6px;
  }
  .so-user-name {
    font-weight: 700;
    color: #0f172a;
  }
  .so-log-msg {
    font-size: 12px;
    color: #334155;
    background: #f8fafc;
    padding: 6px 10px;
    border-radius: 6px;
    border-left: 3px solid #6366f1;
    margin-bottom: 10px;
  }
  .so-changes-box {
    background: #fdfdfe;
    border: 1px solid #f1f5f9;
    border-radius: 6px;
    padding: 10px;
    margin-top: 8px;
    font-size: 12px;
  }
  .so-change-row {
    padding: 5px 0;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 8px;
  }
  .so-change-row:last-child {
    border-bottom: none;
  }
  .so-change-field {
    font-weight: 600;
    color: #475569;
    min-width: 120px;
  }
  .so-change-val {
    text-align: right;
    word-break: break-word;
  }
  .so-old-val {
    color: #ef4444;
    text-decoration: line-through;
    margin-right: 4px;
  }
  .so-new-val {
    color: #10b981;
    font-weight: 700;
  }
  .so-pro-table {
    width: 100%;
    font-size: 11px;
    margin-top: 6px;
    border-collapse: collapse;
  }
  .so-pro-table th {
    background: #f1f5f9;
    color: #475569;
    padding: 6px 8px;
    font-weight: 600;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
  }
  .so-pro-table td {
    padding: 6px 8px;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
  }
  .so-pro-table tr:last-child td {
    border-bottom: none;
  }
  .so-section-subtitle {
    font-size: 11px;
    text-transform: uppercase;
    font-weight: 700;
    color: #64748b;
    letter-spacing: 0.5px;
    margin: 8px 0 4px 0;
  }
  .so-stat-chip {
    display: inline-block;
    background: #f1f5f9;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 11px;
    margin-right: 4px;
    margin-bottom: 4px;
    color: #334155;
  }
  .so-stat-chip strong {
    color: #0f172a;
  }
</style>

<div class="so-history-container">
  <?php if (!empty($order)): ?>
    <div class="so-order-summary">
      <div class="d-flex justify-content-between align-items-start flex-wrap">
        <div>
          <div class="so-meta-title">
            <i class="fa fa-file-text-o text-primary mr-1"></i> Order #<?php echo htmlspecialchars($order['order_no'] ?? ''); ?>
            <?php if (!empty($order['refrence_no'])): ?>
              <span class="text-muted" style="font-size: 11px; font-weight: normal;">(Ref: <?php echo htmlspecialchars($order['refrence_no']); ?>)</span>
            <?php endif; ?>
          </div>
          <div class="so-meta-sub">
            <strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name'] ?? '-'); ?>
            <?php if (!empty($order['warehouse_name'])): ?>
              &bull; <strong>Warehouse:</strong> <?php echo htmlspecialchars($order['warehouse_name']); ?>
            <?php endif; ?>
          </div>
        </div>
        <div class="text-end">
          <span class="badge bg-dark" style="font-size: 12px; font-weight: 700;">
            Grand Total: ₹<?php echo number_format((float)($order['grand_total'] ?? 0), 2); ?>
          </span>
          <div class="so-meta-sub mt-1">
            <?php echo !empty($order['date']) ? date('d M, Y', strtotime($order['date'])) : ''; ?>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <?php if (empty($logs) && empty($sales_history_records)): ?>
    <div class="text-center py-5 text-muted">
      <i class="fa fa-history fa-3x mb-3 text-secondary" style="opacity: 0.5;"></i>
      <p class="mb-0" style="font-size: 13px;">No history recorded for this sales order.</p>
    </div>
  <?php else: ?>
    <div class="so-timeline">
      <?php 
      foreach ($logs as $log): 
        $action = strtolower(trim($log['action'] ?? ''));
        $data_payload = json_decode($log['json'] ?? '', true);

        // Badge determination
        $badge_class = 'badge-other';
        $badge_icon = 'fa-info-circle';
        $badge_label = ucfirst($action);

        if ($action == 'add') {
          $badge_class = 'badge-add';
          $badge_icon = 'fa-plus-circle';
          $badge_label = 'Booking Created';
        } elseif ($action == 'add_salesman_preapproved') {
          $badge_class = 'badge-add';
          $badge_icon = 'fa-check-square-o';
          $badge_label = 'Pre-Approved Order';
        } elseif ($action == 'add_company_sales') {
          $badge_class = 'badge-add';
          $badge_icon = 'fa-building-o';
          $badge_label = 'Company Sale Added';
        } elseif ($action == 'add_conversion') {
          $badge_class = 'badge-conversion';
          $badge_icon = 'fa-random';
          $badge_label = 'Conversion Order';
        } elseif ($action == 'approve') {
          $badge_class = 'badge-approve';
          $badge_icon = 'fa-check-circle';
          $badge_label = 'Approved';
        } elseif ($action == 'edit' || $action == 'update') {
          $badge_class = 'badge-edit';
          $badge_icon = 'fa-pencil-square-o';
          $badge_label = 'Edited';
        } elseif ($action == 'delete' || $action == 'cancel') {
          $badge_class = 'badge-delete';
          $badge_icon = 'fa-trash';
          $badge_label = 'Deleted / Cancelled';
        }

        $created_time_str = !empty($log['created_at']) 
            ? (function_exists('formatHistoryTime') ? formatHistoryTime($log['created_at']) : date('d M Y, h:i A', strtotime($log['created_at'])))
            : '';
      ?>
        <div class="so-history-item">
          <div class="so-history-card">
            <div class="card-body">
              <!-- Header: Action Badge & Time -->
              <div class="so-history-header">
                <span class="so-action-badge <?php echo $badge_class; ?>">
                  <i class="fa <?php echo $badge_icon; ?>"></i> <?php echo $badge_label; ?>
                </span>
                <span class="so-history-time">
                  <i class="fa fa-clock-o"></i> <?php echo $created_time_str; ?>
                </span>
              </div>

              <!-- User Info -->
              <div class="so-user-info">
                Performed by: 
                <span class="so-user-name"><?php echo htmlspecialchars($log['added_by_name'] ?? 'System User'); ?></span>
                <?php if (!empty($log['added_by_type'])): ?>
                  <span class="text-muted font-weight-normal" style="font-size: 11px;">(<?php echo htmlspecialchars($log['added_by_type']); ?>)</span>
                <?php endif; ?>
              </div>

              <!-- Message -->
              <?php if (!empty($log['message'])): ?>
                <div class="so-log-msg">
                  <?php echo htmlspecialchars($log['message']); ?>
                </div>
              <?php endif; ?>

              <!-- Content Payload Details -->
              <?php if (!empty($data_payload) && is_array($data_payload)): ?>
                
                <!-- 1. APPROVE / EDIT with old_sale_order and new_sale_order -->
                <?php if (isset($data_payload['old_sale_order']) || isset($data_payload['new_sale_order'])): 
                  $old_so = $data_payload['old_sale_order']['sale_order'] ?? [];
                  $new_so = $data_payload['new_sale_order']['sale_order'] ?? [];
                  $old_prods = $data_payload['old_sale_order']['products'] ?? [];
                  $new_prods = $data_payload['new_sale_order']['products'] ?? [];

                  // Calculate scalar field diffs
                  $diffs = [];
                  foreach ($new_so as $k => $v) {
                    if (is_array($v)) continue;
                    $old_v = $old_so[$k] ?? null;
                    if (strval($old_v) !== strval($v) && in_array($k, array_keys($field_labels))) {
                      $diffs[$k] = [
                        'old' => $old_v,
                        'new' => $v
                      ];
                    }
                  }
                ?>
                  <div class="so-changes-box">
                    <?php if (!empty($diffs)): ?>
                      <div class="so-section-subtitle">Updated Information:</div>
                      <?php foreach ($diffs as $field_key => $diff): 
                        $label = $field_labels[$field_key] ?? ucwords(str_replace('_', ' ', $field_key));
                        $is_price = in_array($field_key, ['grand_total', 'basic_value', 'gst_total', 'total_black_amt', 'net_sales_value_1', 'net_sales_value_2']);
                        $old_display = $is_price && is_numeric($diff['old']) ? '₹' . number_format((float)$diff['old'], 2) : strval($diff['old'] ?? '-');
                        $new_display = $is_price && is_numeric($diff['new']) ? '₹' . number_format((float)$diff['new'], 2) : strval($diff['new'] ?? '-');
                      ?>
                        <div class="so-change-row">
                          <span class="so-change-field"><?php echo htmlspecialchars($label); ?></span>
                          <span class="so-change-val">
                            <span class="so-old-val"><?php echo htmlspecialchars($old_display); ?></span>
                            &rarr;
                            <span class="so-new-val"><?php echo htmlspecialchars($new_display); ?></span>
                          </span>
                        </div>
                      <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Products in New/Approved State -->
                    <?php if (!empty($new_prods)): ?>
                      <div class="so-section-subtitle mt-2">
                        Allocated Products (<?php echo count($new_prods); ?>):
                      </div>
                      <div class="table-responsive">
                        <table class="so-pro-table">
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
                                  <?php if (!empty($icode)): ?>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($icode); ?></small>
                                  <?php endif; ?>
                                </td>
                                <td class="text-center">
                                  <?php echo $pqty; ?>
                                  <?php if (isset($np['white_qty']) || isset($np['black_qty'])): ?>
                                    <br><span class="text-muted" style="font-size: 10px;">(W: <?php echo (float)($np['white_qty'] ?? 0); ?> | B: <?php echo (float)($np['black_qty'] ?? 0); ?>)</span>
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

                <!-- 2. ADD / BOOKING CREATION payload -->
                <?php elseif (isset($data_payload['sale_order']) || isset($data_payload['products'])): 
                  $so_data = $data_payload['sale_order'] ?? [];
                  $so_prods = $data_payload['products'] ?? [];
                  $so_charges = $data_payload['other_charges'] ?? [];
                ?>
                  <div class="so-changes-box">
                    <!-- Key stats summary -->
                    <div class="mb-2">
                      <?php if (!empty($so_data['grand_total'])): ?>
                        <span class="so-stat-chip">Grand Total: <strong>₹<?php echo number_format((float)$so_data['grand_total'], 2); ?></strong></span>
                      <?php endif; ?>
                      <?php if (!empty($so_data['warehouse_name'])): ?>
                        <span class="so-stat-chip">Warehouse: <strong><?php echo htmlspecialchars($so_data['warehouse_name']); ?></strong></span>
                      <?php endif; ?>
                      <?php if (!empty($so_data['gst_type'])): ?>
                        <span class="so-stat-chip">GST: <strong><?php echo htmlspecialchars($so_data['gst_type']); ?></strong></span>
                      <?php endif; ?>
                      <?php if (!empty($so_data['date'])): ?>
                        <span class="so-stat-chip">Date: <strong><?php echo date('d M, Y', strtotime($so_data['date'])); ?></strong></span>
                      <?php endif; ?>
                    </div>

                    <!-- Products Table -->
                    <?php if (!empty($so_prods)): ?>
                      <div class="so-section-subtitle">Products Added (<?php echo count($so_prods); ?>):</div>
                      <div class="table-responsive">
                        <table class="so-pro-table">
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
                                  <?php if (!empty($icode)): ?>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($icode); ?></small>
                                  <?php endif; ?>
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

                    <!-- Other Charges -->
                    <?php if (!empty($so_charges)): ?>
                      <div class="so-section-subtitle mt-2">Other Charges:</div>
                      <div style="font-size: 11px; color: #475569;">
                        <?php foreach ($so_charges as $charge): ?>
                          <div>
                            &bull; <strong><?php echo htmlspecialchars($charge['type'] ?? 'Charge'); ?>:</strong>
                            ₹<?php echo number_format((float)($charge['total_amt'] ?? $charge['amount'] ?? 0), 2); ?>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    <?php endif; ?>
                  </div>

                <!-- 3. DELETE / CANCEL payload -->
                <?php elseif ($action == 'delete' || $action == 'cancel'): ?>
                  <div class="so-changes-box">
                    <div class="text-danger" style="font-size: 12px;">
                      <i class="fa fa-exclamation-triangle"></i> Order was cancelled and allocated batches/stock were reverted.
                    </div>
                  </div>
                <?php endif; ?>

              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
