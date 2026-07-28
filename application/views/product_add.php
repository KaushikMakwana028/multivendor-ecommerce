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
                        <div class="col-md-4">
                            <label class="form-label">Category <span style="color:var(--primary-red)">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">-- Select Category --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat->id ?>"><?= htmlspecialchars($cat->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">HSN Code</label>
                            <input type="text" name="hsn_code" class="form-control" placeholder="Enter HSN code (e.g. 6912)" value="<?= isset($_POST['hsn_code']) ? htmlspecialchars($_POST['hsn_code']) : '' ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">SKU</label>
                            <input type="text" name="sku" class="form-control" placeholder="Enter SKU (e.g. GM-001)" value="<?= isset($_POST['sku']) ? htmlspecialchars($_POST['sku']) : '' ?>">
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
                        <?php
                        $gst_rates = [0, 3, 5, 12, 18, 28];
                        ?>

                        <div class="col-md-4">
                            <label class="form-label">GST (%)</label>
                            <select name="gst_percent" class="form-select">
                                <option value="">Select GST</option>
                                <?php foreach ($gst_rates as $gst): ?>
                                    <option value="<?= $gst ?>" <?= (isset($_POST['gst_percent']) && $_POST['gst_percent'] == $gst) ? 'selected' : ''; ?>>
                                        <?= $gst ?>%
                                    </option>
                                <?php endforeach; ?>
                            </select>
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
                            name="image[]"
                            id="productImages"
                            class="form-control"
                            accept="image/*"
                            multiple
                            onchange="handleFileSelect(this, 'productImages', 'imagePreviewContainer')">
                        <small style="color:#666;font-size:11px;">Select multiple images (one at a time or together). First image = primary.</small>
                    </div>
                    <div id="imagePreviewContainer" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:10px;"></div>
                    <div id="imagePreviewContainer" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:10px;"></div>
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
    // Store selected files per input id, so multiple pickers on the page don't clash
    const fileStore = {};

    function handleFileSelect(input, storeKey, containerId) {
        if (!fileStore[storeKey]) {
            fileStore[storeKey] = [];
        }

        // Append newly picked files to existing array
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

    // Rebuild the <input type="file"> FileList from our stored array
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
                img.style.border = index === 0 ?
                    '2px solid var(--primary-red)' :
                    '1px solid var(--border-color)';

                wrapper.appendChild(img);

                if (index === 0) {
                    const badge = document.createElement('span');
                    badge.textContent = 'Primary';
                    badge.style.cssText = 'position:absolute;bottom:20px;left:2px;background:var(--primary-red);color:#fff;font-size:9px;padding:1px 4px;border-radius:3px;';
                    wrapper.appendChild(badge);
                }

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