<?php $status = $status ?? ''; ?>
<div class="table-responsive">
    <?php if ($status == 'expense' || $status == 'no_expense') : ?>
        <table class="table table-bordered table-striped align-middle font-small-2 mb-0">
            <thead class="table-dark">
                <tr>
                    <th class="text-center">#</th>
                    <th>Batch No</th>
                    <th class="text-center">Date</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($pos)) : ?>
                    <?php
                    $sr = 0;
                    $total_amount = 0;
                    foreach ($pos as $po) :
                        $sr++;
                        $amount = floatval($po['amount'] ?? 0);
                        $total_amount += $amount;
                        $order_date = (!empty($po['date']) && $po['date'] != '0000-00-00')
                            ? date('d-M-Y', strtotime($po['date']))
                            : '-';
                    ?>
                        <tr>
                            <td class="text-center"><?= $sr ?></td>
                            <td><?= htmlspecialchars($po['voucher_no'] ?? '-') ?></td>
                            <td class="text-center"><?= $order_date ?></td>
                            <td class="text-end"><?= number_format($amount, 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="table-light fw-bolder">
                        <td colspan="3" class="text-end">Total Amount:</td>
                        <td class="text-end"><?= number_format($total_amount, 2) ?></td>
                    </tr>
                <?php else : ?>
                    <tr>
                        <td colspan="4" class="text-center py-3 text-muted">No <?= htmlspecialchars($status) ?> orders found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php elseif ($status == 'loading') : ?>
        <table class="table table-bordered table-striped align-middle font-small-2 mb-0">
            <thead class="table-dark">
                <tr>
                    <th class="text-center">#</th>
                    <th>Batch No</th>
                    <th class="text-center">Loading Date</th>
                    <th class="text-center">Expected Arrival Date</th>
                    <th class="text-end">Qty</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($pos)) : ?>
                    <?php
                    $sr = 0;
                    $total_qty = 0;
                    foreach ($pos as $po) :
                        $sr++;
                        $qty = intval($po['quantity'] ?? 0);
                        $total_qty += $qty;
                        $loading_date = (!empty($po['expected_date']) && $po['expected_date'] != '0000-00-00')
                            ? date('d-M-Y', strtotime($po['expected_date']))
                            : '-';
                        $arrival_date = (!empty($po['arrival_date']) && $po['arrival_date'] != '0000-00-00')
                            ? date('d-M-Y', strtotime($po['arrival_date']))
                            : '-';
                    ?>
                        <tr>
                            <td class="text-center"><?= $sr ?></td>
                            <td><?= htmlspecialchars($po['voucher_no'] ?? '-') ?></td>
                            <td class="text-center"><?= $loading_date ?></td>
                            <td class="text-center"><?= $arrival_date ?></td>
                            <td class="text-end fw-bold"><?= number_format($qty) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="table-light fw-bolder">
                        <td colspan="4" class="text-end">Total Qty:</td>
                        <td class="text-end"><?= number_format($total_qty) ?></td>
                    </tr>
                <?php else : ?>
                    <tr>
                        <td colspan="5" class="text-center py-3 text-muted">No loading orders found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php else : ?>
        <table class="table table-bordered table-striped align-middle font-small-2 mb-0">
            <thead class="table-dark">
                <tr>
                    <th class="text-center">#</th>
                    <th>Batch No</th>
                    <th class="text-center">Order Date</th>
                    <th class="text-center">Expected Loading Date</th>
                    <th class="text-end">Qty</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($pos)) : ?>
                    <?php
                    $sr = 0;
                    $total_qty = 0;
                    foreach ($pos as $po) :
                        $sr++;
                        $qty = intval($po['quantity'] ?? 0);
                        $total_qty += $qty;
                        $order_date = (!empty($po['date']) && $po['date'] != '0000-00-00')
                            ? date('d-M-Y', strtotime($po['date']))
                            : '-';
                        $expected_date = (!empty($po['expected_date']) && $po['expected_date'] != '0000-00-00')
                            ? date('d-M-Y', strtotime($po['expected_date']))
                            : '-';
                    ?>
                        <tr>
                            <td class="text-center"><?= $sr ?></td>
                            <td><?= htmlspecialchars($po['voucher_no'] ?? '-') ?></td>
                            <td class="text-center"><?= $order_date ?></td>
                            <td class="text-center"><?= $expected_date ?></td>
                            <td class="text-end fw-bold"><?= number_format($qty) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="table-light fw-bolder">
                        <td colspan="4" class="text-end">Total Qty:</td>
                        <td class="text-end"><?= number_format($total_qty) ?></td>
                    </tr>
                <?php else : ?>
                    <tr>
                        <td colspan="5" class="text-center py-3 text-muted">No <?= htmlspecialchars($status ?: 'pending') ?> orders found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
