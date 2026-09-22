<?php
$customer_id = (int)$param2;
$customer = $this->common_model->getRowById('customer', '*', ['id' => $customer_id]);
if (!is_array($customer)) {
    $customer = [];
}

$history_limit = 50;
$customer_history = $this->db->where('customer_id', $customer_id)
    ->order_by('id', 'DESC')
    ->limit($history_limit)
    ->get('customer_log')
    ->result_array();
if (!is_array($customer_history)) {
    $customer_history = [];
}

$total_history = (int) $this->db->where('customer_id', $customer_id)->count_all_results('customer_log');
$has_more = $total_history > count($customer_history);

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
    'sales_add' => ['class' => 'cth-add', 'icon' => 'fa-shopping-cart'],
    'sales_edit' => ['class' => 'cth-update', 'icon' => 'fa-pencil'],
    'sales_approve' => ['class' => 'cth-add', 'icon' => 'fa-check'],
    'sales_delete' => ['class' => 'cth-delete', 'icon' => 'fa-trash'],
    'sales_cancel' => ['class' => 'cth-delete', 'icon' => 'fa-ban'],
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
  .cth-load-more { text-align: center; padding: 10px 0 4px; color: #94a3b8; font-size: 12px; display: none; }
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
    <div class="cth-scroll" id="cth_history_scroll"
         data-customer-id="<?php echo (int)$customer_id; ?>"
         data-offset="<?php echo count($customer_history); ?>"
         data-limit="<?php echo (int)$history_limit; ?>"
         data-has-more="<?php echo $has_more ? '1' : '0'; ?>"
         data-loading="0">
      <div class="cth-timeline" id="cth_history_timeline">
        <?php
          $this->load->view('backend/inventory/partial_customer_history_items', [
            'customer_history' => $customer_history,
            'field_labels' => $field_labels,
            'action_meta' => $action_meta,
            'display' => $display,
          ]);
        ?>
      </div>
      <div class="cth-load-more" id="cth_history_loader">
        <span class="spinner-border spinner-border-sm text-primary" role="status"></span> Loading more...
      </div>
    </div>
  <?php endif; ?>
</div>

<script>
(function () {
  var $scroll = $('#cth_history_scroll');
  if (!$scroll.length) return;

  var loading = false;
  var ajaxUrl = '<?php echo base_url("inventory/get_customer_history_ajax"); ?>';

  function loadMoreHistory() {
    if (loading) return;
    if ($scroll.attr('data-has-more') !== '1') return;

    loading = true;
    $scroll.attr('data-loading', '1');
    $('#cth_history_loader').show();

    var customerId = $scroll.data('customer-id');
    var offset = parseInt($scroll.attr('data-offset'), 10) || 0;
    var limit = parseInt($scroll.attr('data-limit'), 10) || 50;

    $.ajax({
      url: ajaxUrl,
      type: 'POST',
      dataType: 'json',
      data: {
        customer_id: customerId,
        offset: offset,
        limit: limit,
        view: 'timeline'
      },
      success: function (res) {
        if (res && res.html) {
          $('#cth_history_timeline').append(res.html);
        }
        var nextOffset = (res && typeof res.next_offset !== 'undefined') ? res.next_offset : (offset + limit);
        $scroll.attr('data-offset', nextOffset);
        $scroll.attr('data-has-more', (res && res.has_more) ? '1' : '0');
      },
      complete: function () {
        loading = false;
        $scroll.attr('data-loading', '0');
        $('#cth_history_loader').hide();
      }
    });
  }

  $scroll.off('scroll.cthHistory').on('scroll.cthHistory', function () {
    var el = this;
    if (el.scrollTop + el.clientHeight >= el.scrollHeight - 40) {
      loadMoreHistory();
    }
  });
})();
</script>
