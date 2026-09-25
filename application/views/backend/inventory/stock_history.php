<?php
$ledger     = $ledger ?? null;
$from_date  = $from_date ?? date('Y-m-d');
$to_date    = $to_date ?? date('Y-m-d');
$date_range = $date_range ?? (date('d-m-Y', strtotime($from_date)) . ' - ' . date('d-m-Y', strtotime($to_date)));
$product    = $ledger['product'] ?? [];
$product_id = (int) ($id ?? ($product['id'] ?? 0));
$base_url   = base_url('inventory/stock-history/' . $product_id);

$fmt_qty = function ($n) {
	return number_format((float) $n, 0);
};
$fmt_val = function ($n) {
	return number_format((float) $n, 2);
};

$render_channel_table = function ($channel_key, $channel_data, $title = '') use ($fmt_qty, $fmt_val) {
	$opening = $channel_data['opening'] ?? ['qty' => 0, 'val' => 0];
	$months  = $channel_data['months'] ?? [];
	$grand   = $channel_data['grand'] ?? [
		'in_qty' => 0, 'in_val' => 0, 'out_qty' => 0, 'out_val' => 0, 'close_qty' => 0, 'close_val' => 0,
	];
	$table_id = 'sh-table-' . $channel_key . '-' . substr(md5($title . $channel_key), 0, 6);
	?>
	<div class="sh-channel-block mb-3">
		<?php if ($title !== ''): ?>
			<div class="sh-channel-title"><?= html_escape($title) ?></div>
		<?php endif; ?>
		<div class="table-responsive">
			<table class="table ledger-table sh-summary-table mb-0" id="<?= html_escape($table_id) ?>">
				<thead>
					<tr>
						<th rowspan="2" class="align-middle">Month</th>
						<th colspan="2" class="text-center">Inward</th>
						<th colspan="2" class="text-center">Outward</th>
						<th colspan="2" class="text-center">Closing</th>
					</tr>
					<tr>
						<th class="text-end">Qty</th>
						<th class="text-end">Value</th>
						<th class="text-end">Qty</th>
						<th class="text-end">Value</th>
						<th class="text-end">Qty</th>
						<th class="text-end">Value</th>
					</tr>
				</thead>
				<tbody>
					<tr class="ledger-row ledger-row-opening">
						<td>Opening Balance</td>
						<td class="text-end">—</td>
						<td class="text-end">—</td>
						<td class="text-end">—</td>
						<td class="text-end">—</td>
						<td class="text-end"><?= $fmt_qty($opening['qty'] ?? 0) ?></td>
						<td class="text-end"><?= $fmt_val($opening['val'] ?? 0) ?></td>
					</tr>
					<?php if (empty($months)): ?>
						<tr class="ledger-row">
							<td colspan="7" class="text-center text-muted py-3">No stock movements in this period</td>
						</tr>
					<?php else: ?>
						<?php foreach ($months as $mi => $m): ?>
							<?php $row_id = $table_id . '-m' . $mi; ?>
							<tr class="ledger-row sh-month-row" data-target="#<?= html_escape($row_id) ?>" role="button">
								<td>
									<span class="sh-expand-icon"><i class="fa fa-chevron-right"></i></span>
									<?= html_escape($m['label']) ?>
								</td>
								<td class="text-end"><?= $fmt_qty($m['in_qty']) ?></td>
								<td class="text-end"><?= $fmt_val($m['in_val']) ?></td>
								<td class="text-end"><?= $fmt_qty($m['out_qty']) ?></td>
								<td class="text-end"><?= $fmt_val($m['out_val']) ?></td>
								<td class="text-end"><?= $fmt_qty($m['close_qty']) ?></td>
								<td class="text-end"><?= $fmt_val($m['close_val']) ?></td>
							</tr>
							<tr class="sh-detail-row" id="<?= html_escape($row_id) ?>" style="display:none;">
								<td colspan="7" class="p-0">
									<div class="sh-detail-wrap">
										<table class="table ledger-table sh-detail-table mb-0">
											<thead>
												<tr>
													<th rowspan="2" class="align-middle">Date</th>
													<th rowspan="2" class="align-middle">Particular Name</th>
													<th rowspan="2" class="align-middle">Voucher Type</th>
													<th rowspan="2" class="align-middle">Voucher No</th>
													<th colspan="2" class="text-center">Inward</th>
													<th colspan="2" class="text-center">Outward</th>
													<th colspan="2" class="text-center">Closing</th>
													<th rowspan="2" class="align-middle">Added By</th>
												</tr>
												<tr>
													<th class="text-end">Qty</th>
													<th class="text-end">Value</th>
													<th class="text-end">Qty</th>
													<th class="text-end">Value</th>
													<th class="text-end">Qty</th>
													<th class="text-end">Value</th>
												</tr>
											</thead>
											<tbody>
												<?php foreach (($m['entries'] ?? []) as $e): ?>
													<tr class="ledger-row">
														<td><?= html_escape($e['date']) ?></td>
														<td><?= html_escape($e['particular']) ?></td>
														<td><?= html_escape($e['voucher_type']) ?></td>
														<td><?= html_escape($e['voucher_no']) ?></td>
														<td class="text-end"><?= $e['in_qty'] ? $fmt_qty($e['in_qty']) : '' ?></td>
														<td class="text-end"><?= $e['in_val'] ? $fmt_val($e['in_val']) : '' ?></td>
														<td class="text-end"><?= $e['out_qty'] ? $fmt_qty($e['out_qty']) : '' ?></td>
														<td class="text-end"><?= $e['out_val'] ? $fmt_val($e['out_val']) : '' ?></td>
														<td class="text-end"><?= $fmt_qty($e['close_qty']) ?></td>
														<td class="text-end"><?= $fmt_val($e['close_val']) ?></td>
														<td><?= html_escape($e['added_by']) ?></td>
													</tr>
												<?php endforeach; ?>
											</tbody>
											<tfoot>
												<?php $eg = $m['entries_grand'] ?? $m; ?>
												<tr class="tfoot-strong">
													<td colspan="4">Grand Total</td>
													<td class="text-end"><?= $fmt_qty($eg['in_qty'] ?? 0) ?></td>
													<td class="text-end"><?= $fmt_val($eg['in_val'] ?? 0) ?></td>
													<td class="text-end"><?= $fmt_qty($eg['out_qty'] ?? 0) ?></td>
													<td class="text-end"><?= $fmt_val($eg['out_val'] ?? 0) ?></td>
													<td class="text-end"><?= $fmt_qty($eg['close_qty'] ?? 0) ?></td>
													<td class="text-end"><?= $fmt_val($eg['close_val'] ?? 0) ?></td>
													<td></td>
												</tr>
											</tfoot>
										</table>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
				<tfoot>
					<tr class="tfoot-strong">
						<td>Grand Total</td>
						<td class="text-end"><?= $fmt_qty($grand['in_qty'] ?? 0) ?></td>
						<td class="text-end"><?= $fmt_val($grand['in_val'] ?? 0) ?></td>
						<td class="text-end"><?= $fmt_qty($grand['out_qty'] ?? 0) ?></td>
						<td class="text-end"><?= $fmt_val($grand['out_val'] ?? 0) ?></td>
						<td class="text-end"><?= $fmt_qty($grand['close_qty'] ?? 0) ?></td>
						<td class="text-end"><?= $fmt_val($grand['close_val'] ?? 0) ?></td>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
	<?php
};
?>

<style>
  .sh-page .customer-card-shell,
  .sh-page .ledger-card-shell {
    border: 1px solid #e8eaed;
    border-radius: 10px;
    overflow: hidden;
  }
  .sh-page .card-soft-header {
    border-bottom: 1px solid #f0f2f5;
    background: #fafbfc;
  }
  .sh-page .fs-10 { font-size: 10px; }
  .sh-page .fs-11 { font-size: 11px; }
  .sh-page .fs-13 { font-size: 13px; }
  .sh-page .fs-15 { font-size: 15px; }
  .sh-page .track-1 { letter-spacing: 0.6px; color: #9ca3af; }

  .filter-bar-shell {
    background: #fff;
    border: 1px solid #e8eaed;
    border-radius: 10px;
    padding: 10px 12px;
  }

  .ledger-tabs-bar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 16px;
    background: #fafbfc;
    border-bottom: 1px solid #f0f2f5;
  }
  .ledger-seg {
    display: inline-flex;
    align-items: center;
    padding: 3px;
    background: #eef0f3;
    border-radius: 8px;
    gap: 2px;
  }
  .ledger-seg .seg-btn {
    appearance: none;
    border: none;
    background: transparent;
    color: #6b7280;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.2px;
    padding: 7px 14px;
    border-radius: 6px;
    line-height: 1.2;
    cursor: pointer;
    transition: background 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
  }
  .ledger-seg .seg-btn:hover { color: #111827; }
  .ledger-seg .seg-btn.active {
    background: #ffffff;
    color: #1e652e;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
    border: 1px solid #1e652e;
  }

  .ledger-table thead th {
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    color: #6b7280;
    background: #f9fafb;
    border-bottom: 1px solid #e8eaed;
    white-space: nowrap;
    vertical-align: middle;
    padding: 8px 8px !important;
  }
  .ledger-table tbody td,
  .ledger-table tfoot td {
    font-size: 11px;
    padding: 6px 8px !important;
    white-space: nowrap;
    vertical-align: middle;
  }
  .ledger-row { border-bottom: 1px solid #f3f4f6; background: #fff; }
  .ledger-row:hover { background: #f9fafb; }
  .ledger-row-opening { background: #fafbfc; font-weight: 600; }
  .tfoot-strong { background: #f3f4f6; font-weight: 700; }

  .sh-month-row { cursor: pointer; }
  .sh-month-row.open { background: #f0fdf4; }
  .sh-expand-icon {
    display: inline-flex;
    width: 16px;
    color: #9ca3af;
    margin-right: 4px;
    transition: transform 0.15s ease;
  }
  .sh-month-row.open .sh-expand-icon { transform: rotate(90deg); color: #1e652e; }

  .sh-detail-wrap {
    background: #fcfcfd;
    border-top: 1px solid #e8eaed;
    border-bottom: 1px solid #e8eaed;
    padding: 8px 10px 10px;
  }
  .sh-detail-table thead th { background: #f3f4f6; }
  .sh-channel-title {
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.4px;
    text-transform: uppercase;
    color: #374151;
    padding: 10px 16px 0;
  }
  .sh-pane { display: none; padding: 12px 12px 16px; }
  .sh-pane.active { display: block; }
  .sh-both-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
  }
  @media (min-width: 1200px) {
    .sh-both-grid { grid-template-columns: 1fr 1fr; }
  }
</style>

<div class="sh-page">
  <div class="bg-white customer-card-shell mb-2">
    <div class="d-flex align-items-center justify-content-between px-3 py-2 card-soft-header flex-wrap gap-2">
      <div>
        <div class="text-uppercase fw-semibold fs-10 track-1 mb-1">Stock History</div>
        <div class="fw-semibold fs-15">
          <?= html_escape(trim(($product['item_code'] ?? '') . ' — ' . ($product['product_name'] ?? ''), ' —')) ?>
        </div>
      </div>
      <div class="d-flex align-items-center flex-wrap gap-2">
        <a href="<?= base_url('inventory/my-stock') ?>" class="btn btn-sm btn-outline-secondary fs-11">Back</a>
      </div>
    </div>
  </div>

  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
  <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

  <div class="filter-bar-shell mb-2">
    <form method="get" action="<?= html_escape($base_url) ?>" class="d-flex flex-wrap align-items-end gap-2" id="stockHistoryFilterForm">
      <div style="min-width: 260px;">
        <label class="fs-10 text-uppercase text-secondary fw-semibold d-block mb-1">Date</label>
        <input type="text" autocomplete="off" class="form-control form-control-sm bg-white datepicker_report"
               name="date_range" value="<?= html_escape($date_range) ?>" placeholder="Select date range">
      </div>
      <button type="submit" class="btn btn-sm btn-primary">Apply</button>
    </form>
  </div>
  <script>
    $('.datepicker_report').daterangepicker({
      autoUpdateInput: false,
      autoApply: false,
      startDate: moment('<?= html_escape($from_date) ?>', 'YYYY-MM-DD'),
      endDate: moment('<?= html_escape($to_date) ?>', 'YYYY-MM-DD'),
      locale: { format: 'DD-MM-YYYY' }
    });
    $('.datepicker_report').on('apply.daterangepicker', function(ev, picker) {
      $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
    });
  </script>

  <?php if (!$ledger): ?>
    <div class="alert alert-warning">No stock history found for this product.</div>
  <?php else: ?>
    <div class="bg-white ledger-card-shell" id="stockHistoryShell" data-mode="actual">
      <div class="ledger-tabs-bar">
        <div class="ledger-seg" id="stockHistoryTabs" role="tablist">
          <button type="button" class="seg-btn active" data-mode="actual">Actual</button>
          <button type="button" class="seg-btn" data-mode="white">White</button>
          <button type="button" class="seg-btn" data-mode="black">Black</button>
          <button type="button" class="seg-btn" data-mode="both">Both</button>
        </div>
      </div>

      <div class="sh-pane active" data-pane="actual">
        <?php $render_channel_table('actual', $ledger['actual'] ?? [], ''); ?>
      </div>
      <div class="sh-pane" data-pane="white">
        <?php $render_channel_table('white', $ledger['white'] ?? [], ''); ?>
      </div>
      <div class="sh-pane" data-pane="black">
        <?php $render_channel_table('black', $ledger['black'] ?? [], ''); ?>
      </div>
      <div class="sh-pane" data-pane="both">
        <div class="sh-both-grid">
          <?php $render_channel_table('both-actual', $ledger['actual'] ?? [], 'Actual'); ?>
          <?php $render_channel_table('both-white', $ledger['white'] ?? [], 'White'); ?>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<script>
(function ($) {
  var $shell = $('#stockHistoryShell');
  $('#stockHistoryTabs').on('click', '.seg-btn', function () {
    var mode = $(this).data('mode');
    $(this).addClass('active').siblings().removeClass('active');
    $shell.attr('data-mode', mode);
    $shell.find('.sh-pane').removeClass('active');
    $shell.find('.sh-pane[data-pane="' + mode + '"]').addClass('active');
  });

  $(document).on('click', '.sh-month-row', function () {
    var $row = $(this);
    var target = $row.data('target');
    var $detail = $(target);
    var open = $detail.is(':visible');
    $detail.toggle(!open);
    $row.toggleClass('open', !open);
  });
})(jQuery);
</script>
