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
                        <div class="col-md-6">
                            <label class="form-label">HSN Code</label>
                            <input type="text" name="hsn_code" class="form-control" placeholder="Enter HSN code (e.g. 6912)" value="<?= htmlspecialchars($product->hsn_code ?? '') ?>">
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
                                <?php for ($i = 1; $i <= 24; $i++): ?>
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
                    <div class="pei-wrap">

                        <!-- PRIMARY IMAGE -->
                        <label class="pei-section-label">Primary Image</label>
                        <?php if (!empty($product->image)): ?>
                            <div class="pei-primary" id="primaryImageWrapper">
                                <div class="pei-primary-frame">
                                    <img src="<?= base_url('uploads/products/' . $product->image) ?>" alt="Primary image">
                                    <span class="pei-badge"><i class="fas fa-check-circle"></i> Active Primary</span>
                                </div>
                                <label class="pei-remove-toggle" for="removePrimary">
                                    <input class="form-check-input" type="checkbox" name="remove_primary" value="1" id="removePrimary" onchange="togglePrimaryImageRemoval(this)">
                                    <span id="removePrimaryLabel"><i class="fas fa-trash-alt"></i> Remove Primary Image</span>
                                </label>
                            </div>
                        <?php else: ?>
                            <div class="pei-empty">
                                <i class="fas fa-image"></i>
                                <p>No primary image set.</p>
                            </div>
                        <?php endif; ?>

                        <!-- EXISTING GALLERY IMAGES -->
                        <label class="pei-section-label">Gallery Images</label>
                        <?php if (!empty($product_images)): ?>
                            <div class="pei-gallery">
                                <?php foreach ($product_images as $img): ?>
                                    <div class="pei-gallery-card" id="galleryCard<?= $img->id ?>">
                                        <div class="pei-gallery-thumb">
                                            <img src="<?= base_url('uploads/products/' . $img->image) ?>" alt="Gallery image">
                                        </div>
                                        <div class="pei-gallery-actions">
                                            <label class="pei-icon-btn pei-icon-btn--primary select-primary-btn"
                                                id="btnPrimaryLabel<?= $img->id ?>" title="Make primary">
                                                <input type="radio" name="set_primary_image_id" value="<?= $img->id ?>"
                                                    onchange="handleSelectPrimary(this, <?= $img->id ?>)">
                                                <i class="far fa-star star-icon"></i>
                                                <span class="btn-text">Primary</span>
                                            </label>
                                            <label class="pei-icon-btn pei-icon-btn--danger delete-image-btn"
                                                id="btnDeleteLabel<?= $img->id ?>" title="Delete image">
                                                <input type="checkbox" name="delete_images[]" value="<?= $img->id ?>"
                                                    onchange="handleDeleteGalleryImage(this, <?= $img->id ?>)">
                                                <i class="fas fa-trash-alt trash-icon"></i>
                                                <span class="btn-text">Delete</span>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="pei-empty pei-empty--sm">
                                <p>No gallery images uploaded.</p>
                            </div>
                        <?php endif; ?>

                        <!-- ADD NEW IMAGES -->
                        <label class="pei-section-label">Add More Images</label>
                        <div class="pei-dropzone" id="peiDropzone">
                            <input type="file"
                                name="image[]"
                                id="editProductImages"
                                accept="image/*"
                                multiple
                                onchange="handleFileSelect(this, 'editProductImages', 'newImagePreviewContainer')">
                            <div class="pei-dropzone-content">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Click to choose images</span>
                            </div>
                        </div>
                        <small class="pei-hint">If no primary image exists, first new upload becomes primary.</small>

                        <div id="newImagePreviewContainer" class="pei-preview-grid"></div>
                    </div>
                </div>
            </div>

            <div class="card-dark">
                <div class="card-body-dark">
                    <button type="submit" class="btn-red w-100 justify-content-center">
                        <i class="fas fa-save"></i> Update Product
                    </button>
                    <a href="<?= site_url('product') ?>" class="btn-outline-light-custom w-100 justify-content-center mt-2 d-flex align-items-center">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</form>

<style>
    .pei-wrap {
        --pei-red: var(--primary-red, #e63946);
        --pei-gold: #e0a800;
        --pei-bg: #151515;
        --pei-border: #2a2a2a;
        --pei-radius: 10px;
    }

    .pei-section-label {
        display: block;
        margin-bottom: 8px;
        font-size: 12px;
        font-weight: 600;
        color: #bbb;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .pei-wrap .pei-section-label:not(:first-child) {
        margin-top: 20px;
    }

    /* PRIMARY IMAGE */
    .pei-primary {
        margin-bottom: 4px;
    }

    .pei-primary-frame {
        position: relative;
        border-radius: var(--pei-radius);
        overflow: hidden;
        border: 2px solid var(--pei-red);
        background: #0c0c0c;
        aspect-ratio: 1 / 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .pei-primary-frame img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
        transition: filter 0.3s ease;
    }

    .pei-badge {
        position: absolute;
        top: 8px;
        left: 8px;
        background: var(--pei-red);
        color: #fff;
        font-size: 10px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 4px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    .pei-remove-toggle {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        padding: 8px 12px;
        background: #1c1c1c;
        border: 1px solid var(--pei-border);
        border-top: none;
        border-radius: 0 0 var(--pei-radius) var(--pei-radius);
        font-size: 12px;
        color: #ddd;
        cursor: pointer;
    }

    .pei-remove-toggle input {
        margin: 0;
        flex-shrink: 0;
    }

    .pei-empty {
        text-align: center;
        padding: 24px 12px;
        background: var(--pei-bg);
        border: 1px dashed #444;
        border-radius: 8px;
        color: #777;
    }

    .pei-empty i {
        font-size: 22px;
        color: #555;
        margin-bottom: 6px;
        display: block;
    }

    .pei-empty p {
        margin: 0;
        font-size: 12px;
    }

    .pei-empty--sm {
        padding: 14px 12px;
    }

    /* GALLERY GRID */
    .pei-gallery {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }

    .pei-gallery-card {
        border: 1px solid var(--pei-border);
        border-radius: 8px;
        overflow: hidden;
        background: var(--pei-bg);
        transition: all 0.2s ease;
    }

    .pei-gallery-thumb {
        aspect-ratio: 1 / 1;
        background: #0c0c0c;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .pei-gallery-thumb img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
        transition: filter 0.3s ease;
    }

    .pei-gallery-actions {
        display: flex;
        border-top: 1px solid var(--pei-border);
    }

    .pei-icon-btn {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 7px 4px;
        margin: 0;
        font-size: 10.5px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        background: transparent;
    }

    .pei-icon-btn input {
        display: none;
    }

    .pei-icon-btn--primary {
        color: var(--pei-gold);
        border-right: 1px solid var(--pei-border);
    }

    .pei-icon-btn--danger {
        color: #ff4d4d;
    }

    .pei-icon-btn i {
        font-size: 11px;
    }

    .pei-gallery-card.marked-primary {
        border-color: var(--pei-gold);
    }

    .pei-gallery-card.marked-delete {
        border-color: #dc3545;
    }

    /* DROPZONE */
    .pei-dropzone {
        position: relative;
        border: 1px dashed #444;
        border-radius: 8px;
        background: var(--pei-bg);
        padding: 18px 12px;
        text-align: center;
        cursor: pointer;
        transition: border-color 0.2s ease, background 0.2s ease;
    }

    .pei-dropzone:hover {
        border-color: var(--pei-red);
        background: #1a1414;
    }

    .pei-dropzone input[type="file"] {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }

    .pei-dropzone-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        color: #999;
        font-size: 12px;
        pointer-events: none;
    }

    .pei-dropzone-content i {
        font-size: 20px;
        color: #666;
    }

    .pei-hint {
        display: block;
        margin-top: 6px;
        color: #666;
        font-size: 11px;
    }

    .pei-preview-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        margin-top: 10px;
    }

    .pei-preview-grid:empty {
        margin-top: 0;
    }

    .pei-preview-item {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #444;
        background: #222;
        aspect-ratio: 1 / 1;
    }

    .pei-preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .pei-preview-remove {
        position: absolute;
        top: 3px;
        right: 3px;
        background: #dc3545;
        color: #fff;
        border: none;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        font-size: 10px;
        line-height: 18px;
        text-align: center;
        padding: 0;
        cursor: pointer;
        font-weight: bold;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
    }

    /* MOBILE */
    @media (max-width: 576px) {
        .pei-gallery {
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }

        .pei-icon-btn {
            font-size: 10px;
            padding: 6px 2px;
        }

        .pei-icon-btn .btn-text {
            display: none;
        }

        .pei-icon-btn i {
            font-size: 13px;
        }

        .pei-preview-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }
</style>

<script>
    const fileStore = {};

    function togglePrimaryImageRemoval(checkbox) {
        const wrapper = document.getElementById('primaryImageWrapper');
        const frame = wrapper.querySelector('.pei-primary-frame');
        const label = document.getElementById('removePrimaryLabel');
        const img = wrapper.querySelector('img');

        if (checkbox.checked) {
            frame.style.borderColor = '#dc3545';
            img.style.filter = 'grayscale(1) brightness(0.4)';
            label.innerHTML = '<i class="fas fa-undo"></i> Keep Primary Image';
            label.parentElement.style.color = '#ff4d4d';
        } else {
            frame.style.borderColor = 'var(--primary-red)';
            img.style.filter = 'none';
            label.innerHTML = '<i class="fas fa-trash-alt"></i> Remove Primary Image';
            label.parentElement.style.color = '#ddd';
        }
    }

    function handleSelectPrimary(radio, imgId) {
        // Reset all other gallery cards primary state
        document.querySelectorAll('.pei-gallery-card').forEach(card => {
            card.classList.remove('marked-primary');

            const star = card.querySelector('.select-primary-btn .star-icon');
            if (star) {
                star.className = 'far fa-star star-icon';
            }
            const text = card.querySelector('.select-primary-btn .btn-text');
            if (text) {
                text.textContent = 'Primary';
            }
            const btn = card.querySelector('.select-primary-btn');
            if (btn) {
                btn.style.background = 'transparent';
                btn.style.color = 'var(--pei-gold, #e0a800)';
            }
        });

        if (radio.checked) {
            const card = document.getElementById('galleryCard' + imgId);
            card.classList.add('marked-primary');

            const star = card.querySelector('.select-primary-btn .star-icon');
            if (star) {
                star.className = 'fas fa-star star-icon';
            }
            const text = card.querySelector('.select-primary-btn .btn-text');
            if (text) {
                text.textContent = 'Primary Choice';
            }
            const btn = card.querySelector('.select-primary-btn');
            if (btn) {
                btn.style.background = '#e0a800';
                btn.style.color = '#000';
            }

            // If this image was selected for delete, uncheck delete
            const deleteCheckbox = card.querySelector('.delete-image-btn input');
            if (deleteCheckbox && deleteCheckbox.checked) {
                deleteCheckbox.checked = false;
                handleDeleteGalleryImage(deleteCheckbox, imgId);
            }
        }
    }

    function handleDeleteGalleryImage(checkbox, imgId) {
        const card = document.getElementById('galleryCard' + imgId);
        const img = card.querySelector('img');
        const starBtn = card.querySelector('.select-primary-btn');
        const trashLabel = document.getElementById('btnDeleteLabel' + imgId);
        const text = trashLabel.querySelector('.btn-text');

        if (checkbox.checked) {
            card.classList.add('marked-delete');
            img.style.filter = 'grayscale(1) brightness(0.3)';
            trashLabel.style.background = '#dc3545';
            trashLabel.style.color = '#fff';
            text.textContent = 'Undo';

            // Disable "Make Primary" if marked for deletion
            const primaryRadio = card.querySelector('.select-primary-btn input');
            if (primaryRadio && primaryRadio.checked) {
                primaryRadio.checked = false;
                handleSelectPrimary(primaryRadio, imgId);
            }
            starBtn.style.pointerEvents = 'none';
            starBtn.style.opacity = '0.3';
        } else {
            card.classList.remove('marked-delete');
            img.style.filter = 'none';
            trashLabel.style.background = 'transparent';
            trashLabel.style.color = '#ff4d4d';
            text.textContent = 'Delete';

            starBtn.style.pointerEvents = 'auto';
            starBtn.style.opacity = '1';
        }
    }

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
                wrapper.className = 'pei-preview-item';

                const img = document.createElement('img');
                img.src = e.target.result;
                wrapper.appendChild(img);

                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'pei-preview-remove';
                removeBtn.textContent = '✕';
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