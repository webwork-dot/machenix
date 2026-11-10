<?php
$products = isset($products) && is_array($products) ? $products : [];
$gridCols = 10;
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title>Loading Checklist</title>
  <style>
    body {
      font-family: DejaVu Sans, Arial, sans-serif;
      font-size: 11px;
      color: #000;
      margin: 0;
      padding: 20px;
    }
    table.checklist {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
    }
    table.checklist th,
    table.checklist td {
      vertical-align: middle;
      text-align: center;
      padding: 4px 3px;
      font-size: 11px;
    }
    table.checklist th {
      border: 1px solid #000;
      font-weight: bold;
    }
    .info-cell {
      border: 1px solid #000;
      word-wrap: break-word;
    }
    .grid-cell {
      border: 1px solid #000;
      width: 18px;
      height: 18px;
      padding: 0;
    }
    .grid-cell-empty {
      border: none;
      width: 18px;
      height: 18px;
      padding: 0;
    }
    .col-name { width: 38%; }
    .col-model { width: 18%; }
    .col-qty { width: 8%; }
  </style>
</head>
<body>
  <table class="checklist">
    <thead>
      <tr>
        <th class="col-name">Products Name</th>
        <th class="col-model">Model No</th>
        <th class="col-qty">Qty</th>
        <?php for ($i = 0; $i < $gridCols; $i++): ?>
          <th style="border:none; width:18px;"></th>
        <?php endfor; ?>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($products)): ?>
        <?php foreach ($products as $product): ?>
          <?php
            $qty = isset($product['loading_qty']) ? (int) $product['loading_qty'] : 0;
            if ($qty <= 0) {
              continue;
            }
            $productName = isset($product['product_name']) ? html_entity_decode($product['product_name']) : '';
            $modelNo = isset($product['item_code']) ? html_entity_decode($product['item_code']) : '';
            $rowsNeeded = (int) ceil($qty / $gridCols);
            $remaining = $qty;
          ?>
          <?php for ($rowIndex = 0; $rowIndex < $rowsNeeded; $rowIndex++): ?>
            <?php $cellsThisRow = min($gridCols, $remaining); ?>
            <tr>
              <?php if ($rowIndex === 0): ?>
                <td class="info-cell col-name" rowspan="<?php echo $rowsNeeded; ?>"><?php echo htmlspecialchars($productName, ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="info-cell col-model" rowspan="<?php echo $rowsNeeded; ?>"><?php echo htmlspecialchars($modelNo, ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="info-cell col-qty" rowspan="<?php echo $rowsNeeded; ?>"><?php echo $qty; ?></td>
              <?php endif; ?>
              <?php for ($c = 0; $c < $gridCols; $c++): ?>
                <?php if ($c < $cellsThisRow): ?>
                  <td class="grid-cell">&nbsp;</td>
                <?php else: ?>
                  <td class="grid-cell-empty">&nbsp;</td>
                <?php endif; ?>
              <?php endfor; ?>
            </tr>
            <?php $remaining -= $cellsThisRow; ?>
          <?php endfor; ?>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</body>
</html>
