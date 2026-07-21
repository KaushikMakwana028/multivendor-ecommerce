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
                            <label class="form-label">MRP (₹) <span style="color:var(--primary-red)">*</span></label>
                            <input type="number" step="0.01" name="mrp" class="form-control" value="<?= $product->mrp ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Selling Price (₹) <span style="color:var(--primary-red)">*</span></label>
                            <input type="number" step="0.01" name="price" class="form-control" value="<?= $product->price ?>" required>
                        </div>
                        <div class="col-md-4">
    <label class="form-label">GST (%) <span style="color:var(--primary-red)">*</span></label>
    <select name="gst_percent" class="form-select" required>
        <option value="">-- Select GST --</option>
        <?php for($i=1; $i<=24; $i++): ?>
            <option value="<?= $i ?>" <?= ($product->gst_percent == $i) ? 'selected' : '' ?>>
                <?= $i ?>%
            </option>
        <?php endfor; ?>
    </select>
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

        <!-- RIGHT: IMAGES -->
        <div class="col-lg-4">
            <div class="card-dark mb-4">
                <div class="card-header-dark">
                    <h6><i class="fas fa-images me-2" style="color:var(--primary-red);"></i>Product Images</h6>
                </div>
                <div class="card-body-dark">

                    <!-- PRIMARY IMAGE -->
                    <?php if (!empty($product->image)): ?>
                        <div style="margin-bottom:10px;position:relative;">
                            <img src="<?= base_url('uploads/products/' . $product->image) ?>"
                                style="width:100%;border-radius:10px;border:2px solid var(--primary-red);object-fit:cover;">
                            <span style="position:absolute;top:5px;left:5px;background:var(--primary-red);color:#fff;font-size:10px;padding:2px 6px;border-radius:3px;">Primary</span>
                            <div class="form-check mt-1">
                                <input class="form-check-input" type="checkbox" name="remove_primary" value="1" id="removePrimary">
                                <label class="form-check-label" for="removePrimary" style="font-size:12px;color:#aaa;">
                                    Remove this image
                                </label>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- EXISTING GALLERY IMAGES -->
                    <?php if (!empty($product_images)): ?>
                        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:15px;">
                            <?php foreach ($product_images as $img): ?>
                                <div style="position:relative;width:80px;">
                                    <img src="<?= base_url('uploads/products/' . $img->image) ?>"
                                        style="width:80px;height:80px;object-fit:cover;border-radius:8px;border:1px solid var(--border-color);">
                                    <div class="form-check" style="font-size:10px;">
                                        <input class="form-check-input" type="checkbox"
                                            name="delete_images[]" value="<?= $img->id ?>"
                                            id="delImg<?= $img->id ?>">
                                        <label class="form-check-label" for="delImg<?= $img->id ?>" style="font-size:10px;color:#aaa;">
                                            Delete
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- ADD NEW IMAGES -->
                   <div class="mb-3">
    <label class="form-label">Add More Images</label>
    <input type="file"
        name="image[]"
        id="editProductImages"
        class="form-control"
        accept="image/*"
        multiple
        onchange="handleFileSelect(this, 'editProductImages', 'newImagePreviewContainer')">
    <small style="color:#666;font-size:11px;">
        If no primary image exists, first new upload becomes primary.
    </small>
</div>
<div id="newImagePreviewContainer" style="display:flex;flex-wrap:wrap;gap:8px;"></div>
                    <div id="newImagePreviewContainer" style="display:flex;flex-wrap:wrap;gap:8px;"></div>
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
    const fileStore = {};

    function handleFileSelect(input, storeKey, containerId) {
        if (!fileStore[storeKey]) {
            fileStore[storeKey] = [];
        }

        const newFiles = Array.from(input.files);
        fileStore[storeKey] = fileStore[storeKey].concat(newFiles);

        syncInputFiles(input, storeKey);
        renderPreviews(storeKey, containerId, input);
    }

    function removeFile(storeKey, index, input, containerId) {
        fileStore[storeKey].splice(index, 1);
        syncInputFiles(input, storeKey);
        renderPreviews(storeKey, containerId, input);
    }

    function syncInputFiles(input, storeKey) {
        const dataTransfer = new DataTransfer();
        fileStore[storeKey].forEach(file => dataTransfer.items.add(file));
        input.files = dataTransfer.files;
    }

    function renderPreviews(storeKey, containerId, input) {
        const container = document.getElementById(containerId);
        container.innerHTML = '';

        fileStore[storeKey].forEach((file, index) => {
            const reader = new FileReader();

            reader.onload = function(e) {
                const wrapper = document.createElement('div');
                wrapper.style.position = 'relative';
                wrapper.style.width = '80px';

                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.width = '80px';
                img.style.height = '80px';
                img.style.objectFit = 'cover';
                img.style.borderRadius = '8px';
                img.style.border = '1px solid var(--border-color)';

                wrapper.appendChild(img);

                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.textContent = '✕';
                removeBtn.style.cssText = 'position:absolute;top:-6px;right:-6px;background:#dc3545;color:#fff;border:none;border-radius:50%;width:18px;height:18px;font-size:10px;line-height:1;cursor:pointer;';
                removeBtn.onclick = function() {
                    removeFile(storeKey, index, input, containerId);
                };
                wrapper.appendChild(removeBtn);

                container.appendChild(wrapper);
            };

            reader.readAsDataURL(file);
        });
    }
</script>