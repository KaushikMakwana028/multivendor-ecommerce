<?php if (validation_errors()): ?>
    <div class="alert-custom alert-danger-custom">
        <i class="fas fa-exclamation-circle"></i>
        <?= validation_errors() ?>
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
        <a href="<?= site_url('home_banners') ?>" class="btn-outline-light-custom" style="margin-bottom: 10px;">
            <i class="fas fa-arrow-left"></i> Back to Banners
        </a>
        <h4><?= isset($banner) ? 'Edit Banner' : 'Add New Banner' ?></h4>
        <p><?= isset($banner) ? 'Update banner information' : 'Create a new banner for homepage' ?></p>
    </div>
</div>

<form action="<?= isset($banner) ? site_url('home_banners/update/' . $banner['id']) : site_url('home_banners/store') ?>" 
      method="post" 
      enctype="multipart/form-data">
<input type="hidden"
       name="<?= $this->security->get_csrf_token_name(); ?>"
       value="<?= $this->security->get_csrf_hash(); ?>">
    <div class="row g-4">
        <!-- Left Column -->
        <div class="col-lg-8">
            
            <!-- Banner Information -->
            <div class="card-dark mb-4">
                <div class="card-header-dark">
                    <h6><i class="fas fa-info-circle"></i> Banner Information</h6>
                </div>
                <div class="card-body-dark">
                    
                    <!-- Banner Title -->
                    <div class="mb-3">
                        <label class="form-label">Banner Title <span style="color:var(--primary-red);">*</span></label>
                        <input type="text" 
                               name="title" 
                               class="form-control" 
                               placeholder="Enter banner title" 
                               value="<?= set_value('title', isset($banner) ? $banner['title'] : '') ?>" 
                               required>
                    </div>

                    <!-- Subtitle -->
                    <div class="mb-3">
                        <label class="form-label">Subtitle</label>
                        <input type="text" 
                               name="subtitle" 
                               class="form-control" 
                               placeholder="Enter banner subtitle (optional)" 
                               value="<?= set_value('subtitle', isset($banner) ? $banner['subtitle'] : '') ?>">
                    </div>

                    <!-- Button Text -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Button Text</label>
                            <input type="text" 
                                   name="button_text" 
                                   class="form-control" 
                                   placeholder="e.g., Shop Now" 
                                   value="<?= set_value('button_text', isset($banner) ? $banner['button_text'] : '') ?>">
                        </div>

                        <!-- Button Link -->
                        <div class="col-md-6">
                            <label class="form-label">Button Link</label>
                            <input type="text" 
                                   name="button_link" 
                                   class="form-control" 
                                   placeholder="e.g., products/category/shoes" 
                                   value="<?= set_value('button_link', isset($banner) ? $banner['button_link'] : '') ?>">
                        </div>
                    </div>

                </div>
            </div>

            <!-- Banner Schedule -->
            <div class="card-dark">
                <div class="card-header-dark">
                    <h6><i class="fas fa-calendar-alt"></i> Banner Schedule</h6>
                </div>
                <div class="card-body-dark">
                    <div class="row g-3">
                        <!-- Start Date -->
                        <div class="col-md-6">
                            <label class="form-label">Start Date</label>
                            <input type="date" 
                                   name="start_date" 
                                   class="form-control" 
                                   value="<?= set_value('start_date', isset($banner) ? $banner['start_date'] : '') ?>">
                            <small style="color:#666;">Leave empty for no start date restriction</small>
                        </div>

                        <!-- End Date -->
                        <div class="col-md-6">
                            <label class="form-label">End Date</label>
                            <input type="date" 
                                   name="end_date" 
                                   class="form-control" 
                                   value="<?= set_value('end_date', isset($banner) ? $banner['end_date'] : '') ?>">
                            <small style="color:#666;">Leave empty for no expiry</small>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            
            <!-- Banner Image -->
            <div class="card-dark mb-4">
                <div class="card-header-dark">
                    <h6><i class="fas fa-image"></i> Banner Image <?= !isset($banner) ? '<span style="color:var(--primary-red);">*</span>' : '' ?></h6>
                </div>
                <div class="card-body-dark">
                    <div class="image-upload-wrapper">
                        <input type="file" 
                               name="image" 
                               id="bannerImage" 
                               class="form-control" 
                               accept="image/jpeg,image/jpg,image/png,image/webp"
                               onchange="previewImage(event)"
                               <?= !isset($banner) ? 'required' : '' ?>>
                        <small style="color:#666;display:block;margin-top:8px;">
                            Allowed: JPG, JPEG, PNG, WEBP (Max: 2MB)
                        </small>
                        
                        <!-- Image Preview -->
                        <div id="imagePreview" style="margin-top:15px;">
                            <?php if (isset($banner) && !empty($banner['image'])): ?>
                                <img src="<?= base_url('uploads/banners/' . $banner['image']) ?>" 
                                     style="width:100%;height:auto;border-radius:8px;border:1px solid var(--border-color);">
                                <small style="color:#999;display:block;margin-top:8px;">
                                    Current image - Upload new to replace
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Banner Settings -->
            <div class="card-dark mb-4">
                <div class="card-header-dark">
                    <h6><i class="fas fa-cog"></i> Banner Settings</h6>
                </div>
                <div class="card-body-dark">
                    
                    <!-- Banner Type -->
                    <div class="mb-3">
                        <label class="form-label">Banner Type</label>
                        <select name="banner_type" class="form-select">
                            <option value="home" <?= set_select('banner_type', 'home', (isset($banner) && $banner['banner_type'] == 'home')) ?>>Home</option>
                            <option value="offer" <?= set_select('banner_type', 'offer', (isset($banner) && $banner['banner_type'] == 'offer')) ?>>Offer</option>
                            <option value="category" <?= set_select('banner_type', 'category', (isset($banner) && $banner['banner_type'] == 'category')) ?>>Category</option>
                            <option value="product" <?= set_select('banner_type', 'product', (isset($banner) && $banner['banner_type'] == 'product')) ?>>Product</option>
                        </select>
                    </div>

                    <!-- Display Order -->
                    <div class="mb-3">
                        <label class="form-label">Display Order</label>
                        <input type="number" 
                               name="display_order" 
                               class="form-control" 
                               placeholder="0" 
                               value="<?= set_value('display_order', isset($banner) ? $banner['display_order'] : '0') ?>"
                               min="0">
                        <small style="color:#666;">Lower number appears first</small>
                    </div>

                    <!-- Status -->
                    <div class="mb-0">
                        <label class="form-label">Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   name="is_active" 
                                   id="isActive" 
                                   value="1"
                                   <?= set_checkbox('is_active', '1', (isset($banner) ? $banner['is_active'] : true)) ?>>
                            <label class="form-check-label" for="isActive" style="color:#ccc;font-size:13px;">
                                Active
                            </label>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="card-dark">
                <div class="card-body-dark">
                    <button type="submit" class="btn-red" style="width:100%;margin-bottom:10px;">
                        <i class="fas fa-save"></i> <?= isset($banner) ? 'Update Banner' : 'Save Banner' ?>
                    </button>
                    <a href="<?= site_url('home_banners') ?>" class="btn-outline-light-custom" style="width:100%;">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </div>

        </div>
    </div>

</form>

<style>
.form-check-input {
    background-color: rgba(255, 255, 255, 0.1);
    border: 1px solid var(--border-color);
    width: 45px;
    height: 24px;
    cursor: pointer;
}

.form-check-input:checked {
    background-color: var(--primary-red);
    border-color: var(--primary-red);
}

.form-check-input:focus {
    box-shadow: 0 0 0 3px rgba(224, 16, 32, 0.1);
    border-color: var(--primary-red);
}

.image-upload-wrapper {
    position: relative;
}

#imagePreview img {
    display: block;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .col-lg-8, .col-lg-4 {
        width: 100%;
    }
}
</style>

<script>
function previewImage(event) {
    const reader = new FileReader();
    const imagePreview = document.getElementById('imagePreview');
    
    reader.onload = function() {
        imagePreview.innerHTML = '<img src="' + reader.result + '" style="width:100%;height:auto;border-radius:8px;border:1px solid var(--border-color);">';
    }
    
    if (event.target.files[0]) {
        reader.readAsDataURL(event.target.files[0]);
    }
}
</script>