<?php if ($this->session->flashdata('success')): ?>
    <div class="alert-custom alert-success-custom"><i class="fas fa-check-circle"></i><?= $this->session->flashdata('success') ?></div>
<?php endif; ?>

<div class="page-header">
    <div><h4>Offers Management</h4><p>Manage all promotional offers</p></div>
    <a href="<?= site_url('offers/create') ?>" class="btn-red"><i class="fas fa-plus"></i> Add Offer</a>
</div>

<div class="card-dark mb-4">
    <div class="card-body-dark">
        <form method="get" action="<?= site_url('offers') ?>" class="row g-3">
            <div class="col-md-3"><input type="text" name="search" class="form-control" placeholder="Search..." value="<?= $this->input->get('search') ?>"></div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="1" <?= $this->input->get('status') === '1' ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= $this->input->get('status') === '0' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <option value="flat">Flat</option>
                    <option value="percentage">Percentage</option>
                    <option value="bogo">BOGO</option>
                    <option value="free_delivery">Free Delivery</option>
                    <option value="cashback">Cashback</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="featured" class="form-select">
                    <option value="">All Featured</option>
                    <option value="1">Featured</option>
                    <option value="0">Not Featured</option>
                </select>
            </div>
            <div class="col-md-3"><button type="submit" class="btn-red" style="width:auto;"><i class="fas fa-filter"></i> Filter</button> <a href="<?= site_url('offers') ?>" class="btn-outline-light-custom" style="width:auto;"><i class="fas fa-redo"></i> Reset</a></div>
        </form>
    </div>
</div>

<?php if (!empty($offers)): ?>
    <div class="card-dark">
        <div class="card-body-dark" style="padding:0;">
            <div class="table-responsive">
                <table class="table-dark-custom">
                    <thead>
                        <tr>
                            <th>#</th><th>Image</th><th>Offer Title</th><th>Coupon</th><th>Type</th><th>Discount</th><th>Applicable</th><th>Valid Till</th><th>Status</th><th>Featured</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($offers as $i => $offer): ?>
                            <tr>
                                <td style="color:#666;"><?= $i + 1 ?></td>
                                <td>
                                    <?php if (!empty($offer['image'])): ?>
                                        <img src="<?= base_url('uploads/offers/' . $offer['image']) ?>" style="width:80px;height:50px;border-radius:6px;object-fit:cover;border:1px solid var(--border-color);">
                                    <?php else: ?>
                                        <div style="width:80px;height:50px;background:var(--light-gray);border-radius:6px;display:flex;align-items:center;justify-content:center;border:1px solid var(--border-color);color:#555;"><i class="fas fa-image"></i></div>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= htmlspecialchars($offer['title']) ?></strong><br><small style="color:#666;"><?= htmlspecialchars(substr($offer['subtitle'] ?? '', 0, 30)) ?></small></td>
                                <td><?= $offer['coupon_code'] ? '<span style="background:rgba(255,255,255,0.1);padding:4px 8px;border-radius:4px;">' . htmlspecialchars($offer['coupon_code']) . '</span>' : '-' ?></td>
                                <td><span class="offer-badge" style="background:rgba(33,150,243,0.15);color:#42a5f5;padding:4px 10px;border-radius:4px;font-size:11px;"><?= ucfirst($offer['offer_type']) ?></span></td>
                                <td>
                                    <?php if ($offer['discount_type'] == 'percentage'): ?>
                                        <strong><?= $offer['discount_value'] ?>%</strong> off
                                    <?php else: ?>
                                        <strong>₹<?= number_format($offer['discount_value'], 2) ?></strong>
                                    <?php endif; ?>
                                </td>
                                <td><small><?= ucfirst($offer['applicable_on']) ?></small></td>
                                <td>
                                    <small><?= date('d M Y', strtotime($offer['end_date'])) ?></small>
                                    <?php if ($offer['end_date'] < date('Y-m-d')): ?><br><span style="color:#ff9800;font-size:10px;">Expired</span><?php endif; ?>
                                </td>
                                <td><?= $offer['is_active'] ? '<span class="badge-active">Active</span>' : '<span class="badge-inactive">Inactive</span>' ?></td>
                                <td><?= $offer['is_featured'] ? '<span style="background:rgba(76,175,80,0.15);color:#81c784;padding:4px 10px;border-radius:4px;font-size:11px;">Featured</span>' : '' ?></td>
                                <td>
                                    <div style="display:flex;gap:5px;">
                                        <a href="<?= site_url('offers/edit/' . $offer['id']) ?>" class="action-btn edit" title="Edit"><i class="fas fa-pencil-alt"></i></a>
                                        <a href="<?= site_url('offers/change_status/' . $offer['id']) ?>" class="action-btn <?= $offer['is_active'] ? 'delete' : 'view' ?>" title="<?= $offer['is_active'] ? 'Deactivate' : 'Activate' ?>"><i class="fas fa-<?= $offer['is_active'] ? 'eye-slash' : 'eye' ?>"></i></a>
                                        <a href="<?= site_url('offers/toggle_featured/' . $offer['id']) ?>" class="action-btn <?= $offer['is_featured'] ? 'delete' : 'view' ?>" title="<?= $offer['is_featured'] ? 'Unfeature' : 'Feature' ?>"><i class="fas fa-<?= $offer['is_featured'] ? 'star' : 'star' ?>"></i></a>
                                        <a href="<?= site_url('offers/delete/' . $offer['id']) ?>" class="action-btn delete" title="Delete" onclick="return confirm('Delete this offer?')"><i class="fas fa-trash-alt"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div style="margin-top:20px;"><?= $pagination ?></div>
<?php else: ?>
    <div class="card-dark"><div class="card-body-dark"><div style="padding:50px;text-align:center;color:#666;"><i class="fas fa-tag" style="font-size:42px;margin-bottom:14px;display:block;"></i>No offers found. <a href="<?= site_url('offers/create') ?>" style="color:var(--primary-red);">Create your first offer</a></div></div></div>
<?php endif; ?>