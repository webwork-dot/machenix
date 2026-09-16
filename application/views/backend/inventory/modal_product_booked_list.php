<div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
        <thead class="table-dark">
            <tr>
                <th class="text-center">#</th>
                <th>Order No</th>
                <th>Date</th>
                <th>Customer</th>
                <th class="text-end">Booked Qty</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($orders)) : ?>
                <?php 
                $sr = 0;
                $total_booked = 0;
                foreach ($orders as $order) : 
                    $sr++;
                    $total_booked += intval($order['quantity']);
                ?>
                    <tr>
                        <td class="text-center"><?= $sr ?></td>
                        <td>
                            <a href="<?= base_url('inventory/sales-order-view/' . $order['id']) ?>" target="_blank" class="text-primary fw-bold">
                                <?= htmlspecialchars($order['order_no'] ?? '-') ?> <i class="fa fa-external-link font-small-2"></i>
                            </a>
                        </td>
                        <td><?= !empty($order['date']) ? date('d-M-Y', strtotime($order['date'])) : '-' ?></td>
                        <td><?= htmlspecialchars($order['customer_name'] ?? '-') ?></td>
                        <td class="text-end fw-bold text-danger"><?= number_format($order['quantity']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="table-light fw-bolder">
                    <td colspan="4" class="text-end">Total Booked Qty:</td>
                    <td class="text-end text-danger"><?= number_format($total_booked) ?></td>
                </tr>
            <?php else : ?>
                <tr>
                    <td colspan="5" class="text-center py-3 text-muted">No unapproved booked sales orders found for this product.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
