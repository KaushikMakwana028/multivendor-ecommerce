<?php if (!empty($error)): ?>
    <div class="alert-custom alert-danger-custom"><i class="fas fa-exclamation-circle"></i><?= $error ?></div>
<?php endif; ?>

<div class="page-header">
    <div>
        <h4>Add Category</h4>
        <p>Create a new product category.</p>
    </div>
    <a href="<?= base_url('category') ?>" class="btn-outline-light-custom">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="card-dark">
            <div class="card-header-dark">
                <h6><i class="fas fa-list me-2" style="color:var(--primary-red);"></i>Category Details</h6>
            </div>
            <div class="card-body-dark">
                <form method="post" action="<?= site_url('category/store') ?>" enctype="multipart/form-data">
                    <input type="hidden"
                        name="<?= $this->security->get_csrf_token_name(); ?>"
                        value="<?= $this->security->get_csrf_hash(); ?>">

                    <div class="mb-3">
                        <label class="form-label">Category Name <span style="color:var(--primary-red)">*</span></label>
                        <input type="text"
                            name="name"
                            class="form-control"
                            placeholder="e.g. Idols & Figurines"
                            value="<?= isset($_POST['name']) ? $_POST['name'] : '' ?>"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Category Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImg(this,'imgPreview')">
                        <small style="color:#666;font-size:11px;">JPG, PNG, WEBP — Max 2MB</small>
                        <div style="margin-top:10px;">
                            <img id="imgPreview" src="" style="display:none;width:80px;height:80px;object-fit:cover;border-radius:8px;border:1px solid var(--border-color);">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-select">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-red">
                        <i class="fas fa-save"></i> Save Category
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