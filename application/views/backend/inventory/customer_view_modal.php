<?php
$customer_id = (int) $param2;
$customer = $this->inventory_model->get_customer_by_id($customer_id)->row_array();

if (empty($customer) || !empty($customer['is_deleted'])) {
	echo '<div class="alert alert-danger mb-0">Record not found.</div>';
	return;
}

$is_lead = (isset($customer['type']) && $customer['type'] === 'leads');

$val = function ($value) {
	$value = trim((string) ($value ?? ''));
	return $value !== '' ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : '-';
};

$distributor = ((int) ($customer['is_distributor'] ?? 0) === 1)
	? '<span class="badge bg-light-success text-success">Yes</span>'
	: '<span class="badge bg-light-secondary text-secondary">No</span>';

$type_badge = $is_lead
	? '<span class="badge bg-light-warning text-warning">Lead</span>'
	: '<span class="badge bg-light-primary text-primary">Customer</span>';

$status_badge = '-';
if (!empty($customer['status'])) {
	$status_text = !empty($customer['status_label']) ? $customer['status_label'] : ucfirst($customer['status']);
	$status_class = 'badge-secondary';
	if ($customer['status'] === 'fresh') {
		$status_class = 'badge-primary';
	} elseif ($customer['status'] === 'follow' || $customer['status'] === 'stalking') {
		$status_class = 'badge-warning';
	} elseif ($customer['status'] === 'lost') {
		$status_class = 'badge-danger';
	}
	$status_badge = '<span class="badge ' . $status_class . '">' . htmlspecialchars($status_text, ENT_QUOTES, 'UTF-8') . '</span>';
}

$staff_name = '-';
if (!empty($customer['added_by_id'])) {
	$staff = $this->db->select('first_name')->where('id', $customer['added_by_id'])->get('sys_users')->row_array();
	$staff_name = !empty($staff['first_name']) ? $staff['first_name'] : ($customer['added_by_name'] ?? '-');
} elseif (!empty($customer['added_by_name'])) {
	$staff_name = $customer['added_by_name'];
}

$added_date = (!empty($customer['added_date']) && $customer['added_date'] != '0000-00-00 00:00:00')
	? date('d M Y, h:i A', strtotime($customer['added_date']))
	: '-';

$status_date = (!empty($customer['status_date']) && $customer['status_date'] != '0000-00-00 00:00:00')
	? date('d M Y, h:i A', strtotime($customer['status_date']))
	: '-';
?>

<style>
  .customer-view-modal {
    padding: 6px 2px 10px;
  }
  .customer-view-modal .meta-dashboard {
    background: #ffffff;
    border-radius: 12px;
    padding: 18px;
    margin-bottom: 14px;
    border: 1px solid #e2e8f0;
  }
  .customer-view-modal .meta-dashboard-title {
    font-size: 1rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 12px;
    border-bottom: 1px solid #f1f5f9;
    padding-bottom: 8px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
  }
  .customer-view-modal .meta-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 14px;
  }
  .customer-view-modal .meta-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }
  .customer-view-modal .meta-label {
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #64748b;
    font-weight: 700;
  }
  .customer-view-modal .meta-value {
    font-size: 0.92rem;
    font-weight: 600;
    color: #0f172a;
    word-break: break-word;
  }
  .customer-view-modal .meta-value.address-value {
    white-space: pre-wrap;
    font-weight: 500;
    line-height: 1.45;
  }
</style>

<div class="customer-view-modal">
  <div class="meta-dashboard">
    <div class="meta-dashboard-title">
      <span>General Details</span>
      <?php echo $type_badge; ?>
    </div>
    <div class="meta-grid">
      <div class="meta-item">
        <span class="meta-label">Company Name</span>
        <span class="meta-value"><?php echo $val($customer['company_name'] ?? ''); ?></span>
      </div>
      <div class="meta-item">
        <span class="meta-label">Distributor</span>
        <span class="meta-value"><?php echo $distributor; ?></span>
      </div>
      <div class="meta-item">
        <span class="meta-label">GST Name</span>
        <span class="meta-value"><?php echo $val($customer['gst_name'] ?? ''); ?></span>
      </div>
      <div class="meta-item">
        <span class="meta-label">GST No.</span>
        <span class="meta-value"><?php echo $val($customer['gst_no'] ?? ''); ?></span>
      </div>
      <div class="meta-item">
        <span class="meta-label">State</span>
        <span class="meta-value"><?php echo $val($customer['state_name'] ?? ''); ?></span>
      </div>
      <div class="meta-item">
        <span class="meta-label">City</span>
        <span class="meta-value"><?php echo $val($customer['city_name'] ?? ''); ?></span>
      </div>
      <div class="meta-item">
        <span class="meta-label">Pincode</span>
        <span class="meta-value"><?php echo $val($customer['pincode'] ?? ''); ?></span>
      </div>
      <?php if (!$is_lead) { ?>
      <div class="meta-item">
        <span class="meta-label">Opening Amount</span>
        <span class="meta-value"><?php echo isset($customer['outstanding']) ? number_format((float) $customer['outstanding'], 2) : '-'; ?></span>
      </div>
      <div class="meta-item">
        <span class="meta-label">Outstanding Limit</span>
        <span class="meta-value"><?php echo isset($customer['outstanding_limit']) ? number_format((float) $customer['outstanding_limit'], 2) : '-'; ?></span>
      </div>
      <?php } ?>
      <div class="meta-item">
        <span class="meta-label">Staff</span>
        <span class="meta-value"><?php echo $val($staff_name); ?></span>
      </div>
      <div class="meta-item">
        <span class="meta-label">Status</span>
        <span class="meta-value"><?php echo $status_badge; ?></span>
      </div>
      <div class="meta-item">
        <span class="meta-label">Status Date</span>
        <span class="meta-value"><?php echo $val($status_date); ?></span>
      </div>
      <div class="meta-item">
        <span class="meta-label">Added Date</span>
        <span class="meta-value"><?php echo $val($added_date); ?></span>
      </div>
    </div>
  </div>

  <div class="meta-dashboard">
    <div class="meta-dashboard-title">Address</div>
    <div class="meta-grid">
      <div class="meta-item" style="grid-column: span 2;">
        <span class="meta-label">Address Line 1</span>
        <span class="meta-value address-value"><?php echo $val($customer['address'] ?? ''); ?></span>
      </div>
      <div class="meta-item" style="grid-column: span 2;">
        <span class="meta-label">Address Line 2</span>
        <span class="meta-value address-value"><?php echo $val($customer['address_2'] ?? ''); ?></span>
      </div>
    </div>
  </div>

  <div class="meta-dashboard">
    <div class="meta-dashboard-title">Owner</div>
    <div class="meta-grid">
      <div class="meta-item">
        <span class="meta-label">Owner Name</span>
        <span class="meta-value"><?php echo $val($customer['owner_name'] ?? ''); ?></span>
      </div>
      <div class="meta-item">
        <span class="meta-label">Owner Email</span>
        <span class="meta-value"><?php echo $val($customer['owner_email'] ?? ''); ?></span>
      </div>
      <div class="meta-item">
        <span class="meta-label">Owner Mobile</span>
        <span class="meta-value"><?php echo $val($customer['owner_mobile'] ?? ''); ?></span>
      </div>
      <div class="meta-item">
        <span class="meta-label">Owner WhatsApp</span>
        <span class="meta-value"><?php echo $val($customer['owner_whatsapp'] ?? ''); ?></span>
      </div>
    </div>
  </div>

  <div class="meta-dashboard">
    <div class="meta-dashboard-title">Purchase Manager</div>
    <div class="meta-grid">
      <div class="meta-item">
        <span class="meta-label">Name</span>
        <span class="meta-value"><?php echo $val($customer['pm_name'] ?? ''); ?></span>
      </div>
      <div class="meta-item">
        <span class="meta-label">Email</span>
        <span class="meta-value"><?php echo $val($customer['pm_email'] ?? ''); ?></span>
      </div>
      <div class="meta-item">
        <span class="meta-label">Mobile</span>
        <span class="meta-value"><?php echo $val($customer['pm_mobile'] ?? ''); ?></span>
      </div>
      <div class="meta-item">
        <span class="meta-label">WhatsApp</span>
        <span class="meta-value"><?php echo $val($customer['pm_whatsapp'] ?? ''); ?></span>
      </div>
    </div>
  </div>

  <div class="meta-dashboard mb-0">
    <div class="meta-dashboard-title">Other Contact</div>
    <div class="meta-grid">
      <div class="meta-item">
        <span class="meta-label">Name</span>
        <span class="meta-value"><?php echo $val($customer['other_name'] ?? ''); ?></span>
      </div>
      <div class="meta-item">
        <span class="meta-label">Email</span>
        <span class="meta-value"><?php echo $val($customer['other_email'] ?? ''); ?></span>
      </div>
      <div class="meta-item">
        <span class="meta-label">Mobile</span>
        <span class="meta-value"><?php echo $val($customer['other_mobile'] ?? ''); ?></span>
      </div>
      <div class="meta-item">
        <span class="meta-label">WhatsApp</span>
        <span class="meta-value"><?php echo $val($customer['other_whatsapp'] ?? ''); ?></span>
      </div>
    </div>
  </div>
</div>
