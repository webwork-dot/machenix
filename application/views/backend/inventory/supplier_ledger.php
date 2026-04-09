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
$locationLine = trim($cityState . ($pincode ? " – $pincode" : ''));
?>

<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=DM+Mono:wght@400;500&display=swap"
  rel="stylesheet">

<!-- ───── Supplier Info Card ───── -->
<div style="
  
  background: #ffffff;
  border: 1px solid #e8eaed;
  border-radius: 12px;
  overflow: hidden;
  margin-bottom: 16px;
  box-shadow: 0 1px 4px rgba(0,0,0,.05);
">

  <!-- Card Header -->
  <div style="
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    border-bottom: 1px solid #f0f2f5;
    background: #fafbfc;
  ">
    <div>
      <div
        style="font-size: 10px; font-weight: 600; letter-spacing: 1px; color: #9ca3af; text-transform: uppercase; margin-bottom: 3px;">
        Supplier
      </div>
      <div style="font-size: 15px; font-weight: 600; color: #111827;">
        <?= html_escape($supplier['name'] ?? '—') ?>
      </div>
    </div>

    <?php if (!empty($supplier['gst_no'])): ?>
      <div style="
        
        font-size: 11px;
        font-weight: 500;
        color: #2563eb;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 6px;
        padding: 4px 10px;
        letter-spacing: 0.5px;
      ">
        GST &nbsp;<?= html_escape($supplier['gst_no']) ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Card Body -->
  <div style="display: flex; gap: 0; flex-wrap: wrap;">

    <!-- Address -->
    <div style="flex: 1 1 220px; padding: 14px 16px; border-right: 1px solid #f0f2f5;">
      <div
        style="font-size: 10px; font-weight: 600; letter-spacing: 1px; color: #9ca3af; text-transform: uppercase; margin-bottom: 6px;">
        Address
      </div>
      <?php if (!empty($addrLines)): ?>
        <div style="font-size: 13px; color: #1f2937; line-height: 1.7;">
          <?= implode("<br>", array_map('html_escape', $addrLines)) ?>
        </div>
      <?php else: ?>
        <div style="font-size: 13px; color: #9ca3af;">—</div>
      <?php endif; ?>
      <?php if (!empty($locationLine)): ?>
        <div style="font-size: 11px; color: #6b7280; margin-top: 4px; font-weight: 500;">
          <?= html_escape($locationLine) ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Key Info -->
    <div style="flex: 1 1 220px; padding: 14px 16px;">
      <div
        style="font-size: 10px; font-weight: 600; letter-spacing: 1px; color: #9ca3af; text-transform: uppercase; margin-bottom: 10px;">
        Key Info
      </div>
      <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
        <tr>
          <td style="color: #6b7280; font-weight: 500; padding: 3px 0; width: 40%;">Contact Person</td>
          <td style="color: #111827; font-weight: 600; padding: 3px 0;">
            <?= html_escape($supplier['contact_name'] ?? '—') ?>
          </td>
        </tr>
        <tr>
          <td style="color: #6b7280; font-weight: 500; padding: 3px 0;">Phone</td>
          <td style="padding: 3px 0;">
            <?php if (!empty($supplier['contact_no'])): ?>
              <a href="tel:<?= html_escape($supplier['contact_no']) ?>"
                style="color: #2563eb; text-decoration: none; font-weight: 600;  font-size: 11px;">
                <?= html_escape($supplier['contact_no']) ?>
              </a>
            <?php else: ?>
              <span style="color: #9ca3af;">—</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php if (!empty($supplier['gst_name'])): ?>
          <tr>
            <td style="color: #6b7280; font-weight: 500; padding: 3px 0;">GST Name</td>
            <td style="color: #111827; font-weight: 600; padding: 3px 0;">
              <?= html_escape($supplier['gst_name']) ?>
            </td>
          </tr>
        <?php endif; ?>
      </table>
    </div>

  </div>
</div>


<?php
// --- 1. DATA MERGE & SORTING ---
$ledger = [];

if (!empty($outstanding)) {
  foreach ($outstanding as $row) {
    $ledger[] = [
      'date' => $row['date'],
      'ref' => $row['voucher_no'],
      'type' => 'PURCHASE',
      'status' => $row['delivery_status'],
      'rmb' => (float) $row['total_actual_rmb'],
      'usd' => (float) $row['total_actual_usd'],
      'inr' => (float) $row['total_actual_inr'],
      'added_by' => $row['added_by_name'],
      'is_payment' => false
    ];
  }
}

if (!empty($payments)) {
  foreach ($payments as $pay) {
    $ledger[] = [
      'date' => $pay['payment_date'],
      'ref' => $pay['invoice_no'],
      'batch' => $pay['batch_no'],
      'type' => 'PAYMENT',
      'status' => $pay['payment_type'],
      'rmb' => (float) $pay['amount_rmb'],
      'usd' => (float) $pay['amount_dollar'],
      'inr' => (float) $pay['amount_rs'],
      'added_by' => $pay['added_by_name'],
      'is_payment' => true
    ];
  }
}

usort($ledger, function ($a, $b) {
  return strtotime($b['date']) - strtotime($a['date']);
});

$totals = [
  'purchase' => ['rmb' => 0, 'usd' => 0, 'inr' => 0],
  'payment' => ['rmb' => 0, 'usd' => 0, 'inr' => 0]
];

foreach ($ledger as $item) {
  $tKey = $item['is_payment'] ? 'payment' : 'purchase';
  $totals[$tKey]['rmb'] += $item['rmb'];
  $totals[$tKey]['usd'] += $item['usd'];
  $totals[$tKey]['inr'] += $item['inr'];
}

$balance = [
  'rmb' => $totals['purchase']['rmb'] - $totals['payment']['rmb'],
  'usd' => $totals['purchase']['usd'] - $totals['payment']['usd'],
  'inr' => $totals['purchase']['inr'] - $totals['payment']['inr']
];
?>


<!-- ───── Unified Ledger ───── -->
<div style="">
  <div style="
    background: #ffffff;
    border: 1px solid #e8eaed;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
  ">

    <!-- Ledger Header -->
    <div style="
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 12px 16px;
      border-bottom: 1px solid #f0f2f5;
      background: #fafbfc;
    ">
      <div style="font-size: 13px; font-weight: 600; color: #111827; letter-spacing: 0.2px;">
        Supplier Ledger
      </div>

      <!-- Balance Summary Pills -->
      <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
        <?php
        $balanceColor = $balance['inr'] > 0 ? '#dc2626' : '#16a34a';
        $balanceBg = $balance['inr'] > 0 ? '#fef2f2' : '#f0fdf4';
        $balanceBorder = $balance['inr'] > 0 ? '#fecaca' : '#bbf7d0';
        ?>
        <div style="
          font-size: 10px;
          font-weight: 500;
          color: #6b7280;
          padding: 4px 10px;
          background: #f3f4f6;
          border: 1px solid #e5e7eb;
          border-radius: 20px;
        ">
          Purchases &nbsp;<strong style="color:#1f2937; font-family:'DM Mono',monospace;">
            ₹ <?= number_format($totals['purchase']['inr'], 2) ?>
          </strong>
        </div>
        <div style="
          font-size: 10px;
          font-weight: 500;
          color: #6b7280;
          padding: 4px 10px;
          background: #f3f4f6;
          border: 1px solid #e5e7eb;
          border-radius: 20px;
        ">
          Payments &nbsp;<strong style="color:#1f2937; font-family:'DM Mono',monospace;">
            ₹ <?= number_format($totals['payment']['inr'], 2) ?>
          </strong>
        </div>
        <div style="
          font-size: 11px;
          font-weight: 700;
          color: <?= $balanceColor ?>;
          background: <?= $balanceBg ?>;
          border: 1px solid <?= $balanceBorder ?>;
          border-radius: 20px;
          padding: 4px 12px;
          
        ">
          Balance &nbsp;₹ <?= number_format($balance['inr'], 2) ?>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div style="overflow-x: auto;">
      <table style="
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
        
      ">
        <thead>
          <tr style="background: #f9fafb; border-bottom: 1px solid #e8eaed;">
            <th
              style="padding: 8px 16px; text-align: left; font-size: 10px; font-weight: 600; color: #9ca3af; letter-spacing: 0.8px; text-transform: uppercase; white-space: nowrap;">
              Date</th>
            <th
              style="padding: 8px 10px; text-align: left; font-size: 10px; font-weight: 600; color: #9ca3af; letter-spacing: 0.8px; text-transform: uppercase;">
              Type</th>
            <th
              style="padding: 8px 10px; text-align: left; font-size: 10px; font-weight: 600; color: #9ca3af; letter-spacing: 0.8px; text-transform: uppercase;">
              Reference</th>
            <th
              style="padding: 8px 10px; text-align: right; font-size: 10px; font-weight: 600; color: #9ca3af; letter-spacing: 0.8px; text-transform: uppercase;">
              RMB</th>
            <th
              style="padding: 8px 10px; text-align: right; font-size: 10px; font-weight: 600; color: #9ca3af; letter-spacing: 0.8px; text-transform: uppercase;">
              USD</th>
            <th
              style="padding: 8px 10px; text-align: right; font-size: 10px; font-weight: 600; color: #9ca3af; letter-spacing: 0.8px; text-transform: uppercase;">
              INR</th>
            <th
              style="padding: 8px 16px; text-align: left; font-size: 10px; font-weight: 600; color: #9ca3af; letter-spacing: 0.8px; text-transform: uppercase;">
              Added By</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($ledger)): ?>
            <?php foreach ($ledger as $i => $item): ?>
              <?php
              $rowBg = $item['is_payment'] ? '#f0fdf4' : '#ffffff';
              $amtColor = $item['is_payment'] ? '#16a34a' : '#dc2626';
              $sign = $item['is_payment'] ? '−' : '+';
              ?>
              <tr style="
                background: <?= $rowBg ?>;
                border-bottom: 1px solid #f3f4f6;
                transition: background .12s;
              " onmouseover="this.style.background='<?= $item['is_payment'] ? '#dcfce7' : '#f9fafb' ?>'"
                onmouseout="this.style.background='<?= $rowBg ?>'">

                <!-- Date -->
                <td style="padding: 9px 16px; color: #6b7280; font-size: 11px; white-space: nowrap; ">
                  <?= date('d M y', strtotime($item['date'])) ?>
                </td>

                <!-- Type Badge -->
                <td style="padding: 9px 10px;">
                  <?php if ($item['is_payment']): ?>
                    <span style="
                      display: inline-block;
                      font-size: 9px;
                      font-weight: 700;
                      letter-spacing: 0.6px;
                      color: #0891b2;
                      background: #ecfeff;
                      border: 1px solid #a5f3fc;
                      border-radius: 4px;
                      padding: 2px 7px;
                      text-transform: uppercase;
                    ">Payment</span>
                  <?php else: ?>
                    <span style="
                      display: inline-block;
                      font-size: 9px;
                      font-weight: 700;
                      letter-spacing: 0.6px;
                      color: #7c3aed;
                      background: #f5f3ff;
                      border: 1px solid #ddd6fe;
                      border-radius: 4px;
                      padding: 2px 7px;
                      text-transform: uppercase;
                    ">Purchase</span>
                  <?php endif; ?>
                </td>

                <!-- Ref -->
                <td style="padding: 9px 10px;">
                  <div style="font-weight: 600; color: #1f2937;  font-size: 11px;">
                    <?= html_escape($item['ref']) ?>
                  </div>
                  <?php if ($item['is_payment'] && !empty($item['batch'])): ?>
                    <div style="font-size: 9px; color: #9ca3af; margin-top: 2px;">
                      Batch: <?= html_escape($item['batch']) ?>
                    </div>
                  <?php endif; ?>
                </td>

                <!-- Amounts -->
                <td style="padding: 9px 10px; text-align: right;  color: <?= $amtColor ?>; font-weight: 600;">
                  <?= $sign ?>     <?= number_format($item['rmb'], 2) ?>
                </td>
                <td style="padding: 9px 10px; text-align: right;  color: <?= $amtColor ?>; font-weight: 600;">
                  <?= $sign ?>     <?= number_format($item['usd'], 2) ?>
                </td>
                <td
                  style="padding: 9px 10px; text-align: right;  color: <?= $amtColor ?>; font-weight: 600; font-size: 12px;">
                  <?= $sign ?>     <?= number_format($item['inr'], 2) ?>
                </td>

                <!-- Added By -->
                <td style="padding: 9px 16px; color: #9ca3af; font-size: 10px; white-space: nowrap;">
                  <?= html_escape($item['added_by'] ?: '—') ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" style="padding: 40px; text-align: center; color: #9ca3af; font-size: 13px;">
                No transactions found for this supplier.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>

        <!-- Footer Totals -->
        <tfoot>
          <tr style="background: #f9fafb; border-top: 2px solid #e8eaed;">
            <td colspan="3"
              style="padding: 8px 16px; text-align: right; font-size: 10px; font-weight: 600; color: #6b7280; letter-spacing: 0.5px; text-transform: uppercase;">
              Total Purchases
            </td>
            <td style="padding: 8px 10px; text-align: right;  font-size: 11px; color: #dc2626; font-weight: 600;">
              <?= number_format($totals['purchase']['rmb'], 2) ?>
            </td>
            <td style="padding: 8px 10px; text-align: right;  font-size: 11px; color: #dc2626; font-weight: 600;">
              <?= number_format($totals['purchase']['usd'], 2) ?>
            </td>
            <td style="padding: 8px 10px; text-align: right;  font-size: 11px; color: #dc2626; font-weight: 600;">
              <?= number_format($totals['purchase']['inr'], 2) ?>
            </td>
            <td></td>
          </tr>

          <tr style="background: #f9fafb;">
            <td colspan="3"
              style="padding: 8px 16px; text-align: right; font-size: 10px; font-weight: 600; color: #6b7280; letter-spacing: 0.5px; text-transform: uppercase;">
              Total Payments
            </td>
            <td style="padding: 8px 10px; text-align: right;  font-size: 11px; color: #16a34a; font-weight: 600;">
              <?= number_format($totals['payment']['rmb'], 2) ?>
            </td>
            <td style="padding: 8px 10px; text-align: right;  font-size: 11px; color: #16a34a; font-weight: 600;">
              <?= number_format($totals['payment']['usd'], 2) ?>
            </td>
            <td style="padding: 8px 10px; text-align: right;  font-size: 11px; color: #16a34a; font-weight: 600;">
              <?= number_format($totals['payment']['inr'], 2) ?>
            </td>
            <td></td>
          </tr>

          <?php
          $balFontColor = $balance['inr'] > 0 ? '#dc2626' : '#16a34a';
          $balRowBg = $balance['inr'] > 0 ? '#fff5f5' : '#f0fdf4';
          ?>
          <tr style="background: <?= $balRowBg ?>; border-top: 2px solid #e8eaed;">
            <td colspan="3"
              style="padding: 10px 16px; text-align: right; font-size: 11px; font-weight: 700; color: #111827; letter-spacing: 0.3px;">
              Remaining Balance
            </td>
            <td
              style="padding: 10px 10px; text-align: right;  font-size: 12px; font-weight: 700; color: <?= $balFontColor ?>;">
              <?= number_format($balance['rmb'], 2) ?>
            </td>
            <td
              style="padding: 10px 10px; text-align: right;  font-size: 12px; font-weight: 700; color: <?= $balFontColor ?>;">
              <?= number_format($balance['usd'], 2) ?>
            </td>
            <td
              style="padding: 10px 10px; text-align: right;  font-size: 12px; font-weight: 700; color: <?= $balFontColor ?>;">
              <?= number_format($balance['inr'], 2) ?>
            </td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>

  </div>
</div>

<script>
  if (typeof feather !== 'undefined') feather.replace();
</script>