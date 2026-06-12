<?php if ($this->session->flashdata('success')): ?>
    <div class="alert-custom alert-success-custom"><i class="fas fa-check-circle"></i><?= $this->session->flashdata('success') ?></div>
<?php endif; ?>

<div class="page-header">
    <div>
        <h4>Categories</h4>
        <p>Manage your product categories.</p>
    </div>
    <a href="<?= site_url('category/add') ?>" class="btn-red">
        <i class="fas fa-plus"></i> Add Category
    </a>
</div>

<div class="card-dark">
    <div class="card-body-dark" style="padding:0;">
        <?php if (!empty($categories)): ?>
            <table class="table-dark-custom">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $i => $cat): ?>
                        <tr>
                            <td style="color:#666;"><?= $i + 1 ?></td>
                            <td>
                                <?php if (!empty($cat->image)): ?>
                                    <img src="<?= base_url('uploads/categories/' . $cat->image) ?>" style="width:42px;height:42px;border-radius:8px;object-fit:cover;border:1px solid var(--border-color);">
                                <?php else: ?>
                                    <div style="width:42px;height:42px;background:var(--light-gray);border-radius:8px;display:flex;align-items:center;justify-content:center;border:1px solid var(--border-color);color:#555;"><i class="fas fa-image"></i></div>
                                <?php endif; ?>
                            </td>
                            <td style="font-weight:600;"><?= htmlspecialchars($cat->name) ?></td>
                            <td>
                                <?php if ($cat->is_active): ?>
                                    <span class="badge-active">Active</span>
                                <?php else: ?>
                                    <span class="badge-inactive">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td style="color:#666;font-size:12px;"><?= date('d M Y', strtotime($cat->created_at)) ?></td>
                            <td>
                                <a href="<?= site_url('category/edit/' . $cat->id) ?>" class="action-btn edit" title="Edit"><i class="fas fa-pencil-alt"></i></a>
                                <a href="<?= site_url('category/delete/' . $cat->id) ?>" class="action-btn delete" title="Delete" onclick="return confirm('Delete this category?')"><i class="fas fa-trash-alt"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="padding:50px;text-align:center;color:#666;">
                <i class="fas fa-list" style="font-size:42px;margin-bottom:14px;display:block;"></i>
                No categories found. <a href="<?= site_url('category/add') ?>" style="color:var(--primary-red);">Create your first category</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    /* ===== MOBILE RESPONSIVE (Table to Cards - No UI Change) ===== */
    @media (max-width: 768px) {

        /* Hide table headers on mobile */
        .table-dark-custom thead {
            display: none;
        }

        /* Make table rows behave like cards */
        .table-dark-custom,
        .table-dark-custom tbody,
        .table-dark-custom tr,
        .table-dark-custom td {
            display: block;
            width: 100%;
        }

        .table-dark-custom tr {
            margin-bottom: 20px;
            border: 1px solid var(--border-color);
            border-radius: 16px;
            background: var(--card-bg);
            padding: 16px;
            position: relative;
        }

        .table-dark-custom td {
            padding: 10px 0;
            border-bottom: none;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        /* Add labels before each cell value */
        .table-dark-custom td:first-child {
            padding-top: 0;
        }

        .table-dark-custom td:last-child {
            padding-bottom: 0;
            border-bottom: none;
        }

        .table-dark-custom td:nth-of-type(1):before {
            content: "#: ";
            font-weight: 600;
            color: var(--text-muted);
            min-width: 70px;
        }

        .table-dark-custom td:nth-of-type(2):before {
            content: "Image: ";
            font-weight: 600;
            color: var(--text-muted);
            min-width: 70px;
        }

        .table-dark-custom td:nth-of-type(3):before {
            content: "Name: ";
            font-weight: 600;
            color: var(--text-muted);
            min-width: 70px;
        }

        .table-dark-custom td:nth-of-type(4):before {
            content: "Status: ";
            font-weight: 600;
            color: var(--text-muted);
            min-width: 70px;
        }

        .table-dark-custom td:nth-of-type(5):before {
            content: "Created: ";
            font-weight: 600;
            color: var(--text-muted);
            min-width: 70px;
        }

        .table-dark-custom td:nth-of-type(6):before {
            content: "Actions: ";
            font-weight: 600;
            color: var(--text-muted);
            min-width: 70px;
        }

        /* Fix image alignment */
        .table-dark-custom td:nth-of-type(2) {
            display: flex;
            align-items: center;
        }

        /* Action buttons stay inline */
        .table-dark-custom td:last-child {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .table-dark-custom td:last-child:before {
            display: inline-block;
        }

        /* Adjust badge and button sizes for mobile */
        .badge-active,
        .badge-inactive {
            font-size: 0.7rem;
            padding: 4px 10px;
        }

        .action-btn {
            width: 36px;
            height: 36px;
        }

        /* Page header responsive */
        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .btn-red {
            width: 100%;
            justify-content: center;
        }

        /* Alert responsive */
        .alert-custom {
            font-size: 0.85rem;
            padding: 12px;
        }
    }
</style>