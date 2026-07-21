<?php if (validation_errors()): ?>
    <div class="alert-custom alert-danger-custom"><i class="fas fa-exclamation-circle"></i><?= validation_errors() ?></div>
<?php endif; ?>

<div class="page-header">
    <div><a href="<?= site_url('offers') ?>" class="btn-outline-light-custom" style="margin-bottom:10px;"><i class="fas fa-arrow-left"></i> Back</a><h4><?= isset($offer) ? 'Edit Offer' : 'Add Offer' ?></h4></div>
</div>

<form action="<?= isset($offer) ? site_url('offers/update/' . $offer['id']) : site_url('offers/store') ?>" method="post" enctype="multipart/form-data">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card-dark mb-4">
                <div class="card-header-dark"><h6><i class="fas fa-info-circle"></i> Basic Information</h6></div>
                <div class="card-body-dark">
                    <div class="mb-3"><label class="form-label">Offer Title *</label><input type="text" name="title" class="form-control" placeholder="Enter offer title" value="<?= set_value('title', isset($offer) ? $offer['title'] : '') ?>" required></div>
                    <div class="mb-3"><label class="form-label">Subtitle</label><input type="text" name="subtitle" class="form-control" placeholder="Enter subtitle" value="<?= set_value('subtitle', isset($offer) ? $offer['subtitle'] : '') ?>"></div>
                    <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3" placeholder="Enter description"><?= set_value('description', isset($offer) ? $offer['description'] : '') ?></textarea></div>
                </div>
            </div>
<input type="hidden"
       name="<?= $this->security->get_csrf_token_name(); ?>"
       value="<?= $this->security->get_csrf_hash(); ?>">
            <div class="card-dark mb-4">
                <div class="card-header-dark"><h6><i class="fas fa-percent"></i> Offer Details</h6></div>
                <div class="card-body-dark">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Offer Type *</label>
                            <select name="offer_type" class="form-select" onchange="toggleDiscountFields()" required>
                                <option value="">Select Type</option>
                                <option value="flat" <?= set_select('offer_type', 'flat', isset($offer) && $offer['offer_type'] == 'flat') ?>>Flat Discount</option>
                                <option value="percentage" <?= set_select('offer_type', 'percentage', isset($offer) && $offer['offer_type'] == 'percentage') ?>>Percentage Discount</option>
                                <option value="bogo" <?= set_select('offer_type', 'bogo', isset($offer) && $offer['offer_type'] == 'bogo') ?>>Buy One Get One</option>
                                <option value="free_delivery" <?= set_select('offer_type', 'free_delivery', isset($offer) && $offer['offer_type'] == 'free_delivery') ?>>Free Delivery</option>
                                <option value="cashback" <?= set_select('offer_type', 'cashback', isset($offer) && $offer['offer_type'] == 'cashback') ?>>Cashback</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Discount Type</label>
                            <select name="discount_type" class="form-select">
                                <option value="">Select Type</option>
                                <option value="amount" <?= set_select('discount_type', 'amount', isset($offer) && $offer['discount_type'] == 'amount') ?>>Amount (₹)</option>
                                <option value="percentage" <?= set_select('discount_type', 'percentage', isset($offer) && $offer['discount_type'] == 'percentage') ?>>Percentage (%)</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mt-2">
                        <div class="col-md-6"><label class="form-label">Discount Value</label><input type="number" name="discount_value" class="form-control" placeholder="Enter value" step="0.01" value="<?= set_value('discount_value', isset($offer) ? $offer['discount_value'] : '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Maximum Discount</label><input type="number" name="maximum_discount" class="form-control" placeholder="Enter maximum discount" step="0.01" value="<?= set_value('maximum_discount', isset($offer) ? $offer['maximum_discount'] : '') ?>"></div>
                    </div>
                    <div class="row g-3 mt-2">
                        <div class="col-md-6"><label class="form-label">Coupon Code</label><input type="text" name="coupon_code" class="form-control" placeholder="Enter coupon code" value="<?= set_value('coupon_code', isset($offer) ? $offer['coupon_code'] : '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Minimum Order Amount</label><input type="number" name="minimum_order_amount" class="form-control" placeholder="0" step="0.01" value="<?= set_value('minimum_order_amount', isset($offer) ? $offer['minimum_order_amount'] : '0') ?>"></div>
                    </div>
                </div>
            </div>

            <div class="card-dark">
                <div class="card-header-dark"><h6><i class="fas fa-calendar-alt"></i> Validity</h6></div>
                <div class="card-body-dark">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Start Date *</label><input type="date" name="start_date" class="form-control" value="<?= set_value('start_date', isset($offer) ? $offer['start_date'] : '') ?>" required></div>
                        <div class="col-md-6"><label class="form-label">End Date *</label><input type="date" name="end_date" class="form-control" value="<?= set_value('end_date', isset($offer) ? $offer['end_date'] : '') ?>" required></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-dark mb-4">
                <div class="card-header-dark"><h6><i class="fas fa-image"></i> Offer Image</h6></div>
                <div class="card-body-dark">
                    <input type="file" name="image" class="form-control" accept="image/jpeg,image/jpg,image/png,image/webp" onchange="previewImage(event)">
                    <small style="color:#666;display:block;margin-top:5px;">JPG, PNG, WEBP (Max 2MB)</small>
                    <div id="imagePreview" style="margin-top:10px;">
                        <?php if (isset($offer) && !empty($offer['image'])): ?>
                            <img src="<?= base_url('uploads/offers/' . $offer['image']) ?>" style="width:100%;border-radius:6px;border:1px solid var(--border-color);">
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card-dark mb-4">
                <div class="card-header-dark"><h6><i class="fas fa-cog"></i> Settings</h6></div>
                <div class="card-body-dark">
                    <div class="mb-3">
                        <label class="form-label">Applicable On</label>
                        <select name="applicable_on" class="form-select" onchange="toggleApplicableFields()">
                            <option value="all" <?= set_select('applicable_on', 'all', !isset($offer) || $offer['applicable_on'] == 'all') ?>>All Products</option>
                            <option value="category" <?= set_select('applicable_on', 'category', isset($offer) && $offer['applicable_on'] == 'category') ?>>Specific Category</option>
                            <option value="product" <?= set_select('applicable_on', 'product', isset($offer) && $offer['applicable_on'] == 'product') ?>>Specific Product</option>
                        </select>
                    </div>
                    <div id="categoryField" style="display:none;" class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">Select Category</option>
                            <?php foreach ($categories ?? [] as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= set_select('category_id', $cat['id'], isset($offer) && $offer['category_id'] == $cat['id']) ?>><?= $cat['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div id="productField" style="display:none;" class="mb-3">
                        <label class="form-label">Product</label>
                        <select name="product_id" class="form-select">
                            <option value="">Select Product</option>
                            <?php foreach ($products ?? [] as $prod): ?>
                                <option value="<?= $prod['id'] ?>" <?= set_select('product_id', $prod['id'], isset($offer) && $offer['product_id'] == $prod['id']) ?>><?= $prod['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Display Order</label>
                        <input type="number" name="display_order" class="form-control" value="<?= set_value('display_order', isset($offer) ? $offer['display_order'] : '0') ?>" min="0">
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_featured" id="isFeatured" value="1" <?= set_checkbox('is_featured', '1', isset($offer) && $offer['is_featured']) ?>>
                        <label class="form-check-label" for="isFeatured" style="color:#ccc;">Featured Offer</label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" <?= set_checkbox('is_active', '1', !isset($offer) || $offer['is_active']) ?>>
                        <label class="form-check-label" for="isActive" style="color:#ccc;">Active</label>
                    </div>
                </div>
            </div>

            <div class="card-dark">
                <div class="card-body-dark">
                    <button type="submit" class="btn-red" style="width:100%;margin-bottom:10px;"><i class="fas fa-save"></i> <?= isset($offer) ? 'Update Offer' : 'Save Offer' ?></button>
                    <a href="<?= site_url('offers') ?>" class="btn-outline-light-custom" style="width:100%;"><i class="fas fa-times"></i> Cancel</a>
                </div>
            </div>
        </div>
    </div>
</form>

<style>
.form-check-input {
    background-color: rgba(255,255,255,0.1);
    border: 1px solid var(--border-color);
    width: 45px;
    height: 24px;
}
.form-check-input:checked {
    background-color: var(--primary-red);
    border-color: var(--primary-red);
}
</style>

<script>
function previewImage(e) {
    const reader = new FileReader();
    reader.onload = function() {
        document.getElementById('imagePreview').innerHTML = '<img src="' + reader.result + '" style="width:100%;border-radius:6px;border:1px solid var(--border-color);">';
    }
    if (e.target.files[0]) reader.readAsDataURL(e.target.files[0]);
}

function toggleApplicableFields() {
    const val = document.querySelector('[name="applicable_on"]').value;
    document.getElementById('categoryField').style.display = val === 'category' ? 'block' : 'none';
    document.getElementById('productField').style.display = val === 'product' ? 'block' : 'none';
}

function toggleDiscountFields() {

    const offerType = document.querySelector('[name="offer_type"]').value;

    const show = ['flat', 'percentage', 'cashback'].includes(offerType);

    document.querySelector('[name="discount_type"]')
        .closest('.col-md-6').style.display = show ? 'block' : 'none';

    document.querySelector('[name="discount_value"]')
        .closest('.col-md-6').style.display = show ? 'block' : 'none';

    document.querySelector('[name="maximum_discount"]')
        .closest('.col-md-6').style.display = show ? 'block' : 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    toggleApplicableFields();
    toggleDiscountFields();
});
</script>