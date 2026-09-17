<?php
$customer_id = (int)$param2;
$customer = $this->common_model->getRowById('customer', '*', ['id' => $customer_id]);
if (!is_array($customer)) {
    $customer = [];
}

$customer_history = $this->common_model->getResultById('customer_log', '*', ['customer_id' => $customer_id]);
if (!is_array($customer_history)) {
    $customer_history = [];
}
$customer_history = array_reverse($customer_history);

$is_lead = (($customer['type'] ?? '') === 'leads');
$title = $customer['company_name'] ?? $customer['gst_name'] ?? $customer['owner_name'] ?? ($is_lead ? 'Lead' : 'Customer');

$field_labels = [
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
];

$action_meta = [
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
];

$display = function ($value) {
    $text = trim(strval($value));
    return $text === '' ? '—' : htmlspecialchars($text);
};
?>

<style>
  .cth-wrap { font-family: inherit; color: #334155; }
  .cth-hero {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    margin-bottom: 12px;
    background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
    border: 1px solid #e2e8f0;
    border-radius: 12px;
  }
  .cth-thumb {
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
  .cth-name { font-size: 14px; font-weight: 700; color: #0f172a; line-height: 1.3; margin-bottom: 4px; }
  .cth-chips { display: flex; flex-wrap: wrap; gap: 6px; }
  .cth-chip {
    font-size: 11px;
    color: #475569;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 999px;
    padding: 2px 8px;
    line-height: 1.5;
  }
  .cth-chip strong { color: #1e293b; font-weight: 600; }
  .cth-scroll { max-height: 420px; overflow-y: auto; padding-right: 4px; }
  .cth-scroll::-webkit-scrollbar { width: 6px; }
  .cth-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
  .cth-timeline { position: relative; padding-left: 18px; }
  .cth-timeline:before {
    content: "";
    position: absolute;
    left: 5px;
    top: 6px;
    bottom: 6px;
    width: 2px;
    background: #e2e8f0;
  }
  .cth-item { position: relative; margin-bottom: 12px; }
  .cth-item:last-child { margin-bottom: 0; }
  .cth-dot {
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
  .cth-item.cth-add .cth-dot { background: #16a34a; box-shadow: 0 0 0 2px #bbf7d0; }
  .cth-item.cth-update .cth-dot { background: #d97706; box-shadow: 0 0 0 2px #fde68a; }
  .cth-item.cth-delete .cth-dot { background: #dc2626; box-shadow: 0 0 0 2px #fecaca; }
  .cth-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 12px; }
  .cth-head { display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 4px; }
  .cth-badge {
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
  .cth-item.cth-add .cth-badge { background: #dcfce7; color: #166534; }
  .cth-item.cth-update .cth-badge { background: #fef3c7; color: #92400e; }
  .cth-item.cth-delete .cth-badge { background: #fee2e2; color: #991b1b; }
  .cth-time { font-size: 11px; color: #94a3b8; white-space: nowrap; }
  .cth-user { font-size: 12px; color: #64748b; margin-bottom: 6px; }
  .cth-user strong { color: #0f172a; }
  .cth-note { font-size: 12px; color: #64748b; }
  .cth-remark {
    font-size: 12px;
    color: #334155;
    background: #f8fafc;
    border-left: 3px solid #6366f1;
    border-radius: 6px;
    padding: 6px 8px;
    margin-top: 6px;
  }
  .cth-changes {
    margin-top: 6px;
    background: #f8fafc;
    border: 1px solid #eef2f7;
    border-radius: 8px;
    padding: 6px 8px;
  }
  .cth-row {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    padding: 6px 0;
    border-bottom: 1px solid #eef2f7;
    font-size: 12px;
  }
  .cth-row:last-child { border-bottom: none; }
  .cth-field { color: #475569; font-weight: 600; min-width: 110px; }
  .cth-vals { text-align: right; word-break: break-word; }
  .cth-old { color: #ef4444; text-decoration: line-through; margin-right: 4px; }
  .cth-new { color: #059669; font-weight: 700; }
  .cth-empty { text-align: center; padding: 28px 12px; color: #94a3b8; }
  .cth-empty i { font-size: 28px; opacity: .45; margin-bottom: 8px; display: block; }
</style>

<div class="cth-wrap">
  <div class="cth-hero">
    <div class="cth-thumb"><i class="fa <?php echo $is_lead ? 'fa-user' : 'fa-building-o'; ?>"></i></div>
    <div>
      <div class="cth-name"><?php echo htmlspecialchars($title); ?></div>
      <div class="cth-chips">
        <span class="cth-chip"><strong><?php echo $is_lead ? 'Lead' : 'Customer'; ?></strong></span>
        <?php if (!empty($customer['status_label'])): ?>
          <span class="cth-chip"><?php echo htmlspecialchars($customer['status_label']); ?></span>
        <?php endif; ?>
        <?php if (!empty($customer['city_name']) || !empty($customer['state_name'])): ?>
          <span class="cth-chip"><?php echo htmlspecialchars(trim(($customer['city_name'] ?? '') . (!empty($customer['state_name']) ? ', ' . $customer['state_name'] : ''), ', ')); ?></span>
        <?php endif; ?>
        <?php if (!empty($customer['owner_mobile'])): ?>
          <span class="cth-chip"><strong>Mobile</strong> <?php echo htmlspecialchars($customer['owner_mobile']); ?></span>
        <?php endif; ?>
        <?php if (!empty($customer['gst_no'])): ?>
          <span class="cth-chip"><strong>GST</strong> <?php echo htmlspecialchars($customer['gst_no']); ?></span>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <?php if (empty($customer_history)): ?>
    <div class="cth-empty">
      <i class="fa fa-history"></i>
      <div>No history found for this <?php echo $is_lead ? 'lead' : 'customer'; ?>.</div>
    </div>
  <?php else: ?>
    <div class="cth-scroll">
      <div class="cth-timeline">
        <?php foreach ($customer_history as $history):
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

          if ($action == 'reassign' || $action == 'update') {
            $by_label = 'Updated by';
          } elseif ($action == 'assign') {
            $by_label = 'Assigned to';
          } elseif ($action == 'move') {
            $by_label = 'Moved by';
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
        ?>
          <div class="cth-item <?php echo $meta['class']; ?>">
            <span class="cth-dot"></span>
            <div class="cth-card">
              <div class="cth-head">
                <span class="cth-badge"><i class="fa <?php echo $meta['icon']; ?>"></i> <?php echo htmlspecialchars($badge_text); ?></span>
                <span class="cth-time"><?php echo !empty($history['added_date']) ? formatHistoryTime($history['added_date']) : ''; ?></span>
              </div>
              <div class="cth-user"><?php echo $by_label; ?> <strong><?php echo htmlspecialchars($actor ?: 'System'); ?></strong></div>

              <?php if (!empty($json['status_date']) && strtotime($json['status_date']) > 0): ?>
                <div class="cth-note">Follow up: <strong><?php echo date('d M Y, h:i A', strtotime($json['status_date'])); ?></strong></div>
              <?php endif; ?>

              <?php if (!empty($history['message']) && $history['message'] !== $badge_text): ?>
                <div class="cth-note"><?php echo htmlspecialchars($history['message']); ?></div>
              <?php endif; ?>

              <?php if (!empty($json['remark'])): ?>
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
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</div>
