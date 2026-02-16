<?php 
// Get data from page_data
$data = isset($data) ? $data : [];
$supplier = $data;
$company_info = isset($data['company_info']) ? $data['company_info'] : [];
$products = isset($data['products']) ? $data['products'] : [];
$invoice_type = isset($data['invoice_type']) ? $data['invoice_type'] : '1';

// Supplier stamp image
$stamp_image = '-';
if (!empty($supplier['signature_image'])) {
    // $stamp_image = $supplier['signature_image'];
    $stamp_image = FCPATH . $supplier['signature_image'];
    $stamp_image = "data:image/png;base64," . base64_encode(file_get_contents($stamp_image));
} 

// echo ($stamp_image);


// Format supplier address
$supplier_address = '';
if (!empty($supplier['address'])) {
    $supplier_address = $supplier['address'];
    if (!empty($supplier['address_2'])) {
        $supplier_address .= ', ' . $supplier['address_2'];
    }
    if (!empty($supplier['address_3'])) {
        $supplier_address .= ', ' . $supplier['address_3'];
    }
    if (!empty($supplier['city_name'])) {
        $supplier_address .= ', ' . $supplier['city_name'];
    }
    if (!empty($supplier['state_name'])) {
        $supplier_address .= ', ' . $supplier['state_name'];
    }
    if (!empty($supplier['pincode'])) {
        $supplier_address .= ', ' . $supplier['pincode'];
    }
}

// Format supplier contact info
$supplier_contact = '';
$contact_parts = [];
if (!empty($supplier['contact_name'])) {
    $contact_parts[] = 'Contact: ' . $supplier['contact_name'];
}
if (!empty($supplier['contact_no'])) {
    $contact_parts[] = 'Tel: ' . $supplier['contact_no'];
}
// Note: Email and mobile might not be in supplier table, using contact_no as fallback

// Format company address
$company_address = '';
if (!empty($company_info['address'])) {
    $company_address = $company_info['address'];
    if (!empty($company_info['address_2'])) {
        $company_address .= ', ' . $company_info['address_2'];
    }
    if (!empty($company_info['address_3'])) {
        $company_address .= ', ' . $company_info['address_3'];
    }
    if (!empty($company_info['city_name'])) {
        $company_address .= ', ' . $company_info['city_name'];
    }
    if (!empty($company_info['state_name'])) {
        $company_address .= ', ' . $company_info['state_name'];
    }
    if (!empty($company_info['pincode'])) {
        $company_address .= '. ' . $company_info['pincode'];
    }
}

// Format invoice date
$invoice_date_formatted = '';
if (!empty($company_info['invoice_date'])) {
    $invoice_date_formatted = date('Y-m-d', strtotime($company_info['invoice_date']));
} else {
    $invoice_date_formatted = date('Y-m-d');
}

// Get invoice information from po_products
$invoice_no = '-';
$invoice_date_formatted = '-';
$payment_terms = '90 Days';
$price_terms = '-';

// Get invoice data from first product (all products in same invoice should have same invoice info)
if (!empty($products) && count($products) > 0) {
    $first_product = $products[0];
    $invoice_no = isset($first_product['invoice']) ? $first_product['invoice'] : '';
    if (!empty($first_product['invoice_date'])) {
        $invoice_date_formatted = date('Y-m-d', strtotime($first_product['invoice_date']));
    }
    $payment_terms = isset($first_product['invoice_terms']) ? $first_product['invoice_terms'] : '';
    $price_terms = isset($first_product['invoice_price_terms']) ? $first_product['invoice_price_terms'] : '';
}

// Default values if still empty
if (empty($invoice_date_formatted)) {
    $invoice_date_formatted = date('Y-m-d');
}

?>
<html xmlns="http://www.w3.org/1999/xhtml" moznomarginboxes="" mozdisallowselectionprint="">

<head>
  <title>Commercial Invoice</title>
  <link rel="stylesheet" href="<?= base_url(); ?>assets/pdf/bootstrap.min.css">
  <link rel="stylesheet" href="<?= base_url(); ?>assets/pdf/custom.css">
</head>

<body>
  <div style="background: none repeat scroll 0 0 #ffffff;margin: 0 auto;width: 100%;padding: 0px;">
    <!-- Company Header (Seller) -->
    <table style="width: 100%; margin-bottom: 10px;">
      <tbody>
        <tr>
          <td style="text-align: center; padding: 0px 3px; line-height: 1.2;">
            <span style="color: #000; font-size: 14px; font-weight: bold;">
              <?php echo !empty($supplier['name']) ? htmlspecialchars($supplier['name']) : 'GUANGZHOU WEI GE MACHINERY EQUIPMENT CO., LIMITED'; ?>
            </span><br>
            <?php if (!empty($supplier_address)): ?>
            <span style="color: #000; font-size: 11px; font-weight: bold;">
              <?php echo htmlspecialchars($supplier_address); ?>
            </span><br>
            <?php endif; ?>
            <?php if (!empty($supplier_contact) || !empty($supplier['contact_no'])): ?>
            <span style="color: #000; font-size: 11px; font-weight: bold;">
              <?php 
              $contact_display = [];
              if (!empty($supplier['email'])) {
                  $contact_display[] = htmlspecialchars($supplier['email']);
              }
              if (!empty($supplier['contact_no'])) {
                  $contact_display[] = 'Tel: ' . htmlspecialchars($supplier['contact_no']);
              }
              if (!empty($supplier['contact_name'])) {
                  $contact_display[] = 'Contact: ' . htmlspecialchars($supplier['contact_name']);
              }
              echo implode(' | ', $contact_display);
              ?>
            </span>
            <?php endif; ?>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Commercial Invoice Title -->
    <table style="width: 100%; margin-bottom: 15px;">
      <tbody>
        <tr>
          <td style="width:100%;text-align: center;padding: 8px 3px; line-height: 1.0; height: auto;" colspan="6">
            <b style="font-size: 16px;color: #000;">COMMERCIAL INVOICE</b>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Buyer Information and Invoice Details -->
    <table style="width: 100%; margin-bottom: 10px;">
      <tbody>
        <tr>
          <td style="width:50%;text-align: left;padding: 5px 3px; line-height: 1.3; height: auto;" colspan="3">
            <span style="color: #000;font-size: 11px; font-weight: bold;"><b>Buyer:</b> <?php echo !empty($company_info['name']) ? htmlspecialchars($company_info['name']) : 'Central Exportrade'; ?></span><br>
            <?php if (!empty($company_address)): ?>
            <span style="color: #000;font-size: 11px; font-weight: bold;">
              <b>Add:</b> <?php echo htmlspecialchars($company_address); ?>
            </span><br>
            <?php endif; ?>
            <?php if (!empty($company_info['contact_no'])): ?>
            <span style="color: #000;font-size: 11px; font-weight: bold;"><b>Tel:</b> <?php echo htmlspecialchars($company_info['contact_no']); ?></span><br>
            <?php endif; ?>
            <?php if (!empty($company_info['email'])): ?>
            <span style="color: #000;font-size: 11px; font-weight: bold;"><b>E-mail:</b> <?php echo htmlspecialchars($company_info['email']); ?></span>
            <?php endif; ?>
          </td>
          <td style="width:50%;text-align: left;padding: 5px 3px; line-height: 1.3; height: auto;" colspan="3">
            <?php if (!empty($invoice_no)): ?>
            <span style="color: #000;font-size: 11px; font-weight: bold;"><b>C/I No:</b> <?php echo htmlspecialchars($invoice_no); ?></span><br>
            <?php endif; ?>
            <span style="color: #000;font-size: 11px; font-weight: bold;"><b>Date:</b> <?php echo $invoice_date_formatted; ?></span><br>
            <span style="color: #000;font-size: 11px; font-weight: bold;"><b>Terms Of Price:</b> <?php echo htmlspecialchars($price_terms); ?></span><br>
            <?php if ($invoice_type == '1'): ?>
              <span style="color: #000;font-size: 11px; font-weight: bold;"><b>Terms Of Payment:</b> <?php echo htmlspecialchars($payment_terms); ?></span>
              <?php endif; ?>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Products Table -->
    <table style="width: 100%; border-collapse: collapse; margin-top: 5px;">
      <thead>
        <tr>
          <td style="text-align: center;padding: 4px 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; background-color: #f0f0f0;">
            <b>No.</b>
          </td>
          <td style="text-align: center;padding: 4px 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; background-color: #f0f0f0;">
            <b>Product Name</b>
          </td>
          <td style="text-align: center;padding: 4px 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; background-color: #f0f0f0;">
            <b>Item No</b>
          </td>
          <td style="text-align: center;padding: 4px 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; background-color: #f0f0f0;">
            <b>Quantity</b>
          </td>
          <td style="text-align: center;padding: 4px 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; background-color: #f0f0f0;">
            <b>Unit Price<br/>(USD)</b>
          </td>
          <td style="text-align: center;padding: 4px 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; background-color: #f0f0f0;">
            <b>Total Amount<br/>(USD)</b>
          </td>
        </tr>
      </thead>
      <tbody>
        <?php 
        $sr_no = 1;
        $total_qty = 0;
        $total_amount = 0;
        
        if (!empty($products)): 
            foreach ($products as $product): 
                $official_ci_qty = isset($product['official_ci_qty']) ? intval($product['official_ci_qty']) : 0;
                $official_ci_unit_price_usd = isset($product['official_ci_unit_price_usd']) ? floatval($product['official_ci_unit_price_usd']) : 0;
                $total_amount_usd = isset($product['total_amount_usd']) ? floatval($product['total_amount_usd']) : 0;
                
                // Accumulate totals
                $total_qty += $official_ci_qty;
                $total_amount += $total_amount_usd;
        ?>
        <tr>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;"><?php echo $sr_no++; ?></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;"><?php echo htmlspecialchars($product['product_name'] ?? '-'); ?></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;"><?php echo htmlspecialchars($product['item_code'] ?? '-'); ?></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;"><?php echo $official_ci_qty; ?></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;">US$<?php echo number_format($official_ci_unit_price_usd, 2, '.', ','); ?></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;">US$<?php echo number_format($total_amount_usd, 2, '.', ','); ?></td>
        </tr>
        <?php 
            endforeach; 
        endif; 
        ?>
      </tbody>
      <tfoot>
        <!-- Totals Row -->
        <?php 
        // Recalculate totals from all products
        $total_qty = 0;
        $total_amount = 0;
        
        if (!empty($products)) {
            foreach ($products as $product) {
                $official_ci_qty = isset($product['official_ci_qty']) ? intval($product['official_ci_qty']) : 0;
                $total_amount_usd = isset($product['total_amount_usd']) ? floatval($product['total_amount_usd']) : 0;
                
                $total_qty += $official_ci_qty;
                $total_amount += $total_amount_usd;
            }
        }
        ?>
        <tr>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;"></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;">Total</td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;"></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;"><?php echo $total_qty; ?></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;"></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;">US$<?php echo number_format($total_amount, 2, '.', ','); ?></td>
        </tr>
      </tfoot>
    </table>

    <!-- Bank Details Section -->
    <table style="width: 100%; margin-top: 20px; margin-bottom: 20px;">
      <tbody>
        <tr>
          <td style="width:50%;text-align: left; padding: 5px 3px; line-height: 1.3;">
            <span style="color: #000; font-size: 12px; font-weight: bold;">BANK DETAILS</span><br><br>
            <span style="color: #000; font-size: 11px; font-weight: bold;">
              <?php if (!empty($supplier['beneficiary'])): ?>
              1. BENEFICIARY: <?php echo htmlspecialchars($supplier['beneficiary']); ?><br>
              <?php else: ?>
              1. BENEFICIARY: <?php echo !empty($supplier['name']) ? htmlspecialchars($supplier['name']) : '-'; ?><br>
              <?php endif; ?>
              <?php if (!empty($supplier['account_no'])): ?>
              2. ACCOUNT NO: <?php echo htmlspecialchars($supplier['account_no']); ?><br>
              <?php endif; ?>
              <?php if (!empty($supplier['advising_bank'])): ?>
              3. ADVISING BANK: <?php echo htmlspecialchars($supplier['advising_bank']); ?><br>
              <?php endif; ?>
              <?php if (!empty($supplier['bank_address'])): ?>
              4. BANK ADDRESS: <?php echo htmlspecialchars($supplier['bank_address']); ?><br>
              <?php endif; ?>
              <?php if (!empty($supplier['swift_code'])): ?>
              5. SWIFT CODE: <?php echo htmlspecialchars($supplier['swift_code']); ?>
              <?php endif; ?>
            </span>
          </td>
          <td style="width:50%;text-align: right; padding: 10px 3px; line-height: 1.2; vertical-align: top;">
              <?php if (!empty($stamp_image) && $stamp_image != '-'): ?>
                <img src="<?= $stamp_image; ?>" style="max-width: 150px; height: auto; margin-top: 10px;" >
                <br><br>
              <?php endif; ?>
              <span style="color: #000; font-size: 12px; font-weight: bold;">
                BUYER: <?php echo !empty($company_info['name']) ? htmlspecialchars($company_info['name']) : '-'; ?>
              </span>
            </td>
        </tr>
      </tbody>
    </table>

    <!-- Seller and Buyer Section at Bottom with Stamp -->
    <table style="width: 100%; margin-top: 30px;">
      <tbody>
        <tr>
          <td style="width:50%;text-align: left; padding: 10px 3px; line-height: 1.2; vertical-align: top;">
            <span style="color: #000; font-size: 12px; font-weight: bold;">
              SELLER: <?php echo !empty($supplier['name']) ? htmlspecialchars($supplier['name']) : '-'; ?>
            </span>
          </td>
          
        </tr>
      </tbody>
    </table>
  </div>
</body>

</html>

