<div class="table-responsive">
    <table class="table table-bordered table-striped align-middle font-small-2 mb-0">
        <thead class="table-dark">
            <tr>
                <th class="text-center">#</th>
                <th>Sales Person</th>
                <th class="text-center">Days from Booking</th>
                <th>Booking No</th>
                <th class="text-center">Booking Date</th>
                <th class="text-center">Delivery Date</th>
                <th>Warehouse</th>
                <th>Customer</th>
                <th class="text-end">Booked Qty</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($orders)) : ?>
                <?php
                $sr = 0;
                $total_booked = 0;
                $today = strtotime(date('Y-m-d'));
                foreach ($orders as $order) :
                    $sr++;
                    $qty = intval($order['quantity'] ?? 0);
                    $total_booked += $qty;

                    $sales_person = trim($order['sales_person_name'] ?? '');
                    if ($sales_person === '') {
                        $sales_person = '-';
                    }

                    $booking_days = '-';
                    if (!empty($order['date']) && $order['date'] != '0000-00-00') {
                        $booking_days = (int) floor(($today - strtotime($order['date'])) / 86400);
                        if ($booking_days < 0) {
                            $booking_days = 0;
                        }
                    }

                    $booking_date = (!empty($order['date']) && $order['date'] != '0000-00-00')
                        ? date('d-M-Y', strtotime($order['date']))
                        : '-';
                    $delivery_date = (!empty($order['expected_date']) && $order['expected_date'] != '0000-00-00')
                        ? date('d-M-Y', strtotime($order['expected_date']))
                        : '-';
                ?>
                    <tr>
                        <td class="text-center"><?= $sr ?></td>
                        <td><?= htmlspecialchars($sales_person) ?></td>
                        <td class="text-center"><?= $booking_days ?></td>
                        <td><?= htmlspecialchars($order['order_no'] ?? '-') ?></td>
                        <td class="text-center"><?= $booking_date ?></td>
                        <td class="text-center"><?= $delivery_date ?></td>
                        <td><?= htmlspecialchars($order['warehouse_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($order['customer_name'] ?? '-') ?></td>
                        <td class="text-end fw-bold text-danger"><?= number_format($qty) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="table-light fw-bolder">
                    <td colspan="8" class="text-end">Total Booked Qty:</td>
                    <td class="text-end text-danger"><?= number_format($total_booked) ?></td>
                </tr>
            <?php else : ?>
                <tr>
                    <td colspan="9" class="text-center py-3 text-muted">No unapproved booked sales orders found for this product.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
