<?php if (!empty($error)): ?>
    <div class="alert-custom alert-danger-custom">
        <i class="fas fa-exclamation-circle"></i>
        <?= $error ?>
    </div>
<?php endif; ?>

<div class="page-header">
    <div>
        <h4>Edit Policy Page</h4>
        <p>Modify the content of <?= htmlspecialchars($page->title, ENT_QUOTES, 'UTF-8') ?></p>
    </div>
    <a href="<?= site_url('admin/pages') ?>" class="btn-outline-light-custom">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
</div>

<form method="POST" action="<?= site_url('admin/pages/update/' . $page->id) ?>">
    <!-- CSRF Protection -->
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

    <div class="row g-4">
        <!-- Editor Column -->
        <div class="col-lg-8">
            <div class="card-dark mb-4">
                <div class="card-header-dark">
                    <h6><i class="fas fa-edit me-2" style="color:var(--primary-red);"></i>Page Content Editor</h6>
                </div>
                <div class="card-body-dark">
                    <div class="mb-3">
                        <label class="form-label">Page Title <span style="color:var(--primary-red)">*</span></label>
                        <input type="text" name="title" class="form-control-dark w-100" style="padding: 10px; background: #222; border: 1px solid #444; color: #fff; border-radius: 6px;" value="<?= htmlspecialchars(set_value('title', $page->title), ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Page Slug (Read-only)</label>
                        <input type="text" class="form-control-dark w-100" style="padding: 10px; background: #151515; border: 1px solid #333; color: #888; border-radius: 6px;" value="<?= htmlspecialchars($page->slug, ENT_QUOTES, 'UTF-8') ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label mb-2">Content <span style="color:var(--primary-red)">*</span></label>
                        <!-- Textarea for Summernote -->
                        <textarea id="policyEditor" name="content" class="form-control" rows="15"><?= set_value('content', $page->content) ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Instructions Column & Action Panel -->
        <div class="col-lg-4">
            <!-- Action Panel -->
            <div class="card-dark mb-4">
                <div class="card-header-dark">
                    <h6><i class="fas fa-save me-2" style="color:var(--primary-red);"></i>Actions</h6>
                </div>
                <div class="card-body-dark d-grid gap-2">
                    <button type="submit" class="btn-red w-100 py-2.5">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                    <a href="<?= site_url('admin/pages') ?>" class="btn btn-outline-secondary w-100 py-2.5" style="border-color: #444; color: #ccc;">
                        Cancel
                    </a>
                </div>
            </div>

            <!-- Writing Instructions -->
            <div class="card-dark">
                <div class="card-header-dark">
                    <h6><i class="fas fa-info-circle me-2" style="color:var(--primary-red);"></i>Writing Guidelines</h6>
                </div>
                <div class="card-body-dark" style="font-size: 14px; line-height: 1.6; color: #ccc;">
                    <p class="mb-3">Follow these guidelines to keep policies clear, professional, and visually consistent with the customer application interface:</p>
                    
                    <ul class="ps-3 mb-3" style="list-style-type: decimal;">
                        <li class="mb-2">
                            <strong>Use Section Headings:</strong> 
                            Highlight main sections using the <strong>Header 2 (H2)</strong> format. (Avoid Header 1 as the system already defines one at the top).
                        </li>
                        <li class="mb-2">
                            <strong>Keep Paragraphs Readable:</strong> 
                            Structure explanations into brief paragraphs. Press <strong>Enter</strong> to create a new paragraph.
                        </li>
                        <li class="mb-2">
                            <strong>Format Lists Properly:</strong> 
                            Use the bulleted or numbered list options for itemized clauses (e.g., types of data collected, non-refundable situations).
                        </li>
                        <li class="mb-2">
                            <strong>Emphasize Key Terms:</strong> 
                            Use **Bold** styling (`<strong>`) on terms, rights, and labels to make scanning easy.
                        </li>
                        <li class="mb-2">
                            <strong>Complete All Details:</strong> 
                            Double check that no placeholder text is saved. Replace all brackets/temp text with actual contact emails or physical address info.
                        </li>
                    </ul>

                    <div style="background: #222; border-left: 3px solid var(--primary-red); padding: 10px; border-radius: 4px; font-size: 13px; color: #bbb;">
                        <i class="fas fa-lightbulb me-1" style="color: #ffd700;"></i>
                        <strong>Aesthetics tip:</strong> The frontend application renders this content with a light background and custom typography. Using bullet lists and structured headings guarantees the layout adapts beautifully.
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Include Summernote Lite CDN dependencies -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<style>
    /* Styling adjustments to integrate Summernote Lite nicely with the dark admin theme */
    .note-editor.note-frame {
        border: 1px solid #444 !important;
        background-color: #fff !important;
        border-radius: 6px;
        overflow: hidden;
    }
    
    /* Make editor text area clear and legible (resembles the ivory light theme on public page) */
    .note-editable {
        background-color: #fff !important;
        color: #222 !important;
        font-family: Arial, sans-serif;
        font-size: 15px;
    }

    .note-placeholder {
        color: #888 !important;
    }

    .note-toolbar {
        background-color: #f5f5f5 !important;
        border-bottom: 1px solid #ddd !important;
    }

    .btn-red {
        background-color: var(--primary-red);
        color: #fff;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        transition: background 0.2s;
    }

    .btn-red:hover {
        background-color: var(--dark-red);
        color: #fff;
    }
    
    .btn-outline-light-custom {
        border: 1px solid #555;
        color: #ddd;
        background: transparent;
        padding: 6px 12px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 14px;
        transition: all 0.2s;
    }
    .btn-outline-light-custom:hover {
        background: #333;
        color: #fff;
        border-color: #777;
    }
</style>

<script>
    $(document).ready(function() {
        $('#policyEditor').summernote({
            placeholder: 'Write your policy content here...',
            tabsize: 2,
            height: 450,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'hr']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            styleTags: [
                'p',
                { title: 'Blockquote', tag: 'blockquote', className: 'blockquote', value: 'blockquote' },
                'h2', 'h3', 'h4'
            ]
        });
    });
</script>
