<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Voucher No</th>
                <th>Date</th>
                <th>Supplier</th>
                <th>Quantity</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($pos)) : ?>
                <?php foreach ($pos as $po) : ?>
                    <tr>
                        <td>
                            <a href="<?= base_url('inventory/view-purchase-order/'.$po['id']) ?>" target="_blank" class="text-primary fw-bold">
                                <?= $po['voucher_no'] ?>
                            </a>
                        </td>
                        <td><?= date('d-M-Y', strtotime($po['date'])) ?></td>
                        <td><?= $po['supplier_name'] ?></td>
                        <td><?= $po['quantity'] ?></td>
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
