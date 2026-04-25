
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
          <td style="width:50%;border:1px solid #000;padding:0;vertical-align:top;">

            <!-- Seller -->
            <div style="padding:5px 6px;border-bottom:1px solid #000;line-height:1.55;">
              <b>Caterbell Industries 2023-24</b><br>
              NO 25, BHASKAR LAYOUT, Anjanapura Main Road,<br>
              AVALAHALLI VILLAGE, Bengaluru, Bengaluru Urban<br>
              Karnataka, 560062<br>
              GSTIN/UIN: 29EFMPK8325B1Z9<br>
              State Name : Karnataka, Code : 29
            </div>

            <!-- Consignee -->
            <div style="padding:5px 6px;border-bottom:1px solid #000;line-height:1.55;">
              Consignee (Ship to)<br>
              <b>Kumar Food Machinery</b><br>
              Papanayakanpalayam<br>
              Coimbatore - 641037<br>
              Mob - 97895 17516<br>
              GSTIN/UIN &nbsp;&nbsp;&nbsp;&nbsp;: 33AINPP7824Q1ZE<br>
              State Name &nbsp;&nbsp;: Tamil Nadu, Code : 33
            </div>

            <!-- Buyer -->
            <div style="padding:5px 6px;line-height:1.55;">
              Buyer (Bill to)<br>
              <b>Kumar Food Machinery</b><br>
              Papanayakanpalayam<br>
              Coimbatore - 641037<br>
              Mob - 97895 17516<br>
              GSTIN/UIN &nbsp;&nbsp;&nbsp;&nbsp;: 33AINPP7824Q1ZE<br>
              State Name &nbsp;&nbsp;: Tamil Nadu, Code : 33
            </div>

          </td>

          <!-- RIGHT: Invoice Meta Grid -->
          <td style="width:50%;border:1px solid #000;padding:0;vertical-align:top;">
            <table style="width:100%;border-collapse:collapse;font-size:10px;">
              <tr>
                <td style="width:50%;border-bottom:1px solid #000;border-right:1px solid #000;padding:4px 6px;">Invoice No.</td>
                <td style="width:50%;border-bottom:1px solid #000;padding:4px 6px;">Dated</td>
              </tr>
              <tr>
                <td style="border-bottom:1px solid #000;border-right:1px solid #000;padding:4px 6px;"><b>830/2025-26</b></td>
                <td style="border-bottom:1px solid #000;padding:4px 6px;"><b>23-Feb-26</b></td>
              </tr>
              <tr>
                <td style="border-bottom:1px solid #000;border-right:1px solid #000;padding:4px 6px;">Delivery Note</td>
                <td style="border-bottom:1px solid #000;padding:4px 6px;">Mode/Terms of Payment</td>
              </tr>
              <tr>
                <td style="border-bottom:1px solid #000;border-right:1px solid #000;padding:4px 6px;">Reference No. &amp; Date.</td>
                <td style="border-bottom:1px solid #000;padding:4px 6px;">Other References</td>
              </tr>
              <tr>
                <td style="border-bottom:1px solid #000;border-right:1px solid #000;padding:4px 6px;">Buyer's Order No.</td>
                <td style="border-bottom:1px solid #000;padding:4px 6px;">Dated</td>
              </tr>
              <tr>
                <td style="border-bottom:1px solid #000;border-right:1px solid #000;padding:4px 6px;">Dispatch Doc No.</td>
                <td style="border-bottom:1px solid #000;padding:4px 6px;">Delivery Note Date</td>
              </tr>
              <tr>
                <td style="border-bottom:1px solid #000;border-right:1px solid #000;padding:4px 6px;">Dispatched through</td>
                <td style="border-bottom:1px solid #000;padding:4px 6px;">Destination</td>
              </tr>
              <tr>
                <td colspan="2" style="padding:4px 6px;">Terms of Delivery</td>
              </tr>
            </table>
          </td>

        </tr>
      </tbody>
    </table>

    <!-- Items Table -->
    <table style="width:100%;border-collapse:collapse;font-size:10px;margin-top:5px;">
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
        <tr>
          <td style="border:1px solid;padding:4px;text-align:center;">1</td>
          <td style="border:1px solid;padding:4px;">
            Spiral Mixer 40L (HS-40L)
          </td>
          <td style="border:1px solid;padding:4px;text-align:center;">84381010</td>
          <td style="border:1px solid;padding:4px;text-align:center;">1 Pc</td>
          <td style="border:1px solid;padding:4px;text-align:right;">21,000.00</td>
          <td style="border:1px solid;padding:4px;text-align:center;">Pc</td>
          <td style="border:1px solid;padding:4px;text-align:right;">21,000.00</td>
        </tr>

        <!-- Tax Row -->
        <tr>
          <td style="border:1px solid;padding:4px;"></td>
          <td style="border:1px solid;padding:4px;"><b>IGST</b></td>
          <td style="border:1px solid;padding:4px;"></td>
          <td style="border:1px solid;padding:4px;"></td>
          <td style="border:1px solid;padding:4px;"></td>
          <td style="border:1px solid;padding:4px;"></td>
          <td style="border:1px solid;padding:4px;text-align:right;">3,780.00</td>
        </tr>
      </tbody>

      <tfoot>
        <tr>
          <td colspan="3" style="border:1px solid;padding:4px;text-align:right;"><b>Total</b></td>
          <td style="border:1px solid;padding:4px;text-align:center;">1 Pc</td>
          <td style="border:1px solid;padding:4px;"></td>
          <td style="border:1px solid;padding:4px;"></td>
          <td style="border:1px solid;padding:4px;text-align:right;"><b>₹ 24,780.00</b></td>
        </tr>
      </tfoot>
    </table>

    <!-- Amount in Words -->
    <table style="width:100%;border-collapse:collapse;font-size:10px;">
      <tr>
        <td style="border:1px solid;padding:5px;">
          <b>Amount Chargeable (in words)</b><br>
          INR Twenty Four Thousand Seven Hundred Eighty Only
        </td>
      </tr>
    </table>

    <!-- Tax Summary Table -->
    <table style="width:100%;border-collapse:collapse;font-size:10px;">
      <thead>
        <tr>
          <td style="border:1px solid;padding:4px;text-align:center;"><b>HSN/SAC</b></td>
          <td style="border:1px solid;padding:4px;text-align:center;"><b>Taxable Value</b></td>
          <td style="border:1px solid;padding:4px;text-align:center;"><b>IGST Rate</b></td>
          <td style="border:1px solid;padding:4px;text-align:center;"><b>IGST Amount</b></td>
          <td style="border:1px solid;padding:4px;text-align:center;"><b>Total Tax Amount</b></td>
        </tr>
      </thead>

      <tbody>
        <tr>
          <td style="border:1px solid;padding:4px;text-align:center;">84381010</td>
          <td style="border:1px solid;padding:4px;text-align:right;">21,000.00</td>
          <td style="border:1px solid;padding:4px;text-align:center;">18%</td>
          <td style="border:1px solid;padding:4px;text-align:right;">3,780.00</td>
          <td style="border:1px solid;padding:4px;text-align:right;">3,780.00</td>
        </tr>

        <tr>
          <td style="border:1px solid;padding:4px;text-align:right;"><b>Total</b></td>
          <td style="border:1px solid;padding:4px;text-align:right;"><b>21,000.00</b></td>
          <td style="border:1px solid;padding:4px;"></td>
          <td style="border:1px solid;padding:4px;text-align:right;"><b>3,780.00</b></td>
          <td style="border:1px solid;padding:4px;text-align:right;"><b>3,780.00</b></td>
        </tr>
      </tbody>
    </table>

    <!-- Footer Section -->
    <table style="width:100%;border-collapse:collapse;font-size:10px;margin-top:5px;">
      <tr>
        <td style="width:60%;border:1px solid;padding:6px;">
          <b>Tax Amount (in words):</b><br>
          INR Three Thousand Seven Hundred Eighty Only
          <br><br>
          <b>Declaration</b><br>
          We declare that this invoice shows the actual price of the goods
          described and that all particulars are true and correct.
        </td>

        <td style="width:40%;border:1px solid;padding:6px;text-align:right;vertical-align:bottom;">
          for Caterbell Industries 2023-24
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

