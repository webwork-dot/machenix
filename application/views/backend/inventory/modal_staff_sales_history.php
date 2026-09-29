<?php
$staff_id = (int) $param2;
$staff = $this->db->get_where('sys_users', ['id' => $staff_id, 'is_deleted' => '0'])->row_array();

if (empty($staff)) {
	echo '<div class="alert alert-danger mb-0">Staff member not found.</div>';
	return;
}

$staff_name = trim(($staff['first_name'] ?? '') . ' ' . ($staff['last_name'] ?? ''));
$access_name = '-';
if (!empty($staff['staff_access'])) {
	$access = $this->db->get_where('access', ['id' => $staff['staff_access']])->row_array();
	$access_name = $access['name'] ?? '-';
}

$logs = $this->db->query("
	SELECT
		sl.id,
		sl.parent_id,
		sl.ref_id,
		sl.module,
		sl.action,
		sl.message,
		sl.json,
		sl.table_name,
		sl.added_by_name,
		sl.added_by_type,
		sl.created_at,
		so.order_no,
		so.refrence_no,
		so.date AS order_date,
		so.customer_name,
		so.warehouse_name,
		so.grand_total,
		so.expected_date
	FROM sys_logs AS sl
	INNER JOIN sales_order AS so ON so.id = sl.parent_id
	WHERE sl.added_by = ?
		AND (sl.module = 'sales' OR sl.table_name = 'sales_order')
		AND (so.is_deleted = '0' OR so.is_deleted IS NULL)
	ORDER BY sl.created_at DESC, sl.id DESC
", [$staff_id])->result_array();

$field_labels = [
	'order_no' => 'Order No',
	'refrence_no' => 'Reference No',
	'date' => 'Order Date',
	'expected_date' => 'Delivery Date',
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
	'other_charges_name' => 'Other Charges Name',
	'other_charges_amount' => 'Other Charges Amount',
	'shipping_address' => 'Shipping Address',
	'billing_address' => 'Billing Address',
];

$total_orders = [];
$total_amount = 0;
foreach ($logs as $row) {
	$oid = (int) ($row['parent_id'] ?? 0);
	if ($oid > 0 && !isset($total_orders[$oid])) {
		$total_orders[$oid] = true;
		$total_amount += (float) ($row['grand_total'] ?? 0);
	}
}
?>

<style>
	.ssh-wrap { font-family: inherit; color: #334155; }
	.ssh-hero {
		display: flex;
		align-items: center;
		gap: 12px;
		padding: 10px 12px;
		margin-bottom: 12px;
		background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
		border: 1px solid #e2e8f0;
		border-radius: 12px;
	}
	.ssh-thumb {
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
		overflow: hidden;
	}
	.ssh-thumb img { width: 100%; height: 100%; object-fit: cover; }
	.ssh-name { font-size: 14px; font-weight: 700; color: #0f172a; line-height: 1.3; margin-bottom: 4px; }
	.ssh-chips { display: flex; flex-wrap: wrap; gap: 6px; }
	.ssh-chip {
		font-size: 11px;
		color: #475569;
		background: #fff;
		border: 1px solid #e2e8f0;
		border-radius: 999px;
		padding: 2px 8px;
		line-height: 1.5;
	}
	.ssh-chip strong { color: #1e293b; font-weight: 600; }
	.ssh-scroll { max-height: 520px; overflow-y: auto; padding-right: 4px; }
	.ssh-scroll::-webkit-scrollbar { width: 6px; }
	.ssh-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
	.ssh-timeline { position: relative; padding-left: 18px; }
	.ssh-timeline:before {
		content: "";
		position: absolute;
		left: 5px;
		top: 6px;
		bottom: 6px;
		width: 2px;
		background: #e2e8f0;
	}
	.ssh-item { position: relative; margin-bottom: 12px; }
	.ssh-item:last-child { margin-bottom: 0; }
	.ssh-dot {
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
	.ssh-item.ssh-add .ssh-dot { background: #16a34a; box-shadow: 0 0 0 2px #bbf7d0; }
	.ssh-item.ssh-update .ssh-dot { background: #d97706; box-shadow: 0 0 0 2px #fde68a; }
	.ssh-item.ssh-delete .ssh-dot { background: #dc2626; box-shadow: 0 0 0 2px #fecaca; }
	.ssh-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 12px; }
	.ssh-head { display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 4px; flex-wrap: wrap; }
	.ssh-badge {
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
	.ssh-item.ssh-add .ssh-badge { background: #dcfce7; color: #166534; }
	.ssh-item.ssh-update .ssh-badge { background: #fef3c7; color: #92400e; }
	.ssh-item.ssh-delete .ssh-badge { background: #fee2e2; color: #991b1b; }
	.ssh-time { font-size: 11px; color: #94a3b8; white-space: nowrap; }
	.ssh-order {
		font-size: 12px;
		font-weight: 700;
		color: #0f172a;
		margin-bottom: 4px;
	}
	.ssh-order span { font-weight: 500; color: #64748b; }
	.ssh-user { font-size: 12px; color: #64748b; margin-bottom: 6px; }
	.ssh-user strong { color: #0f172a; }
	.ssh-note {
		font-size: 12px;
		color: #334155;
		background: #f8fafc;
		border-left: 3px solid #6366f1;
		border-radius: 6px;
		padding: 6px 8px;
		margin-bottom: 6px;
	}
	.ssh-changes {
		margin-top: 6px;
		background: #f8fafc;
		border: 1px solid #eef2f7;
		border-radius: 8px;
		padding: 6px 8px;
	}
	.ssh-row {
		display: flex;
		justify-content: space-between;
		gap: 10px;
		padding: 6px 0;
		border-bottom: 1px solid #eef2f7;
		font-size: 12px;
	}
	.ssh-row:last-child { border-bottom: none; }
	.ssh-field { color: #475569; font-weight: 600; min-width: 110px; }
	.ssh-vals { text-align: right; word-break: break-word; }
	.ssh-old { color: #ef4444; text-decoration: line-through; margin-right: 4px; }
	.ssh-new { color: #059669; font-weight: 700; }
	.ssh-sub {
		font-size: 10px;
		text-transform: uppercase;
		font-weight: 700;
		color: #94a3b8;
		letter-spacing: .4px;
		margin: 8px 0 4px;
	}
	.ssh-table { width: 100%; font-size: 11px; border-collapse: collapse; }
	.ssh-table th {
		background: #f1f5f9;
		color: #475569;
		padding: 5px 6px;
		font-weight: 600;
		text-align: left;
	}
	.ssh-table td { padding: 5px 6px; border-top: 1px solid #eef2f7; color: #334155; vertical-align: top; }
	.ssh-empty { text-align: center; padding: 28px 12px; color: #94a3b8; }
	.ssh-empty i { font-size: 28px; opacity: .45; margin-bottom: 8px; display: block; }
</style>

<div class="ssh-wrap">
	<div class="ssh-hero">
		<div class="ssh-thumb">
			<?php if (!empty($staff['profile_img'])): ?>
				<img src="<?php echo base_url($staff['profile_img']); ?>" alt="Staff">
			<?php else: ?>
				<i class="fa fa-user"></i>
			<?php endif; ?>
		</div>
		<div>
			<div class="ssh-name"><?php echo htmlspecialchars($staff_name !== '' ? $staff_name : 'Staff'); ?></div>
			<div class="ssh-chips">
				<span class="ssh-chip"><strong>Role</strong> <?php echo htmlspecialchars($access_name); ?></span>
				<span class="ssh-chip"><strong>Orders</strong> <?php echo count($total_orders); ?></span>
				<span class="ssh-chip"><strong>History</strong> <?php echo count($logs); ?></span>
				<span class="ssh-chip"><strong>Total</strong> ₹<?php echo number_format($total_amount, 2); ?></span>
			</div>
		</div>
	</div>

	<?php if (empty($logs)): ?>
		<div class="ssh-empty">
			<i class="fa fa-history"></i>
			<div>No sales history found for this staff member.</div>
		</div>
	<?php else: ?>
		<div class="ssh-scroll">
			<div class="ssh-timeline">
				<?php foreach ($logs as $log):
					$action = strtolower(trim($log['action'] ?? ''));
					$data_payload = json_decode($log['json'] ?? '', true);
					$item_class = '';
					$badge_icon = 'fa-info-circle';
					$badge_label = ucfirst($action ?: 'Log');

					if ($action == 'add' || $action == 'add_salesman_preapproved' || $action == 'add_company_sales') {
						$item_class = 'ssh-add';
						$badge_icon = 'fa-plus';
						$badge_label = $action == 'add_salesman_preapproved' ? 'Pre-Approved' : ($action == 'add_company_sales' ? 'Company Sale' : 'Created');
					} elseif ($action == 'add_conversion') {
						$badge_icon = 'fa-random';
						$badge_label = 'Conversion';
					} elseif ($action == 'approve') {
						$item_class = 'ssh-add';
						$badge_icon = 'fa-check';
						$badge_label = 'Approved';
					} elseif ($action == 'edit' || $action == 'update' || $action == 'edit_conversion') {
						$item_class = 'ssh-update';
						$badge_icon = 'fa-pencil';
						$badge_label = 'Updated';
					} elseif ($action == 'delete' || $action == 'cancel') {
						$item_class = 'ssh-delete';
						$badge_icon = 'fa-trash';
						$badge_label = 'Cancelled';
					}

					$created_time_str = !empty($log['created_at'])
						? (function_exists('formatHistoryTime') ? formatHistoryTime($log['created_at']) : date('d M Y, h:i A', strtotime($log['created_at'])))
						: '';

					$order_no = $log['order_no'] ?? ('#' . ($log['parent_id'] ?? ''));
					$customer_name = $log['customer_name'] ?? '-';
					$order_total = isset($log['grand_total']) ? (float) $log['grand_total'] : null;
				?>
					<div class="ssh-item <?php echo $item_class; ?>">
						<span class="ssh-dot"></span>
						<div class="ssh-card">
							<div class="ssh-head">
								<span class="ssh-badge"><i class="fa <?php echo $badge_icon; ?>"></i> <?php echo htmlspecialchars($badge_label); ?></span>
								<span class="ssh-time"><?php echo $created_time_str; ?></span>
							</div>

							<div class="ssh-order">
								Order #<?php echo htmlspecialchars($order_no); ?>
								<?php if (!empty($log['refrence_no'])): ?>
									<span>(<?php echo htmlspecialchars($log['refrence_no']); ?>)</span>
								<?php endif; ?>
							</div>

							<div class="ssh-chips" style="margin-bottom:6px;">
								<span class="ssh-chip"><strong>Customer</strong> <?php echo htmlspecialchars($customer_name); ?></span>
								<?php if (!empty($log['order_date']) && $log['order_date'] != '0000-00-00'): ?>
									<span class="ssh-chip"><?php echo date('d M Y', strtotime($log['order_date'])); ?></span>
								<?php endif; ?>
								<?php if ($order_total !== null): ?>
									<span class="ssh-chip"><strong>Total</strong> ₹<?php echo number_format($order_total, 2); ?></span>
								<?php endif; ?>
								<?php if (!empty($log['warehouse_name'])): ?>
									<span class="ssh-chip"><?php echo htmlspecialchars($log['warehouse_name']); ?></span>
								<?php endif; ?>
							</div>

							<div class="ssh-user">
								by <strong><?php echo htmlspecialchars($log['added_by_name'] ?? 'System'); ?></strong>
								<?php if (!empty($log['added_by_type'])): ?>
									<span>(<?php echo htmlspecialchars($log['added_by_type']); ?>)</span>
								<?php endif; ?>
							</div>

							<?php if (!empty($log['message'])): ?>
								<div class="ssh-note"><?php echo htmlspecialchars($log['message']); ?></div>
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
										<div class="ssh-changes">
											<?php foreach ($diffs as $field_key => $diff):
												$label = $field_labels[$field_key] ?? ucwords(str_replace('_', ' ', $field_key));
												$is_price = in_array($field_key, ['grand_total', 'basic_value', 'gst_total', 'total_black_amt', 'net_sales_value_1', 'net_sales_value_2', 'other_charges_amount']);
												$old_display = $is_price && is_numeric($diff['old']) ? '₹' . number_format((float)$diff['old'], 2) : strval($diff['old'] ?? '—');
												$new_display = $is_price && is_numeric($diff['new']) ? '₹' . number_format((float)$diff['new'], 2) : strval($diff['new'] ?? '—');
											?>
												<div class="ssh-row">
													<span class="ssh-field"><?php echo htmlspecialchars($label); ?></span>
													<span class="ssh-vals">
														<span class="ssh-old"><?php echo htmlspecialchars($old_display); ?></span>
														<span class="ssh-new"><?php echo htmlspecialchars($new_display); ?></span>
													</span>
												</div>
											<?php endforeach; ?>
											<?php if (!empty($new_prods)): ?>
												<div class="ssh-sub">Products (<?php echo count($new_prods); ?>)</div>
												<div class="table-responsive">
													<table class="ssh-table">
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
																	<td class="text-center"><?php echo $pqty; ?></td>
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
									<div class="ssh-changes">
										<div class="ssh-chips" style="margin-bottom:6px;">
											<?php if (!empty($so_data['grand_total'])): ?>
												<span class="ssh-chip">Total <strong>₹<?php echo number_format((float)$so_data['grand_total'], 2); ?></strong></span>
											<?php endif; ?>
											<?php if (!empty($so_data['warehouse_name'])): ?>
												<span class="ssh-chip"><?php echo htmlspecialchars($so_data['warehouse_name']); ?></span>
											<?php endif; ?>
											<?php if (!empty($so_data['gst_type'])): ?>
												<span class="ssh-chip">GST <?php echo htmlspecialchars($so_data['gst_type']); ?></span>
											<?php endif; ?>
										</div>
										<?php if (!empty($so_prods)): ?>
											<div class="ssh-sub">Products (<?php echo count($so_prods); ?>)</div>
											<div class="table-responsive">
												<table class="ssh-table">
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
											<div class="ssh-sub">Other charges</div>
											<?php foreach ($so_charges as $charge): ?>
												<div class="ssh-note" style="margin-bottom:4px;">
													<?php echo htmlspecialchars($charge['type'] ?? 'Charge'); ?>:
													₹<?php echo number_format((float)($charge['total_amt'] ?? $charge['amount'] ?? 0), 2); ?>
												</div>
											<?php endforeach; ?>
										<?php endif; ?>
									</div>
								<?php elseif ($action == 'delete' || $action == 'cancel'): ?>
									<div class="ssh-note">Order was cancelled and allocated stock was reverted.</div>
								<?php endif; ?>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>
</div>
