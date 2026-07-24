<?php if ($this->session->flashdata('success')): ?>
    <div class="alert-custom alert-success-custom">
        <i class="fas fa-check-circle"></i>
        <?= $this->session->flashdata('success') ?>
    </div>
<?php endif; ?>

<?php if ($this->session->flashdata('error')): ?>
    <div class="alert-custom alert-danger-custom">
        <i class="fas fa-exclamation-circle"></i>
        <?= $this->session->flashdata('error') ?>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert-custom alert-danger-custom">
        <i class="fas fa-exclamation-circle"></i>
        <?= $error ?>
    </div>
<?php endif; ?>

<div class="page-header">
    <div>
        <h4>Help & Support Settings</h4>
        <p>Manage contact info and social media channels shown in the mobile application</p>
    </div>
</div>

<form method="POST" action="<?= site_url('admin/pages/help-support/update') ?>" class="hps-wrap">
    <!-- CSRF Protection -->
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

    <div class="row g-4">
        <!-- LEFT: Contact Details -->
        <div class="col-lg-6">
            <div class="card-dark h-100">
                <div class="card-header-dark">
                    <h6><i class="fas fa-address-book me-2" style="color:var(--primary-red);"></i>Direct Contact Details</h6>
                </div>
                <div class="card-body-dark">
                    <!-- Admin Number -->
                    <div class="hps-field">
                        <label class="hps-label">
                            <span class="hps-icon hps-icon--phone"><i class="fas fa-phone-alt"></i></span>
                            <span>Admin Contact Number</span>
                        </label>
                        <input type="text" name="phone_number" class="hps-input"
                            placeholder="e.g. +91 98765 43210"
                            value="<?= htmlspecialchars(set_value('phone_number', $settings->phone_number), ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <!-- Email Address -->
                    <div class="hps-field">
                        <label class="hps-label">
                            <span class="hps-icon hps-icon--email"><i class="fas fa-envelope"></i></span>
                            <span>Admin Email Address</span>
                        </label>
                        <input type="email" name="email" class="hps-input"
                            placeholder="e.g. contact@ghanshyammurtibhandar.com"
                            value="<?= htmlspecialchars(set_value('email', $settings->email), ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <!-- WhatsApp Number -->
                    <div class="hps-field hps-field--last">
                        <label class="hps-label">
                            <span class="hps-icon hps-icon--whatsapp"><i class="fab fa-whatsapp"></i></span>
                            <span>WhatsApp Number</span>
                        </label>
                        <input type="text" name="whatsapp_number" class="hps-input"
                            placeholder="e.g. +91 98765 43210"
                            value="<?= htmlspecialchars(set_value('whatsapp_number', $settings->whatsapp_number), ENT_QUOTES, 'UTF-8') ?>">
                        <small class="hps-hint">
                            Provide the number (with country code, without symbols) to initiate direct chat links.
                        </small>
                    </div>

                    <div class="hps-info-note">
                        <i class="fas fa-circle-info"></i>
                        These details power the "Contact Us" quick actions in the mobile app.
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT: Social Media Links -->
        <div class="col-lg-6">
            <div class="card-dark h-100">
                <div class="card-header-dark">
                    <h6><i class="fas fa-share-alt me-2" style="color:var(--primary-red);"></i>Social Channels & Handles</h6>
                </div>
                <div class="card-body-dark">
                    <!-- Telegram Link -->
                    <div class="hps-field">
                        <label class="hps-label">
                            <span class="hps-icon hps-icon--telegram"><i class="fab fa-telegram-plane"></i></span>
                            <span>Telegram Username or Group Link</span>
                        </label>
                        <input type="text" name="telegram_link" class="hps-input"
                            placeholder="e.g. https://t.me/ghanshyammurtibhandar"
                            value="<?= htmlspecialchars(set_value('telegram_link', $settings->telegram_link), ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <!-- Instagram Link -->
                    <div class="hps-field">
                        <label class="hps-label">
                            <span class="hps-icon hps-icon--instagram"><i class="fab fa-instagram"></i></span>
                            <span>Instagram Username or Profile Link</span>
                        </label>
                        <input type="text" name="instagram_link" class="hps-input"
                            placeholder="e.g. https://instagram.com/ghanshyammurtibhandar"
                            value="<?= htmlspecialchars(set_value('instagram_link', $settings->instagram_link), ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <!-- Facebook Link -->
                    <div class="hps-field">
                        <label class="hps-label">
                            <span class="hps-icon hps-icon--facebook"><i class="fab fa-facebook-f"></i></span>
                            <span>Facebook Profile or Page Link</span>
                        </label>
                        <input type="text" name="facebook_link" class="hps-input"
                            placeholder="e.g. https://facebook.com/ghanshyammurtibhandar"
                            value="<?= htmlspecialchars(set_value('facebook_link', $settings->facebook_link), ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <!-- YouTube Link -->
                    <div class="hps-field hps-field--last">
                        <label class="hps-label">
                            <span class="hps-icon hps-icon--youtube"><i class="fab fa-youtube"></i></span>
                            <span>YouTube Channel Link</span>
                        </label>
                        <input type="text" name="youtube_link" class="hps-input"
                            placeholder="e.g. https://youtube.com/c/ghanshyammurtibhandar"
                            value="<?= htmlspecialchars(set_value('youtube_link', $settings->youtube_link), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- ACTION BUTTONS -->
        <div class="col-12">
            <div class="card-dark">
                <div class="card-body-dark hps-actions">
                    <button type="submit" class="btn-red">
                        <i class="fas fa-save me-2"></i> Save Settings
                    </button>
                    <a href="<?= site_url('admin/pages') ?>" class="btn-outline-light-custom">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

<style>
    .hps-wrap {
        --hps-bg: #1c1c1c;
        --hps-border: #333;
        --hps-border-focus: var(--primary-red, #e63946);
        --hps-text: #fff;
        --hps-muted: #777;
        --hps-radius: 8px;
    }

    .hps-field {
        margin-bottom: 22px;
    }

    .hps-field--last {
        margin-bottom: 8px;
    }

    .hps-label {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 8px;
        font-size: 12.5px;
        font-weight: 600;
        color: #ccc;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .hps-icon {
        flex-shrink: 0;
        width: 26px;
        height: 26px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 7px;
        font-size: 12px;
        background: rgba(255, 255, 255, 0.06);
    }

    .hps-icon--phone {
        color: #0d6efd;
    }

    .hps-icon--email {
        color: #ea4335;
    }

    .hps-icon--whatsapp {
        color: #25d366;
    }

    .hps-icon--telegram {
        color: #0088cc;
    }

    .hps-icon--instagram {
        color: #e1306c;
    }

    .hps-icon--facebook {
        color: #1877f2;
    }

    .hps-icon--youtube {
        color: #ff0000;
    }

    .hps-input {
        width: 100%;
        padding: 11px 14px;
        background: var(--hps-bg);
        border: 1px solid var(--hps-border);
        color: var(--hps-text);
        border-radius: var(--hps-radius);
        font-size: 14px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }

    .hps-input::placeholder {
        color: #666;
    }

    .hps-input:focus {
        outline: none;
        border-color: var(--hps-border-focus);
        background: #201414;
        box-shadow: 0 0 0 3px rgba(230, 57, 70, 0.15);
    }

    .hps-hint {
        display: block;
        margin-top: 6px;
        color: var(--hps-muted);
        font-size: 11px;
        line-height: 1.4;
    }

    .hps-info-note {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        margin-top: 8px;
        padding: 10px 12px;
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid var(--hps-border);
        border-radius: var(--hps-radius);
        font-size: 11.5px;
        color: #999;
        line-height: 1.5;
    }

    .hps-info-note i {
        margin-top: 1px;
        color: #666;
    }

    .hps-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }

    .hps-actions .btn-red,
    .hps-actions .btn-outline-light-custom {
        padding: 10px 28px;
    }

    /* MOBILE */
    @media (max-width: 576px) {
        .hps-field {
            margin-bottom: 18px;
        }

        .hps-input {
            padding: 10px 12px;
            font-size: 13.5px;
        }

        .hps-actions {
            flex-direction: column-reverse;
            gap: 10px;
        }

        .hps-actions .btn-red,
        .hps-actions .btn-outline-light-custom {
            width: 100%;
            text-align: center;
            justify-content: center;
        }
    }
</style>