<?php
  $po_id = $param2;

  $po_data = $this->db->query("SELECT * FROM purchase_order WHERE id = '$po_id'")->row_array();

  if (empty($po_data)) {
    echo '<div class="alert alert-danger">Purchase Order not found.</div>';
    return;
  }

  $warehouse = $this->inventory_model->get_warehouse_by_id($po_data['warehouse_id'])->row_array();
  $inr_rate = (float)($po_data['inr_rate'] ?? 0);

  $products_raw = $this->db->query("
      SELECT
          pop.*,
          s.name AS supplier_name
      FROM purchase_in_product pop
      LEFT JOIN supplier s ON s.id = pop.supplier_id
      WHERE pop.parent_id = '$po_id' AND pop.loading_qty > 0 AND pop.is_deleted = 0
      ORDER BY pop.id ASC
  ")->result_array();

  $supplier_products = [];
  foreach ($products_raw as $product) {
      $supplier_id = $product['supplier_id'] ?? 0;
      if (!isset($supplier_products[$supplier_id])) {
          $supplier_products[$supplier_id] = [
              'supplier_name' => $product['supplier_name'] ?? 'Unknown Supplier',
              'products' => [],
              'invoice' => '',
              'invoice_date' => '',
              'currency_type' => !empty($product['currency_type']) ? $product['currency_type'] : 'usd',
              'rmb_usd_con_rate' => (float)($product['rmb_usd_con_rate'] ?? 0),
              'inr_con_rate' => (float)($product['inr_con_rate'] ?? 0),
          ];
      }
      $supplier_products[$supplier_id]['products'][] = $product;

      if (!empty($product['invoice']) && isset($product['invoice_supplier_id']) && $product['invoice_supplier_id'] == $supplier_id) {
          $supplier_products[$supplier_id]['invoice'] = $product['invoice'];
          $supplier_products[$supplier_id]['invoice_date'] = $product['invoice_date'];
      }
  }

  $g_actual_qty        = 0;
  $g_total_rmb         = 0;
  $g_total_usd         = 0;
  $g_total_inr         = 0;
  $g_official_qty      = 0;
  $g_official_total_rs = 0;
  $g_duty_amt          = 0;
  $g_duty_surcharge    = 0;
  $g_taxable_value     = 0;
  $g_gst_amt           = 0;
  $g_total_amt         = 0;
?>

<style>
  .po-meta-container {
    background-color: #f8f9fa;
    padding: 8px 12px;
    border-radius: 4px;
    margin-bottom: 12px;
    border: 1px solid #dee2e6;
  }

  .po-meta-item {
    display: inline-flex;
    align-items: center;
    margin-right: 20px;
    margin-bottom: 4px;
    font-size: 11px;
    color: #495057;
  }

  .po-meta-item strong {
    color: #212529;
    margin-right: 4px;
  }

  .supplier-header {
    font-weight: bold;
    font-size: 13px;
    color: #5a79c0;
    margin-top: 12px;
    margin-bottom: 4px;
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 2px;
  }

  .supplier-rate-meta {
    font-size: 11px;
    color: #495057;
    margin-bottom: 6px;
  }

  .supplier-rate-meta span {
    margin-right: 16px;
  }

  .table-responsive {
    max-height: 450px;
    overflow-x: auto;
    overflow-y: auto;
    border: 1px solid #dee2e6;
    border-radius: 4px;
  }

  .compact-table {
    min-width: 2400px;
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 0;
    font-size: 11px;
  }

  .compact-table th {
    position: sticky;
    top: 0;
    z-index: 10;
    background-color: #f1f3f5;
    color: #495057;
    padding: 4px 6px;
    font-weight: 600;
    border: 1px solid #dee2e6;
    text-align: center;
    font-size: 11px;
  }

  .compact-table td {
    padding: 3px 6px;
    border: 1px solid #dee2e6;
    vertical-align: middle;
    font-size: 11px;
  }

  .compact-table tbody tr:nth-child(even) {
    background-color: #f8fafc;
  }

  .compact-table .text-right {
    text-align: right;
  }

  .compact-table .text-center {
    text-align: center;
  }

  .totals-row-bg {
    background-color: #f1f3f5 !important;
    font-weight: bold;
  }

  .table-warning-row {
    background-color: #fff3cd !important;
  }

  .grand-totals-bar {
    background-color: #f8f9fa;
    border-top: 2px solid #5a79c0;
    margin-top: 15px;
    padding-top: 10px;
  }

  .grand-totals-bar .totals-grid {
    display: grid;
    grid-template-columns: repeat(8, 1fr);
    text-align: center;
    font-size: 11px;
    gap: 10px;
  }

  .grand-totals-bar .total-block {
    border-right: 1px solid #dee2e6;
    padding: 2px 5px;
  }

  .grand-totals-bar .total-block:last-child {
    border-right: none;
  }

  .grand-totals-bar .total-title {
    color: #6c757d;
    font-size: 9px;
    text-transform: uppercase;
    font-weight: 600;
    margin-bottom: 2px;
  }

  .grand-totals-bar .total-value {
    font-size: 13px;
    font-weight: bold;
    color: #5a79c0;
  }
</style>

<div class="row">
  <div class="col-12">

    <div class="po-meta-container">
      <div class="d-flex flex-wrap">
        <div class="po-meta-item"><strong>Batch No:</strong> <?php echo $po_data['voucher_no']; ?></div>
        <div class="po-meta-item"><strong>Order Date:</strong> <?php echo date('d M, Y', strtotime($po_data['date'])); ?></div>
        <div class="po-meta-item"><strong>BOE No:</strong> <?php echo !empty($po_data['boe_no']) ? $po_data['boe_no'] : 'N/A'; ?></div>
        <div class="po-meta-item"><strong>BOE Date:</strong> <?php echo (!empty($po_data['boe_date']) && $po_data['boe_date'] !== '0000-00-00 00:00:00' && $po_data['boe_date'] !== '0000-00-00') ? date('d M, Y', strtotime($po_data['boe_date'])) : 'N/A'; ?></div>
        <div class="po-meta-item"><strong>INR Rate:</strong> <?php echo number_format($inr_rate, 2); ?></div>
        <div class="po-meta-item"><strong>Warehouse:</strong> <?php echo $warehouse['name'] ?? 'N/A'; ?></div>
        <?php if (!empty($po_data['mode_of_payment'])) { ?>
          <div class="po-meta-item"><strong>Payment Mode/Terms:</strong> <?php echo $po_data['mode_of_payment']; ?></div>
        <?php } ?>
        <div class="po-meta-item w-100 mt-1"><strong>Delivery Address:</strong> <?php echo $po_data['delivery_address'] ?? 'N/A'; ?></div>
      </div>
    </div>

    <?php if (!empty($supplier_products)): ?>
        <?php foreach ($supplier_products as $supplier_id => $supplier_data): ?>
            <?php
            $sup_currency_type = $supplier_data['currency_type'];
            $sup_con_rate_label = ($sup_currency_type === 'rmb') ? 'USD Rate' : 'RMB Rate';
            $shown_replace_products = [];
            ?>
            <div>
                <div class="supplier-header">
                    Supplier: <?php echo html_entity_decode($supplier_data['supplier_name']); ?>
                    <?php if (!empty($supplier_data['invoice'])): ?>
                        <span class="badge badge-soft-primary ml-2" style="font-size: 0.75rem; background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe; font-weight: 500;">
                            <i class="fa fa-file-invoice mr-1"></i> Inv: <?php echo htmlspecialchars($supplier_data['invoice']); ?>
                        </span>
                        <span class="badge badge-soft-secondary ml-1" style="font-size: 0.75rem; background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; font-weight: 500;">
                            <i class="fa fa-calendar-alt mr-1"></i> <?php echo !empty($supplier_data['invoice_date']) ? date('d-M-Y', strtotime($supplier_data['invoice_date'])) : '-'; ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="supplier-rate-meta">
                    <span><strong>Currency:</strong> <?php echo ($sup_currency_type === 'rmb') ? 'RMB to USD' : 'USD to RMB'; ?></span>
                    <span><strong><?php echo $sup_con_rate_label; ?>:</strong> <?php echo number_format($supplier_data['rmb_usd_con_rate'], 5); ?></span>
                    <span><strong>INR Rate:</strong> <?php echo number_format($supplier_data['inr_con_rate'], 5); ?></span>
                </div>
                <div class="table-responsive">
                    <table class="compact-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">Sr No.</th>
                                <th style="width: 220px;">Product Name</th>
                                <th style="width: 130px;">Model No.</th>
                                <th style="width: 80px;">Actual Qty</th>
                                <th style="width: 90px;">Received Qty</th>
                                <th style="width: 100px;">Qty to Receive</th>
                                <th style="width: 90px;">Actual RMB</th>
                                <th style="width: 90px;">Total RMB</th>
                                <th style="width: 90px;">Actual USD</th>
                                <th style="width: 90px;">Total USD</th>
                                <th style="width: 90px;">Actual INR</th>
                                <th style="width: 90px;">Total INR</th>
                                <th style="width: 90px;">Official Qty</th>
                                <th style="width: 110px;">Official Rate USD</th>
                                <th style="width: 110px;">Official Rate Rs.</th>
                                <th style="width: 110px;">Official Total Rs.</th>
                                <th style="width: 70px;">Duty %</th>
                                <th style="width: 90px;">Duty Amt</th>
                                <th style="width: 110px;">Duty Surcharge 10%</th>
                                <th style="width: 100px;">Taxable Value</th>
                                <th style="width: 90px;">GST Amt</th>
                                <th style="width: 110px;">Total Duty/GST</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sr_no = 1;
                            $t_actual_qty        = 0;
                            $t_total_rmb         = 0;
                            $t_total_usd         = 0;
                            $t_total_inr         = 0;
                            $t_official_qty      = 0;
                            $t_official_total_rs = 0;
                            $t_duty_amt          = 0;
                            $t_duty_surcharge    = 0;
                            $t_taxable_value     = 0;
                            $t_gst_amt           = 0;
                            $t_total_amt         = 0;

                            foreach ($supplier_data['products'] as $product):
                                $actual_qty = (float)($product['actual_qty'] ?? 0);
                                if ($actual_qty <= 0) {
                                    continue;
                                }

                                $actual_rmb = (float)($product['actual_rmb'] ?? 0);
                                $actual_usd = (float)($product['actual_usd'] ?? 0);
                                $actual_inr = (float)($product['actual_inr'] ?? 0);
                                $total_rmb  = (float)($product['total_rmb'] ?? ($actual_qty * $actual_rmb));
                                $total_usd  = $actual_qty * $actual_usd;
                                $total_inr  = $actual_qty * $actual_inr;

                                $official_qty = (float)($product['official_ci_qty'] ?? 0);
                                $official_rate_usd = (float)($product['official_ci_unit_price_usd'] ?? 0);
                                $official_rate_rs = (float)($product['official_rate_rs'] ?? 0);
                                if ($official_rate_rs == 0 && $official_qty > 0) {
                                    $official_rate_rs = $official_rate_usd * $inr_rate;
                                }
                                $official_total_rs = (float)($product['official_total_rs'] ?? 0);
                                if ($official_total_rs == 0) {
                                    $official_total_rs = $official_qty * $official_rate_rs;
                                }

                                $duty_percent = (float)($product['duty_percent'] ?? 0);
                                $duty_amt = (float)($product['duty_amt'] ?? 0);
                                $duty_surcharge = (float)($product['duty_surcharge'] ?? 0);
                                $taxable_value = (float)($product['taxable_value'] ?? 0);
                                $gst_amt = (float)($product['gst_amt'] ?? 0);
                                $total_amt = (float)($product['total_amt'] ?? 0);

                                $is_row_replace = (isset($product['is_replace']) && $product['is_replace'] == 1);
                                $show_rp = false;
                                $rep_total = 0;
                                $rep_recv = 0;
                                $pid = $product['product_id'];
                                if ($is_row_replace && !in_array($pid, $shown_replace_products)) {
                                    $show_rp = true;
                                    $rep_total = (int)($product['receivable_qty'] ?? 0);
                                    $rep_recv = (int)($product['received_qty'] ?? 0);
                                    $shown_replace_products[] = $pid;
                                }

                                $t_actual_qty        += $actual_qty;
                                $t_total_rmb         += $total_rmb;
                                $t_total_usd         += $total_usd;
                                $t_total_inr         += $total_inr;
                                $t_official_qty      += $official_qty;
                                $t_official_total_rs += $official_total_rs;
                                $t_duty_amt          += $duty_amt;
                                $t_duty_surcharge    += $duty_surcharge;
                                $t_taxable_value     += $taxable_value;
                                $t_gst_amt           += $gst_amt;
                                $t_total_amt         += $total_amt;

                                $g_actual_qty        += $actual_qty;
                                $g_total_rmb         += $total_rmb;
                                $g_total_usd         += $total_usd;
                                $g_total_inr         += $total_inr;
                                $g_official_qty      += $official_qty;
                                $g_official_total_rs += $official_total_rs;
                                $g_duty_amt          += $duty_amt;
                                $g_duty_surcharge    += $duty_surcharge;
                                $g_taxable_value     += $taxable_value;
                                $g_gst_amt           += $gst_amt;
                                $g_total_amt         += $total_amt;
                            ?>
                            <tr class="<?php echo $is_row_replace ? 'table-warning-row' : ''; ?>">
                                <td class="text-center"><?php echo $sr_no++; ?></td>
                                <td><?php echo html_entity_decode($product['product_name'] ?? 'N/A'); ?></td>
                                <td><?php echo html_entity_decode($product['item_code'] ?? 'N/A'); ?></td>
                                <td class="text-center"><?php echo number_format($actual_qty, 0); ?></td>
                                <td class="text-center"><?php echo $show_rp ? number_format($rep_recv, 0) : '-'; ?></td>
                                <td class="text-center"><?php echo $show_rp ? number_format($rep_total, 0) : '-'; ?></td>
                                <td class="text-right"><?php echo number_format($actual_rmb, 2); ?></td>
                                <td class="text-right"><?php echo number_format($total_rmb, 2); ?></td>
                                <td class="text-right"><?php echo number_format($actual_usd, 5); ?></td>
                                <td class="text-right"><?php echo number_format($total_usd, 5); ?></td>
                                <td class="text-right"><?php echo number_format($actual_inr, 2); ?></td>
                                <td class="text-right"><?php echo number_format($total_inr, 2); ?></td>
                                <td class="text-center"><?php echo number_format($official_qty, 0); ?></td>
                                <td class="text-right"><?php echo number_format($official_rate_usd, 5); ?></td>
                                <td class="text-right"><?php echo number_format($official_rate_rs, 2); ?></td>
                                <td class="text-right"><?php echo number_format($official_total_rs, 2); ?></td>
                                <td class="text-center"><?php echo number_format($duty_percent, 1); ?></td>
                                <td class="text-right"><?php echo number_format($duty_amt, 2); ?></td>
                                <td class="text-right"><?php echo number_format($duty_surcharge, 2); ?></td>
                                <td class="text-right"><?php echo number_format($taxable_value, 2); ?></td>
                                <td class="text-right"><?php echo number_format($gst_amt, 2); ?></td>
                                <td class="text-right"><?php echo number_format($total_amt, 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="totals-row-bg">
                                <td colspan="3" class="text-right">Total:</td>
                                <td class="text-center"><?php echo number_format($t_actual_qty, 0); ?></td>
                                <td class="text-center">-</td>
                                <td class="text-center">-</td>
                                <td class="text-right">-</td>
                                <td class="text-right"><?php echo number_format($t_total_rmb, 2); ?></td>
                                <td class="text-right">-</td>
                                <td class="text-right"><?php echo number_format($t_total_usd, 5); ?></td>
                                <td class="text-right">-</td>
                                <td class="text-right"><?php echo number_format($t_total_inr, 2); ?></td>
                                <td class="text-center"><?php echo number_format($t_official_qty, 0); ?></td>
                                <td class="text-right">-</td>
                                <td class="text-right">-</td>
                                <td class="text-right"><?php echo number_format($t_official_total_rs, 2); ?></td>
                                <td class="text-center">-</td>
                                <td class="text-right"><?php echo number_format($t_duty_amt, 2); ?></td>
                                <td class="text-right"><?php echo number_format($t_duty_surcharge, 2); ?></td>
                                <td class="text-right"><?php echo number_format($t_taxable_value, 2); ?></td>
                                <td class="text-right"><?php echo number_format($t_gst_amt, 2); ?></td>
                                <td class="text-right"><?php echo number_format($t_total_amt, 2); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="grand-totals-bar">
            <div class="totals-grid">
                <div class="total-block">
                    <div class="total-title">Actual Qty</div>
                    <div class="total-value"><?php echo number_format($g_actual_qty, 0); ?></div>
                </div>
                <div class="total-block">
                    <div class="total-title">Total RMB</div>
                    <div class="total-value"><?php echo number_format($g_total_rmb, 2); ?></div>
                </div>
                <div class="total-block">
                    <div class="total-title">Total USD</div>
                    <div class="total-value"><?php echo number_format($g_total_usd, 5); ?></div>
                </div>
                <div class="total-block">
                    <div class="total-title">Total INR</div>
                    <div class="total-value"><?php echo number_format($g_total_inr, 2); ?></div>
                </div>
                <div class="total-block">
                    <div class="total-title">Official Qty</div>
                    <div class="total-value"><?php echo number_format($g_official_qty, 0); ?></div>
                </div>
                <div class="total-block">
                    <div class="total-title">Official Total Rs.</div>
                    <div class="total-value"><?php echo number_format($g_official_total_rs, 2); ?></div>
                </div>
                <div class="total-block">
                    <div class="total-title">Duty Amt</div>
                    <div class="total-value"><?php echo number_format($g_duty_amt, 2); ?></div>
                </div>
                <div class="total-block">
                    <div class="total-title">Total Duty/GST</div>
                    <div class="total-value"><?php echo number_format($g_total_amt, 2); ?></div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No purchase in data found for this Purchase Order.</div>
    <?php endif; ?>

  </div>
</div>
