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
  <!-- <link rel="stylesheet" href="<?= base_url(); ?>assets/pdf/custom.css"> -->
  <style>
    @font-face {
        font-family: 'OpenSans-Bold';
        src: url("<?= base_url('assets/fonts/OpenSans-Bold.ttf') ?>") format('truetype');
        font-weight: bold;
    }

    .bold-text {
        font-family: 'OpenSans-Bold', Arial, sans-serif;
        font-weight: bold;
    }
  </style>
</head>

<body>
  <div style="background: none repeat scroll 0 0 #ffffff;margin: 0 auto;width: 100%;padding: 0px 55px;">
    <!-- Company Header (Seller) -->
    <table style="width: 100%;">
      <tbody>
        <tr>
          <td style="text-align: center; padding: 0px 3px; line-height: 1;">
            <span class="bold-text" style="color: #000; font-size: 16px; line-height: 1.5;">
              <?php echo !empty($supplier['name']) ? html_entity_decode($supplier['name']) : ''; ?>
            </span><br>
            <?php if (!empty($supplier_address)): ?>
            <span class="bold-text" style="color: #000; font-size: 9px;">
              <?php echo html_entity_decode($supplier_address); ?>
            </span><br>
            <?php endif; ?>
            <?php if (!empty($supplier_contact) || !empty($supplier['contact_no'])): ?>
            <span class="bold-text" style="color: #000; font-size: 9px;">
              <?php 
              $contact_display = [];
              if (!empty($supplier['email'])) {
                  $contact_display[] = 'Email: ' . html_entity_decode($supplier['email']);
              }
              if (!empty($supplier['tel_no'])) {
                  $contact_display[] = 'Tel: ' . (($supplier['t_code']) ? '+' . $supplier['t_code'] : '') . ' ' . html_entity_decode($supplier['tel_no']);
              }
              if (!empty($supplier['contact_no'])) {
                  $contact_display[] = 'Mobile: ' . (($supplier['c_code']) ? '+' . $supplier['c_code'] : '') . ' ' . html_entity_decode($supplier['contact_no']);
              }
              if (!empty($supplier['contact_name'])) {
                  $contact_display[] = 'Contact: ' . html_entity_decode($supplier['contact_name']);
              }
              echo implode(', ', $contact_display);
              ?>
            </span>
            <?php endif; ?>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Commercial Invoice Title -->
    <table style="width: 100%;">
      <tbody>
        <tr>
          <td style="width:100%;text-align: center;padding: 8px 0px 5px 0px; line-height: 0.6; height: auto;" colspan="6">
            <span class="bold-text" style="font-size: 14px;color: #000;">COMMERCIAL INVOICE</span>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Buyer Information and Invoice Details -->
    <table style="width: 100%; margin-bottom: 10px;">
      <tbody>
        <tr>
          <td style="width:62%;text-align: left;padding: 5px 3px; line-height: 0.9; vertical-align: top;" colspan="3">
            <table style="width: 100%; border-collapse: collapse; border: none; margin: 0; padding: 0;">
              <tbody>
                <tr>
                  <td class="bold-text" style="width: 30%; color: #000; font-size: 11px; vertical-align: top; padding: 1px 0;">Buyer:</td>
                  <td class="bold-text" style="width: 70%; color: #000; font-size: 11px; vertical-align: top; padding: 1px 0;"><?php echo !empty($company_info['name']) ? html_entity_decode($company_info['name']) : 'Central Exportrade'; ?></td>
                </tr>
                <?php if (!empty($company_address)): ?>
                <tr>
                  <td class="bold-text" style="width: 30%; color: #000; font-size: 11px; vertical-align: top; padding: 1px 0;">Add:</td>
                  <td class="bold-text" style="width: 70%; color: #000; font-size: 11px; vertical-align: top; padding: 1px 0;"><?php echo html_entity_decode($company_address); ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($company_info['contact_no'])): ?>
                <tr>
                  <td class="bold-text" style="width: 30%; color: #000; font-size: 11px; vertical-align: top; padding: 1px 0;">Tel:</td>
                  <td class="bold-text" style="width: 70%; color: #000; font-size: 11px; vertical-align: top; padding: 1px 0;"><?php echo html_entity_decode($company_info['contact_no']); ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($company_info['email'])): ?>
                <tr>
                  <td class="bold-text" style="width: 30%; color: #000; font-size: 11px; vertical-align: top; padding: 1px 0;">E-mail:</td>
                  <td class="bold-text" style="width: 70%; color: #000; font-size: 11px; vertical-align: top; padding: 1px 0;"><?php echo html_entity_decode($company_info['email']); ?></td>
                </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </td>
          <td style="width:38%;text-align: left;padding: 5px 3px; line-height: 0.9; vertical-align: top;" colspan="3">
            <table style="width: 100%; border-collapse: collapse; border: none; margin: 0; padding: 0;">
              <tbody>
                <?php if (!empty($invoice_no)): ?>
                <tr>
                  <td class="bold-text" style="width: 50%; color: #000; font-size: 11px; vertical-align: top; padding: 1px 0;">C/I No:</td>
                  <td class="bold-text" style="width: 50%; color: #000; font-size: 11px; vertical-align: top; padding: 1px 0;"><?php echo html_entity_decode($invoice_no); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                  <td class="bold-text" style="width: 50%; color: #000; font-size: 11px; vertical-align: top; padding: 1px 0;">Date:</td>
                  <td class="bold-text" style="width: 50%; color: #000; font-size: 11px; vertical-align: top; padding: 1px 0;"><?php echo $invoice_date_formatted; ?></td>
                </tr>
                <tr>
                  <td class="bold-text" style="width: 50%; color: #000; font-size: 11px; vertical-align: top; padding: 1px 0;">Terms Of Price:</td>
                  <td class="bold-text" style="width: 50%; color: #000; font-size: 11px; vertical-align: top; padding: 1px 0;"><?php echo html_entity_decode($price_terms); ?></td>
                </tr>
                <?php if ($invoice_type == '1'): ?>
                <tr>
                  <td class="bold-text" style="width: 50%; color: #000; font-size: 11px; vertical-align: top; padding: 1px 0;">Terms Of Payment:</td>
                  <td class="bold-text" style="width: 50%; color: #000; font-size: 11px; vertical-align: top; padding: 1px 0;"><?php echo html_entity_decode($payment_terms); ?></td>
                </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Products Table -->
    <table style="width: 100%; border-collapse: collapse; margin-top: 5px;">
      <thead>
        <tr>
          <td class="bold-text" style="width: 12%;text-align: center;padding: 4px 3px; line-height: 0.8; height: auto;border:1px solid; font-size: 11px; color: #000;">
            No.
          </td>
          <td class="bold-text" style="width: 35%;text-align: center;padding: 4px 3px; line-height: 0.8; height: auto;border:1px solid; font-size: 11px; color: #000;">
            Product Name
          </td>
          <td class="bold-text" style="width: 15%;text-align: center;padding: 4px 3px; line-height: 0.8; height: auto;border:1px solid; font-size: 11px; color: #000;">
            Item No
          </td>
          <td class="bold-text" style="width: 10%;text-align: center;padding: 4px 0px; line-height: 0.8; height: auto;border:1px solid; font-size: 11px; color: #000;">
            Quantity
          </td>
          <td class="bold-text" style="width: 14%;text-align: center;padding: 4px 3px; line-height: 0.8; height: auto;border:1px solid; font-size: 11px; color: #000;">
            Unit Price<br/>(USD)
          </td>
          <td class="bold-text" style="width: 14%;text-align: center;padding: 4px 3px; line-height: 0.8; height: auto;border:1px solid; font-size: 11px; color: #000;">
            Total Amount<br/>(USD)
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
          <td class="bold-text" style="text-align: center;padding: 3px; line-height: 0.6; height: auto;border:1px solid; font-size: 10px; color: #000;"><?php echo $sr_no++; ?></td>
          <td class="bold-text" style="text-align: center;padding: 3px; line-height: 0.6; height: auto;border:1px solid; font-size: 10px; color: #000;"><?php echo html_entity_decode(explode('(', $product['product_name'])[0] ?? '-'); ?></td>
          <td class="bold-text" style="text-align: center;padding: 3px; line-height: 0.6; height: auto;border:1px solid; font-size: 10px; color: #000;"><?php echo html_entity_decode($product['item_code'] ?? '-'); ?></td>
          <td class="bold-text" style="text-align: center;padding: 3px; line-height: 0.6; height: auto;border:1px solid; font-size: 10px; color: #000;"><?php echo $official_ci_qty; ?></td>
          <td class="bold-text" style="text-align: center;padding: 3px; line-height: 0.6; height: auto;border:1px solid; font-size: 10px; color: #000;">US$<?php echo number_format($official_ci_unit_price_usd, 2, '.', ','); ?></td>
          <td class="bold-text" style="text-align: center;padding: 3px; line-height: 0.6; height: auto;border:1px solid; font-size: 10px; color: #000;">US$<?php echo number_format($total_amount_usd, 2, '.', ','); ?></td>
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
          <td class="bold-text" style="text-align: center;padding: 3px; line-height: 0.6; height: auto;border:1px solid; font-size: 10px; color: #000;"></td>
          <td class="bold-text" style="text-align: center;padding: 3px; line-height: 0.6; height: auto;border:1px solid; font-size: 10px; color: #000;">Total</td>
          <td class="bold-text" style="text-align: center;padding: 3px; line-height: 0.6; height: auto;border:1px solid; font-size: 10px; color: #000;"></td>
          <td class="bold-text" style="text-align: center;padding: 3px; line-height: 0.6; height: auto;border:1px solid; font-size: 10px; color: #000;"><?php echo $total_qty; ?></td>
          <td class="bold-text" style="text-align: center;padding: 3px; line-height: 0.6; height: auto;border:1px solid; font-size: 10px; color: #000;"></td>
          <td class="bold-text" style="text-align: center;padding: 3px; line-height: 0.6; height: auto;border:1px solid; font-size: 10px; color: #000;">US$<?php echo number_format($total_amount, 2, '.', ','); ?></td>
        </tr>
      </tfoot>
    </table>

    <!-- Bank Details Section -->
    <table style="width: 100%; ">
      <tbody>
        <tr>
          <td style="width:75%;text-align: left; padding: 0px 3px; line-height: 0.9;">
            <span class="bold-text" style="color: #000; font-size: 12px;">BANK DETAILS</span><br>
            <span class="bold-text" style="color: #000; font-size: 11px;">
              <?php if (!empty($supplier['beneficiary'])): ?>
              1. BENEFICIARY: <?php echo html_entity_decode($supplier['beneficiary']); ?><br>
              <?php else: ?>
              1. BENEFICIARY: <?php echo !empty($supplier['name']) ? html_entity_decode($supplier['name']) : '-'; ?><br>
              <?php endif; ?>
              <?php if (!empty($supplier['account_no'])): ?>
              2. ACCOUNT NO: <?php echo html_entity_decode($supplier['account_no']); ?><br>
              <?php endif; ?>
              <?php if (!empty($supplier['advising_bank'])): ?>
              3. ADVISING BANK: <?php echo html_entity_decode($supplier['advising_bank']); ?><br>
              <?php endif; ?>
              <?php if (!empty($supplier['bank_address'])): ?>
              4. BANK ADDRESS: <?php echo html_entity_decode($supplier['bank_address']); ?><br>
              <?php endif; ?>
              <?php if (!empty($supplier['swift_code'])): ?>
              5. SWIFT CODE: <?php echo html_entity_decode($supplier['swift_code']); ?>
              <?php endif; ?>
            </span>
          </td>
          <td style="width:25%;text-align: right; padding: 0px 3px; line-height: 1.0; vertical-align: top;">
              <?php if (!empty($stamp_image) && $stamp_image != '-'): ?>
                <img src="<?= $stamp_image; ?>" style="max-width: 150px; height: auto; margin-top: 10px;" >
                <br><br>
              <?php endif; ?>
            </td>
        </tr>
      </tbody>
    </table>

    <!-- Seller and Buyer Section at Bottom with Stamp -->
    <table style="width: 100%; ">
      <tbody>
        <tr>
          
          <td style="width:75%;text-align: left; padding: 0px 3px; line-height: 1.0; vertical-align: top;">
            <span class="bold-text" style="color: #000; font-size: 12px;">
              SELLER: <br><?php echo !empty($supplier['name']) ? html_entity_decode($supplier['name']) : '-'; ?>
            </span>
          </td>
          <td style="width:25%;text-align: left; padding: 0px 3px; line-height: 1.0; vertical-align: top;">
            <span class="bold-text" style="color: #000; font-size: 12px;">
                BUYER: <br><?php echo !empty($company_info['name']) ? html_entity_decode($company_info['name']) : '-'; ?>
              </span>
          </td>

        </tr>
      </tbody>
    </table>
    
  </div>
</body>

</html>

