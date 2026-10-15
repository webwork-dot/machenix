<?php
$sales_order_id = (int) $param2;
$mode = !empty($param3) ? $param3 : 'add';
$is_view = ($mode === 'view');
$is_edit = ($mode === 'edit');

$sales_order = $this->db->query("SELECT * FROM sales_order WHERE id = ? AND is_deleted = '0'", array($sales_order_id))->row_array();
if (empty($sales_order)) {
	echo '<div class="alert alert-danger">Sales Order not found.</div>';
	return;
}

$products = $this->db->query("SELECT * FROM sales_order_product WHERE order_id = ?", array($sales_order_id))->result_array();
$company_id = (int) $this->session->userdata('company_id');

$existing_rows = $this->db->query(
	"SELECT * FROM sales_order_intimation_products WHERE sales_order_id = ? AND is_deleted = 0 ORDER BY id ASC",
	array($sales_order_id)
)->result_array();

$existing_by_product = array();
foreach ($existing_rows as $row) {
	$op_id = (int) $row['order_product_id'];
	if (!isset($existing_by_product[$op_id])) {
		$existing_by_product[$op_id] = array();
	}
	$existing_by_product[$op_id][] = $row;
}

$stock_by_product = array();
$product_ids = array();
foreach ($products as $product) {
	$pid = (int) $product['product_id'];
	if ($pid > 0) {
		$product_ids[$pid] = $pid;
	}
}
if (!empty($product_ids)) {
	$ids_list = implode(',', array_map('intval', $product_ids));
	$stock_rows = $this->db->query(
		"SELECT product_id,
			COALESCE(SUM(CASE WHEN company_id = ? THEN official_qty ELSE 0 END), 0) AS company_white,
			COALESCE(SUM(CASE WHEN company_id = ? THEN black_qty ELSE 0 END), 0) AS company_black,
			COALESCE(SUM(official_qty), 0) AS overall_white,
			COALESCE(SUM(black_qty), 0) AS overall_black
		FROM inventory
		WHERE product_id IN ($ids_list)
		GROUP BY product_id",
		array($company_id, $company_id)
	)->result_array();
	foreach ($stock_rows as $sr) {
		$stock_by_product[(int) $sr['product_id']] = array(
			'company_white' => (int) $sr['company_white'],
			'company_black' => (int) $sr['company_black'],
			'overall_white' => (int) $sr['overall_white'],
			'overall_black' => (int) $sr['overall_black'],
		);
	}
}

$warehouse_name_map = array();
$warehouse_ids = array();
foreach ($existing_rows as $row) {
	$wid = (int) $row['warehouse_id'];
	if ($wid > 0) {
		$warehouse_ids[$wid] = $wid;
	}
}
if (!empty($warehouse_ids)) {
	$wh_list = implode(',', array_map('intval', $warehouse_ids));
	$wh_rows = $this->db->query("SELECT id, name FROM warehouse WHERE id IN ($wh_list)")->result_array();
	foreach ($wh_rows as $wh) {
		$warehouse_name_map[(int) $wh['id']] = $wh['name'];
	}
}

$intimation_remark = isset($sales_order['intimation_remark']) ? $sales_order['intimation_remark'] : '';
$expected_date = (!empty($sales_order['expected_date']) && $sales_order['expected_date'] != '0000-00-00')
	? $sales_order['expected_date']
	: date('Y-m-d');

if (!function_exists('intimation_batch_row_html')) {
function intimation_batch_row_html($p_index, $batch, $is_view, $warehouse_name = '') {
	$batch_type = $batch['batch_type'];
	$batch_value = $batch_type . '|' . $batch['batch_id'];
	$show_avail = ($batch_type === 'inventory');
	$label = $batch['batch_no'];
	if ($warehouse_name !== '') {
		$label .= ' - ' . $warehouse_name;
	}
	if ($batch_type === 'po') {
		$label .= ' (PO Pending)';
	} elseif ($batch_type === 'loading') {
		$label .= ' (Loading)';
	}
	$disabled = $is_view ? 'disabled' : '';
	$readonly = $is_view ? 'readonly' : '';
	ob_start();
	?>
	<div class="intimation-batch-row" data-product-index="<?php echo $p_index; ?>">
		<div class="intimation-batch-grid">
			<div class="ibg-batch">
				<select class="form-control form-control-sm intimation-batch-select" <?php echo $disabled; ?> onchange="onIntimationBatchChange(this)">
					<option value="">Select Batch</option>
					<option value="<?php echo htmlspecialchars($batch_value, ENT_QUOTES, 'UTF-8'); ?>"
						data-batch-type="<?php echo htmlspecialchars($batch_type, ENT_QUOTES, 'UTF-8'); ?>"
						data-batch-id="<?php echo (int) $batch['batch_id']; ?>"
						data-batch-no="<?php echo htmlspecialchars($batch['batch_no'], ENT_QUOTES, 'UTF-8'); ?>"
						data-warehouse-id="<?php echo (int) $batch['warehouse_id']; ?>"
						selected>
						<?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
					</option>
				</select>
				<input type="hidden" class="batch-type" value="<?php echo htmlspecialchars($batch_type, ENT_QUOTES, 'UTF-8'); ?>">
				<input type="hidden" class="batch-id" value="<?php echo (int) $batch['batch_id']; ?>">
				<input type="hidden" class="batch-no" value="<?php echo htmlspecialchars($batch['batch_no'], ENT_QUOTES, 'UTF-8'); ?>">
				<input type="hidden" class="warehouse-id" value="<?php echo (int) $batch['warehouse_id']; ?>">
			</div>
			<div class="ibg-qty">
				<div class="qty-box">
					<label>White <span class="avail-white-badge <?php echo $show_avail ? '' : 'd-none'; ?>">(<span class="avail-white-text">-</span>)</span></label>
					<input type="number" min="0" step="1" class="form-control form-control-sm text-center white-qty" value="<?php echo (int) $batch['white_qty']; ?>" <?php echo $readonly; ?> oninput="updateIntimationAllocated(<?php echo $p_index; ?>)">
					<input type="hidden" class="available-white-qty" value="0">
				</div>
				<div class="qty-box">
					<label>Black <span class="avail-black-badge <?php echo $show_avail ? '' : 'd-none'; ?>">(<span class="avail-black-text">-</span>)</span></label>
					<input type="number" min="0" step="1" class="form-control form-control-sm text-center black-qty" value="<?php echo (int) $batch['black_qty']; ?>" <?php echo $readonly; ?> oninput="updateIntimationAllocated(<?php echo $p_index; ?>)">
					<input type="hidden" class="available-black-qty" value="0">
				</div>
			</div>
			<?php if (!$is_view) { ?>
			<div class="ibg-action">
				<button type="button" class="btn btn-icon btn-sm btn-outline-danger" title="Remove" onclick="removeIntimationBatchRow(this, <?php echo $p_index; ?>)"><i class="fa fa-times"></i></button>
			</div>
			<?php } ?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
}
?>

<style>
.intimation-modal { font-size: 13px; color: #334155; }
.intimation-modal .im-meta {
	display: flex; flex-wrap: wrap; gap: 8px 16px; align-items: center;
	padding: 8px 12px; margin-bottom: 10px;
	background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px;
}
.intimation-modal .im-meta span { color: #64748b; }
.intimation-modal .im-meta strong { color: #0f172a; font-weight: 600; }
.intimation-modal .im-top {
	display: grid; grid-template-columns: minmax(160px, 220px) 1fr; gap: 10px;
	margin-bottom: 12px; align-items: end;
}
.intimation-modal .im-top label {
	display: block; font-size: 11px; font-weight: 600; color: #64748b;
	text-transform: uppercase; letter-spacing: .03em; margin-bottom: 4px;
}
.intimation-modal .im-top .form-control {
	height: 38px;
}
.intimation-modal .select2-container {
	width: 100% !important;
}
.intimation-modal .select2-container .select2-selection--single {
	height: 38px !important;
	min-height: 38px !important;
	display: flex !important;
	align-items: center !important;
	border: 1px solid #d8d6de !important;
	border-radius: 0.357rem !important;
	background-color: #fff !important;
}
.intimation-modal .select2-container--default .select2-selection--single .select2-selection__rendered {
	line-height: normal !important;
	padding-left: 12px !important;
	padding-right: 28px !important;
	color: #6e6b7b !important;
	width: 100%;
}
.intimation-modal .select2-container--default .select2-selection--single .select2-selection__arrow {
	height: 100% !important;
	top: 0 !important;
	right: 6px !important;
	display: flex;
	align-items: center;
}
.intimation-modal .select2-container--default .select2-selection--single .select2-selection__placeholder {
	color: #b9b9c3 !important;
}
.intimation-modal .select2-container--default.select2-container--focus .select2-selection--single,
.intimation-modal .select2-container--default.select2-container--open .select2-selection--single {
	border-color: #7367f0 !important;
}
.intimation-modal .product-card {
	border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 8px;
	background: #fff; overflow: hidden;
}
.intimation-modal .product-head {
	display: flex; justify-content: space-between; align-items: center; gap: 8px;
	padding: 8px 10px; background: #f8fafc; border-bottom: 1px solid #eef2f7;
}
.intimation-modal .product-title { font-weight: 600; color: #0f172a; line-height: 1.2; }
.intimation-modal .product-title .code { color: #94a3b8; font-weight: 500; font-size: 12px; }
.intimation-modal .product-meta {
	display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
}
.intimation-modal .chip {
	display: inline-flex; align-items: center; gap: 4px;
	padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 600;
	background: #eef2ff; color: #4338ca; white-space: nowrap;
}
.intimation-modal .chip.stock { background: #ecfdf5; color: #047857; }
.intimation-modal .chip.overall { background: #f1f5f9; color: #334155; }
.intimation-modal .chip.alloc { background: #f1f5f9; color: #475569; }
.intimation-modal .chip.alloc.ok { background: #dcfce7; color: #166534; }
.intimation-modal .chip.alloc.warn { background: #fef3c7; color: #92400e; }
.intimation-modal .chip.alloc.over { background: #fee2e2; color: #991b1b; }
.intimation-modal .product-body { padding: 8px 10px; }
.intimation-modal .empty-batches {
	font-size: 12px; color: #94a3b8; padding: 4px 0 2px;
}
.intimation-modal .intimation-batch-row + .intimation-batch-row { margin-top: 6px; }
.intimation-modal .intimation-batch-grid {
	display: grid;
	grid-template-columns: minmax(220px, 1.6fr) minmax(180px, 1fr) 36px;
	gap: 8px; align-items: end;
}
.intimation-modal .ibg-qty {
	display: grid; grid-template-columns: 1fr 1fr; gap: 6px;
}
.intimation-modal .qty-box label {
	display: block; font-size: 11px; color: #64748b; margin-bottom: 2px; font-weight: 500;
}
.intimation-modal .qty-box .avail-white-badge { color: #16a34a; font-weight: 600; }
.intimation-modal .qty-box .avail-black-badge { color: #64748b; font-weight: 600; }
.intimation-modal .ibg-action { display: flex; justify-content: center; padding-bottom: 1px; }
.intimation-modal .im-actions {
	display: flex; justify-content: flex-end; gap: 8px; margin-top: 12px;
	padding-top: 10px; border-top: 1px solid #e2e8f0;
}
.intimation-modal .btn-icon { width: 30px; height: 30px; padding: 0; }
@media (max-width: 768px) {
	.intimation-modal .im-top { grid-template-columns: 1fr; }
	.intimation-modal .intimation-batch-grid { grid-template-columns: 1fr; }
	.intimation-modal .ibg-action { justify-content: flex-start; }
}
</style>

<div class="intimation-modal">
	<div class="im-meta">
		<div><span>Order</span> <strong><?php echo htmlspecialchars($sales_order['order_no'] ?? '', ENT_QUOTES, 'UTF-8'); ?></strong></div>
		<div><span>Customer</span> <strong><?php echo htmlspecialchars($sales_order['customer_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></strong></div>
	</div>

	<form id="sales_order_intimation_form" onsubmit="return submitSalesOrderIntimation(event);">
		<div class="im-top">
			<div>
				<label>Expected Delivery Date</label>
				<input type="date" class="form-control" name="expected_date" id="intimation_expected_date" <?php echo $is_view ? 'readonly' : ''; ?> required value="<?php echo htmlspecialchars($expected_date, ENT_QUOTES, 'UTF-8'); ?>">
			</div>
			<div>
				<label>Remark</label>
				<input type="text" class="form-control" name="intimation_remark" id="intimation_remark" <?php echo $is_view ? 'readonly' : ''; ?> placeholder="Optional remark" value="<?php echo htmlspecialchars($intimation_remark, ENT_QUOTES, 'UTF-8'); ?>">
			</div>
		</div>

		<?php if (empty($products)) { ?>
			<div class="alert alert-warning mb-0">No products found on this sales order.</div>
		<?php } else { ?>
			<?php foreach ($products as $p_index => $product) {
				$op_id = (int) $product['id'];
				$product_id = (int) $product['product_id'];
				$ordered_qty = (int) $product['qty'];
				$existing = isset($existing_by_product[$op_id]) ? $existing_by_product[$op_id] : array();
				$company_white = isset($stock_by_product[$product_id]) ? $stock_by_product[$product_id]['company_white'] : 0;
				$company_black = isset($stock_by_product[$product_id]) ? $stock_by_product[$product_id]['company_black'] : 0;
				$overall_white = isset($stock_by_product[$product_id]) ? $stock_by_product[$product_id]['overall_white'] : 0;
				$overall_black = isset($stock_by_product[$product_id]) ? $stock_by_product[$product_id]['overall_black'] : 0;
			?>
			<div class="product-card intimation-product" data-index="<?php echo $p_index; ?>" data-order-product-id="<?php echo $op_id; ?>" data-product-id="<?php echo $product_id; ?>" data-ordered-qty="<?php echo $ordered_qty; ?>">
				<div class="product-head">
					<div class="product-title">
						<?php echo htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8'); ?>
						<?php if (!empty($product['item_code'])) { ?>
							<span class="code"><?php echo htmlspecialchars($product['item_code'], ENT_QUOTES, 'UTF-8'); ?></span>
						<?php } ?>
					</div>
					<div class="product-meta">
						<span class="chip">Qty <?php echo $ordered_qty; ?></span>
						<span class="chip stock" title="Current company stock">Company W:<?php echo $company_white; ?> / B:<?php echo $company_black; ?></span>
						<span class="chip overall" title="Overall stock (all companies)">Overall W:<?php echo $overall_white; ?> / B:<?php echo $overall_black; ?></span>
						<span class="chip alloc" id="allocated_info_<?php echo $p_index; ?>">0 / <?php echo $ordered_qty; ?></span>
						<?php if (!$is_view) { ?>
							<button type="button" class="btn btn-sm btn-primary" onclick="addIntimationBatchRow(<?php echo $p_index; ?>)">
								<i class="fa fa-plus"></i> Batch
							</button>
						<?php } ?>
					</div>
				</div>
				<div class="product-body">
					<div class="intimation-batch-body" id="intimation_batch_body_<?php echo $p_index; ?>">
						<?php
						if (!empty($existing)) {
							foreach ($existing as $batch) {
								$wh_name = isset($warehouse_name_map[(int) $batch['warehouse_id']]) ? $warehouse_name_map[(int) $batch['warehouse_id']] : '';
								echo intimation_batch_row_html($p_index, $batch, $is_view, $wh_name);
							}
						}
						?>
					</div>
					<div class="empty-batches <?php echo !empty($existing) ? 'd-none' : ''; ?>" id="empty_batches_<?php echo $p_index; ?>">
						No batches added<?php echo $is_view ? '' : ' — optional'; ?>.
					</div>
				</div>
			</div>
			<?php } ?>
		<?php } ?>

		<div class="im-actions">
			<?php if (!$is_view) { ?>
				<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
				<button type="submit" class="btn btn-primary" id="intimation_submit_btn">
					<?php echo $is_edit ? 'Update Intimation' : 'Save Intimation'; ?>
				</button>
			<?php } else { ?>
				<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
			<?php } ?>
		</div>
	</form>
</div>

<script>
(function() {
	var intimationOrderId = <?php echo (int) $sales_order_id; ?>;
	var intimationIsView = <?php echo $is_view ? 'true' : 'false'; ?>;
	var intimationBatchCounters = {};

	function toggleEmptyState(productIndex) {
		var hasRows = $('#intimation_batch_body_' + productIndex + ' .intimation-batch-row').length > 0;
		$('#empty_batches_' + productIndex).toggleClass('d-none', hasRows);
	}

	function initIntimationSelect2(context) {
		var $scope = context ? $(context) : $('#sales_order_intimation_form');
		$scope.find('select.select2, select.intimation-batch-select').each(function() {
			var $el = $(this);
			if ($el.hasClass('select2-hidden-accessible')) {
				$el.select2('destroy');
			}
			$el.select2({
				dropdownParent: $('#large-modal'),
				width: '100%'
			});
		});
	}

	function batchRowTemplate(productIndex) {
		return `
			<div class="intimation-batch-row" data-product-index="${productIndex}">
				<div class="intimation-batch-grid">
					<div class="ibg-batch">
						<select class="form-control form-control-sm intimation-batch-select" onchange="onIntimationBatchChange(this)">
							<option value="">Select Batch</option>
						</select>
						<input type="hidden" class="batch-type" value="">
						<input type="hidden" class="batch-id" value="">
						<input type="hidden" class="batch-no" value="">
						<input type="hidden" class="warehouse-id" value="">
					</div>
					<div class="ibg-qty">
						<div class="qty-box">
							<label>White <span class="avail-white-badge d-none">(<span class="avail-white-text">0</span>)</span></label>
							<input type="number" min="0" step="1" class="form-control form-control-sm text-center white-qty" value="0" oninput="updateIntimationAllocated(${productIndex})">
							<input type="hidden" class="available-white-qty" value="0">
						</div>
						<div class="qty-box">
							<label>Black <span class="avail-black-badge d-none">(<span class="avail-black-text">0</span>)</span></label>
							<input type="number" min="0" step="1" class="form-control form-control-sm text-center black-qty" value="0" oninput="updateIntimationAllocated(${productIndex})">
							<input type="hidden" class="available-black-qty" value="0">
						</div>
					</div>
					<div class="ibg-action">
						<button type="button" class="btn btn-icon btn-sm btn-outline-danger" title="Remove" onclick="removeIntimationBatchRow(this, ${productIndex})"><i class="fa fa-times"></i></button>
					</div>
				</div>
			</div>
		`;
	}

	function loadBatchOptions($select, productId, onDone) {
		$.ajax({
			type: "POST",
			url: "<?php echo base_url(); ?>inventory/get_intimation_batches",
			data: { product_id: productId },
			success: function(res) {
				if (typeof onDone === 'function') {
					onDone(res);
				} else {
					$select.append(res);
					initIntimationSelect2($select.parent());
				}
			}
		});
	}

	window.addIntimationBatchRow = function(productIndex) {
		if (intimationIsView) return;

		if (typeof intimationBatchCounters[productIndex] === 'undefined') {
			intimationBatchCounters[productIndex] = $('#intimation_batch_body_' + productIndex + ' .intimation-batch-row').length;
		}
		intimationBatchCounters[productIndex]++;

		var $body = $('#intimation_batch_body_' + productIndex);
		$body.append(batchRowTemplate(productIndex));
		toggleEmptyState(productIndex);

		var $select = $body.find('.intimation-batch-row:last .intimation-batch-select');
		var productId = $('.intimation-product[data-index="' + productIndex + '"]').data('product-id');

		loadBatchOptions($select, productId, function(res) {
			$select.append(res);
			initIntimationSelect2($select.parent());
			$select.select2('open');
		});

		updateIntimationAllocated(productIndex);
	};

	window.removeIntimationBatchRow = function(btn, productIndex) {
		$(btn).closest('.intimation-batch-row').remove();
		toggleEmptyState(productIndex);
		updateIntimationAllocated(productIndex);
	};

	window.onIntimationBatchChange = function(el) {
		var $select = $(el);
		var $row = $select.closest('.intimation-batch-row');
		var $option = $select.find('option:selected');
		var value = $select.val() || '';
		var batchType = $option.data('batch-type') || '';
		var batchId = $option.data('batch-id') || '';
		var batchNo = $option.data('batch-no') || '';
		var warehouseId = $option.data('warehouse-id') || '';

		if (!batchType && value) {
			var parts = value.split('|');
			batchType = parts[0] || '';
			batchId = parts[1] || '';
		}

		$row.find('.batch-type').val(batchType);
		$row.find('.batch-id').val(batchId);
		$row.find('.batch-no').val(batchNo);
		$row.find('.warehouse-id').val(warehouseId);

		$row.find('.available-white-qty').val(0);
		$row.find('.available-black-qty').val(0);
		$row.find('.avail-white-text').text('0');
		$row.find('.avail-black-text').text('0');
		$row.find('.avail-white-badge, .avail-black-badge').addClass('d-none');

		if (!value || !batchType) {
			return;
		}

		if (batchType === 'inventory' && batchId) {
			var optWhite = parseInt($option.data('official-qty'), 10);
			var optBlack = parseInt($option.data('black-qty'), 10);
			if (!isNaN(optWhite) || !isNaN(optBlack)) {
				var white = isNaN(optWhite) ? 0 : optWhite;
				var black = isNaN(optBlack) ? 0 : optBlack;
				$row.find('.available-white-qty').val(white);
				$row.find('.available-black-qty').val(black);
				$row.find('.avail-white-text').text(white);
				$row.find('.avail-black-text').text(black);
				$row.find('.avail-white-badge, .avail-black-badge').removeClass('d-none');
			} else {
				$.ajax({
					type: "POST",
					url: "<?php echo base_url(); ?>inventory/get_batch_qty_details",
					data: { batch_id: batchId },
					dataType: "json",
					success: function(res) {
						var white = parseInt(res.official_qty || 0, 10);
						var black = parseInt(res.black_qty || 0, 10);
						$row.find('.available-white-qty').val(white);
						$row.find('.available-black-qty').val(black);
						$row.find('.avail-white-text').text(white);
						$row.find('.avail-black-text').text(black);
						$row.find('.avail-white-badge, .avail-black-badge').removeClass('d-none');
					}
				});
			}
		}
	};

	window.updateIntimationAllocated = function(productIndex) {
		var $card = $('.intimation-product[data-index="' + productIndex + '"]');
		var ordered = parseInt($card.data('ordered-qty'), 10) || 0;
		var total = 0;
		$card.find('.intimation-batch-row').each(function() {
			var white = parseInt($(this).find('.white-qty').val(), 10) || 0;
			var black = parseInt($(this).find('.black-qty').val(), 10) || 0;
			total += (white + black);
		});
		var $info = $('#allocated_info_' + productIndex);
		$info.text(total + ' / ' + ordered);
		$info.removeClass('ok warn over');
		if (total > ordered) {
			$info.addClass('over');
		} else if (total === ordered && ordered > 0) {
			$info.addClass('ok');
		} else if (total > 0) {
			$info.addClass('warn');
		}
	};

	function reloadAllBatchOptions() {
		if (intimationIsView) return;
		$('.intimation-product').each(function() {
			var productIndex = $(this).data('index');
			var productId = $(this).data('product-id');
			$(this).find('.intimation-batch-row').each(function() {
				var $row = $(this);
				var $select = $row.find('.intimation-batch-select');
				var currentVal = $select.val();
				var currentType = $row.find('.batch-type').val();
				var currentId = $row.find('.batch-id').val();
				var currentNo = $row.find('.batch-no').val();
				var currentWh = $row.find('.warehouse-id').val();
				var white = $row.find('.white-qty').val();
				var black = $row.find('.black-qty').val();

				loadBatchOptions($select, productId, function(res) {
					$select.html('<option value="">Select Batch</option>' + res);
					var restoreVal = currentType && currentId ? (currentType + '|' + currentId) : currentVal;
					if (restoreVal && $select.find('option[value="' + restoreVal + '"]').length === 0 && currentType && currentId) {
						var label = currentNo || restoreVal;
						if (currentType === 'po') label += ' (PO Pending)';
						if (currentType === 'loading') label += ' (Loading)';
						$select.append(
							'<option value="' + restoreVal + '" data-batch-type="' + currentType + '" data-batch-id="' + currentId + '" data-batch-no="' + (currentNo || '') + '" data-warehouse-id="' + (currentWh || '') + '">' + label + '</option>'
						);
					}
					if (restoreVal) {
						$select.val(restoreVal).trigger('change');
					}
					$row.find('.white-qty').val(white);
					$row.find('.black-qty').val(black);
					initIntimationSelect2($row);
					updateIntimationAllocated(productIndex);
				});
			});
		});
	}

	window.submitSalesOrderIntimation = function(e) {
		if (e) e.preventDefault();
		if (intimationIsView) return false;

		var batches = [];
		var validationError = '';
		var seen = {};

		$('.intimation-product').each(function() {
			if (validationError) return false;
			var $card = $(this);
			var orderProductId = $card.data('order-product-id');
			var productId = $card.data('product-id');
			var orderedQty = parseInt($card.data('ordered-qty'), 10) || 0;
			var allocated = 0;
			var rows = $card.find('.intimation-batch-row');

			rows.each(function() {
				if (validationError) return false;
				var $row = $(this);
				var batchType = $row.find('.batch-type').val();
				var batchId = parseInt($row.find('.batch-id').val(), 10) || 0;
				var batchNo = $row.find('.batch-no').val() || '';
				var warehouseId = parseInt($row.find('.warehouse-id').val(), 10) || 0;
				var white = parseInt($row.find('.white-qty').val(), 10) || 0;
				var black = parseInt($row.find('.black-qty').val(), 10) || 0;
				var availWhite = parseInt($row.find('.available-white-qty').val(), 10) || 0;
				var availBlack = parseInt($row.find('.available-black-qty').val(), 10) || 0;

				// Skip completely empty rows
				if (!batchType && !batchId && white === 0 && black === 0) {
					return;
				}

				if (!batchType || !batchId) {
					validationError = 'Please select a batch for all filled rows.';
					return false;
				}
				if (white < 0 || black < 0 || (white + black) <= 0) {
					validationError = 'Enter valid white/black quantity for each selected batch.';
					return false;
				}
				if (batchType === 'inventory') {
					if (white > availWhite) {
						validationError = 'White quantity cannot exceed available white (' + availWhite + ').';
						return false;
					}
					if (black > availBlack) {
						validationError = 'Black quantity cannot exceed available black (' + availBlack + ').';
						return false;
					}
				}

				var dupKey = orderProductId + '|' + batchType + '|' + batchId;
				if (seen[dupKey]) {
					validationError = 'Duplicate batch selected for the same product.';
					return false;
				}
				seen[dupKey] = true;
				allocated += (white + black);

				batches.push({
					order_product_id: orderProductId,
					product_id: productId,
					batch_type: batchType,
					batch_id: batchId,
					batch_no: batchNo,
					warehouse_id: warehouseId,
					white_qty: white,
					black_qty: black
				});
			});

			if (!validationError && allocated > orderedQty) {
				validationError = 'Allocated quantity cannot exceed ordered qty (' + orderedQty + ').';
				return false;
			}
		});

		if (validationError) {
			Swal.fire({ title: "Validation Error", text: validationError, icon: "error", customClass: { confirmButton: "btn btn-primary" }, buttonsStyling: false });
			return false;
		}

		var $submitBtn = $('#intimation_submit_btn');
		var originalText = $submitBtn.html();
		$submitBtn.attr('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
		if (typeof $(".loader") !== 'undefined') { $(".loader").show(); }

		$.ajax({
			type: 'POST',
			url: '<?php echo base_url(); ?>inventory/sales_order_intimation_save/' + intimationOrderId,
			data: {
				expected_date: $('#intimation_expected_date').val(),
				intimation_remark: $('#intimation_remark').val(),
				batches: batches
			},
			dataType: 'json',
			success: function(res) {
				if (typeof $(".loader") !== 'undefined') { $(".loader").fadeOut("slow"); }
				if (res.status == '200' || res.status == 200) {
					Swal.fire({
						title: "Success!",
						text: res.message || "Intimation saved successfully",
						icon: "success",
						customClass: { confirmButton: "btn btn-primary" },
						buttonsStyling: false
					}).then(function() {
						$('#large-modal').modal('hide');
						if (typeof dataTable !== 'undefined' && dataTable.ajax) {
							dataTable.ajax.reload(null, false);
						} else if ($.fn.DataTable && $.fn.DataTable.isDataTable('#report-datatable')) {
							$('#report-datatable').DataTable().ajax.reload(null, false);
						} else {
							location.reload();
						}
					});
				} else {
					Swal.fire({
						title: "Error!",
						text: res.message || "Failed to save intimation",
						icon: "error",
						customClass: { confirmButton: "btn btn-primary" },
						buttonsStyling: false
					});
					$submitBtn.html(originalText).attr('disabled', false);
				}
			},
			error: function() {
				if (typeof $(".loader") !== 'undefined') { $(".loader").fadeOut("slow"); }
				Swal.fire({
					title: "Error!",
					text: "An error occurred while saving intimation",
					icon: "error",
					customClass: { confirmButton: "btn btn-primary" },
					buttonsStyling: false
				});
				$submitBtn.html(originalText).attr('disabled', false);
			}
		});

		return false;
	};

	$(document).ready(function() {
		initIntimationSelect2();
		$('.intimation-product').each(function() {
			var idx = $(this).data('index');
			toggleEmptyState(idx);
			updateIntimationAllocated(idx);
		});

		<?php if ($is_edit || $is_view) { ?>
		$('.intimation-batch-row').each(function() {
			var $row = $(this);
			if ($row.find('.batch-type').val() === 'inventory') {
				var batchId = $row.find('.batch-id').val();
				if (batchId) {
					$.ajax({
						type: "POST",
						url: "<?php echo base_url(); ?>inventory/get_batch_qty_details",
						data: { batch_id: batchId },
						dataType: "json",
						success: function(res) {
							var white = parseInt(res.official_qty || 0, 10);
							var black = parseInt(res.black_qty || 0, 10);
							$row.find('.available-white-qty').val(white);
							$row.find('.available-black-qty').val(black);
							$row.find('.avail-white-text').text(white);
							$row.find('.avail-black-text').text(black);
							$row.find('.avail-white-badge, .avail-black-badge').removeClass('d-none');
						}
					});
				}
			}
		});
		<?php if ($is_edit) { ?>
		reloadAllBatchOptions();
		<?php } ?>
		<?php } ?>
	});
})();
</script>
