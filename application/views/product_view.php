<?php if ($this->session->flashdata('success')): ?>
    <div class="alert-custom alert-success-custom"><i class="fas fa-check-circle"></i><?= $this->session->flashdata('success') ?></div>
<?php endif; ?>

<div class="page-header">
    <div>
        <h4>Products</h4>
        <p>Manage all your products.</p>
    </div>
    <a href="<?= site_url('product/add') ?>" class="btn-red">
        <i class="fas fa-plus"></i> Add Product
    </a>
</div>

<div class="card-dark">
    <div class="card-body-dark" style="padding:0;">
        <?php if (!empty($products)): ?>
            <table class="table-dark-custom">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>MRP / Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $i => $product): ?>
                        <tr>
                            <td style="color:#666;"><?= $i + 1 ?></td>
                            <td>
                                <?php if (!empty($product['image'])): ?>
                                    <img src="<?= base_url('uploads/products/' . $product['image']) ?>"
                                        style="width:48px;height:48px;border-radius:8px;object-fit:cover;border:1px solid var(--border-color);">
                                <?php else: ?>
                                    <div style="width:48px;height:48px;background:var(--light-gray);border-radius:8px;display:flex;align-items:center;justify-content:center;border:1px solid var(--border-color);color:#555;">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-weight:600;">
                                    <?= htmlspecialchars($product['name'] ?? '') ?>
                                </div>
                            </td>

                            <td>
                                <span style="background:rgba(224,16,32,0.1);color:var(--primary-red);padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">
                                    <?= htmlspecialchars($product['category_name'] ?? '-') ?>
                                </span>
                            </td>
                            <td>
                                <div style="font-size:11px;color:#999;">
                                    MRP: ₹<?= number_format($product['mrp'], 2) ?>
                                </div>

                                <div style="color:#4caf50;font-weight:600;">
                                    Price: ₹<?= number_format($product['price'], 2) ?>
                                </div>
                            </td>
                            <td>
                                <span style="<?= $product['stock'] < 5 ? 'color:var(--primary-red)' : 'color:#fff' ?>;">
                                    <?= $product['stock'] ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($product['is_active']): ?>
                                    <span class="badge-active">Active</span>
                                <?php else: ?>
                                    <span class="badge-inactive">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= site_url('product/edit/' . $product['id']) ?>" class="action-btn edit" title="Edit"><i class="fas fa-pencil-alt"></i></a>
                                <a href="<?= site_url('product/delete/' . $product['id']) ?>" class="action-btn delete" title="Delete" onclick="return confirm('Delete this product?')"><i class="fas fa-trash-alt"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="padding:50px;text-align:center;color:#666;">
                <i class="fas fa-box-open" style="font-size:42px;margin-bottom:14px;display:block;"></i>
                No products found. <a href="<?= site_url('product/add') ?>" style="color:var(--primary-red);">Add your first product</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    /* ===== MOBILE RESPONSIVE FOR ADD PRODUCT PAGE ===== */
    @media (max-width: 768px) {

        /* Make columns stack vertically */
        .row.g-4 {
            display: block;
        }

        .col-lg-8,
        .col-lg-4 {
            width: 100%;
            float: none;
            margin-bottom: 20px;
        }

        /* Fix clearfix for row */
        .row:after {
            content: "";
            display: table;
            clear: both;
        }

        /* Page header - stack vertically */
        .page-header {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        /* Back button on mobile - inline style fix */
        .page-header .btn-outline-light-custom {
            display: inline-flex;
            width: auto;
        }

        /* Make form inputs full width */
        .form-control,
        .form-select {
            width: 100%;
            box-sizing: border-box;
        }

        /* Stack pricing fields vertically */
        .row.g-3 {
            display: block;
        }

        .row.g-3 .col-md-4,
        .row.g-3 .col-md-6 {
            width: 100%;
            margin-bottom: 15px;
            float: none;
        }

        /* Clearfix for inner rows */
        .row.g-3:after {
            content: "";
            display: table;
            clear: both;
        }

        /* Fix card margins */
        .card-dark {
            width: 100%;
            margin-bottom: 20px;
            overflow: visible;
        }

        /* Make buttons full width on mobile */
        .btn-red,
        .btn-outline-light-custom {
            width: 100%;
            display: flex;
            justify-content: center;
        }

        /* Fix button spacing */
        .card-dark.mt-4 {
            margin-top: 0;
        }

        /* Responsive image preview */
        #imagePreview {
            width: 100%;
            height: auto;
            max-height: 250px;
            object-fit: cover;
        }

        /* Adjust textarea height */
        textarea.form-control {
            min-height: 100px;
        }

        /* Small text adjustments */
        .form-label {
            display: block;
            margin-bottom: 5px;
        }

        /* Ensure proper spacing between elements */
        .mb-3 {
            margin-bottom: 16px;
        }

        .card-body-dark {
            padding: 16px;
        }

        .card-header-dark {
            padding: 12px 16px;
        }
    }

    /* Extra small devices (phones under 480px) */
    @media (max-width: 480px) {
        .card-body-dark {
            padding: 12px;
        }

        .form-control,
        .form-select {
            font-size: 16px;
            /* Prevents zoom on iOS */
            padding: 8px 12px;
        }

        .btn-red,
        .btn-outline-light-custom {
            padding: 10px;
            font-size: 14px;
        }

        .page-header h4 {
            font-size: 20px;
            margin-bottom: 5px;
        }

        .page-header p {
            font-size: 12px;
        }
    }
</style>