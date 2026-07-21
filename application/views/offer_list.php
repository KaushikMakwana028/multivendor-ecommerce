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
        <h4>Offers Management</h4>
        <p>Manage all promotional offers</p>
    </div>
    <a href="<?= site_url('offers/create') ?>" class="btn-red">
        <i class="fas fa-plus"></i> Add Offer
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
                       placeholder="Search by title or coupon..." 
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
                    <option value="flat" <?= strtolower($this->input->get('type') ?? '') === 'flat' ? 'selected' : '' ?>>Flat</option>
                    <option value="percentage" <?= strtolower($this->input->get('type') ?? '') === 'percentage' ? 'selected' : '' ?>>Percentage</option>
                    <option value="bogo" <?= strtolower($this->input->get('type') ?? '') === 'bogo' ? 'selected' : '' ?>>BOGO</option>
                    <option value="free_delivery" <?= strtolower($this->input->get('type') ?? '') === 'free_delivery' ? 'selected' : '' ?>>Free Delivery</option>
                    <option value="cashback" <?= strtolower($this->input->get('type') ?? '') === 'cashback' ? 'selected' : '' ?>>Cashback</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select id="featuredFilter" class="form-control-dark">
                    <option value="">All Featured</option>
                    <option value="1" <?= $this->input->get('featured') === '1' ? 'selected' : '' ?>>Featured</option>
                    <option value="0" <?= $this->input->get('featured') === '0' ? 'selected' : '' ?>>Not Featured</option>
                </select>
            </div>
            <div class="col-6 col-md-3 d-flex align-items-center gap-2">
                <button id="resetFilters" class="btn-clear" style="display: none;">
                    <i class="fas fa-redo"></i> Reset
                </button>
                <span id="resultsInfo" style="color: #999; font-size: 12px; margin-left: auto;">
                    Showing <?= !empty($offers) ? count($offers) : 0 ?> of <?= $total_records ?? count($offers ?? []) ?> offers
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
    <p style="color: #999; margin-top: 15px;">Updating offers list...</p>
</div>

<!-- Offers Table Card -->
<div class="card-dark" id="offersTableContainer">
    <div class="card-body-dark" style="padding:0;">
        <div class="table-responsive">
            <table class="table-dark-custom">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Offer Title</th>
                        <th>Coupon</th>
                        <th>Type</th>
                        <th>Discount</th>
                        <th>Applicable</th>
                        <th>Valid Till</th>
                        <th>Status</th>
                        <th>Featured</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="offersTableBody">
                    <?php if (!empty($offers)): ?>
                        <?php foreach ($offers as $i => $offer): ?>
                            <tr>
                                <td style="color:#666;"><?= $i + 1 ?></td>
                                <td>
                                    <?php if (!empty($offer['image'])): ?>
                                        <img src="<?= base_url('uploads/offers/' . $offer['image']) ?>" style="width:80px;height:50px;border-radius:6px;object-fit:cover;border:1px solid var(--border-color);">
                                    <?php else: ?>
                                        <div style="width:80px;height:50px;background:var(--light-gray);border-radius:6px;display:flex;align-items:center;justify-content:center;border:1px solid var(--border-color);color:#555;"><i class="fas fa-image"></i></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($offer['title']) ?></strong><br>
                                    <small style="color:#666;"><?= htmlspecialchars(substr($offer['subtitle'] ?? '', 0, 30)) ?></small>
                                </td>
                                <td>
                                    <?= $offer['coupon_code'] ? '<span style="background:rgba(255,255,255,0.1);padding:4px 8px;border-radius:4px;">' . htmlspecialchars($offer['coupon_code']) . '</span>' : '-' ?>
                                </td>
                                <td>
                                    <span class="offer-badge" style="background:rgba(33,150,243,0.15);color:#42a5f5;padding:4px 10px;border-radius:4px;font-size:11px;"><?= ucfirst($offer['offer_type']) ?></span>
                                </td>
                                <td>
                                    <?php if ($offer['discount_type'] == 'percentage'): ?>
                                        <strong><?= $offer['discount_value'] ?>%</strong> off
                                    <?php else: ?>
                                        <strong>₹<?= number_format($offer['discount_value'], 2) ?></strong>
                                    <?php endif; ?>
                                </td>
                                <td><small><?= ucfirst($offer['applicable_on']) ?></small></td>
                                <td>
                                    <small><?= date('d M Y', strtotime($offer['end_date'])) ?></small>
                                    <?php if ($offer['end_date'] < date('Y-m-d')): ?>
                                        <br><span style="color:#ff9800;font-size:10px;">Expired</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $offer['is_active'] ? '<span class="badge-active">Active</span>' : '<span class="badge-inactive">Inactive</span>' ?></td>
                                <td><?= $offer['is_featured'] ? '<span style="background:rgba(76,175,80,0.15);color:#81c784;padding:4px 10px;border-radius:4px;font-size:11px;">Featured</span>' : '' ?></td>
                                <td>
                                    <div style="display:flex;gap:5px;">
                                        <a href="<?= site_url('offers/edit/' . $offer['id']) ?>" class="action-btn edit" title="Edit"><i class="fas fa-pencil-alt"></i></a>
                                        <a href="<?= site_url('offers/change_status/' . $offer['id']) ?>" class="action-btn <?= $offer['is_active'] ? 'delete' : 'view' ?>" title="<?= $offer['is_active'] ? 'Deactivate' : 'Activate' ?>"><i class="fas fa-<?= $offer['is_active'] ? 'eye-slash' : 'eye' ?>"></i></a>
                                        <a href="<?= site_url('offers/toggle_featured/' . $offer['id']) ?>" class="action-btn <?= $offer['is_featured'] ? 'delete' : 'view' ?>" title="<?= $offer['is_featured'] ? 'Unfeature' : 'Feature' ?>"><i class="fas fa-<?= $offer['is_featured'] ? 'star' : 'star' ?>"></i></a>
                                        <a href="<?= site_url('offers/delete/' . $offer['id']) ?>" class="action-btn delete" title="Delete" onclick="return confirm('Delete this offer?')"><i class="fas fa-trash-alt"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" style="text-align:center; padding:50px; color:#666;">
                                <i class="fas fa-tag" style="font-size:42px; margin-bottom:14px; display:block; opacity:0.4;"></i>
                                <p style="font-size:14px; color:#999; margin:0;">No offers found.</p>
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

#offersTableContainer {
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
    let currentPage    = 1;
    let searchQuery    = $('#searchInput').val() ? $('#searchInput').val().trim() : '';
    let statusFilter   = $('#statusFilter').val() || '';
    let typeFilter     = $('#typeFilter').val() || '';
    let featuredFilter = $('#featuredFilter').val() || '';
    let searchTimeout  = null;
    let csrfToken      = '<?= $this->security->get_csrf_hash(); ?>';

    updateResetButton();

    // Auto Search on Keyup (Debounced)
    $('#searchInput').on('keyup input', function() {
        clearTimeout(searchTimeout);
        const val = $(this).val().trim();
        searchTimeout = setTimeout(function() {
            searchQuery = val;
            currentPage = 1;
            loadOffers();
            updateResetButton();
        }, 300);
    });

    // Auto Filter on Select Changes
    $('#statusFilter').on('change', function() {
        statusFilter = $(this).val();
        currentPage  = 1;
        loadOffers();
        updateResetButton();
    });

    $('#typeFilter').on('change', function() {
        typeFilter  = $(this).val();
        currentPage = 1;
        loadOffers();
        updateResetButton();
    });

    $('#featuredFilter').on('change', function() {
        featuredFilter = $(this).val();
        currentPage    = 1;
        loadOffers();
        updateResetButton();
    });

    // Reset Filters
    $('#resetFilters').on('click', function() {
        $('#searchInput').val('');
        $('#statusFilter').val('');
        $('#typeFilter').val('');
        $('#featuredFilter').val('');
        searchQuery    = '';
        statusFilter   = '';
        typeFilter     = '';
        featuredFilter = '';
        currentPage    = 1;
        updateResetButton();
        loadOffers();
    });

    // Pagination Click
    $(document).on('click', '.pagination-btn:not(.disabled)', function() {
        const page = $(this).data('page');
        if (page) {
            currentPage = page;
            loadOffers();
            $('html, body').animate({
                scrollTop: $('#offersTableContainer').offset().top - 100
            }, 300);
        }
    });

    // AJAX Load Function
    function loadOffers() {
        showLoading();

        $.ajax({
            url: '<?= site_url("offers/get_offers") ?>',
            type: 'POST',
            data: {
                page: currentPage,
                search: searchQuery,
                status: statusFilter,
                type: typeFilter,
                featured: featuredFilter,
                csrf_test_name: csrfToken
            },
            dataType: 'json',
            success: function(response) {
                if (response.csrf_hash) {
                    csrfToken = response.csrf_hash;
                }
                if (response.status) {
                    $('#offersTableBody').html(response.html);
                    $('#paginationContainer').html(response.pagination);

                    if (response.total_records > 0) {
                        const start = ((response.current_page - 1) * 10) + 1;
                        const end   = Math.min(response.current_page * 10, response.total_records);
                        let info    = `Showing ${start}-${end} of ${response.total_records} offers`;

                        let activeFilters = [];
                        if (searchQuery)    activeFilters.push(`search: "${searchQuery}"`);
                        if (statusFilter)   activeFilters.push(`status: ${statusFilter == '1' ? 'Active' : 'Inactive'}`);
                        if (typeFilter)     activeFilters.push(`type: ${typeFilter}`);
                        if (featuredFilter) activeFilters.push(`featured: ${featuredFilter == '1' ? 'Featured' : 'Not Featured'}`);
                        if (activeFilters.length) info += ` (${activeFilters.join(', ')})`;

                        $('#resultsInfo').text(info);
                    } else {
                        $('#resultsInfo').text('No offers found');
                    }
                } else {
                    showError('Failed to load offers');
                }
            },
            error: function(xhr) {
                console.error('AJAX Error:', xhr);
                showError('Something went wrong loading offers.');
            },
            complete: function() {
                hideLoading();
            }
        });
    }

    function updateResetButton() {
        if (searchQuery || statusFilter || typeFilter || featuredFilter) {
            $('#resetFilters').show();
        } else {
            $('#resetFilters').hide();
        }
    }

    function showLoading() {
        $('#loadingSpinner').show();
        $('#offersTableContainer').css('opacity', '0.5');
    }

    function hideLoading() {
        $('#loadingSpinner').hide();
        $('#offersTableContainer').css('opacity', '1');
    }

    function showError(msg) {
        $('#offersTableBody').html(`
            <tr>
                <td colspan="11" style="text-align:center; padding:50px; color:#E01020;">
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