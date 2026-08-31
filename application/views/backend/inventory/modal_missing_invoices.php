<?php
/**
 * Missing Invoices Modal Component
 * 
 * Expected variables:
 * @var array $missing_invoices - Array of missing invoice numbers, e.g. ['SO/2026-27/4', 'SO/2026-27/5']
 * @var string $target_input_id - Target input ID to populate (default 'invoice_no')
 */
$target_input_id = !empty($target_input_id) ? $target_input_id : 'invoice_no';
$missing_invoices = !empty($missing_invoices) ? $missing_invoices : [];
?>

<!-- Missing Invoices Modal -->
<div class="modal fade" id="missing_invoices_modal" tabindex="-1" aria-labelledby="missingInvoicesModalLabel" aria-hidden="true" data-bs-backdrop="false" style="z-index: 1070; background: rgba(0, 0, 0, 0.45);">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-md">
    <div class="modal-content shadow-lg border-0">
      <div class="modal-header bg-warning bg-opacity-10 border-bottom border-warning border-opacity-25 py-2 px-3">
        <h5 class="modal-title text-dark fw-bold d-flex align-items-center gap-2 mb-0" id="missingInvoicesModalLabel">
          <i class="fa fa-exclamation-triangle text-warning"></i>
          <span>Missing Invoice Numbers</span>
          <span class="badge bg-danger rounded-pill fs-7"><?= count($missing_invoices); ?> Missing</span>
        </h5>
        <button type="button" class="btn-close" onclick="closeMissingInvoicesModal();" aria-label="Close"></button>
      </div>

      <div class="modal-body p-3">
        <div class="alert alert-light-warning mb-3 py-2 px-3 border border-warning border-opacity-25 rounded d-flex align-items-center gap-2" style="font-size: 13px;">
          <i class="fa fa-info-circle text-warning fs-5 flex-shrink-0"></i>
          <div>
            The invoice numbers below were skipped in the sequence. Click <strong>"Use This"</strong> to assign any missing number to this invoice.
          </div>
        </div>

        <?php if (!empty($missing_invoices)): ?>
          <div class="mb-2">
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-light"><i class="fa fa-search text-muted"></i></span>
              <input type="text" id="search_missing_invoice_input" class="form-control form-control-sm" placeholder="Search missing invoice no..." onkeyup="filterMissingInvoices();">
              <button class="btn btn-outline-secondary" type="button" onclick="$('#search_missing_invoice_input').val(''); filterMissingInvoices();"><i class="fa fa-times"></i></button>
            </div>
          </div>

          <div class="table-responsive rounded border" style="max-height: 280px;">
            <table class="table table-sm table-hover mb-0 align-middle" id="missing_invoices_table">
              <thead class="table-light sticky-top">
                <tr>
                  <th style="width: 45px;" class="text-center">#</th>
                  <th>Invoice Number</th>
                  <th style="width: 110px;" class="text-center">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($missing_invoices as $idx => $inv_num): ?>
                  <tr class="missing-invoice-item">
                    <td class="text-center text-muted fw-bold fs-7"><?= $idx + 1; ?></td>
                    <td>
                      <span class="badge bg-light-danger text-danger fw-semibold px-2 py-1 fs-6 font-monospace border border-danger border-opacity-25">
                        <i class="fa fa-file-text-o me-1"></i><?= htmlspecialchars($inv_num); ?>
                      </span>
                    </td>
                    <td class="text-center">
                      <button type="button" class="btn btn-sm btn-primary py-0 px-2 fs-7 waves-effect waves-float waves-light d-inline-flex align-items-center gap-1" onclick="selectMissingInvoice('<?= htmlspecialchars($inv_num, ENT_QUOTES); ?>', '<?= htmlspecialchars($target_input_id, ENT_QUOTES); ?>');">
                        <i class="fa fa-check"></i> Use This
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="text-center py-4 text-muted">
            <i class="fa fa-check-circle text-success fs-1 mb-2"></i>
            <p class="mb-0 fw-semibold">No missing invoices found!</p>
            <small>All invoice numbers in the current series are sequential.</small>
          </div>
        <?php endif; ?>
      </div>

      <div class="modal-footer py-2 px-3 bg-light border-top d-flex justify-content-between align-items-center">
        <small class="text-muted"><i class="fa fa-lightbulb-o text-warning me-1"></i>Clicking "Use This" sets the input and closes this dialog.</small>
        <button type="button" class="btn btn-sm btn-secondary" onclick="closeMissingInvoicesModal();">Close</button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
function openMissingInvoicesModal() {
  $('#missing_invoices_modal').modal('show');
  setTimeout(function() {
    $('#search_missing_invoice_input').focus();
  }, 300);
}

function closeMissingInvoicesModal() {
  $('#missing_invoices_modal').modal('hide');
}

function selectMissingInvoice(invNo, targetId) {
  var target = targetId || '<?= $target_input_id; ?>';
  var $input = $('#' + target);
  if ($input.length === 0) {
    $input = $('input[name="invoice_no"]');
  }
  
  if ($input.length > 0) {
    $input.val(invNo);
    
    // Add brief visual feedback highlight
    $input.addClass('border-success text-success fw-bold');
    $input.css({
      'transition': 'all 0.3s ease',
      'background-color': '#d1e7dd',
      'box-shadow': '0 0 0 0.25rem rgba(25, 135, 84, 0.25)'
    });
    
    setTimeout(function() {
      $input.css({
        'background-color': '',
        'box-shadow': ''
      });
      $input.removeClass('text-success fw-bold');
    }, 1800);
  }
  
  closeMissingInvoicesModal();
}

function filterMissingInvoices() {
  var value = $('#search_missing_invoice_input').val().toLowerCase().trim();
  $('#missing_invoices_table tbody tr.missing-invoice-item').each(function() {
    var text = $(this).text().toLowerCase();
    $(this).toggle(text.indexOf(value) > -1);
  });
}
</script>
