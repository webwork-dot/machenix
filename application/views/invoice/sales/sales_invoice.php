
<html xmlns="http://www.w3.org/1999/xhtml" moznomarginboxes="" mozdisallowselectionprint="">

<head>
  <title>Sales Invoice</title>
  <link rel="stylesheet" href="<?= base_url(); ?>assets/pdf/bootstrap.min.css">
  <link rel="stylesheet" href="<?= base_url(); ?>assets/pdf/custom.css">
</head>

<body>
  <div style="background:#ffffff;margin:0 auto;width:100%;padding:0px;">

    <!-- Company Header (Seller) -->
    <table style="width:100%; margin-bottom:10px;">
      <tbody>
        <tr>
          <td style="text-align:center;padding:0px 3px;line-height:1.2;">
            <span style="color:#000;font-size:14px;font-weight:bold;">
              GUANGZHOU WEI GE MACHINERY EQUIPMENT CO., LIMITED
            </span><br>
            <span style="color:#000;font-size:11px;font-weight:bold;">
              Supplier Address Here
            </span><br>
            <span style="color:#000;font-size:11px;font-weight:bold;">
              email@example.com | Tel: +86-123456789 | Contact: John Doe
            </span>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Commercial Invoice Title -->
    <table style="width:100%; margin-bottom:15px;">
      <tbody>
        <tr>
          <td style="width:100%;text-align:center;padding:8px 3px;line-height:1.0;" colspan="6">
            <b style="font-size:16px;color:#000;">COMMERCIAL INVOICE</b>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Buyer Information and Invoice Details -->
    <table style="width:100%; margin-bottom:10px;">
      <tbody>
        <tr>
          <td style="width:50%;text-align:left;padding:5px 3px;line-height:1.3;" colspan="3">
            <span style="color:#000;font-size:11px;font-weight:bold;"><b>Buyer:</b> Central Exportrade</span><br>
            <span style="color:#000;font-size:11px;font-weight:bold;">
              <b>Add:</b> Buyer Address Here
            </span><br>
            <span style="color:#000;font-size:11px;font-weight:bold;"><b>Tel:</b> +91-000000000</span><br>
            <span style="color:#000;font-size:11px;font-weight:bold;"><b>E-mail:</b> buyer@email.com</span>
          </td>

          <td style="width:50%;text-align:left;padding:5px 3px;line-height:1.3;" colspan="3">
            <span style="color:#000;font-size:11px;font-weight:bold;"><b>C/I No:</b> CI-001</span><br>
            <span style="color:#000;font-size:11px;font-weight:bold;"><b>Date:</b> 16 Mar 2026</span><br>
            <span style="color:#000;font-size:11px;font-weight:bold;"><b>Terms Of Price:</b> FOB</span><br>
            <span style="color:#000;font-size:11px;font-weight:bold;"><b>Terms Of Payment:</b> T/T</span>
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

