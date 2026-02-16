<?php
$supplier = $data ?? [];

// Build address (only non-empty lines)
$addrLines = array_filter([
  trim($supplier['address'] ?? ''),
  trim($supplier['address_2'] ?? ''),
  trim($supplier['address_3'] ?? ''),
]);

$cityState = trim(implode(', ', array_filter([
  trim($supplier['city_name'] ?? ''),
  trim($supplier['state_name'] ?? ''),
])));

$pincode = trim($supplier['pincode'] ?? '');
$locationLine = trim($cityState . ($pincode ? " - $pincode" : ''));
?>

<style>
  /* keep ONLY what bootstrap can't do nicely */
  .supplier-card{
    border:1px solid #e7eaf2;
    border-radius:10px;
    box-shadow:0 4px 12px rgba(0,0,0,.04);
  }
  .meta-label{
    font-size:10px;
    text-transform:uppercase;
    letter-spacing:.5px;
    color:#8b93a6;
  }
  .gst-pill{
    display:inline-flex;
    align-items:center;
    gap:4px;
    padding:4px 8px;
    border-radius:999px;
    font-size:11px;
    font-weight:600;
    border:1px solid rgba(49,130,206,.3);
    color:#2b6cb0;
    background:rgba(49,130,206,.06);
    white-space:nowrap;
  }
</style>

<div class="card supplier-card mb-2">
  <div class="card-header bg-transparent d-flex align-items-center justify-content-between py-2 px-2 border-bottom">
    <div>
      <div class="meta-label mb-1">Supplier Details</div>
      <h6 class="mb-0 fw-semibold text-dark">
        <?= html_escape($supplier['name'] ?? '-') ?>
      </h6>
    </div>

    <?php if (!empty($supplier['gst_no'])): ?>
      <div class="gst-pill">GST: <?= html_escape($supplier['gst_no']) ?></div>
    <?php endif; ?>
  </div>

  <div class="card-body p-1">
    <div class="row g-2">
      <!-- Address -->
      <div class="col-lg-6">
        <div class="border rounded-3 p-1 h-100">
          <div class="meta-label mb-1">Address</div>

          <div class="fw-semibold text-dark" style="font-size:13px;">
            <?php if (!empty($addrLines)): ?>
              <?= implode("<br>", array_map('html_escape', $addrLines)) ?>
            <?php else: ?>
              -
            <?php endif; ?>
          </div>

          <?php if (!empty($locationLine)): ?>
            <div class="text-muted mt-1" style="font-size:11px;">
              <?= html_escape($locationLine) ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Key info -->
      <div class="col-lg-6">
        <div class="border rounded-3 p-1 h-100">
          <div class="meta-label mb-2">Key Info</div>

          <div class="row g-2 align-items-start" style="font-size:13px;">
            <div class="col-5 text-muted text-uppercase" style="font-size:10px; letter-spacing:.5px;">
              Contact Person
            </div>
            <div class="col-7 fw-semibold text-dark">
              <?= html_escape($supplier['contact_name'] ?? '-') ?>
            </div>

            <div class="col-5 mt-1 text-muted text-uppercase" style="font-size:10px; letter-spacing:.5px;">
              Phone
            </div>
            <div class="col-7 mt-1 fw-semibold">
              <?php if (!empty($supplier['contact_no'])): ?>
                <a class="text-decoration-none" href="tel:<?= html_escape($supplier['contact_no']) ?>">
                  <?= html_escape($supplier['contact_no']) ?>
                </a>
              <?php else: ?>
                <span class="text-dark">-</span>
              <?php endif; ?>
            </div>

            <?php if (!empty($supplier['gst_name'])): ?>
              <div class="col-5 mt-1 text-muted text-uppercase" style="font-size:10px; letter-spacing:.5px;">
                GST Name
              </div>
              <div class="col-7 mt-1 fw-semibold text-dark">
                <?= html_escape($supplier['gst_name']) ?>
              </div>
            <?php endif; ?>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>



<div class="row d-none">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="row">
          <div class="col-md-12 mt-10">
            <h5 class="mb-0"><b>Ledger</b>
            </h5>
          </div>
        </div>
        <div class="row">
          <table class="table leads-table" id="report-datatable">
            <thead>
              <tr>
                <th>#</th>
                <th>Supplier Name</th>
                <th>Contact Person Name</th>
                <th>Contact Person Number</th>
                <th>Actions</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>