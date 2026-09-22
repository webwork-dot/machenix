<?php
if (!isset($customer_history) || !is_array($customer_history)) {
    $customer_history = [];
}

$field_labels = $field_labels ?? [
    'company_name' => 'Company',
    'gst_name' => 'GST Name',
    'gst_no' => 'GST No',
    'address' => 'Address',
    'address_2' => 'Address 2',
    'city_name' => 'City',
    'state_name' => 'State',
    'pincode' => 'Pincode',
    'outstanding' => 'Outstanding',
    'owner_name' => 'Owner',
    'owner_email' => 'Owner Email',
    'owner_mobile' => 'Owner Mobile',
    'owner_whatsapp' => 'Owner WhatsApp',
    'pm_name' => 'Purchase Manager',
    'pm_email' => 'PM Email',
    'pm_mobile' => 'PM Mobile',
    'pm_whatsapp' => 'PM WhatsApp',
    'other_name' => 'Other Contact',
    'other_email' => 'Other Email',
    'other_mobile' => 'Other Mobile',
    'other_whatsapp' => 'Other WhatsApp',
    'added_by_name' => 'Assigned Staff',
    'status' => 'Status',
    'status_label' => 'Status Label',
    'is_distributor' => 'Distributor',
    'type' => 'Type',
    'refrence_no' => 'Reference No',
    'date' => 'Order Date',
    'customer_name' => 'Customer',
    'warehouse_name' => 'Warehouse',
    'remark' => 'Remark',
    'narration' => 'Narration',
    'gst_type' => 'GST Type',
    'basic_value' => 'Basic Value',
    'net_sales_value_1' => 'Net Sales Value 1',
    'total_black_amt' => 'Total Black Amt',
    'central_gst' => 'CGST',
    'state_gst' => 'SGST',
    'igst' => 'IGST',
    'gst_total' => 'GST Total',
    'net_sales_value_2' => 'Net Sales Value 2',
    'round_of' => 'Round Off',
    'grand_total' => 'Grand Total',
    'other_charges_name' => 'Other Charges',
    'other_charges_amount' => 'Other Charges Amt',
    'shipping_address' => 'Shipping Address',
    'billing_address' => 'Billing Address',
    'shipping_state_name' => 'Shipping State',
    'shipping_city_name' => 'Shipping City',
    'billing_state_name' => 'Billing State',
    'billing_city_name' => 'Billing City',
    'qty' => 'Qty',
    'amount' => 'Rate',
    'bill_amount' => 'Bill Amount',
    'final_total' => 'Final Total',
    'black_amount' => 'Black Amount',
];

$action_meta = $action_meta ?? [
    'create' => ['class' => 'cth-add', 'icon' => 'fa-plus'],
    'assign' => ['class' => 'cth-add', 'icon' => 'fa-user-plus'],
    'move' => ['class' => 'cth-add', 'icon' => 'fa-exchange'],
    'share' => ['class' => 'cth-add', 'icon' => 'fa-share-alt'],
    'update' => ['class' => 'cth-update', 'icon' => 'fa-pencil'],
    'reassign' => ['class' => 'cth-update', 'icon' => 'fa-random'],
    'follow' => ['class' => 'cth-update', 'icon' => 'fa-phone'],
    'stalking' => ['class' => 'cth-update', 'icon' => 'fa-phone'],
    'lost' => ['class' => 'cth-delete', 'icon' => 'fa-times'],
    'delete' => ['class' => 'cth-delete', 'icon' => 'fa-trash'],
    'sales_add' => ['class' => 'cth-add', 'icon' => 'fa-shopping-cart'],
    'sales_edit' => ['class' => 'cth-update', 'icon' => 'fa-pencil'],
    'sales_approve' => ['class' => 'cth-add', 'icon' => 'fa-check'],
    'sales_delete' => ['class' => 'cth-delete', 'icon' => 'fa-trash'],
    'sales_cancel' => ['class' => 'cth-delete', 'icon' => 'fa-ban'],
];

$display = $display ?? function ($value) {
    $text = trim(strval($value));
    return $text === '' ? '—' : htmlspecialchars($text);
};

$skip_diff_keys = ['sale_order', 'products', 'other_charges', 'product_changes', 'reverted_data', 'order_id', 'order_no', 'grand_total'];

foreach ($customer_history as $history):
  $json = [];
  $label = [];
  if (!empty($history['json'])) {
    $decoded = json_decode($history['json'], true);
    $json = is_array($decoded) ? $decoded : [];
  }
  if (!empty($history['label'])) {
    $decoded_label = json_decode($history['label'], true);
    $label = is_array($decoded_label) ? $decoded_label : [];
  }

  $action = strtolower($history['action'] ?? '');
  $meta = $action_meta[$action] ?? ['class' => '', 'icon' => 'fa-circle'];
  $badge_text = !empty($label['message']) ? $label['message'] : ucfirst($action ?: 'Event');

  if (in_array($action, ['reassign', 'update', 'sales_edit'], true)) {
    $by_label = 'Updated by';
  } elseif ($action === 'assign') {
    $by_label = 'Assigned to';
  } elseif ($action === 'move') {
    $by_label = 'Moved by';
  } elseif ($action === 'sales_approve') {
    $by_label = 'Approved by';
  } elseif (in_array($action, ['sales_delete', 'delete'], true)) {
    $by_label = 'Deleted by';
  } elseif ($action === 'sales_cancel') {
    $by_label = 'Cancelled by';
  } else {
    $by_label = 'Added by';
  }

  if ($action == 'assign') {
    $actor = $json['added_by_name'] ?? $history['added_by_name'];
  } else {
    $actor = $history['added_by_name'] ?? 'System';
  }

  $diffs = [];
  foreach ($json as $key => $val) {
    if (in_array($key, $skip_diff_keys, true)) {
      continue;
    }
    if (!is_array($val) || !array_key_exists('old', $val) || !array_key_exists('new', $val)) {
      continue;
    }
    if (substr($key, -3) === '_id') {
      continue;
    }
    $diffs[$key] = $val;
  }
  if (isset($json['old_added_by_name']) || isset($json['added_by_name'])) {
    if (strval($json['old_added_by_name'] ?? '') !== strval($json['added_by_name'] ?? '')) {
      $diffs['added_by_name'] = [
        'old' => $json['old_added_by_name'] ?? '',
        'new' => $json['added_by_name'] ?? '',
      ];
    }
  }

  $is_sales = strpos($action, 'sales_') === 0;
  $order_no = $json['order_no'] ?? '';
  $grand_total = $json['grand_total'] ?? null;
  $product_changes = (isset($json['product_changes']) && is_array($json['product_changes'])) ? $json['product_changes'] : [];
  $products = (isset($json['products']) && is_array($json['products'])) ? $json['products'] : [];
?>
  <div class="cth-item <?php echo $meta['class']; ?>" data-history-id="<?php echo (int)($history['id'] ?? 0); ?>">
    <span class="cth-dot"></span>
    <div class="cth-card">
      <div class="cth-head">
        <span class="cth-badge"><i class="fa <?php echo $meta['icon']; ?>"></i> <?php echo htmlspecialchars($badge_text); ?></span>
        <span class="cth-time"><?php echo !empty($history['added_date']) ? formatHistoryTime($history['added_date']) : ''; ?></span>
      </div>
      <div class="cth-user"><?php echo $by_label; ?> <strong><?php echo htmlspecialchars($actor ?: 'System'); ?></strong></div>

      <?php if ($is_sales && ($order_no !== '' || $grand_total !== null)): ?>
        <div class="cth-note">
          <?php if ($order_no !== ''): ?>
            Order: <strong><?php echo htmlspecialchars($order_no); ?></strong>
          <?php endif; ?>
          <?php if ($grand_total !== null && $grand_total !== ''): ?>
            <?php echo $order_no !== '' ? ' · ' : ''; ?>Total: <strong><?php echo htmlspecialchars((string)$grand_total); ?></strong>
          <?php endif; ?>
          <?php if (!empty($products) && $action !== 'sales_edit'): ?>
            · Items: <strong><?php echo count($products); ?></strong>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($json['status_date']) && strtotime($json['status_date']) > 0): ?>
        <div class="cth-note">Follow up: <strong><?php echo date('d M Y, h:i A', strtotime($json['status_date'])); ?></strong></div>
      <?php endif; ?>

      <?php if (!empty($history['message']) && $history['message'] !== $badge_text): ?>
        <div class="cth-note"><?php echo htmlspecialchars($history['message']); ?></div>
      <?php endif; ?>

      <?php if (!empty($json['remark']) && !$is_sales): ?>
        <div class="cth-remark"><strong>Remark:</strong> <?php echo nl2br(htmlspecialchars($json['remark'])); ?></div>
      <?php endif; ?>

      <?php if (!empty($diffs)): ?>
        <div class="cth-changes">
          <?php foreach ($diffs as $field => $change):
            $label_text = $field_labels[$field] ?? ucwords(str_replace('_', ' ', $field));
          ?>
            <div class="cth-row">
              <span class="cth-field"><?php echo htmlspecialchars($label_text); ?></span>
              <span class="cth-vals">
                <span class="cth-old"><?php echo $display($change['old']); ?></span>
                <span class="cth-new"><?php echo $display($change['new']); ?></span>
              </span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($product_changes)): ?>
        <div class="cth-changes">
          <div class="cth-row" style="border-bottom: none; padding-bottom: 2px;">
            <span class="cth-field" style="min-width: auto;">Product Changes</span>
          </div>
          <?php foreach ($product_changes as $pc):
            $pname = $pc['product_name'] ?? 'Product';
            $pchange = $pc['change'] ?? 'updated';
            if ($pchange === 'added' || $pchange === 'removed'):
              $qty_old = $pc['qty']['old'] ?? 0;
              $qty_new = $pc['qty']['new'] ?? 0;
          ?>
            <div class="cth-row">
              <span class="cth-field"><?php echo htmlspecialchars($pname); ?> (<?php echo htmlspecialchars($pchange); ?>)</span>
              <span class="cth-vals">
                <span class="cth-old"><?php echo $display($qty_old); ?></span>
                <span class="cth-new"><?php echo $display($qty_new); ?></span>
              </span>
            </div>
          <?php else:
            $fields = $pc['fields'] ?? [];
            foreach ($fields as $fkey => $fchange):
              $flabel = $field_labels[$fkey] ?? ucwords(str_replace('_', ' ', $fkey));
          ?>
            <div class="cth-row">
              <span class="cth-field"><?php echo htmlspecialchars($pname); ?> · <?php echo htmlspecialchars($flabel); ?></span>
              <span class="cth-vals">
                <span class="cth-old"><?php echo $display($fchange['old'] ?? ''); ?></span>
                <span class="cth-new"><?php echo $display($fchange['new'] ?? ''); ?></span>
              </span>
            </div>
          <?php endforeach; endif; endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
<?php endforeach; ?>
