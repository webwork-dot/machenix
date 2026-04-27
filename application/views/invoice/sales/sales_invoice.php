
<html xmlns="http://www.w3.org/1999/xhtml" moznomarginboxes="" mozdisallowselectionprint="">

<head>
  <title>Sales Invoice</title>
  <link rel="stylesheet" href="<?= base_url(); ?>assets/pdf/bootstrap.min.css">
  <link rel="stylesheet" href="<?= base_url(); ?>assets/pdf/custom.css">
</head>

<body>
  <div style="background:#ffffff;margin:0 auto;width:100%;padding:0px;">

    <table style="width:100%; margin-bottom:15px;">
      <tbody>
        <tr>
          <td style="width:100%;text-align:center;padding:8px 3px;line-height:1.0;" colspan="6">
            <b style="font-size:16px;color:#000;">TAX INVOICE</b>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- General Details Section -->
    <table style="width:100%;border-collapse:collapse;font-size:10px;margin-bottom:0;">
      <tbody>
        <tr>

          <!-- LEFT: Seller / Consignee / Buyer stacked in one cell -->
          <td style="width:50%;border:1px solid #000;padding:0px !important;vertical-align:top;text-align:left;">

            <!-- Seller -->
            <div style="padding:5px 6px;border-bottom:1px solid #000;line-height:1.55;">
              <b><?= $data['company']['name']; ?></b><br>
              <?= $data['company']['address']; ?><br>
              <?= ($data['company']['address_2'] != '') ? $data['company']['address_2'] . '<br>' : ''; ?>
              <?= ($data['company']['address_3'] != '') ? $data['company']['address_3'] . '<br>' : ''; ?>
              <?= $data['company']['city_name']; ?>, <?= $data['company']['state_name']; ?> - <?= $data['company']['pincode']; ?><br>
              GSTIN/UIN: <?= $data['company']['gst_no']; ?><br>
              State Name : <?= $data['company']['state_name']; ?>, Code : <?= $data['company']['state_code']; ?>
            </div>

            <!-- Consignee -->
            <div style="padding:5px 6px;border-bottom:1px solid #000;line-height:1.55;">
              Consignee (Ship to)<br>
              <b><?= $data['customer']['company_name']; ?></b><br>
              <?= $data['customer']['address']; ?><br>
              <?= ($data['customer']['address_2'] != '') ? $data['customer']['address_2'] . '<br>' : ''; ?>
              <?= $data['customer']['city_name']; ?> - <?= $data['customer']['pincode']; ?><br>
              Mob - <?= $data['customer']['owner_mobile']; ?><br>
              GSTIN/UIN &nbsp;&nbsp;&nbsp;&nbsp;: <?= $data['customer']['gst_no']; ?><br>
              State Name &nbsp;&nbsp;: <?= $data['customer']['state_name']; ?>, Code : <?= $data['customer']['state_id']; ?>
            </div>

            <!-- Buyer -->
            <div style="padding:5px 6px;line-height:1.55;">
              Buyer (Bill to)<br>
              <b><?= $data['customer']['company_name']; ?></b><br>
              <?= $data['customer']['address']; ?><br>
              <?= ($data['customer']['address_2'] != '') ? $data['customer']['address_2'] . '<br>' : ''; ?>
              <?= $data['customer']['city_name']; ?> - <?= $data['customer']['pincode']; ?><br>
              Mob - <?= $data['customer']['owner_mobile']; ?><br>
              GSTIN/UIN &nbsp;&nbsp;&nbsp;&nbsp;: <?= $data['customer']['gst_no']; ?><br>
              State Name &nbsp;&nbsp;: <?= $data['customer']['state_name']; ?>, Code : <?= $data['customer']['state_id']; ?>
            </div>

          </td>

          <!-- RIGHT: Invoice Meta Grid -->
          <td style="width:50%;border:1px solid #000;padding:0px !important;vertical-align:top;">
            <table style="width:100%;border-collapse:collapse;font-size:10px;">
              <tr>
                <td style="width:50%;border-bottom:1px solid #000;border-right:1px solid #000;padding:4px 6px;text-align:left;">Invoice No.</td>
                <td style="width:50%;border-bottom:1px solid #000;padding:4px 6px;text-align:left;">Dated</td>
              </tr>
              <tr>
                <td style="border-bottom:1px solid #000;border-right:1px solid #000;padding:4px 6px;text-align:left;"><b><?= $data['order_no']; ?></b></td>
                <td style="border-bottom:1px solid #000;padding:4px 6px;text-align:left;"><b><?= date('d-M-y', strtotime($data['date'])); ?></b></td>
              </tr>
              <tr>
                <td style="border-bottom:1px solid #000;border-right:1px solid #000;padding:4px 6px;text-align:left;">Delivery Note<br><b><?= $data['unique_id']; ?></b></td>
                <td style="border-bottom:1px solid #000;padding:4px 6px;text-align:left;">Mode/Terms of Payment<br><b><?= $data['narration']; ?></b></td>
              </tr>
              <tr>
                <td style="border-bottom:1px solid #000;border-right:1px solid #000;padding:4px 6px;text-align:left;">Reference No. &amp; Date.<br><b><?= $data['refrence_no']; ?></b></td>
                <td style="border-bottom:1px solid #000;padding:4px 6px;text-align:left;">Other References</td>
              </tr>
              <tr>
                <td style="border-bottom:1px solid #000;border-right:1px solid #000;padding:4px 6px;text-align:left;">Buyer's Order No.</td>
                <td style="border-bottom:1px solid #000;padding:4px 6px;text-align:left;">Dated</td>
              </tr>
              <tr>
                <td style="border-bottom:1px solid #000;border-right:1px solid #000;padding:4px 6px;text-align:left;">Dispatch Doc No.</td>
                <td style="border-bottom:1px solid #000;padding:4px 6px;text-align:left;">Delivery Note Date</td>
              </tr>
              <tr>
                <td style="border-bottom:1px solid #000;border-right:1px solid #000;padding:4px 6px;text-align:left;">Dispatched through</td>
                <td style="border-bottom:1px solid #000;padding:4px 6px;text-align:left;">Destination<br><b><?= $data['customer']['city_name']; ?></b></td>
              </tr>
              <tr>
                <td colspan="2" style="text-align:left;padding:4px 6px;">Terms of Delivery<br><b><?= $data['remark']; ?></b></td>
              </tr>
            </table>
          </td>

        </tr>
      </tbody>
    </table>

    <!-- Items Table -->
    <table style="width:100%;border-collapse:collapse;font-size:10px;">
      <thead>
        <tr>
          <td style="border:1px solid;padding:4px;text-align:center;"><b>Sl No</b></td>
          <td style="border:1px solid;padding:4px;text-align:center;"><b>Description of Goods</b></td>
          <td style="border:1px solid;padding:4px;text-align:center;"><b>HSN/SAC</b></td>
          <td style="border:1px solid;padding:4px;text-align:center;"><b>Quantity</b></td>
          <td style="border:1px solid;padding:4px;text-align:center;"><b>Rate</b></td>
          <td style="border:1px solid;padding:4px;text-align:center;"><b>Per</b></td>
          <td style="border:1px solid;padding:4px;text-align:center;"><b>Amount</b></td>
        </tr>
      </thead>

      <tbody>
        <?php 
          $total_qty = 0;
          $total_gst = 0;
          $total_total_amount = 0;
          $i = 1;
          foreach ($data['products'] as $product): 
            $total_qty += $product['qtys'];
            $total_gst += $product['gst_amount'];
            $total_total_amount += $product['total'];
            $rate = ($product['qtys'] > 0) ? ($product['amount'] / $product['qtys']) : 0;
        ?>
        <tr>
          <td style="border:1px solid;padding:4px;text-align:center;"><?= $i++; ?></td>
          <td style="border:1px solid;padding:4px;">
            <?= $product['product_name']; ?>
          </td>
          <td style="border:1px solid;padding:4px;text-align:center;"><?= $product['hsn_code']; ?></td>
          <td style="border:1px solid;padding:4px;text-align:center;"><?= $product['qtys']; ?> Pc</td>
          <td style="border:1px solid;padding:4px;text-align:right;"><?= number_format($rate, 2); ?></td>
          <td style="border:1px solid;padding:4px;text-align:center;">Pc</td>
          <td style="border:1px solid;padding:4px;text-align:right;"><?= number_format($product['amount'], 2); ?></td>
        </tr>
        <?php endforeach; ?>

        <!-- Tax Rows -->
        <?php if ($data['gst_type'] == 'IGST' && $total_gst > 0): ?>
        <tr>
          <td style="border:1px solid;padding:4px;"></td>
          <td style="border:1px solid;padding:4px;"><b>IGST</b></td>
          <td style="border:1px solid;padding:4px;"></td>
          <td style="border:1px solid;padding:4px;"></td>
          <td style="border:1px solid;padding:4px;"></td>
          <td style="border:1px solid;padding:4px;"></td>
          <td style="border:1px solid;padding:4px;text-align:right;"><?= number_format($total_gst, 2); ?></td>
        </tr>
        <?php elseif ($data['gst_type'] == 'Central GST / State GST'): ?>
        <?php if ($total_gst > 0): ?>
        <tr>
          <td style="border:1px solid;padding:4px;"></td>
          <td style="border:1px solid;padding:4px;"><b>CGST</b></td>
          <td style="border:1px solid;padding:4px;"></td>
          <td style="border:1px solid;padding:4px;"></td>
          <td style="border:1px solid;padding:4px;"></td>
          <td style="border:1px solid;padding:4px;"></td>
          <td style="border:1px solid;padding:4px;text-align:right;"><?= number_format($total_gst / 2, 2); ?></td>
        </tr>
        <tr>
          <td style="border:1px solid;padding:4px;"></td>
          <td style="border:1px solid;padding:4px;"><b>SGST</b></td>
          <td style="border:1px solid;padding:4px;"></td>
          <td style="border:1px solid;padding:4px;"></td>
          <td style="border:1px solid;padding:4px;"></td>
          <td style="border:1px solid;padding:4px;"></td>
          <td style="border:1px solid;padding:4px;text-align:right;"><?= number_format($total_gst / 2, 2); ?></td>
        </tr>
        <?php endif; ?>
        <?php endif; ?>

        <?php if ($data['other_charges_amount'] > 0): ?>
        <tr>
          <td style="border:1px solid;padding:4px;"></td>
          <td style="border:1px solid;padding:4px;"><b><?= $data['other_charges_name']; ?></b></td>
          <td style="border:1px solid;padding:4px;"></td>
          <td style="border:1px solid;padding:4px;"></td>
          <td style="border:1px solid;padding:4px;"></td>
          <td style="border:1px solid;padding:4px;"></td>
          <td style="border:1px solid;padding:4px;text-align:right;"><?= number_format($data['other_charges_amount'], 2); ?></td>
        </tr>
        <?php endif; ?>
      </tbody>

      <tfoot>
        <?php $final_grand_total = $total_total_amount + $data['other_charges_amount']; ?>
        <tr>
          <td colspan="3" style="border:1px solid;padding:4px;text-align:right;"><b>Total</b></td>
          <td style="border:1px solid;padding:4px;text-align:center;"><?= $total_qty; ?> Pc</td>
          <td style="border:1px solid;padding:4px;"></td>
          <td style="border:1px solid;padding:4px;"></td>
          <td style="border:1px solid;padding:4px;text-align:right;"><b>₹ <?= number_format($final_grand_total, 2); ?></b></td>
        </tr>
      </tfoot>
    </table>

    <!-- Amount in Words -->
    <table style="width:100%;border-collapse:collapse;font-size:10px;">
      <tr>
        <td style="border:1px solid;padding:5px;">
          <b>Amount Chargeable (in words)</b><br>
          <?= rupees_word($final_grand_total); ?>
        </td>
      </tr>
    </table>

    <!-- Tax Summary Table -->
    <table style="width:100%;border-collapse:collapse;font-size:10px;">
      <thead>
        <tr>
          <td style="border:1px solid;padding:4px;text-align:center;"><b>HSN/SAC</b></td>
          <td style="border:1px solid;padding:4px;text-align:center;"><b>Taxable Value</b></td>
          <?php if ($data['gst_type'] == 'IGST'): ?>
          <td style="border:1px solid;padding:4px;text-align:center;"><b>IGST Rate</b></td>
          <td style="border:1px solid;padding:4px;text-align:center;"><b>IGST Amount</b></td>
          <?php else: ?>
          <td style="border:1px solid;padding:4px;text-align:center;"><b>CGST Rate</b></td>
          <td style="border:1px solid;padding:4px;text-align:center;"><b>CGST Amount</b></td>
          <td style="border:1px solid;padding:4px;text-align:center;"><b>SGST Rate</b></td>
          <td style="border:1px solid;padding:4px;text-align:center;"><b>SGST Amount</b></td>
          <?php endif; ?>
          <td style="border:1px solid;padding:4px;text-align:center;"><b>Total Tax Amount</b></td>
        </tr>
      </thead>

      <tbody>
        <?php 
          $sum_taxable = 0;
          $sum_gst = 0;
          foreach ($data['products'] as $product): 
            if ($product['gst_amount'] > 0):
              $sum_taxable += $product['amount'];
              $sum_gst += $product['gst_amount'];
        ?>
        <tr>
          <td style="border:1px solid;padding:4px;text-align:center;"><?= $product['hsn_code']; ?></td>
          <td style="border:1px solid;padding:4px;text-align:right;"><?= number_format($product['amount'], 2); ?></td>
          <?php if ($data['gst_type'] == 'IGST'): ?>
          <td style="border:1px solid;padding:4px;text-align:center;"><?= $product['gst']; ?>%</td>
          <td style="border:1px solid;padding:4px;text-align:right;"><?= number_format($product['gst_amount'], 2); ?></td>
          <?php else: ?>
          <td style="border:1px solid;padding:4px;text-align:center;"><?= $product['gst']/2; ?>%</td>
          <td style="border:1px solid;padding:4px;text-align:right;"><?= number_format($product['gst_amount']/2, 2); ?></td>
          <td style="border:1px solid;padding:4px;text-align:center;"><?= $product['gst']/2; ?>%</td>
          <td style="border:1px solid;padding:4px;text-align:right;"><?= number_format($product['gst_amount']/2, 2); ?></td>
          <?php endif; ?>
          <td style="border:1px solid;padding:4px;text-align:right;"><?= number_format($product['gst_amount'], 2); ?></td>
        </tr>
        <?php 
            endif;
          endforeach; 
        ?>

        <tr>
          <td style="border:1px solid;padding:4px;text-align:right;"><b>Total</b></td>
          <td style="border:1px solid;padding:4px;text-align:right;"><b><?= number_format($sum_taxable, 2); ?></b></td>
          <?php if ($data['gst_type'] == 'IGST'): ?>
          <td style="border:1px solid;padding:4px;"></td>
          <td style="border:1px solid;padding:4px;text-align:right;"><b><?= number_format($sum_gst, 2); ?></b></td>
          <?php else: ?>
          <td style="border:1px solid;padding:4px;"></td>
          <td style="border:1px solid;padding:4px;text-align:right;"><b><?= number_format($sum_gst/2, 2); ?></b></td>
          <td style="border:1px solid;padding:4px;"></td>
          <td style="border:1px solid;padding:4px;text-align:right;"><b><?= number_format($sum_gst/2, 2); ?></b></td>
          <?php endif; ?>
          <td style="border:1px solid;padding:4px;text-align:right;"><b><?= number_format($sum_gst, 2); ?></b></td>
        </tr>
      </tbody>
    </table>

    <!-- Footer Section -->
    <table style="width:100%;border-collapse:collapse;font-size:10px;">
      <tr>
        <td style="width:60%;border:1px solid;padding:6px;">
          <b>Tax Amount (in words):</b><br>
          <?= rupees_word($sum_gst); ?>
          <br><br>
          <b>Declaration</b><br>
          We declare that this invoice shows the actual price of the goods
          described and that all particulars are true and correct.
        </td>

        <td style="width:40%;border:1px solid;padding:6px;text-align:right;vertical-align:bottom;">
          for <?= $data['company']['name']; ?>
          <br><br><br><br>
          <b>Authorised Signatory</b>
        </td>
      </tr>
    </table>

    <!-- Computer Generated Note -->
    <div style="text-align:center;font-size:10px;margin-top:5px;">
      This is a Computer Generated Invoice
    </div>

  </div>
</body>

</html>

