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

          <div class="mt-2">
            <h6 class="mb-1">Batch Expenses</h6>
            <div class="table-responsive">
              <table class="table table-bordered table-striped table-sm mb-0">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Type</th>
                    <th>Payment Type</th>
                    <th>Narration</th>
                    <th class="text-end">Amount</th>
                    <th class="text-end">GST Total</th>
                    <th class="text-end">Grand Total</th>
                  </tr>
                </thead>
                <tbody id="expense-tbody">
                  <tr><td colspan="7" class="text-center">No expense data found</td></tr>
                </tbody>
              </table>
            </div>
          </div>
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

  function setHeaderFields(header) {
    $('#view-boe-no').val(header?.boe_no || '');
    $('#view-boe-date').val(header?.boe_date || '');
    $('#view-received-date').val(header?.received_date || '');
    $('#view-loading-date').val(header?.loading_date || '');
    $('#view-po-date').val(header?.po_date || '');
  }

  function renderSupplierTables(suppliers, grandTotals) {
    const $wrap = $('#supplier-table-wrap');
    $wrap.empty();

    if (!Array.isArray(suppliers) || suppliers.length === 0) {
      $wrap.html('<div class="alert alert-info mb-0">No product line items found for this batch.</div>');
      return;
    }

    suppliers.forEach((supplier, supplierIdx) => {
      const products = Array.isArray(supplier.products) ? supplier.products : [];
      const totals = supplier.totals || {};

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
            <td class="text-end">${fmt(p.rmb_per_pc, 2)}</td>
            <td class="text-end">${fmt(p.rmb_total, 2)}</td>
            <td class="text-end">${fmt(p.usd_per_pc, 2)}</td>
            <td class="text-end">${fmt(p.usd_total, 2)}</td>
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
          </tr>
        `;
      });

      if (rowsHtml === '') {
        rowsHtml = '<tr><td colspan="25" class="text-center">No records</td></tr>';
      }

      const tableHtml = `
        <div class="supplier-section mb-2">
          <h6 class="mb-1">Supplier: ${escapeHtml(supplier.supplier_name || ('Supplier ' + (supplierIdx + 1)))}</h6>
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
                  <th>RMB per pc</th>
                  <th>RMB Total</th>
                  <th>USD per pc</th>
                  <th>USD Total</th>
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
                  <td class="text-end">${fmt(totals.rmb_per_pc, 2)}</td>
                  <td class="text-end">${fmt(totals.rmb_total, 2)}</td>
                  <td class="text-end">${fmt(totals.usd_per_pc, 2)}</td>
                  <td class="text-end">${fmt(totals.usd_total, 2)}</td>
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
                <th>RMB per pc</th>
                <th>RMB Total</th>
                <th>USD per pc</th>
                <th>USD Total</th>
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
                <td class="text-end">${fmt(g.rmb_per_pc, 2)}</td>
                <td class="text-end">${fmt(g.rmb_total, 2)}</td>
                <td class="text-end">${fmt(g.usd_per_pc, 2)}</td>
                <td class="text-end">${fmt(g.usd_total, 2)}</td>
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
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    `);
  }

  function renderExpenses(expenses) {
    const $tbody = $('#expense-tbody');
    $tbody.empty();

    if (!Array.isArray(expenses) || expenses.length === 0) {
      $tbody.html('<tr><td colspan="7" class="text-center">No expense data found</td></tr>');
      return;
    }

    let html = '';
    expenses.forEach((e, i) => {
      html += `
        <tr>
          <td>${i + 1}</td>
          <td>${escapeHtml(e.type)}</td>
          <td>${escapeHtml(e.payment_type)}</td>
          <td>${escapeHtml(e.narration)}</td>
          <td class="text-end">${fmt(e.cheque_amount, 2)}</td>
          <td class="text-end">${fmt(e.gst_total, 2)}</td>
          <td class="text-end">${fmt(e.grand_total, 2)}</td>
        </tr>
      `;
    });
    $tbody.html(html);
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
        url: "<?php echo base_url(); ?>inventory/get_batch_detail_data",
        data: { batch_no: batchNo },
        dataType: 'json',
        success: function(res) {
          if (res && Number(res.status) === 200) {
            setHeaderFields(res.header || {});
            renderSupplierTables(res.suppliers || [], res.grand_totals || {});
            renderExpenses(res.expenses || []);
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

