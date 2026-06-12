<?php if (!empty($error)): ?>
    <div class="alert-custom alert-danger-custom"><i class="fas fa-exclamation-circle me-2"></i><?= $error ?></div>
<?php endif; ?>

<?php if ($this->session->flashdata('success')): ?>
    <div class="alert-custom alert-success-custom"><i class="fas fa-check-circle me-2"></i><?= $this->session->flashdata('success') ?></div>
<?php endif; ?>

<div class="page-header">
    <div>
        <h4>Admin Profile</h4>
        <p>Manage your account settings and shop details.</p>
    </div>
</div>

<form method="POST" action="<?= site_url('profile') ?>" enctype="multipart/form-data">
    <input type="hidden"
        name="<?= $this->security->get_csrf_token_name(); ?>"
        value="<?= $this->security->get_csrf_hash(); ?>">

    <div class="row g-4">
        <!-- LEFT COLUMN: Profile Fields -->
        <div class="col-lg-8">
            <div class="card-dark mb-4">
                <div class="card-header-dark">
                    <h6><i class="fas fa-user-cog me-2" style="color:var(--primary-red);"></i>Personal & Shop Information</h6>
                </div>
                <div class="card-body-dark">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name <span style="color:var(--primary-red)">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Enter your name" value="<?= htmlspecialchars($user->name ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Shop Name <span style="color:var(--primary-red)">*</span></label>
                            <input type="text" name="shop_name" class="form-control" placeholder="Enter shop name" value="<?= htmlspecialchars($user->shop_name ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address <span style="color:var(--primary-red)">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="Enter email address" value="<?= htmlspecialchars($user->email ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mobile Number <span style="color:var(--primary-red)">*</span></label>
                            <input type="text" name="mobile" class="form-control" placeholder="Enter mobile number" value="<?= htmlspecialchars($user->mobile ?? '') ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="3" placeholder="Enter shop/billing address"><?= htmlspecialchars($user->address ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-dark">
                <div class="card-header-dark">
                    <h6><i class="fas fa-key me-2" style="color:var(--primary-red);"></i>Change Password</h6>
                </div>
                <div class="card-body-dark">
                    <p style="font-size:12px;color:#999;margin-bottom:15px;">Leave these fields blank if you do not want to change your password.</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Min 6 characters">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Re-enter password">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: Avatar Upload & Save -->
        <div class="col-lg-4">
            <div class="card-dark mb-4">
                <div class="card-header-dark">
                    <h6><i class="fas fa-camera me-2" style="color:var(--primary-red);"></i>Profile Photo</h6>
                </div>
                <div class="card-body-dark text-center">
                    <div style="margin-bottom:20px; display:flex; justify-content:center;">
                        <div style="width:140px; height:140px; border-radius:50%; overflow:hidden; border:2px solid var(--border-color); background:var(--light-gray); display:flex; align-items:center; justify-content:center; position:relative;">
                            <?php if (!empty($user->image)): ?>
                                <img src="<?= base_url('uploads/profile/' . $user->image) ?>" id="profilePreview" style="width:100%; height:100%; object-fit:cover;">
                            <?php else: ?>
                                <img id="profilePreview" src="" style="display:none; width:100%; height:100%; object-fit:cover;">
                                <span id="profileLetter" style="font-size:48px; font-weight:700; color:#888;"><?= strtoupper(substr($user->name ?? 'A', 0, 1)) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="mb-2">
                        <input type="file" name="image" class="form-control" accept="image/*" onchange="previewProfile(this)">
                        <small style="color:#666;font-size:11px;display:block;margin-top:5px;">JPG, PNG, WEBP — Max 2MB</small>
                    </div>
                </div>
            </div>

            <div class="card-dark">
                <div class="card-body-dark">
                    <button type="submit" class="btn-red w-100 justify-content-center">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="<?= site_url('dashboard') ?>" class="btn-outline-light-custom w-100 justify-content-center mt-2">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    function previewProfile(input) {
        const preview = document.getElementById('profilePreview');
        const letter = document.getElementById('profileLetter');

        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                if (letter) {
                    letter.style.display = 'none';
                }
            };

            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
