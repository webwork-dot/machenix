<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Batch No</th>
                <th>Date</th>
                <!-- <th>Supplier</th> -->
                <?php if($status == "expense" || $status == "no_expense") {?>
                <th>Amount</th>
                <?php } else { ?>
                <th>Quantity</th>
                <?php } ?>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($pos)) : ?>
                <?php foreach ($pos as $po) : ?>
                    <tr>
                        <td>
                            <a href="javascript:void(0)" class="text-primary fw-bold">
                                <?= $po['voucher_no'] ?>
                            </a>
                            <!-- <a href="<?= base_url('inventory/view-purchase-order/'.$po['id']) ?>" target="_blank" class="text-primary fw-bold">
                                <?= $po['voucher_no'] ?>
                            </a> -->
                        </td>
                        <td><?= date('d-M-Y', strtotime($po['date'])) ?></td>
                        <!-- <td><?= $po['supplier_name'] ?></td> -->
                        <?php if($status == "expense" || $status == "no_expense") {?>
                        <td><?= $po['amount'] ?></td>
                        <?php } else { ?>
                        <td><?= $po['quantity'] ?></td>
                        <?php } ?>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="4" class="text-center">No <?= $status ?> orders found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
