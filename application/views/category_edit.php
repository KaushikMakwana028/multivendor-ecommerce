<?php if (!empty($error)): ?>
    <div class="alert-custom alert-danger-custom">
        <i class="fas fa-exclamation-circle me-2"></i>
        <?= $error ?>
    </div>
<?php endif; ?>

<?php if ($this->session->flashdata('success')): ?>
    <div class="alert-custom alert-success-custom">
        <i class="fas fa-check-circle me-2"></i>
        <?= $this->session->flashdata('success'); ?>
    </div>
<?php endif; ?>

<div class="page-header">
    <div>
        <h4>Edit Category</h4>
        <p>Update category details.</p>
    </div>
    <a href="<?= site_url('category') ?>" class="btn-outline-light-custom">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="card-dark">
            <div class="card-header-dark">
                <h6><i class="fas fa-pencil-alt me-2" style="color:var(--primary-red);"></i>Edit: <?= htmlspecialchars($category->name ?? '') ?></h6>
            </div>
            <div class="card-body-dark">
                <form method="POST" action="<?= site_url('category/update/' . $category->id) ?>" enctype="multipart/form-data">
                    <input type="hidden"
                        name="<?= $this->security->get_csrf_token_name(); ?>"
                        value="<?= $this->security->get_csrf_hash(); ?>">

                    <div class="mb-3">
                        <label class="form-label">Category Name <span style="color:var(--primary-red)">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($category->name ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Category Image</label>
                        <?php if (!empty($category->image)): ?>
                            <div style="margin-bottom:10px;">
                                <img src="<?= base_url('uploads/categories/' . $category->image) ?>" id="imgPreview" style="width:80px;height:80px;object-fit:cover;border-radius:8px;border:1px solid var(--border-color);">
                            </div>
                        <?php else: ?>
                            <div style="margin-bottom:10px;">
                                <img id="imgPreview" src="" style="display:none;width:80px;height:80px;object-fit:cover;border-radius:8px;border:1px solid var(--border-color);">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImg(this,'imgPreview')">
                        <small style="color:#666;font-size:11px;">Leave blank to keep current image</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-select">
                            <option value="1" <?= $category->is_active == 1 ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= $category->is_active == 0 ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-red">
                        <i class="fas fa-save"></i> Update Category
                    </button>
                    <a href="<?= site_url('category') ?>" class="btn-outline-light-custom ms-2">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function previewImg(input, previewId) {
        var preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>