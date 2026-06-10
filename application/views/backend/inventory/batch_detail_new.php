<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body py-1 my-0">
        <div class="row">
          <div class="col-12 col-sm-4 mb-1">
            <div class="form-group">
              <label>Batch No <span class="required">*</span></label>
              <select class="form-control select2" name="batch_no" id="batch_no" required>
                <option value="">Select</option>
                <?php foreach ($po as $key => $value): ?>
                  <option value="<?php echo $value['voucher_no']; ?>"><?php echo $value['voucher_no']; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="col-12 col-sm-4 mb-1 d-flex align-items-end">
            <button type="button" class="btn btn-primary me-1 mb-1" id="view-batch-btn">
              <?php echo get_phrase('View'); ?>
            </button>
          </div>
        </div>

        <div id="batch-detail-wrap" class="d-none">
          <div class="row mt-2">
            <div class="col-12 col-sm-4 mb-1">
              <label class="mb-25">BOE No</label>
              <input type="text" class="form-control form-control-sm" id="view-boe-no" readonly>
            </div>
            <div class="col-12 col-sm-4 mb-1">
              <label class="mb-25">BOE Date</label>
              <input type="text" class="form-control form-control-sm" id="view-boe-date" readonly>
            </div>
            <div class="col-12 col-sm-4 mb-1">
              <label class="mb-25">Received Date</label>
              <input type="text" class="form-control form-control-sm" id="view-received-date" readonly>
            </div>
            <div class="col-12 col-sm-4 mb-1">
              <label class="mb-25">Loading Date</label>
              <input type="text" class="form-control form-control-sm" id="view-loading-date" readonly>
            </div>
            <div class="col-12 col-sm-4 mb-1">
              <label class="mb-25">PO Date</label>
              <input type="text" class="form-control form-control-sm" id="view-po-date" readonly>
            </div>
          </div>

          <div class="mt-2" id="supplier-table-wrap"></div>

        </div>
      </div>
    </div>
  </div>
</div>

<script>
  function escapeHtml(str) {
    return String(str ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function toNum(v) {
    const n = parseFloat(v);
    return isNaN(n) ? 0 : n;
  }

  function fmt(v, d = 2) {
    return toNum(v).toFixed(d);
  }

  function getTotalExpense(expenses) {
    if (!Array.isArray(expenses) || expenses.length === 0) return 0;
    return expenses.reduce((sum, e) => sum + toNum(e?.sub_total), 0);
  }

  function setHeaderFields(header) {
    $('#view-boe-no').val(header?.boe_no || '');
    $('#view-boe-date').val(header?.boe_date || '');
    $('#view-received-date').val(header?.received_date || '');
    $('#view-loading-date').val(header?.loading_date || '');
    $('#view-po-date').val(header?.po_date || '');
  }

  function renderSupplierTables(suppliers, grandTotals, expenses, expenseItems, supplierAccounts) {
    const $wrap = $('#supplier-table-wrap');
    $wrap.empty();

    if (!Array.isArray(suppliers) || suppliers.length === 0) {
      $wrap.html('<div class="alert alert-info mb-0">No product line items found for this batch.</div>');
      return;
    }

    const totalExpense = getTotalExpense(expenses);
    
    suppliers.forEach((supplier, supplierIdx) => {
      const products = Array.isArray(supplier.products) ? supplier.products : [];
      const totals = supplier.totals || {};

      const supplierExpense = expenses.find(e => e.supplier_id == supplier.supplier_id)?.sub_total || 0;
      const sAvgExpPerActCbm = toNum(totals.actual_cbm_total) > 0 ? (toNum(supplierExpense) / toNum(totals.actual_cbm_total)) : 0;
      const sAvgExpPerOffCbm = toNum(totals.off_cbm_total) > 0 ? (toNum(supplierExpense) / toNum(totals.off_cbm_total)) : 0;

      let rowsHtml = '';
      products.forEach((p, i) => {
        rowsHtml += `
          <tr>
            <td class="text-center">${i + 1}</td>
            <td>${escapeHtml(p.product_name)}</td>
            <td>${escapeHtml(p.model_no)}</td>
            <td>${escapeHtml(p.hsn_code)}</td>
            <td class="text-end">${fmt(p.official_qty, 0)}</td>
            <td class="text-end">${fmt(p.black_qty, 0)}</td>
            <td class="text-end">${fmt(p.act_qty, 0)}</td>
            <td class="text-end">${fmt(p.cbm_per_pc, 5)}</td>
            <td class="text-end">${fmt(p.off_cbm_total, 5)}</td>
            <td class="text-end">${fmt(p.actual_cbm_total, 5)}</td>
            <td class="text-end">${fmt(toNum(p.act_qty) > 0 ? (toNum(p.total_rs_without_expense) + toNum(p.off_duty_amt) + toNum(p.off_surcharge) + (sAvgExpPerActCbm * toNum(p.actual_cbm_total))) / toNum(p.act_qty) : 0, 2)}</td>
            <td class="text-end">${fmt(p.rmb_per_pc, 2)}</td>
            <td class="text-end">${fmt(p.rmb_total, 2)}</td>
            <td class="text-end">${fmt(p.usd_per_pc, 2)}</td>
            <td class="text-end">${fmt(p.usd_total, 2)}</td>
            <td class="text-end">${fmt(p.cost_without_expense_rs, 2)}</td>
            <td class="text-end">${fmt(p.total_rs_without_expense, 2)}</td>
            <td class="text-end">${fmt(p.off_usd_per_pc, 2)}</td>
            <td class="text-end">${fmt(p.total_off_usd, 2)}</td>
            <td class="text-end">${fmt(p.off_rs_per_pc, 2)}</td>
            <td class="text-end">${fmt(p.total_off_rs, 2)}</td>
            <td class="text-end">${fmt(p.off_duty_percent, 2)}</td>
            <td class="text-end">${fmt(p.off_duty_amt, 2)}</td>
            <td class="text-end">${fmt(p.off_surcharge, 2)}</td>
            <td class="text-end">${fmt(p.off_taxable_value, 2)}</td>
            <td class="text-end">${fmt(p.off_gst_percent, 2)}</td>
            <td class="text-end">${fmt(p.off_gst_amt, 2)}</td>
            <td class="text-end">${fmt(p.total_duty_gst, 2)}</td>
            <td class="text-end">${fmt(sAvgExpPerActCbm * toNum(p.actual_cbm_total), 2)}</td>
            <td class="text-end">${fmt(toNum(p.total_rs_without_expense) + toNum(p.off_duty_amt) + toNum(p.off_surcharge) + (sAvgExpPerActCbm * toNum(p.actual_cbm_total)), 2)}</td>
            <td class="text-end">${fmt(sAvgExpPerOffCbm * toNum(p.off_cbm_total), 2)}</td>
            <td class="text-end">${fmt(toNum(p.total_off_rs) + toNum(p.off_duty_amt) + toNum(p.off_surcharge) + (sAvgExpPerOffCbm * toNum(p.off_cbm_total)), 2)}</td>
            <td class="text-end">${fmt(toNum(p.official_qty) > 0 ? (toNum(p.total_off_rs) + toNum(p.off_duty_amt) + toNum(p.off_surcharge) + (sAvgExpPerOffCbm * toNum(p.off_cbm_total))) / toNum(p.official_qty) : 0, 2)}</td>
          </tr>
        `;
      });

      if (rowsHtml === '') {
        rowsHtml = '<tr><td colspan="33" class="text-center">No records</td></tr>';
      }

      function formatDate(dateStr) {
        if (!dateStr || dateStr === '0000-00-00') return '-';
        const date = new Date(dateStr);
        if (isNaN(date.getTime())) return dateStr;
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return `${date.getDate().toString().padStart(2, '0')}-${months[date.getMonth()]}-${date.getFullYear()}`;
      }

      const tableHtml = `
        <div class="supplier-section mb-2">
          <h6 class="mb-1">
            Supplier: ${escapeHtml(supplier.supplier_name || ('Supplier ' + (supplierIdx + 1)))}
            ${supplier.invoice ? `
              <span class="badge badge-soft-primary ms-2" style="font-size: 0.8rem; background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe;">
                <i class="fa fa-file-invoice me-1"></i> Inv: ${escapeHtml(supplier.invoice)}
              </span>
              <span class="badge badge-soft-secondary ms-1" style="font-size: 0.8rem; background: #f8fafc; color: #475569; border: 1px solid #e2e8f0;">
                <i class="fa fa-calendar-alt me-1"></i> ${formatDate(supplier.invoice_date)}
              </span>
            ` : ''}
          </h6>
          <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm mb-0">
              <thead>
                <tr>
                  <th>Sr No.</th>
                  <th>Product Name</th>
                  <th>Model No</th>
                  <th>HSN Code</th>
                  <th>Official Qty</th>
                  <th>Black Qty</th>
                  <th>Act Qty</th>
                  <th>CBM per pc</th>
                  <th>OFF CBM Total</th>
                  <th>Actual CBM Total</th>
                  <th>Act Cost With Expense Rs.</th>
                  <th>RMB per pc</th>
                  <th>RMB Total</th>
                  <th>USD per pc</th>
                  <th>USD Total</th>
                  <th>Cost Without Expense Rs.</th>
                  <th>Total Rs. Without Expense</th>
                  <th>OFF USD per pc</th>
                  <th>Total OFF. USD</th>
                  <th>OFF Rs per pc</th>
                  <th>Total OFF Rs.</th>
                  <th>OFF Duty %</th>
                  <th>OFF Duty Amt</th>
                  <th>OFF Surcharge 10%</th>
                  <th>OFF Taxable V.</th>
                  <th>OFF GST%</th>
                  <th>OFF GST Amt</th>
                  <th>Total Duty + GST</th>
                  <th>Expense</th>
                  <th>Total Exp</th>
                  <th>Off Exp</th>
                  <th>Off Tot Exp</th>
                  <th>Off Per Pc</th>
                </tr>
              </thead>
              <tbody>${rowsHtml}</tbody>
              <tfoot>
                <tr class="font-weight-bold">
                  <td colspan="4" class="text-end">TOTAL</td>
                  <td class="text-end">${fmt(totals.official_qty, 0)}</td>
                  <td class="text-end">${fmt(totals.black_qty, 0)}</td>
                  <td class="text-end">${fmt(totals.act_qty, 0)}</td>
                  <td class="text-end">${fmt(totals.cbm_per_pc, 5)}</td>
                  <td class="text-end">${fmt(totals.off_cbm_total, 5)}</td>
                  <td class="text-end">${fmt(totals.actual_cbm_total, 5)}</td>
                  <td class="text-end">${fmt(toNum(totals.act_qty) > 0 ? (toNum(totals.total_rs_without_expense) + toNum(totals.off_duty_amt) + toNum(totals.off_surcharge) + (sAvgExpPerActCbm * toNum(totals.actual_cbm_total))) / toNum(totals.act_qty) : 0, 2)}</td>
                  <td class="text-end">${fmt(totals.rmb_per_pc, 2)}</td>
                  <td class="text-end">${fmt(totals.rmb_total, 2)}</td>
                  <td class="text-end">${fmt(totals.usd_per_pc, 2)}</td>
                  <td class="text-end">${fmt(totals.usd_total, 2)}</td>
                  <td class="text-end">${fmt(totals.cost_without_expense_rs, 2)}</td>
                  <td class="text-end">${fmt(totals.total_rs_without_expense, 2)}</td>
                  <td class="text-end">${fmt(totals.off_usd_per_pc, 2)}</td>
                  <td class="text-end">${fmt(totals.total_off_usd, 2)}</td>
                  <td class="text-end">${fmt(totals.off_rs_per_pc, 2)}</td>
                  <td class="text-end">${fmt(totals.total_off_rs, 2)}</td>
                  <td class="text-end">${fmt(totals.off_duty_percent, 2)}</td>
                  <td class="text-end">${fmt(totals.off_duty_amt, 2)}</td>
                  <td class="text-end">${fmt(totals.off_surcharge, 2)}</td>
                  <td class="text-end">${fmt(totals.off_taxable_value, 2)}</td>
                  <td class="text-end">${fmt(totals.off_gst_percent, 2)}</td>
                  <td class="text-end">${fmt(totals.off_gst_amt, 2)}</td>
                  <td class="text-end">${fmt(totals.total_duty_gst, 2)}</td>
                  <td class="text-end">${fmt(sAvgExpPerActCbm * toNum(totals.actual_cbm_total), 2)}</td>
                  <td class="text-end">${fmt(toNum(totals.total_rs_without_expense) + toNum(totals.off_duty_amt) + toNum(totals.off_surcharge) + (sAvgExpPerActCbm * toNum(totals.actual_cbm_total)), 2)}</td>
                  <td class="text-end">${fmt(sAvgExpPerOffCbm * toNum(totals.off_cbm_total), 2)}</td>
                  <td class="text-end">${fmt(toNum(totals.total_off_rs) + toNum(totals.off_duty_amt) + toNum(totals.off_surcharge) + (sAvgExpPerOffCbm * toNum(totals.off_cbm_total)), 2)}</td>
                  <td class="text-end">${fmt(toNum(totals.official_qty) > 0 ? (toNum(totals.total_off_rs) + toNum(totals.off_duty_amt) + toNum(totals.off_surcharge) + (sAvgExpPerOffCbm * toNum(totals.off_cbm_total))) / toNum(totals.official_qty) : 0, 2)}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      `;

      $wrap.append(tableHtml);
    });

    const g = grandTotals || {};
    $wrap.append(`
      <div class="supplier-section mb-2">
        <h6 class="mb-1">Grand Total</h6>
        <div class="table-responsive">
          <table class="table table-bordered table-striped table-sm mb-0">
            <thead>
              <tr>
                <th colspan="4">#</th>
                <th>Official Qty</th>
                <th>Black Qty</th>
                <th>Act Qty</th>
                <th>CBM per pc</th>
                <th>OFF CBM Total</th>
                <th>Actual CBM Total</th>
                <th>Act Cost With Expense Rs.</th>
                <th>RMB per pc</th>
                <th>RMB Total</th>
                <th>USD per pc</th>
                <th>USD Total</th>
                <th>Cost Without Expense Rs.</th>
                <th>Total Rs. Without Expense</th>
                <th>OFF USD per pc</th>
                <th>Total OFF. USD</th>
                <th>OFF Rs per pc</th>
                <th>Total OFF Rs.</th>
                <th>OFF Duty %</th>
                <th>OFF Duty Amt</th>
                <th>OFF Surcharge 10%</th>
                <th>OFF Taxable V.</th>
                <th>OFF GST%</th>
                <th>OFF GST Amt</th>
                <th>Total Duty + GST</th>
                <th>Expense</th>
                <th>Total Exp</th>
                <th>Off Exp</th>
                <th>Off Tot Exp</th>
                <th>Off Per Pc</th>
              </tr>
            </thead>
            <tfoot>
              <tr class="font-weight-bold">
                <td colspan="4" class="text-end">TOTAL</td>
                <td class="text-end">${fmt(g.official_qty, 0)}</td>
                <td class="text-end">${fmt(g.black_qty, 0)}</td>
                <td class="text-end">${fmt(g.act_qty, 0)}</td>
                <td class="text-end">${fmt(g.cbm_per_pc, 5)}</td>
                <td class="text-end">${fmt(g.off_cbm_total, 5)}</td>
                <td class="text-end">${fmt(g.actual_cbm_total, 5)}</td>
                <td class="text-end">${fmt(toNum(g.act_qty) > 0 ? (toNum(g.total_rs_without_expense) + toNum(g.off_duty_amt) + toNum(g.off_surcharge) + toNum(totalExpense)) / toNum(g.act_qty) : 0, 2)}</td>
                <td class="text-end">${fmt(g.rmb_per_pc, 2)}</td>
                <td class="text-end">${fmt(g.rmb_total, 2)}</td>
                <td class="text-end">${fmt(g.usd_per_pc, 2)}</td>
                <td class="text-end">${fmt(g.usd_total, 2)}</td>
                <td class="text-end">${fmt(g.cost_without_expense_rs, 2)}</td>
                <td class="text-end">${fmt(g.total_rs_without_expense, 2)}</td>
                <td class="text-end">${fmt(g.off_usd_per_pc, 2)}</td>
                <td class="text-end">${fmt(g.total_off_usd, 2)}</td>
                <td class="text-end">${fmt(g.off_rs_per_pc, 2)}</td>
                <td class="text-end">${fmt(g.total_off_rs, 2)}</td>
                <td class="text-end">${fmt(g.off_duty_percent, 2)}</td>
                <td class="text-end">${fmt(g.off_duty_amt, 2)}</td>
                <td class="text-end">${fmt(g.off_surcharge, 2)}</td>
                <td class="text-end">${fmt(g.off_taxable_value, 2)}</td>
                <td class="text-end">${fmt(g.off_gst_percent, 2)}</td>
                <td class="text-end">${fmt(g.off_gst_amt, 2)}</td>
                <td class="text-end">${fmt(g.total_duty_gst, 2)}</td>
                <td class="text-end">${fmt(totalExpense, 2)}</td>
                <td class="text-end">${fmt(toNum(g.total_rs_without_expense) + toNum(g.off_duty_amt) + toNum(g.off_surcharge) + toNum(totalExpense), 2)}</td>
                <td class="text-end">${fmt(totalExpense, 2)}</td>
                <td class="text-end">${fmt(toNum(g.total_off_rs) + toNum(g.off_duty_amt) + toNum(g.off_surcharge) + toNum(totalExpense), 2)}</td>
                <td class="text-end">${fmt(toNum(g.official_qty) > 0 ? (toNum(g.total_off_rs) + toNum(g.off_duty_amt) + toNum(g.off_surcharge) + toNum(totalExpense)) / toNum(g.official_qty) : 0, 2)}</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    `);

    $wrap.append(`
      <div class="row mt-4">
        <div class="col-md-6" id="supplier-accounts-col"></div>
        <div class="col-md-6" id="expense-detail-col"></div>
      </div>
    `);

    const $accountsCol = $('#supplier-accounts-col');
    const $expenseCol = $('#expense-detail-col');

    if (Array.isArray(expenseItems) && expenseItems.length > 0) {
      let expRowsHtml = '';
      let totalNet = 0;
      let totalGst = 0;
      let totalFinal = 0;

      expenseItems.forEach(item => {
        const net = toNum(item.amount);
        const gst = toNum(item.gst_amt);
        const final = toNum(item.total_amt);
        totalNet += net;
        totalGst += gst;
        totalFinal += final;

        expRowsHtml += `
          <tr>
            <td>${escapeHtml(item.expense_name)}</td>
            <td class="text-end">${fmt(net, 2)}</td>
            <td class="text-end">${fmt(gst, 2)}</td>
            <td class="text-end">${fmt(final, 2)}</td>
          </tr>
        `;
      });

      $expenseCol.append(`
        <div class="supplier-section mb-2">
          <h6 class="mb-1">Expense Detail</h6>
          <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm mb-0">
              <thead>
                <tr>
                  <th>Expense Name</th>
                  <th class="text-end">Net Amount</th>
                  <th class="text-end">GST Amount</th>
                  <th class="text-end">Final Total</th>
                </tr>
              </thead>
              <tbody>${expRowsHtml}</tbody>
              <tfoot>
                <tr class="font-weight-bold">
                  <td class="text-end">TOTAL</td>
                  <td class="text-end">${fmt(totalNet, 2)}</td>
                  <td class="text-end">${fmt(totalGst, 2)}</td>
                  <td class="text-end">${fmt(totalFinal, 2)}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      `);
    }

    if (Array.isArray(supplierAccounts) && supplierAccounts.length > 0) {
      supplierAccounts.forEach(acc => {
        let paymentRows = '';
        acc.payments.forEach(p => {
          paymentRows += `
            <tr>
              <td>${p.date}</td>
              <td class="text-end">${fmt(p.rmb)}</td>
              <td class="text-end">${fmt(p.usd)}</td>
              <td class="text-end">${fmt(p.inr)}</td>
            </tr>
          `;
        });

        $accountsCol.append(`
          <div class="supplier-section mb-3">
            <h6 class="mb-1">Supplier Account: ${escapeHtml(acc.supplier_name)}</h6>
            <div class="table-responsive">
              <table class="table table-bordered table-striped table-sm mb-0">
                <thead>
                  <tr class="bg-light">
                    <th>Date</th>
                    <th class="text-end">RMB</th>
                    <th class="text-end">USD</th>
                    <th class="text-end">INR</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>${acc.outstanding.date}</td>
                    <td class="text-end">${fmt(acc.outstanding.rmb)}</td>
                    <td class="text-end">${fmt(acc.outstanding.usd)}</td>
                    <td class="text-end">${fmt(acc.outstanding.inr)}</td>
                  </tr>
                  <tr>
                    <td colspan="4" class="py-0" style="background: #eee; height: 1px;"></td>
                  </tr>
                  ${paymentRows}
                </tbody>
                <tfoot>
                  <tr class="font-weight-bold" style="background: #fdfdfd;">
                    <td>Total</td>
                    <td class="text-end">${fmt(acc.total.rmb)}</td>
                    <td class="text-end">${fmt(acc.total.usd)}</td>
                    <td class="text-end">${fmt(acc.total.inr)}</td>
                  </tr>
                  <tr class="text-primary fw-bold">
                    <td>Loaded Amount</td>
                    <td class="text-end">${fmt(acc.loaded.rmb)}</td>
                    <td class="text-end">${fmt(acc.loaded.usd)}</td>
                    <td class="text-end">${fmt(acc.loaded.inr)}</td>
                  </tr>
                  <tr class="bg-dark text-white fw-bold">
                    <td>Remaining Balance</td>
                    <td class="text-end">${fmt(acc.balance.rmb)}</td>
                    <td class="text-end">${fmt(acc.balance.usd)}</td>
                    <td class="text-end">${fmt(acc.balance.inr)}</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        `);
      });
    }
  }


  $(document).ready(function() {
    $('#view-batch-btn').on('click', function() {
      const batchNo = ($('#batch_no').val() || '').trim();
      if (!batchNo) {
        Swal.fire('Error!', 'Please select a Batch No.', 'error');
        return;
      }

      const $btn = $(this);
      const originalText = $btn.text();
      $btn.prop('disabled', true).text('Loading...');

      $.ajax({
        type: 'POST',
        url: "<?php echo base_url(); ?>inventory/get_batch_detail_new_data",
        data: { batch_no: batchNo },
        dataType: 'json',
        success: function(res) {
          if (res && Number(res.status) === 200) {
            setHeaderFields(res.header || {});
            renderSupplierTables(res.suppliers || [], res.grand_totals || {}, res.expenses || [], res.expense_items || [], res.supplier_accounts || []);
            $('#batch-detail-wrap').removeClass('d-none');
          } else {
            $('#batch-detail-wrap').addClass('d-none');
            Swal.fire('Error!', (res && res.message) ? res.message : 'Unable to fetch batch data.', 'error');
          }
        },
        error: function() {
          $('#batch-detail-wrap').addClass('d-none');
          Swal.fire('Error!', 'Request failed. Please try again.', 'error');
        },
        complete: function() {
          $btn.prop('disabled', false).text(originalText);
        }
      });
    });
  });
</script>

