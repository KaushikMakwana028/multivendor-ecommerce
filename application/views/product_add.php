<?php if (!empty($error)): ?>
    <div class="alert-custom alert-danger-custom"><i class="fas fa-exclamation-circle"></i><?= $error ?></div>
<?php endif; ?>

<div class="page-header">
    <div>
        <h4>Add Product</h4>
        <p>Add a new product to your shop.</p>
    </div>
    <a href="<?= site_url('product') ?>" class="btn-outline-light-custom">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<form method="POST" action="<?= site_url('product/store') ?>" enctype="multipart/form-data">
    <input type="hidden"
        name="<?= $this->security->get_csrf_token_name(); ?>"
        value="<?= $this->security->get_csrf_hash(); ?>">
    <div class="row g-4">
        <!-- LEFT -->
        <div class="col-lg-8">
            <div class="card-dark mb-4">
                <div class="card-header-dark">
                    <h6><i class="fas fa-info-circle me-2" style="color:var(--primary-red);"></i>Product Information</h6>
                </div>
                <div class="card-body-dark">
                    <div class="mb-3">
                        <label class="form-label">Product Name <span style="color:var(--primary-red)">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Enter product name" value="<?= isset($_POST['name']) ? $_POST['name'] : '' ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Product description..."><?= isset($_POST['description']) ? $_POST['description'] : '' ?></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Category <span style="color:var(--primary-red)">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">-- Select Category --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat->id ?>"><?= htmlspecialchars($cat->name) ?></option>
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
                            <label class="form-label">MRP (₹) <span style="color:var(--primary-red)">*</span></label>
                            <input type="number"
                                step="0.01"
                                name="mrp"
                                class="form-control"
                                placeholder="0.00"
                                value="<?= isset($_POST['mrp']) ? $_POST['mrp'] : '' ?>"
                                required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Selling Price (₹) <span style="color:var(--primary-red)">*</span></label>
                            <input type="number"
                                step="0.01"
                                name="price"
                                class="form-control"
                                placeholder="0.00"
                                value="<?= isset($_POST['price']) ? $_POST['price'] : '' ?>"
                                required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Stock <span style="color:var(--primary-red)">*</span></label>
                            <input type="number" name="stock" class="form-control" placeholder="0"
                                value="<?= isset($_POST['stock']) ? $_POST['stock'] : '' ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="is_active" class="form-select">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT -->
        <div class="col-lg-4">
            <div class="card-dark">
                <div class="card-header-dark">
                    <h6><i class="fas fa-images me-2" style="color:var(--primary-red);"></i>Product Images</h6>
                </div>
                <div class="card-body-dark">
                    <div class="mb-3">
                        <label class="form-label">Upload Images</label>
                        <input type="file"
                            name="image"
                            class="form-control"
                            accept="image/*"
                            onchange="previewImage(this)">
                        <small style="color:#666;font-size:11px;">Select multiple images. First image = primary.</small>
                    </div>
                    <div style="margin-top:10px;">
                        <img id="imagePreview"
                            src=""
                            style="display:none;width:100%;border-radius:8px;border:1px solid var(--border-color);">
                    </div>
                </div>
            </div>

            <div class="card-dark mt-4">
                <div class="card-body-dark">
                    <button type="submit" class="btn-red w-100 justify-content-center">
                        <i class="fas fa-save"></i> Save Product
                    </button>
                    <a href="<?= site_url('product') ?>" class="btn-outline-light-custom w-100 justify-content-center mt-2">
                        Cancel
                    </a>
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