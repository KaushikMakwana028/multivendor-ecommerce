<?php if ($this->session->flashdata('success')): ?>
    <div class="alert-custom alert-success-custom">
        <i class="fas fa-check-circle"></i>
        <?= $this->session->flashdata('success') ?>
    </div>
<?php endif; ?>

<div class="page-header">
    <div>
        <h4>Products</h4>
        <p>Manage all your products.</p>
    </div>
    <a href="<?= site_url('product/add') ?>" class="btn-red">
        <i class="fas fa-plus"></i> Add Product
    </a>
</div>

<!-- Filters Card -->
<div class="card-dark mb-4">
    <div class="card-body-dark">
        <div class="row g-3 align-items-center">
            <div class="col-md-5 col-sm-6">
                <input type="text"
                    id="searchProduct"
                    class="form-control-dark"
                    placeholder="Search by product name..."
                    autocomplete="off">
            </div>

            <div class="col-md-4 col-sm-6">
                <select id="filterCategory" class="form-control-dark">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3 col-sm-12 d-flex align-items-center gap-2">
                <button id="resetBtn" onclick="resetFilters()" class="btn-clear" style="display:none;">
                    <i class="fas fa-redo"></i> Reset
                </button>
                <span id="resultsInfo" style="color: #999; font-size: 13px; margin-left: auto;"></span>
            </div>
        </div>
    </div>
</div>

<!-- Products Table Card -->
<div class="card-dark">
    <div class="card-body-dark" style="padding:0;">
        <div id="productTableContainer">
            <div style="padding:50px;text-align:center;color:#666;">
                <i class="fas fa-spinner fa-spin" style="font-size:32px; color: var(--primary-red);"></i>
                <p style="margin-top:10px; color:#999;">Loading products...</p>
            </div>
        </div>
    </div>
</div>

<div id="paginationContainer" style="margin-top:20px;"></div>

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

    /* Mobile Responsive Styling */
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .page-header .btn-red {
            width: 100%;
            justify-content: center;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table-dark-custom {
            min-width: 850px;
        }
    }
</style>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    let currentPage = 1;
    let searchTerm = '';
    let categoryFilter = '';
    let searchTimeout = null;

    function loadProducts(page = 1) {
        currentPage = page;
        $.ajax({
            url: '<?= site_url("product/ajax_list") ?>',
            type: 'GET',
            data: {
                page: page,
                search: searchTerm,
                category_id: categoryFilter
            },
            success: function(response) {
                const data = JSON.parse(response);
                renderProducts(data.products, data.pagination);
                renderPagination(data.pagination);
            }
        });
    }

    function renderProducts(products, paginationInfo) {
        updateResetButton();

        if (products.length === 0) {
            $('#productTableContainer').html(`
            <div style="padding:50px;text-align:center;color:#666;">
                <i class="fas fa-box-open" style="font-size:42px;margin-bottom:14px;display:block;opacity:0.4;"></i>
                <p style="font-size:14px; color:#999; margin:0;">No products found. <a href="<?= site_url('product/add') ?>" style="color:var(--primary-red);">Add your first product</a></p>
            </div>
        `);
            $('#resultsInfo').text('No products found');
            return;
        }

        if (paginationInfo) {
            const {
                current_page,
                total_records
            } = paginationInfo;
            const start = ((current_page - 1) * 10) + 1;
            const end = Math.min(current_page * 10, total_records);
            $('#resultsInfo').text(`Showing ${start}-${end} of ${total_records} products`);
        }

        let html = `<div class="table-responsive">
        <table class="table-dark-custom">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>MRP / Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>`;

        products.forEach((p, i) => {
            const img = p.image ?
                `<img src="<?= base_url('uploads/products/') ?>${p.image}" style="width:48px;height:48px;border-radius:8px;object-fit:cover;border:1px solid var(--border-color);">` :
                `<div style="width:48px;height:48px;background:var(--light-gray);border-radius:8px;display:flex;align-items:center;justify-content:center;border:1px solid var(--border-color);color:#555;"><i class="fas fa-image"></i></div>`;

            const stockColor = p.stock < 5 ? 'color:var(--primary-red)' : 'color:#fff';
            const status = p.is_active == 1 ?
                '<span class="badge-active">Active</span>' :
                '<span class="badge-inactive">Inactive</span>';

            html += `<tr>
            <td style="color:#666;">${(currentPage - 1) * 10 + i + 1}</td>
            <td>${img}</td>
            <td><div style="font-weight:600;color:#fff;">${p.name}</div></td>
            <td><span style="background:rgba(224,16,32,0.1);color:var(--primary-red);padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">${p.category_name || '-'}</span></td>
            <td>
                <div style="font-size:11px;color:#999;">MRP: ₹${parseFloat(p.mrp).toFixed(2)}</div>
                <div style="color:#4caf50;font-weight:600;">Price: ₹${parseFloat(p.price).toFixed(2)}</div>
            </td>
            <td><span style="${stockColor}">${p.stock}</span></td>
            <td>${status}</td>
            <td>
                <div style="display:flex;gap:5px;">
                    <a href="<?= site_url('product/edit/') ?>${p.id}" class="action-btn edit" title="Edit"><i class="fas fa-pencil-alt"></i></a>
                    <a href="<?= site_url('product/delete/') ?>${p.id}" class="action-btn delete" title="Delete" onclick="return confirm('Delete this product?')"><i class="fas fa-trash-alt"></i></a>
                </div>
            </td>
        </tr>`;
        });

        html += `</tbody></table></div>`;
        $('#productTableContainer').html(html);
    }

    function renderPagination(pagination) {
        const {
            current_page,
            total_pages
        } = pagination;
        if (total_pages <= 1) {
            $('#paginationContainer').html('');
            return;
        }

        let html = '<div class="custom-pagination"><div class="pagination-container">';

        // Previous button
        if (current_page > 1) {
            html += `<button onclick="loadProducts(${current_page - 1})" class="pagination-btn" title="Previous"><i class="fas fa-chevron-left"></i></button>`;
        } else {
            html += `<button class="pagination-btn disabled" disabled><i class="fas fa-chevron-left"></i></button>`;
        }

        // Show max 3 page buttons
        let startPage = Math.max(1, current_page - 1);
        let endPage = Math.min(total_pages, startPage + 2);

        if (endPage - startPage < 2) {
            startPage = Math.max(1, endPage - 2);
        }

        for (let i = startPage; i <= endPage; i++) {
            const activeClass = i === current_page ? 'active' : '';
            html += `<button onclick="loadProducts(${i})" class="pagination-btn ${activeClass}">${i}</button>`;
        }

        // Next button
        if (current_page < total_pages) {
            html += `<button onclick="loadProducts(${current_page + 1})" class="pagination-btn" title="Next"><i class="fas fa-chevron-right"></i></button>`;
        } else {
            html += `<button class="pagination-btn disabled" disabled><i class="fas fa-chevron-right"></i></button>`;
        }

        html += '</div></div>';
        $('#paginationContainer').html(html);
    }

    function updateResetButton() {
        if (searchTerm || categoryFilter) {
            $('#resetBtn').show();
        } else {
            $('#resetBtn').hide();
        }
    }

    function resetFilters() {
        searchTerm = '';
        categoryFilter = '';
        $('#searchProduct').val('');
        $('#filterCategory').val('');
        loadProducts(1);
    }

    $(document).ready(function() {
        loadProducts(1);

        $('#searchProduct').on('keyup input', function() {
            clearTimeout(searchTimeout);
            const val = $(this).val().trim();
            searchTimeout = setTimeout(function() {
                searchTerm = val;
                loadProducts(1);
            }, 300);
        });

        $('#filterCategory').on('change', function() {
            categoryFilter = $(this).val();
            loadProducts(1);
        });
    });
</script>