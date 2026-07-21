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

<div class="page-header">
    <div>
        <h4>Home Banners</h4>
        <p>Manage all homepage banners</p>
    </div>
    <a href="<?= site_url('home_banners/create') ?>" class="btn-red">
        <i class="fas fa-plus"></i> Add Banner
    </a>
</div>

<!-- Filters Card -->
<div class="card-dark mb-4 filter-card-container">
    <div class="card-body-dark">
        <div class="row g-2 align-items-center">
            <div class="col-12 col-md-3">
                <input type="text" 
                       id="searchInput" 
                       class="form-control-dark" 
                       placeholder="Search by title..." 
                       value="<?= htmlspecialchars($this->input->get('search') ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       autocomplete="off">
            </div>
            <div class="col-6 col-md-2">
                <select id="statusFilter" class="form-control-dark">
                    <option value="">All Status</option>
                    <option value="1" <?= $this->input->get('status') === '1' ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= $this->input->get('status') === '0' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select id="typeFilter" class="form-control-dark">
                    <option value="">All Types</option>
                    <option value="home" <?= strtolower($this->input->get('type') ?? '') === 'home' ? 'selected' : '' ?>>Home</option>
                    <option value="offer" <?= strtolower($this->input->get('type') ?? '') === 'offer' ? 'selected' : '' ?>>Offer</option>
                    <option value="category" <?= strtolower($this->input->get('type') ?? '') === 'category' ? 'selected' : '' ?>>Category</option>
                    <option value="product" <?= strtolower($this->input->get('type') ?? '') === 'product' ? 'selected' : '' ?>>Product</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select id="sortFilter" class="form-control-dark">
                    <option value="newest" <?= ($this->input->get('sort') ?? 'newest') === 'newest' ? 'selected' : '' ?>>Newest First</option>
                    <option value="oldest" <?= $this->input->get('sort') === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                    <option value="display_order" <?= $this->input->get('sort') === 'display_order' ? 'selected' : '' ?>>Display Order</option>
                </select>
            </div>
            <div class="col-6 col-md-3 d-flex align-items-center gap-2">
                <button id="resetFilters" class="btn-clear" style="display: none;">
                    <i class="fas fa-redo"></i> Reset
                </button>
                <span id="resultsInfo" style="color: #999; font-size: 12px; margin-left: auto;">
                    Showing <?= !empty($banners) ? count($banners) : 0 ?> of <?= $total_records ?? count($banners ?? []) ?> banners
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Loading Spinner -->
<div id="loadingSpinner" style="display: none; text-align: center; padding: 50px;">
    <div class="spinner-border" style="color: var(--primary-red);" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <p style="color: #999; margin-top: 15px;">Updating banners list...</p>
</div>

<!-- Banners List -->
<div class="card-dark" id="bannersTableContainer">
    <div class="card-body-dark" style="padding:0;">
        <div class="table-responsive">
            <table class="table-dark-custom">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Banner Title</th>
                        <th>Banner Type</th>
                        <th>Display Order</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="bannersTableBody">
                    <?php if (!empty($banners)): ?>
                        <?php foreach ($banners as $i => $banner): ?>
                            <tr>
                                <td style="color:#666;"><?= $i + 1 ?></td>
                                
                                <!-- Image Preview -->
                                <td>
                                    <?php if (!empty($banner['image'])): ?>
                                        <img src="<?= base_url('uploads/banners/' . $banner['image']) ?>"
                                             style="width:100px;height:60px;border-radius:8px;object-fit:cover;border:1px solid var(--border-color);"
                                             alt="<?= htmlspecialchars($banner['title']) ?>">
                                    <?php else: ?>
                                        <div style="width:100px;height:60px;background:var(--light-gray);border-radius:8px;display:flex;align-items:center;justify-content:center;border:1px solid var(--border-color);color:#555;">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- Banner Title -->
                                <td>
                                    <div style="font-weight:600;color:#fff;">
                                        <?= htmlspecialchars($banner['title']) ?>
                                    </div>
                                    <?php if (!empty($banner['subtitle'])): ?>
                                        <div style="font-size:11px;color:#999;margin-top:3px;">
                                            <?= htmlspecialchars(mb_strlen($banner['subtitle']) > 50 ? mb_substr($banner['subtitle'], 0, 50) . '...' : $banner['subtitle']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($banner['button_text'])): ?>
                                        <div style="font-size:10px;color:var(--primary-red);margin-top:3px;">
                                            <i class="fas fa-link"></i> <?= htmlspecialchars($banner['button_text']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- Banner Type -->
                                <td>
                                    <?php
                                    $type_badges = [
                                        'home'     => ['class' => 'badge-home', 'icon' => 'home'],
                                        'offer'    => ['class' => 'badge-offer', 'icon' => 'tags'],
                                        'category' => ['class' => 'badge-category', 'icon' => 'th-large'],
                                        'product'  => ['class' => 'badge-product', 'icon' => 'box']
                                    ];
                                    $type_badge = $type_badges[$banner['banner_type']] ?? $type_badges['home'];
                                    ?>
                                    <span class="banner-type-badge <?= $type_badge['class'] ?>">
                                        <i class="fas fa-<?= $type_badge['icon'] ?>"></i>
                                        <?= ucfirst($banner['banner_type']) ?>
                                    </span>
                                </td>

                                <!-- Display Order -->
                                <td>
                                    <span style="background:rgba(255,255,255,0.05);padding:4px 10px;border-radius:6px;font-weight:600;font-size:13px;color:#fff;">
                                        <?= $banner['display_order'] ?>
                                    </span>
                                </td>

                                <!-- Start Date -->
                                <td>
                                    <?php if (!empty($banner['start_date'])): ?>
                                        <div style="font-size:12px;color:#ccc;">
                                            <?= date('d M Y', strtotime($banner['start_date'])) ?>
                                        </div>
                                    <?php else: ?>
                                        <span style="color:#666;">-</span>
                                    <?php endif; ?>
                                </td>

                                <!-- End Date -->
                                <td>
                                    <?php if (!empty($banner['end_date'])): ?>
                                        <div style="font-size:12px;color:#ccc;">
                                            <?= date('d M Y', strtotime($banner['end_date'])) ?>
                                        </div>
                                        <?php if ($banner['end_date'] < date('Y-m-d')): ?>
                                            <span style="font-size:10px;color:#e57373;">Expired</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span style="color:#666;">-</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Status -->
                                <td>
                                    <?php if ($banner['is_active']): ?>
                                        <span class="badge-active">Active</span>
                                    <?php else: ?>
                                        <span class="badge-inactive">Inactive</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Created Date -->
                                <td>
                                    <div style="font-size:12px;color:#ccc;">
                                        <?= date('d M Y', strtotime($banner['created_at'])) ?>
                                    </div>
                                    <div style="font-size:11px;color:#666;">
                                        <?= date('h:i A', strtotime($banner['created_at'])) ?>
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td>
                                    <div style="display:flex;gap:5px;">
                                        <a href="<?= site_url('home_banners/edit/' . $banner['id']) ?>" 
                                           class="action-btn edit" 
                                           title="Edit">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>

                                        <a href="<?= site_url('home_banners/change_status/' . $banner['id']) ?>" 
                                           class="action-btn <?= $banner['is_active'] ? 'delete' : 'view' ?>" 
                                           title="<?= $banner['is_active'] ? 'Deactivate' : 'Activate' ?>">
                                            <i class="fas fa-<?= $banner['is_active'] ? 'eye-slash' : 'eye' ?>"></i>
                                        </a>

                                        <a href="<?= site_url('home_banners/delete/' . $banner['id']) ?>" 
                                           class="action-btn delete" 
                                           title="Delete" 
                                           onclick="return confirm('Are you sure you want to delete this banner? This will also delete the uploaded image.')">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" style="text-align:center; padding:50px; color:#666;">
                                <i class="fas fa-images" style="font-size:42px; margin-bottom:14px; display:block; opacity:0.4;"></i>
                                <p style="font-size:14px; color:#999; margin:0;">No banners found.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination Container -->
<div id="paginationContainer" style="margin-top: 20px;">
    <?php if (!empty($pagination)): ?>
        <?= $pagination ?>
    <?php endif; ?>
</div>

<style>
/* Dark Inputs and Filter Controls Styling */
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

select.form-control-dark {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23999999' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E") !important;
    background-repeat: no-repeat !important;
    background-position: right 14px center !important;
    padding-right: 36px !important;
    cursor: pointer;
}

select.form-control-dark option {
    background-color: #1a1a1a;
    color: #ffffff;
    padding: 10px;
}

.btn-clear {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid var(--border-color, #2d2d2d);
    color: #cccccc;
    padding: 9px 18px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease-in-out;
}

.btn-clear:hover {
    background: var(--primary-red, #E01020);
    border-color: var(--primary-red, #E01020);
    color: #ffffff;
}

/* Banner Type Badges */
.banner-type-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.badge-home {
    background: rgba(33, 150, 243, 0.15);
    color: #42a5f5;
    border: 1px solid rgba(33, 150, 243, 0.3);
}

.badge-offer {
    background: rgba(255, 152, 0, 0.15);
    color: #ffa726;
    border: 1px solid rgba(255, 152, 0, 0.3);
}

.badge-category {
    background: rgba(156, 39, 176, 0.15);
    color: #ab47bc;
    border: 1px solid rgba(156, 39, 176, 0.3);
}

.badge-product {
    background: rgba(76, 175, 80, 0.15);
    color: #81c784;
    border: 1px solid rgba(76, 175, 80, 0.3);
}

#bannersTableContainer {
    min-height: 350px;
    transition: opacity 0.3s;
}

/* Custom Pagination Styling */
.custom-pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 20px 0;
}

.pagination-container {
    display: flex;
    gap: 8px;
    align-items: center;
}

.pagination-btn {
    min-width: 40px;
    height: 40px;
    border: 1px solid var(--border-color, #333333);
    background: #1a1a1a;
    color: var(--white-text, #ffffff);
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s ease-in-out;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 12px;
}

.pagination-btn:hover:not(.disabled) {
    background: var(--primary-red, #E01020);
    border-color: var(--primary-red, #E01020);
    color: #ffffff;
    transform: translateY(-2px);
}

.pagination-btn.active {
    background: var(--primary-red, #E01020);
    border-color: var(--primary-red, #E01020);
    font-weight: 700;
    color: #ffffff;
}

.pagination-btn.disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

.pagination-dots {
    color: #666;
    padding: 0 8px;
    font-weight: 600;
}

/* Responsive */
@media (max-width: 768px) {
    .filter-card-container {
        margin-bottom: 12px !important;
    }
    .filter-card-container .card-body-dark {
        padding: 10px 12px !important;
    }
    .filter-card-container .row.g-2 {
        --bs-gutter-x: 6px !important;
        --bs-gutter-y: 6px !important;
    }
    .form-control-dark {
        padding: 6px 8px !important;
        font-size: 11px !important;
        height: 34px !important;
        border-radius: 6px !important;
    }
    select.form-control-dark {
        background-position: right 6px center !important;
        padding-right: 22px !important;
    }
    .btn-clear {
        padding: 6px 8px !important;
        font-size: 11px !important;
        height: 34px !important;
        border-radius: 6px !important;
        width: 100% !important;
        justify-content: center !important;
    }
    #resultsInfo {
        font-size: 10px !important;
        white-space: nowrap;
        text-align: right;
    }
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .table-dark-custom {
        min-width: 900px;
    }
}
</style>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    let currentPage   = 1;
    let searchQuery   = $('#searchInput').val() ? $('#searchInput').val().trim() : '';
    let statusFilter  = $('#statusFilter').val() || '';
    let typeFilter    = $('#typeFilter').val() || '';
    let sortFilter    = $('#sortFilter').val() || 'newest';
    let searchTimeout = null;
    let csrfToken     = '<?= $this->security->get_csrf_hash(); ?>';

    updateResetButton();

    // Auto Search on Keyup (Debounced)
    $('#searchInput').on('keyup input', function() {
        clearTimeout(searchTimeout);
        const val = $(this).val().trim();
        searchTimeout = setTimeout(function() {
            searchQuery = val;
            currentPage = 1;
            loadBanners();
            updateResetButton();
        }, 300);
    });

    // Auto Filter on Select Changes
    $('#statusFilter').on('change', function() {
        statusFilter = $(this).val();
        currentPage  = 1;
        loadBanners();
        updateResetButton();
    });

    $('#typeFilter').on('change', function() {
        typeFilter  = $(this).val();
        currentPage = 1;
        loadBanners();
        updateResetButton();
    });

    $('#sortFilter').on('change', function() {
        sortFilter  = $(this).val();
        currentPage = 1;
        loadBanners();
        updateResetButton();
    });

    // Reset Filters
    $('#resetFilters').on('click', function() {
        $('#searchInput').val('');
        $('#statusFilter').val('');
        $('#typeFilter').val('');
        $('#sortFilter').val('newest');
        searchQuery  = '';
        statusFilter = '';
        typeFilter   = '';
        sortFilter   = 'newest';
        currentPage  = 1;
        updateResetButton();
        loadBanners();
    });

    // Pagination Click
    $(document).on('click', '.pagination-btn:not(.disabled)', function() {
        const page = $(this).data('page');
        if (page) {
            currentPage = page;
            loadBanners();
            $('html, body').animate({
                scrollTop: $('#bannersTableContainer').offset().top - 100
            }, 300);
        }
    });

    // AJAX Load Function
    function loadBanners() {
        showLoading();

        $.ajax({
            url: '<?= site_url("home_banners/get_banners") ?>',
            type: 'POST',
            data: {
                page: currentPage,
                search: searchQuery,
                status: statusFilter,
                type: typeFilter,
                sort: sortFilter,
                csrf_test_name: csrfToken
            },
            dataType: 'json',
            success: function(response) {
                if (response.csrf_hash) {
                    csrfToken = response.csrf_hash;
                }
                if (response.status) {
                    $('#bannersTableBody').html(response.html);
                    $('#paginationContainer').html(response.pagination);

                    if (response.total_records > 0) {
                        const start = ((response.current_page - 1) * 10) + 1;
                        const end   = Math.min(response.current_page * 10, response.total_records);
                        let info    = `Showing ${start}-${end} of ${response.total_records} banners`;

                        let activeFilters = [];
                        if (searchQuery)  activeFilters.push(`search: "${searchQuery}"`);
                        if (statusFilter) activeFilters.push(`status: ${statusFilter == '1' ? 'Active' : 'Inactive'}`);
                        if (typeFilter)   activeFilters.push(`type: ${typeFilter}`);
                        if (activeFilters.length) info += ` (${activeFilters.join(', ')})`;

                        $('#resultsInfo').text(info);
                    } else {
                        $('#resultsInfo').text('No banners found');
                    }
                } else {
                    showError('Failed to load banners');
                }
            },
            error: function(xhr) {
                console.error('AJAX Error:', xhr);
                showError('Something went wrong loading banners.');
            },
            complete: function() {
                hideLoading();
            }
        });
    }

    function updateResetButton() {
        if (searchQuery || statusFilter || typeFilter || (sortFilter && sortFilter !== 'newest')) {
            $('#resetFilters').show();
        } else {
            $('#resetFilters').hide();
        }
    }

    function showLoading() {
        $('#loadingSpinner').show();
        $('#bannersTableContainer').css('opacity', '0.5');
    }

    function hideLoading() {
        $('#loadingSpinner').hide();
        $('#bannersTableContainer').css('opacity', '1');
    }

    function showError(msg) {
        $('#bannersTableBody').html(`
            <tr>
                <td colspan="10" style="text-align:center; padding:50px; color:#E01020;">
                    <i class="fas fa-exclamation-triangle" style="font-size:42px; margin-bottom:14px; display:block;"></i>
                    <p>${msg}</p>
                </td>
            </tr>
        `);
        $('#paginationContainer').html('');
        $('#resultsInfo').text('Error loading data');
    }
});
</script>