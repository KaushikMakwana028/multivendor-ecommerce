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
        <h4>Policy Pages Management</h4>
        <p>Manage dynamic contents for Privacy Policy, Terms & Conditions, and Refund Policy</p>
    </div>
</div>

<!-- Pages Table Card -->
<div class="card-dark">
    <div class="card-body-dark" style="padding:0;">
        <div class="table-responsive">
            <table class="table-dark-custom">
                <thead>
                    <tr>
                        <th style="width: 80px;">#</th>
                        <th>Page Title</th>
                        <th>Slug</th>
                        <th>Last Updated</th>
                        <th style="width: 150px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pages)): ?>
                        <?php $i = 1; foreach ($pages as $page): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td>
                                    <strong style="color: #fff; font-size: 15px;"><?= htmlspecialchars($page['title'], ENT_QUOTES, 'UTF-8') ?></strong>
                                </td>
                                <td>
                                    <code style="color: var(--primary-red); background: #222; padding: 2px 6px; border-radius: 4px; font-size: 13px;">
                                        <?= htmlspecialchars($page['slug'], ENT_QUOTES, 'UTF-8') ?>
                                    </code>
                                </td>
                                <td>
                                    <span style="color: #bbb; font-size: 14px;">
                                        <?= date('d M Y, h:i A', strtotime($page['updated_at'])) ?>
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <a href="<?= site_url('admin/pages/edit/' . $page['id']) ?>" class="action-btn edit" title="Edit Content">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 20px; color: #999;">
                                No policy pages found. Please run database seeding.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
