<?php if (!empty($error)): ?>
    <div class="alert-custom alert-danger-custom"><i class="fas fa-exclamation-circle me-2"></i><?= $error ?></div>
<?php endif; ?>

<?php if ($this->session->flashdata('success')): ?>
    <div class="alert-custom alert-success-custom"><i class="fas fa-check-circle me-2"></i><?= $this->session->flashdata('success') ?></div>
<?php endif; ?>

<div class="page-header">
    <div>
        <h4>Admin Profile</h4>
        <p>Manage your account settings, shop details, and profile photo.</p>
    </div>
</div>

<form method="POST" action="<?= site_url('profile') ?>" enctype="multipart/form-data">
    <input type="hidden"
        name="<?= $this->security->get_csrf_token_name(); ?>"
        value="<?= $this->security->get_csrf_hash(); ?>">

    <div class="row g-4">
        
        <!-- RIGHT COLUMN ON DESKTOP / TOP ON MOBILE: Avatar Upload -->
        <div class="col-lg-4 order-lg-2 order-1">
            <div class="card-dark text-center">
                <div class="card-header-dark">
                    <h6><i class="fas fa-camera me-2" style="color:var(--primary-red);"></i>Profile Photo</h6>
                </div>
                <div class="card-body-dark">
                    
                    <!-- Avatar Upload Circle with Camera Overlay Badge -->
                    <div class="avatar-upload-container">
                        <div class="avatar-wrapper" onclick="document.getElementById('profileImageInput').click();" title="Click to change profile picture">
                            <?php if (!empty($user->image)): ?>
                                <img src="<?= base_url('uploads/profile/' . $user->image) ?>" id="profilePreview" class="avatar-image">
                            <?php else: ?>
                                <img id="profilePreview" src="" style="display:none;" class="avatar-image">
                                <span id="profileLetter" class="avatar-letter-fallback"><?= strtoupper(substr($user->name ?? 'A', 0, 1)) ?></span>
                            <?php endif; ?>
                            
                            <!-- Floating Camera Icon Overlay -->
                            <div class="camera-icon-badge" title="Upload Photo">
                                <i class="fas fa-camera"></i>
                            </div>
                        </div>

                        <!-- Hidden File Input -->
                        <input type="file" name="image" id="profileImageInput" style="display:none;" accept="image/*" onchange="previewProfile(this)">
                        
                        <div class="avatar-hint">
                            <i class="fas fa-info-circle me-1"></i> Click avatar or camera icon to upload
                            <span class="d-block text-muted" style="font-size:11px; margin-top:2px;">JPG, PNG, WEBP — Max 2MB</span>
                        </div>
                    </div>

                    <div class="user-summary-meta">
                        <h5 class="user-name-title"><?= htmlspecialchars($user->name ?? 'Administrator') ?></h5>
                        <span class="user-role-badge"><i class="fas fa-shield-alt me-1"></i> Administrator</span>
                        <div class="user-email-text"><?= htmlspecialchars($user->email ?? '') ?></div>
                    </div>

                </div>
            </div>
        </div>

        <!-- LEFT COLUMN ON DESKTOP / MIDDLE ON MOBILE: Profile Info & Change Password -->
        <div class="col-lg-8 order-lg-1 order-2">
            
            <!-- Personal & Shop Info Card -->
            <div class="card-dark mb-4">
                <div class="card-header-dark">
                    <h6><i class="fas fa-user-cog me-2" style="color:var(--primary-red);"></i>Personal & Shop Information</h6>
                </div>
                <div class="card-body-dark">
                    <div class="row g-3">
                        <div class="col-md-6 col-12">
                            <label class="form-label">Full Name <span style="color:var(--primary-red)">*</span></label>
                            <input type="text" name="name" class="form-control-dark" placeholder="Enter your full name" value="<?= htmlspecialchars($user->name ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 col-12">
                            <label class="form-label">Shop Name <span style="color:var(--primary-red)">*</span></label>
                            <input type="text" name="shop_name" class="form-control-dark" placeholder="Enter shop name" value="<?= htmlspecialchars($user->shop_name ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 col-12">
                            <label class="form-label">Email Address <span style="color:var(--primary-red)">*</span></label>
                            <input type="email" name="email" class="form-control-dark" placeholder="Enter email address" value="<?= htmlspecialchars($user->email ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 col-12">
                            <label class="form-label">Mobile Number <span style="color:var(--primary-red)">*</span></label>
                            <input type="text" name="mobile" class="form-control-dark" placeholder="Enter mobile number" value="<?= htmlspecialchars($user->mobile ?? '') ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control-dark" rows="3" placeholder="Enter shop/billing address"><?= htmlspecialchars($user->address ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Change Password Card -->
            <div class="card-dark">
                <div class="card-header-dark">
                    <h6><i class="fas fa-key me-2" style="color:var(--primary-red);"></i>Change Password</h6>
                </div>
                <div class="card-body-dark">
                    <p style="font-size:12px;color:#999;margin-bottom:15px;">Leave password fields blank if you do not wish to change your current password.</p>
                    <div class="row g-3">
                        <div class="col-md-6 col-12">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control-dark" placeholder="Min 6 characters">
                        </div>
                        <div class="col-md-6 col-12">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control-dark" placeholder="Re-enter password">
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- BOTTOM ACTION CARD (Always at the very bottom of the page) -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card-dark">
                <div class="card-body-dark action-bar-body">
                    <a href="<?= site_url('dashboard') ?>" class="btn-cancel-custom">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn-red px-4 py-2" style="font-size:14px; font-weight:600;">
                        <i class="fas fa-save me-2"></i> Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<style>
/* Dark Inputs Styling */
.form-control-dark {
    background: #1a1a1a !important;
    border: 1px solid var(--border-color, #2d2d2d) !important;
    color: #ffffff !important;
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 13px;
    width: 100%;
    transition: all 0.2s ease-in-out;
}

.form-control-dark:focus {
    outline: none !important;
    border-color: var(--primary-red, #E01020) !important;
    box-shadow: 0 0 0 3px rgba(224, 16, 32, 0.15);
    background: #222222 !important;
}

textarea.form-control-dark {
    min-height: 90px;
    resize: vertical;
}

.form-label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #cccccc;
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Avatar Upload Container & Camera Badge */
.avatar-upload-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 10px 0;
}

.avatar-wrapper {
    position: relative;
    width: 130px;
    height: 130px;
    border-radius: 50%;
    background: #1a1a1a;
    border: 3px solid var(--border-color, #333333);
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
}

.avatar-wrapper:hover {
    border-color: var(--primary-red, #E01020);
    box-shadow: 0 0 0 4px rgba(224, 16, 32, 0.2), 0 8px 20px rgba(0,0,0,0.4);
    transform: translateY(-2px);
}

.avatar-image {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
}

.avatar-letter-fallback {
    font-size: 48px;
    font-weight: 700;
    color: var(--primary-red, #E01020);
}

/* Floating Camera Badge */
.camera-icon-badge {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: var(--primary-red, #E01020);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    border: 3px solid #141414;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
    transition: all 0.2s ease-in-out;
}

.avatar-wrapper:hover .camera-icon-badge {
    background: #ffffff;
    color: var(--primary-red, #E01020);
    transform: scale(1.1);
}

.avatar-hint {
    font-size: 12px;
    color: #999999;
    margin-top: 4px;
}

/* User Meta Summary */
.user-summary-meta {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid var(--border-color, #2d2d2d);
}

.user-name-title {
    font-size: 16px;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 4px;
}

.user-role-badge {
    display: inline-block;
    padding: 3px 10px;
    background: rgba(224, 16, 32, 0.12);
    color: var(--primary-red, #E01020);
    border: 1px solid rgba(224, 16, 32, 0.25);
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 6px;
}

.user-email-text {
    font-size: 12px;
    color: #888888;
}

/* Bottom Action Bar */
.action-bar-body {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
}

.btn-cancel-custom {
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid var(--border-color, #333333);
    color: #cccccc !important;
    padding: 9px 20px;
    border-radius: 8px;
    font-size: 13.5px;
    font-weight: 500;
    text-decoration: none !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease-in-out;
}

.btn-cancel-custom:hover {
    background: rgba(255, 255, 255, 0.12);
    color: #ffffff !important;
    border-color: #555555;
}

/* Mobile Responsive Adjustments */
@media (max-width: 768px) {
    .avatar-wrapper {
        width: 110px;
        height: 110px;
    }
    .camera-icon-badge {
        width: 34px;
        height: 34px;
        font-size: 14px;
    }
    .page-header h4 {
        font-size: 20px;
    }
    .action-bar-body {
        flex-direction: column-reverse;
        gap: 10px;
    }
    .action-bar-body button,
    .action-bar-body a {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
    function previewProfile(input) {
        const preview = document.getElementById('profilePreview');
        const letter  = document.getElementById('profileLetter');

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
