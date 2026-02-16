<?php 
// Get data from page_data
$data = isset($data) ? $data : [];
$supplier = $data;
$company_info = isset($data['company_info']) ? $data['company_info'] : [];
$products = isset($data['products']) ? $data['products'] : [];

// Supplier stamp image
$stamp_image = '-';
if (!empty($supplier['signature_image'])) {
    // $stamp_image = $supplier['signature_image'];
    $stamp_image = FCPATH . $supplier['signature_image'];
    $stamp_image = "data:image/png;base64," . base64_encode(file_get_contents($stamp_image));
} 

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
        $supplier_address .= ' - ' . $supplier['pincode'];
    }
}

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
        $company_address .= ' - ' . $company_info['pincode'];
    }
}

// Get invoice information from po_products
$invoice_no = '-';
$invoice_date_formatted = '-';

// Get invoice data from first product (all products in same invoice should have same invoice info)
if (!empty($products) && count($products) > 0) {
    $first_product = $products[0];
    $invoice_no = isset($first_product['invoice']) ? $first_product['invoice'] : '';
    if (!empty($first_product['invoice_date'])) {
        $invoice_date_formatted = date('Y/m/d', strtotime($first_product['invoice_date']));
    }
}

// Default values if still empty
if (empty($invoice_date_formatted)) {
    $invoice_date_formatted = date('Y/m/d');
}

// Calculate totals
$total_qty = 0;
$total_pkg = 0;
$total_nw = 0;
$total_gw = 0;
$total_cbm = 0;
?>
<html xmlns="http://www.w3.org/1999/xhtml" moznomarginboxes="" mozdisallowselectionprint="">

<head>
  <title>Packing List</title>
  <link rel="stylesheet" href="<?= base_url(); ?>assets/pdf/bootstrap.min.css">
  <link rel="stylesheet" href="<?= base_url(); ?>assets/pdf/custom.css">
</head>

<body>
  <div style="background: none repeat scroll 0 0 #ffffff;margin: 0 auto;width: 100%;padding: 0px;">
    <!-- Company Header (Seller) - Centered -->
    <table style="width: 100%; margin-bottom: 10px;">
      <tbody>
        <tr>
          <td style="text-align: center; padding: 0px 3px; line-height: 1.2;">
            <span style="color: #000; font-size: 14px; font-weight: bold;">
              <?php echo !empty($supplier['name']) ? htmlspecialchars($supplier['name']) : 'GUANGZHOU WEI GE MACHINERY EQUIPMENT CO., LIMITED'; ?>
            </span><br>
            <?php if (!empty($supplier_address)): ?>
            <span style="color: #000; font-size: 11px; font-weight: bold;">
              Add: <?php echo htmlspecialchars($supplier_address); ?>
            </span><br>
            <?php endif; ?>
            <?php if (!empty($supplier['contact_no'])): ?>
            <span style="color: #000; font-size: 11px; font-weight: bold;">
              Tel: <?php echo htmlspecialchars($supplier['contact_no']); ?>
              <?php if (!empty($supplier['gst_no'])): ?>
              Fax: <?php echo htmlspecialchars($supplier['gst_no']); ?>
              <?php endif; ?>
            </span>
            <?php endif; ?>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Packing List Title -->
    <table style="width: 100%; margin-bottom: 15px;">
      <tbody>
        <tr>
          <td style="width:100%;text-align: center;padding: 8px 3px; line-height: 1.0; height: auto;" colspan="12">
            <b style="font-size: 16px;color: #000;">PACKING LIST</b>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- To Section and Invoice Details - NO BORDERS -->
    <table style="width: 100%; margin-bottom: 10px;">
      <tbody>
        <tr>
          <td style="width:70%;text-align: left;padding: 5px 3px; line-height: 1.3; height: auto;" colspan="9">
            <span style="color: #000;font-size: 11px; font-weight: bold;"><b>To:</b> <?php echo !empty($company_info['name']) ? htmlspecialchars($company_info['name']) : 'Central Exportrade'; ?></span><br>
            <?php if (!empty($company_address)): ?>
            <span style="color: #000;font-size: 11px; font-weight: bold;">
              <b>Add:</b> <?php echo htmlspecialchars($company_address); ?>
            </span>
            <?php endif; ?>
          </td>
          <td style="width:30%;text-align: left;padding: 5px 3px; line-height: 1.3; height: auto;" colspan="3">
          <?php if (!empty($invoice_no)): ?>
            <span style="color: #000;font-size: 11px; font-weight: bold;"><b>Invoice No.:</b> <?php echo htmlspecialchars($invoice_no); ?></span><br>
            <?php endif; ?>
            <span style="color: #000;font-size: 11px; font-weight: bold;"><b>Date:</b> <?php echo $invoice_date_formatted; ?></span>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Products Table - WITH BORDERS -->
    <table style="width: 100%; border-collapse: collapse; margin-top: 5px;">
      <thead>
        <tr>
          <td style="text-align: center;padding: 4px 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; background-color: #f0f0f0;" rowspan="2">
            <b>No.</b>
          </td>
          <td style="text-align: center;padding: 4px 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; background-color: #f0f0f0;" rowspan="2">
            <b>Model</b>
          </td>
          <td style="text-align: center;padding: 4px 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; background-color: #f0f0f0;" rowspan="2">
            <b>Product Name</b>
          </td>
          <td style="text-align: center;padding: 4px 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; background-color: #f0f0f0;" rowspan="2">
            <b>Qty<br/>(set)</b>
          </td>
          <td style="text-align: center;padding: 4px 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; background-color: #f0f0f0;" rowspan="2">
            <b>PKG<br/>(ctn)</b>
          </td>
          <td style="text-align: center;padding: 4px 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; background-color: #f0f0f0;" rowspan="2">
            <b>N.W.<br/>(kg)</b>
          </td>
          <td style="text-align: center;padding: 4px 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; background-color: #f0f0f0;" rowspan="2">
            <b>Total<br/>N.W.</b>
          </td>
          <td style="text-align: center;padding: 4px 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; background-color: #f0f0f0;" rowspan="2">
            <b>G.W.<br/>(kg)</b>
          </td>
          <td style="text-align: center;padding: 4px 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; background-color: #f0f0f0;" rowspan="2">
            <b>Total<br/>G.W.</b>
          </td>
          <td style="text-align: center;padding: 4px 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; background-color: #f0f0f0;" colspan="3">
            <b>Specification(mm)</b>
          </td>
          <td style="text-align: center;padding: 4px 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; background-color: #f0f0f0;" rowspan="2">
            <b>Total<br/>CBM</b>
          </td>
        </tr>
        <tr>
          <td style="text-align: center;padding: 2px 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; background-color: #f0f0f0;">
            <b>L</b>
          </td>
          <td style="text-align: center;padding: 2px 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; background-color: #f0f0f0;">
            <b>W</b>
          </td>
          <td style="text-align: center;padding: 2px 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; background-color: #f0f0f0;">
            <b>H</b>
          </td>
        </tr>
      </thead>
      <tbody>
        <?php 
        $sr_no = 1;
        if (!empty($products)): 
            foreach ($products as $product): 
                $loading_qty = isset($product['loading_qty']) ? intval($product['loading_qty']) : 0;
                $official_ci_qty = isset($product['official_ci_qty']) ? intval($product['official_ci_qty']) : 0;
                $pkg_ctn = isset($product['pkg_ctn']) ? intval($product['pkg_ctn']) : 0;
                
                // Get variations from totals array
                $variations = [];
                if (!empty($product['totals']) && is_array($product['totals']) && count($product['totals']) > 0) {
                    $variations = $product['totals'];
                } else {
                    // Fallback to product level data - create a single variation
                    $variations = [[
                        'pkg_ctn' => isset($product['loading_qty']) ? floatval($product['loading_qty']) : 0,
                        'nw_kg' => isset($product['nw_kg']) ? floatval($product['nw_kg']) : 0,
                        'gw_kg' => isset($product['gw_kg']) ? floatval($product['gw_kg']) : 0,
                        'length' => isset($product['length']) ? floatval($product['length']) : 0,
                        'width' => isset($product['width']) ? floatval($product['width']) : 0,
                        'height' => isset($product['height']) ? floatval($product['height']) : 0,
                        'total_nw_kg' => isset($product['total_nw_kg']) ? floatval($product['total_nw_kg']) : 0,
                        'total_gw_kg' => isset($product['total_gw_kg']) ? floatval($product['total_gw_kg']) : 0,
                        'total_cbm_value' => isset($product['total_cbm_value']) ? floatval($product['total_cbm_value']) : 0,
                    ]];
                }
                
                $variation_count = count($variations);
                $rowspan = max(1, $variation_count);
                
                // Display first variation with rowspan for common fields
                $first_variation = $variations[0];
                $pkg_ctn = isset($first_variation['pkg_ctn']) ? floatval($first_variation['pkg_ctn']) : 0;
                $nw_kg = isset($first_variation['nw_kg']) ? floatval($first_variation['nw_kg']) : 0;
                $gw_kg = isset($first_variation['gw_kg']) ? floatval($first_variation['gw_kg']) : 0;
                $length = isset($first_variation['length']) ? floatval($first_variation['length']) : 0;
                $width = isset($first_variation['width']) ? floatval($first_variation['width']) : 0;
                $height = isset($first_variation['height']) ? floatval($first_variation['height']) : 0;
                $total_nw = isset($first_variation['total_nw_kg']) ? floatval($first_variation['total_nw_kg']) : 0;
                $total_gw = isset($first_variation['total_gw_kg']) ? floatval($first_variation['total_gw_kg']) : 0;
                $total_cbm_val = isset($first_variation['total_cbm_value']) ? floatval($first_variation['total_cbm_value']) : 0;
        ?>
        <tr>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;" <?php echo $rowspan > 1 ? 'rowspan="' . $rowspan . '"' : ''; ?>><?php echo $sr_no++; ?></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;" <?php echo $rowspan > 1 ? 'rowspan="' . $rowspan . '"' : ''; ?>><?php echo htmlspecialchars($product['item_code'] ?? '-'); ?></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;" <?php echo $rowspan > 1 ? 'rowspan="' . $rowspan . '"' : ''; ?>><?php echo htmlspecialchars($product['product_name'] ?? '-'); ?></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;" <?php echo $rowspan > 1 ? 'rowspan="' . $rowspan . '"' : ''; ?>><?php echo $official_ci_qty; ?></td>
          <!-- <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;" <?php echo $rowspan > 1 ? 'rowspan="' . $rowspan . '"' : ''; ?>><?php echo $pkg_ctn; ?></td> -->
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;"><?php echo number_format($pkg_ctn, 0, '.', ''); ?></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;"><?php echo number_format($nw_kg, 1, '.', ''); ?></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;"><?php echo number_format($total_nw, 0, '.', ''); ?></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;"><?php echo number_format($gw_kg, 1, '.', ''); ?></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;"><?php echo number_format($total_gw, 0, '.', ''); ?></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;"><?php echo number_format($length, 0, '.', ''); ?></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;"><?php echo number_format($width, 0, '.', ''); ?></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;"><?php echo number_format($height, 0, '.', ''); ?></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;"><?php echo number_format($total_cbm_val, 2, '.', ''); ?></td>
        </tr>
        <?php 
                // Display remaining variations (if any)
                if ($variation_count > 1) {
                    for ($i = 1; $i < $variation_count; $i++) {
                        $variation = $variations[$i];
                        $pkg_ctn = isset($variation['pkg_ctn']) ? floatval($variation['pkg_ctn']) : 0;
                        $nw_kg = isset($variation['nw_kg']) ? floatval($variation['nw_kg']) : 0;
                        $gw_kg = isset($variation['gw_kg']) ? floatval($variation['gw_kg']) : 0;
                        $length = isset($variation['length']) ? floatval($variation['length']) : 0;
                        $width = isset($variation['width']) ? floatval($variation['width']) : 0;
                        $height = isset($variation['height']) ? floatval($variation['height']) : 0;
                        $total_nw = isset($variation['total_nw_kg']) ? floatval($variation['total_nw_kg']) : 0;
                        $total_gw = isset($variation['total_gw_kg']) ? floatval($variation['total_gw_kg']) : 0;
                        $total_cbm_val = isset($variation['total_cbm_value']) ? floatval($variation['total_cbm_value']) : 0;
        ?>
        <tr>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;"><?php echo number_format($pkg_ctn, 0, '.', ''); ?></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;"><?php echo number_format($nw_kg, 1, '.', ''); ?></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;"><?php echo number_format($total_nw, 0, '.', ''); ?></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;"><?php echo number_format($gw_kg, 1, '.', ''); ?></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;"><?php echo number_format($total_gw, 0, '.', ''); ?></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;"><?php echo number_format($length, 0, '.', ''); ?></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;"><?php echo number_format($width, 0, '.', ''); ?></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;"><?php echo number_format($height, 0, '.', ''); ?></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;"><?php echo number_format($total_cbm_val, 2, '.', ''); ?></td>
        </tr>
        <?php 
                    }
                }
            endforeach; 
        endif; 
        ?>
      </tbody>
      <tfoot>
        <!-- Totals Row -->
        <?php 
        // Recalculate totals from all products
        $total_qty = 0;
        $total_pkg = 0;
        $total_nw_sum = 0;
        $total_gw_sum = 0;
        $total_cbm_sum = 0;
        
        if (!empty($products)) {
            foreach ($products as $product) {
                $loading_qty = isset($product['loading_qty']) ? intval($product['loading_qty']) : 0;
                $pkg_ctn = isset($product['pkg_ctn']) ? intval($product['pkg_ctn']) : 0;
                
                $total_nw_item = 0;
                $total_gw_item = 0;
                $total_cbm_item = 0;
                
                if (!empty($product['totals']) && is_array($product['totals']) && count($product['totals']) > 0) {
                    foreach ($product['totals'] as $total_item) {
                        $total_nw_item += isset($total_item['total_nw_kg']) ? floatval($total_item['total_nw_kg']) : 0;
                        $total_gw_item += isset($total_item['total_gw_kg']) ? floatval($total_item['total_gw_kg']) : 0;
                        $total_cbm_item += isset($total_item['total_cbm_value']) ? floatval($total_item['total_cbm_value']) : 0;
                    }
                } else {
                    $total_nw_item = isset($product['total_nw_kg']) ? floatval($product['total_nw_kg']) : 0;
                    $total_gw_item = isset($product['total_gw_kg']) ? floatval($product['total_gw_kg']) : 0;
                    $total_cbm_item = isset($product['total_cbm_value']) ? floatval($product['total_cbm_value']) : 0;
                }
                
                $total_qty += $loading_qty;
                $total_pkg += $pkg_ctn;
                $total_nw_sum += $total_nw_item;
                $total_gw_sum += $total_gw_item;
                $total_cbm_sum += $total_cbm_item;
            }
        }
        ?>
        <tr>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;"></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;">Total</td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;"></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;"><?php echo $total_qty; ?></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;"><?php echo $total_pkg; ?></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;"></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;"><?php echo number_format($total_nw_sum, 0, '.', ''); ?></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;"></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;"><?php echo number_format($total_gw_sum, 0, '.', ''); ?></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;"></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;"></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;"></td>
          <td style="text-align: center;padding: 3px; line-height: 1.0; height: auto;border:1px solid; font-size: 10px; color: #000; font-weight: bold;"><?php echo number_format($total_cbm_sum, 2, '.', ''); ?></td>
        </tr>
      </tfoot>
    </table>

    <!-- Seller Section at Bottom with Stamp -->
    <table style="width: 100%; margin-top: 30px;">
      <tbody>
        <tr>
          <td style="width:50%;text-align: left; padding: 10px 3px; line-height: 1.2; vertical-align: top;">
            <?php if (!empty($stamp_image) && $stamp_image != '-'): ?>
            <img src="<?= $stamp_image; ?>" style="max-width: 150px; height: auto; margin-top: 10px;" >
            <?php endif; ?>
          </td>
          <td style="width:50%;text-align: left; padding: 10px 3px; line-height: 1.2; vertical-align: top;">
            <span style="color: #000; font-size: 12px; font-weight: bold;">
              SELLER:
            </span><br>
            <span style="color: #000; font-size: 12px; font-weight: bold;">
              <?php echo !empty($supplier['name']) ? htmlspecialchars($supplier['name']) : '-'; ?>
            </span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</body>

</html>
