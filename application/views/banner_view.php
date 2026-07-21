<?php if ($this->session->flashdata('success')): ?>
    <div class="alert-custom alert-success-custom">
        <i class="fas fa-check-circle"></i>
        <?= $this->session->flashdata('success') ?>
    </div>
<?php endif; ?>

<?php if ($this->session->flashdata('error')): ?>
    <div class="alert-custom alert-danger-custom">
        <i class="fas fa-exclamation-circle"></i>
        <?= $this->session->flashdata('error') ?>
    </div>
<?php endif; ?>

<div class="page-header">
    <div>
        <h4>Home Banners</h4>
        <p>Manage all homepage banners</p>
    </div>
    <a href="<?= site_url('home_banners/create') ?>" class="btn-red">
        <i class="fas fa-plus"></i> Add Banner
    </a>
</div>

<!-- Filters -->
<div class="card-dark mb-4">
    <div class="card-body-dark">
        <form method="get" action="<?= site_url('home_banners') ?>">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search by title..." value="<?= $this->input->get('search') ?>">
                </div>
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
                        <option value="home" <?= $this->input->get('type') === 'home' ? 'selected' : '' ?>>Home</option>
                        <option value="offer" <?= $this->input->get('type') === 'offer' ? 'selected' : '' ?>>Offer</option>
                        <option value="category" <?= $this->input->get('type') === 'category' ? 'selected' : '' ?>>Category</option>
                        <option value="product" <?= $this->input->get('type') === 'product' ? 'selected' : '' ?>>Product</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="sort" class="form-select">
                        <option value="newest" <?= $this->input->get('sort') === 'newest' ? 'selected' : '' ?>>Newest First</option>
                        <option value="oldest" <?= $this->input->get('sort') === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                        <option value="display_order" <?= $this->input->get('sort') === 'display_order' ? 'selected' : '' ?>>Display Order</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn-red" style="width: auto; margin-right: 10px;">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="<?= site_url('home_banners') ?>" class="btn-outline-light-custom" style="width: auto;">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Banners List -->
<?php if (!empty($banners)): ?>
    <div class="card-dark">
        <div class="card-body-dark" style="padding:0;">
            <div class="table-responsive">
                <table class="table-dark-custom">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Banner Title</th>
                            <th>Banner Type</th>
                            <th>Display Order</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($banners as $i => $banner): ?>
                            <tr>
                                <td style="color:#666;"><?= $i + 1 ?></td>
                                
                                <!-- Image Preview -->
                                <td>
                                    <?php if (!empty($banner['image'])): ?>
                                        <img src="<?= base_url('uploads/banners/' . $banner['image']) ?>"
                                             style="width:100px;height:60px;border-radius:8px;object-fit:cover;border:1px solid var(--border-color);"
                                             alt="<?= htmlspecialchars($banner['title']) ?>">
                                    <?php else: ?>
                                        <div style="width:100px;height:60px;background:var(--light-gray);border-radius:8px;display:flex;align-items:center;justify-content:center;border:1px solid var(--border-color);color:#555;">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- Banner Title -->
                                <td>
                                    <div style="font-weight:600;">
                                        <?= htmlspecialchars($banner['title']) ?>
                                    </div>
                                    <?php if (!empty($banner['subtitle'])): ?>
                                        <div style="font-size:11px;color:#999;margin-top:3px;">
                                            <?= htmlspecialchars(substr($banner['subtitle'], 0, 50)) ?><?= strlen($banner['subtitle']) > 50 ? '...' : '' ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($banner['button_text'])): ?>
                                        <div style="font-size:10px;color:var(--primary-red);margin-top:3px;">
                                            <i class="fas fa-link"></i> <?= htmlspecialchars($banner['button_text']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- Banner Type -->
                                <td>
                                    <?php
                                    $type_badges = [
                                        'home' => ['class' => 'badge-home', 'icon' => 'home'],
                                        'offer' => ['class' => 'badge-offer', 'icon' => 'tags'],
                                        'category' => ['class' => 'badge-category', 'icon' => 'th-large'],
                                        'product' => ['class' => 'badge-product', 'icon' => 'box']
                                    ];
                                    $type_badge = $type_badges[$banner['banner_type']] ?? $type_badges['home'];
                                    ?>
                                    <span class="banner-type-badge <?= $type_badge['class'] ?>">
                                        <i class="fas fa-<?= $type_badge['icon'] ?>"></i>
                                        <?= ucfirst($banner['banner_type']) ?>
                                    </span>
                                </td>

                                <!-- Display Order -->
                                <td>
                                    <span style="background:rgba(255,255,255,0.05);padding:4px 10px;border-radius:6px;font-weight:600;font-size:13px;">
                                        <?= $banner['display_order'] ?>
                                    </span>
                                </td>

                                <!-- Start Date -->
                                <td>
                                    <?php if (!empty($banner['start_date'])): ?>
                                        <div style="font-size:12px;color:#ccc;">
                                            <?= date('d M Y', strtotime($banner['start_date'])) ?>
                                        </div>
                                    <?php else: ?>
                                        <span style="color:#666;">-</span>
                                    <?php endif; ?>
                                </td>

                                <!-- End Date -->
                                <td>
                                    <?php if (!empty($banner['end_date'])): ?>
                                        <div style="font-size:12px;color:#ccc;">
                                            <?= date('d M Y', strtotime($banner['end_date'])) ?>
                                        </div>
                                        <?php
                                        $today = date('Y-m-d');
                                        if ($banner['end_date'] < $today): ?>
                                            <span style="font-size:10px;color:#e57373;">Expired</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span style="color:#666;">-</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Status -->
                                <td>
                                    <?php if ($banner['is_active']): ?>
                                        <span class="badge-active">Active</span>
                                    <?php else: ?>
                                        <span class="badge-inactive">Inactive</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Created Date -->
                                <td>
                                    <div style="font-size:12px;color:#ccc;">
                                        <?= date('d M Y', strtotime($banner['created_at'])) ?>
                                    </div>
                                    <div style="font-size:11px;color:#666;">
                                        <?= date('h:i A', strtotime($banner['created_at'])) ?>
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td>
                                    <div style="display:flex;gap:5px;">
                                        <a href="<?= site_url('home_banners/edit/' . $banner['id']) ?>" 
                                           class="action-btn edit" 
                                           title="Edit">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>

                                        <a href="<?= site_url('home_banners/change_status/' . $banner['id']) ?>" 
                                           class="action-btn <?= $banner['is_active'] ? 'delete' : 'view' ?>" 
                                           title="<?= $banner['is_active'] ? 'Deactivate' : 'Activate' ?>">
                                            <i class="fas fa-<?= $banner['is_active'] ? 'eye-slash' : 'eye' ?>"></i>
                                        </a>

                                        <a href="<?= site_url('home_banners/delete/' . $banner['id']) ?>" 
                                           class="action-btn delete" 
                                           title="Delete" 
                                           onclick="return confirm('Are you sure you want to delete this banner? This will also delete the uploaded image.')">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <?php if (!empty($pagination)): ?>
        <div style="margin-top: 20px;">
            <?= $pagination ?>
        </div>
    <?php endif; ?>

<?php else: ?>
    <!-- Empty State -->
    <div class="card-dark">
        <div class="card-body-dark">
            <div style="padding:50px;text-align:center;color:#666;">
                <i class="fas fa-images" style="font-size:42px;margin-bottom:14px;display:block;"></i>
                <p>No banners found. <a href="<?= site_url('home_banners/create') ?>" style="color:var(--primary-red);">Add your first banner</a></p>
            </div>
        </div>
    </div>
<?php endif; ?>

<style>
/* Banner Type Badges */
.banner-type-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.badge-home {
    background: rgba(33, 150, 243, 0.15);
    color: #42a5f5;
    border: 1px solid rgba(33, 150, 243, 0.3);
}

.badge-offer {
    background: rgba(255, 152, 0, 0.15);
    color: #ffa726;
    border: 1px solid rgba(255, 152, 0, 0.3);
}

.badge-category {
    background: rgba(156, 39, 176, 0.15);
    color: #ab47bc;
    border: 1px solid rgba(156, 39, 176, 0.3);
}

.badge-product {
    background: rgba(76, 175, 80, 0.15);
    color: #81c784;
    border: 1px solid rgba(76, 175, 80, 0.3);
}

/* Responsive */
@media (max-width: 768px) {
    .table-responsive {
        overflow-x: auto;
    }
    
    .table-dark-custom {
        min-width: 1000px;
    }
}
</style>