<?php if (!empty($error)): ?>
    <div class="alert-custom alert-danger-custom"><i class="fas fa-exclamation-circle"></i><?= $error ?></div>
<?php endif; ?>

<div class="page-header">
    <div>
        <h4>Edit Product</h4>
        <p>Update product details.</p>
    </div>
    <a href="<?= site_url('product') ?>" class="btn-outline-light-custom">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<form method="POST" action="<?= site_url('product/update/' . $product->id) ?>" enctype="multipart/form-data">
    <input type="hidden"
        name="<?= $this->security->get_csrf_token_name(); ?>"
        value="<?= $this->security->get_csrf_hash(); ?>">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card-dark mb-4">
                <div class="card-header-dark">
                    <h6><i class="fas fa-info-circle me-2" style="color:var(--primary-red);"></i>Product Information</h6>
                </div>
                <div class="card-body-dark">
                    <div class="mb-3">
                        <label class="form-label">Product Name <span style="color:var(--primary-red)">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product->name ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($product->description ?? '') ?></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Category <span style="color:var(--primary-red)">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">-- Select Category --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat->id ?>" <?= $product->category_id == $cat->id ? 'selected' : '' ?>><?= htmlspecialchars($cat->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                    </div>
                </div>
            </div>

            <div class="card-dark">
                <div class="card-header-dark">
                    <h6><i class="fas fa-tag me-2" style="color:var(--primary-red);"></i>Pricing & Stock</h6>
                </div>
                <div class="card-body-dark">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">
                                MRP (₹)
                                <span style="color:var(--primary-red)">*</span>
                            </label>

                            <input type="number"
                                step="0.01"
                                name="mrp"
                                class="form-control"
                                value="<?= $product->mrp ?>"
                                required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">
                                Selling Price (₹)
                                <span style="color:var(--primary-red)">*</span>
                            </label>

                            <input type="number"
                                step="0.01"
                                name="price"
                                class="form-control"
                                value="<?= $product->price ?>"
                                required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Stock <span style="color:var(--primary-red)">*</span></label>
                            <input type="number" name="stock" class="form-control" value="<?= $product->stock ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="is_active" class="form-select">
                                <option value="1" <?= $product->is_active == 1 ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= $product->is_active == 0 ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-dark mb-4">
                <div class="card-body-dark">

                    <?php if (!empty($product->image)): ?>

                        <div style="margin-bottom:15px;">
                            <img
                                src="<?= base_url('uploads/products/' . $product->image) ?>"
                                id="imagePreview"
                                style="width:100%;border-radius:10px;border:1px solid var(--border-color);object-fit:cover;">
                        </div>

                    <?php else: ?>

                        <div style="margin-bottom:15px;">
                            <img
                                id="imagePreview"
                                src=""
                                style="display:none;width:100%;border-radius:10px;border:1px solid var(--border-color);">
                        </div>

                    <?php endif; ?>

                    <label class="form-label">
                        Product Image
                    </label>

                    <input
                        type="file"
                        name="image"
                        class="form-control"
                        accept="image/*"
                        onchange="previewImage(this)">

                    <small style="color:#666;font-size:11px;">
                        Leave blank to keep existing image
                    </small>

                </div>
            </div>

            <div class="card-dark">
                <div class="card-body-dark">
                    <button type="submit" class="btn-red w-100 justify-content-center">
                        <i class="fas fa-save"></i> Update Product
                    </button>
                    <a href="<?= site_url('product') ?>" class="btn-outline-light-custom w-100 justify-content-center mt-2">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    function previewImage(input) {
        const preview = document.getElementById('imagePreview');

        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };

            reader.readAsDataURL(input.files[0]);
        }
    }
</script>