<?php
$id = (int)($param2 ?? 0);
$company_id = (int)$this->session->userdata('company_id');

$transfer = $this->db->get_where('transferred_cash', ['id' => $id, 'is_deleted' => 0])->row_array();
if (empty($transfer) || (int)($transfer['company_id'] ?? 0) !== $company_id) {
  echo '<div class="alert alert-danger mb-0">Transfer not found.</div>';
  return;
}

$from_type = (($transfer['converted_from'] ?? '') === 'unofficial') ? 'unofficial' : 'official';
$to_type = $transfer['converted_to'] ?? '';
if (!in_array($to_type, ['official', 'unofficial'], true)) {
  $to_type = '';
}
$is_approved = !empty($transfer['is_approved']);
$method_to = $transfer['method_to'] ?? '';
$method_label = ($method_to === 'cheque') ? 'Bank' : (($method_to === 'cash') ? 'Cash' : '-');

$from_bank = '-';
if (!empty($transfer['company_bank_from'])) {
  $ba = $this->db->get_where('bank_accounts', ['id' => $transfer['company_bank_from']])->row_array();
  if (!empty($ba)) {
    $from_bank = ($ba['bank_name'] ?? 'Bank') . ' (' . ($transfer['company_bank_account_from'] ?: ($ba['account_no'] ?? '-')) . ')';
  } elseif (!empty($transfer['company_bank_account_from'])) {
    $from_bank = $transfer['company_bank_account_from'];
  }
}

$type_badge = ($from_type === 'unofficial')
  ? '<span class="badge bg-light-secondary text-secondary">Unofficial</span>'
  : '<span class="badge bg-light-primary text-primary">Official</span>';
$status_badge = $is_approved
  ? '<span class="badge bg-light-success text-success">Approved</span>'
  : '<span class="badge bg-light-warning text-warning">Pending</span>';
?>

<div class="row">
  <div class="col-12">
    <div class="border rounded p-1 mb-1" style="background:#fafafa;">
      <div class="d-flex justify-content-between align-items-start flex-wrap gap-1 mb-1">
        <div>
          <small class="text-muted d-block text-uppercase font-weight-bold" style="font-size:11px;">Transfer To</small>
          <div class="font-weight-bold" style="font-size:1.05rem;"><?= htmlspecialchars($transfer['company_to'] ?: '-'); ?></div>
        </div>
        <div class="text-md-end">
          <small class="text-muted d-block text-uppercase font-weight-bold" style="font-size:11px;">Amount</small>
          <div class="font-weight-bolder text-primary" style="font-size:1.35rem;">₹ <?= number_format((float)$transfer['amount'], 2); ?></div>
        </div>
      </div>
      <div class="row g-1 pt-1" style="border-top:1px dashed #ebe9f1;">
        <div class="col-6 col-md-4"><small class="text-muted d-block">Type</small><?= $type_badge; ?></div>
        <div class="col-6 col-md-4"><small class="text-muted d-block">Status</small><?= $status_badge; ?></div>
        <div class="col-6 col-md-4"><small class="text-muted d-block">Method From</small><span class="font-weight-bold">Bank</span></div>
        <div class="col-12 col-md-6"><small class="text-muted d-block">From Bank</small><span class="font-weight-bold"><?= htmlspecialchars($from_bank); ?></span></div>
        <?php if ($is_approved): ?>
        <div class="col-6 col-md-4"><small class="text-muted d-block">Received As</small><span class="font-weight-bold"><?= htmlspecialchars($method_label); ?></span></div>
        <?php if ($to_type !== ''): ?>
        <div class="col-6 col-md-4"><small class="text-muted d-block">Received Type</small><span class="badge <?= $to_type === 'unofficial' ? 'bg-light-secondary text-secondary' : 'bg-light-primary text-primary'; ?>"><?= ucfirst($to_type); ?></span></div>
        <?php endif; ?>
        <?php endif; ?>
        <div class="col-6 col-md-4"><small class="text-muted d-block">Transferred By</small><span class="font-weight-bold"><?= htmlspecialchars($transfer['added_by_name'] ?: '-'); ?></span></div>
        <div class="col-6 col-md-4"><small class="text-muted d-block">Date</small><span class="font-weight-bold"><?= !empty($transfer['added_date']) ? date('d M, Y h:i A', strtotime($transfer['added_date'])) : '-'; ?></span></div>
      </div>
      <?php if (!empty($transfer['remark'])): ?>
      <div class="mt-1 pt-1" style="border-top:1px dashed #ebe9f1;">
        <small class="text-muted d-block">Remark</small>
        <div><?= nl2br(htmlspecialchars($transfer['remark'])); ?></div>
      </div>
      <?php endif; ?>
    </div>
    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
  </div>
</div>
