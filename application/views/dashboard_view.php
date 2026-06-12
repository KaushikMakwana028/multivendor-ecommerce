<?php if ($this->session->flashdata('success')): ?>
    <div class="alert-custom alert-success-custom"><i class="fas fa-check-circle"></i><?= $this->session->flashdata('success') ?></div>
<?php endif; ?>

<div class="page-header">
    <div>
        <h4>Dashboard</h4>
        <p>Welcome back, <?= htmlspecialchars($this->session->userdata('admin_name')) ?>! Here's your shop overview.</p>
    </div>
</div>

<!-- STAT CARDS -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="card-dark" style="padding:20px;position:relative;overflow:hidden;transition:all 0.3s;" onmouseover="this.style.borderColor='var(--primary-red)';this.style.transform='translateY(-4px)'" onmouseout="this.style.borderColor='var(--border-color)';this.style.transform='translateY(0)'">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
                <p style="font-size:12px;color:#999;text-transform:uppercase;letter-spacing:0.8px;margin:0;">Total Categories</p>
                <div style="width:40px;height:40px;background:rgba(224,16,32,0.1);border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--primary-red);font-size:16px;"><i class="fas fa-list"></i></div>
            </div>
            <div style="font-size:30px;font-weight:700;margin-bottom:4px;"><?= $total_categories ?></div>
            <a href="<?= site_url('category') ?>" style="font-size:12px;color:var(--primary-red);text-decoration:none;">View all <i class="fas fa-arrow-right ms-1"></i></a>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="card-dark" style="padding:20px;position:relative;overflow:hidden;transition:all 0.3s;" onmouseover="this.style.borderColor='var(--primary-red)';this.style.transform='translateY(-4px)'" onmouseout="this.style.borderColor='var(--border-color)';this.style.transform='translateY(0)'">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
                <p style="font-size:12px;color:#999;text-transform:uppercase;letter-spacing:0.8px;margin:0;">Total Products</p>
                <div style="width:40px;height:40px;background:rgba(76,175,80,0.1);border-radius:8px;display:flex;align-items:center;justify-content:center;color:#4caf50;font-size:16px;"><i class="fas fa-boxes"></i></div>
            </div>
            <div style="font-size:30px;font-weight:700;margin-bottom:4px;"><?= $total_products ?></div>
            <a href="<?= site_url('product') ?>" style="font-size:12px;color:#4caf50;text-decoration:none;">View all <i class="fas fa-arrow-right ms-1"></i></a>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card-dark" style="padding:20px;position:relative;overflow:hidden;transition:all 0.3s;" onmouseover="this.style.borderColor='var(--primary-red)';this.style.transform='translateY(-4px)'" onmouseout="this.style.borderColor='var(--border-color)';this.style.transform='translateY(0)'">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
                <p style="font-size:12px;color:#999;text-transform:uppercase;letter-spacing:0.8px;margin:0;">Active Products</p>
                <div style="width:40px;height:40px;background:rgba(255,152,0,0.1);border-radius:8px;display:flex;align-items:center;justify-content:center;color:#ff9800;font-size:16px;"><i class="fas fa-check-circle"></i></div>
            </div>
            <div style="font-size:30px;font-weight:700;margin-bottom:4px;"><?= $active_products ?></div>
            <span style="font-size:12px;color:#ff9800;">Currently live</span>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="card-dark" style="padding:20px;position:relative;overflow:hidden;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
                <p style="font-size:12px;color:#999;text-transform:uppercase;margin:0;">
                    Inactive Products
                </p>

                <div style="width:40px;height:40px;background:rgba(158,158,158,0.1);border-radius:8px;display:flex;align-items:center;justify-content:center;color:#999;">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>

            <div style="font-size:30px;font-weight:700;">
                <?= $inactive_products ?>
            </div>

            <span style="font-size:12px;color:#999;">
                Currently hidden
            </span>
        </div>
    </div>
</div>

<!-- RECENT PRODUCTS TABLE -->
<div class="card-dark">
    <div class="card-header-dark">
        <h6><i class="fas fa-clock me-2" style="color:var(--primary-red);"></i>Recent Products</h6>
        <a href="<?= site_url('product/add') ?>" class="btn-red" style="font-size:12px;padding:7px 14px;">
            <i class="fas fa-plus"></i> Add Product
        </a>
    </div>
    <div class="card-body-dark" style="padding:0;">
        <?php if (!empty($recent_products)): ?>
            <table class="table-dark-custom" style="width:100%;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_products as $i => $product): ?>
                        <tr>
                            <td style="color:#666;"><?= $i + 1 ?></td>
                            <td>
                                <?php if (!empty($product['image'])): ?>
                                    <img src="<?= base_url('uploads/products/' . $product['image']) ?>"
                                        style="width:42px;height:42px;object-fit:cover;border-radius:8px;">
                                <?php else: ?>
                                    <div style="width:42px;height:42px;background:#222;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="font-weight:500;"><?= htmlspecialchars($product['name']) ?></td>
                            <td style="color:#999;"><?= htmlspecialchars($product['category_name'] ?? '-') ?></td>
                            <td style="color:#4caf50;">₹<?= number_format($product['price'], 2) ?></td>
                            <td><?= $product['stock'] ?></td>
                            <td>
                                <?php if ($product['is_active']): ?>
                                    <span class="badge-active">Active</span>
                                <?php else: ?>
                                    <span class="badge-inactive">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= site_url('product/edit/' . $product['id']) ?>" class="action-btn edit" title="Edit"><i class="fas fa-pencil-alt"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="padding:40px;text-align:center;color:#666;">
                <i class="fas fa-box-open" style="font-size:40px;margin-bottom:12px;display:block;"></i>
                No products yet. <a href="<?= site_url('product/add') ?>" style="color:var(--primary-red);">Add your first product</a>
            </div>
        <?php endif; ?>
    </div>
</div>


<style>
    /* ===== MOBILE RESPONSIVE FOR DASHBOARD ===== */
    @media (max-width: 768px) {

        /* Fix row gaps for stat cards */
        .row {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .col-xl-3,
        .col-sm-6 {
            width: 100%;
        }

        /* Make stat cards full width with proper spacing */
        .row [class*="col-"] {
            padding: 0;
        }

        .card-dark[style*="padding:20px"] {
            padding: 18px !important;
        }

        /* Recent products table to card layout */
        .card-header-dark {
            display: flex;
            flex-direction: column;
            gap: 12px;
            align-items: flex-start !important;
            padding: 16px !important;
        }

        .card-header-dark .btn-red {
            width: 100%;
            justify-content: center;
        }

        /* Hide table headers on mobile */
        .table-dark-custom thead {
            display: none;
        }

        /* Convert table rows to cards */
        .table-dark-custom,
        .table-dark-custom tbody,
        .table-dark-custom tr,
        .table-dark-custom td {
            display: block;
            width: 100%;
        }

        .table-dark-custom tr {
            margin-bottom: 16px;
            border: 1px solid var(--border-color);
            border-radius: 16px;
            background: var(--card-bg);
            padding: 14px;
            position: relative;
        }

        .table-dark-custom td {
            padding: 8px 0;
            border-bottom: none;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .table-dark-custom td:first-child {
            padding-top: 0;
        }

        .table-dark-custom td:last-child {
            padding-bottom: 0;
            border-bottom: none;
        }

        /* Add labels before each cell value */
        .table-dark-custom td:nth-of-type(1):before {
            content: "#: ";
            font-weight: 600;
            color: var(--text-muted);
            min-width: 85px;
        }

        .table-dark-custom td:nth-of-type(2):before {
            content: "Image: ";
            font-weight: 600;
            color: var(--text-muted);
            min-width: 85px;
        }

        .table-dark-custom td:nth-of-type(3):before {
            content: "Product: ";
            font-weight: 600;
            color: var(--text-muted);
            min-width: 85px;
        }

        .table-dark-custom td:nth-of-type(4):before {
            content: "Category: ";
            font-weight: 600;
            color: var(--text-muted);
            min-width: 85px;
        }

        .table-dark-custom td:nth-of-type(5):before {
            content: "Price: ";
            font-weight: 600;
            color: var(--text-muted);
            min-width: 85px;
        }

        .table-dark-custom td:nth-of-type(6):before {
            content: "Stock: ";
            font-weight: 600;
            color: var(--text-muted);
            min-width: 85px;
        }

        .table-dark-custom td:nth-of-type(7):before {
            content: "Status: ";
            font-weight: 600;
            color: var(--text-muted);
            min-width: 85px;
        }

        .table-dark-custom td:nth-of-type(8):before {
            content: "Action: ";
            font-weight: 600;
            color: var(--text-muted);
            min-width: 85px;
        }

        /* Fix image alignment */
        .table-dark-custom td:nth-of-type(2) {
            display: flex;
            align-items: center;
        }

        .table-dark-custom td:nth-of-type(2) img,
        .table-dark-custom td:nth-of-type(2) div {
            margin-left: 0;
        }

        /* Action buttons alignment */
        .table-dark-custom td:last-child {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .table-dark-custom td:last-child:before {
            display: inline-block;
        }

        /* Price and stock styling on mobile */
        .table-dark-custom td:nth-of-type(5) {
            color: #4caf50;
            font-weight: 500;
        }

        /* Page header responsive */
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }

        .page-header h4 {
            font-size: 1.5rem;
        }

        .page-header p {
            font-size: 0.85rem;
        }

        /* Alert responsive */
        .alert-custom {
            font-size: 0.85rem;
            padding: 12px;
            margin-bottom: 18px;
        }

        /* Badge adjustments */
        .badge-active,
        .badge-inactive {
            font-size: 0.7rem;
            padding: 4px 10px;
        }

        /* Action button size */
        .action-btn {
            width: 34px;
            height: 34px;
        }

        /* Empty state adjustments */
        .card-body-dark>div[style*="padding:40px"] {
            padding: 30px 20px !important;
        }

        /* Stat card number font size */
        .card-dark div[style*="font-size:30px"] {
            font-size: 26px !important;
        }

        /* Icon box size adjustment */
        .card-dark div[style*="width:40px"] {
            width: 36px !important;
            height: 36px !important;
        }
    }

    /* Small phones (up to 480px) */
    @media (max-width: 480px) {
        .card-dark[style*="padding:20px"] {
            padding: 14px !important;
        }

        .card-dark div[style*="font-size:30px"] {
            font-size: 22px !important;
        }

        .table-dark-custom tr {
            padding: 12px;
        }

        .table-dark-custom td:before {
            min-width: 75px !important;
            font-size: 0.8rem;
        }

        .table-dark-custom td {
            font-size: 0.85rem;
        }
    }
</style>