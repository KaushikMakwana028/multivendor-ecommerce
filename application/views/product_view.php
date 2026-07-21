<?php if ($this->session->flashdata('success')): ?>
    <div class="alert-custom alert-success-custom"><i class="fas fa-check-circle"></i><?= $this->session->flashdata('success') ?></div>
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

<div class="card-dark" style="margin-bottom:20px;">
    <div class="card-body-dark">
        <div style="display:flex;gap:15px;flex-wrap:wrap;">
            <input type="text" id="searchProduct" placeholder="Search by product name..." style="flex:1;min-width:200px;padding:10px;border:1px solid var(--border-color);border-radius:8px;background:var(--card-bg);color:#fff;">
            
            <select id="filterCategory" style="padding:10px;border:1px solid var(--border-color);border-radius:8px;background:var(--card-bg);color:#fff;min-width:200px;">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <button onclick="resetFilters()" style="padding:10px 20px;border:1px solid var(--border-color);border-radius:8px;background:var(--card-bg);color:#fff;cursor:pointer;">
                <i class="fas fa-redo"></i> Reset
            </button>
        </div>
    </div>
</div>

<div class="card-dark">
    <div class="card-body-dark" style="padding:0;">
        <div id="productTableContainer">
            <div style="padding:50px;text-align:center;color:#666;">
                <i class="fas fa-spinner fa-spin" style="font-size:32px;"></i>
            </div>
        </div>
    </div>
</div>

<div id="paginationContainer" style="margin-top:20px;text-align:center;"></div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
let currentPage = 1;
let searchTerm = '';
let categoryFilter = '';

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
            renderProducts(data.products);
            renderPagination(data.pagination);
        }
    });
}

function renderProducts(products) {
    if (products.length === 0) {
        $('#productTableContainer').html(`
            <div style="padding:50px;text-align:center;color:#666;">
                <i class="fas fa-box-open" style="font-size:42px;margin-bottom:14px;display:block;"></i>
                No products found. <a href="<?= site_url('product/add') ?>" style="color:var(--primary-red);">Add your first product</a>
            </div>
        `);
        return;
    }

    let html = `<table class="table-dark-custom">
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
        const img = p.image 
            ? `<img src="<?= base_url('uploads/products/') ?>${p.image}" style="width:48px;height:48px;border-radius:8px;object-fit:cover;border:1px solid var(--border-color);">`
            : `<div style="width:48px;height:48px;background:var(--light-gray);border-radius:8px;display:flex;align-items:center;justify-content:center;border:1px solid var(--border-color);color:#555;"><i class="fas fa-image"></i></div>`;

        const stockColor = p.stock < 5 ? 'color:var(--primary-red)' : 'color:#fff';
        const status = p.is_active == 1 
            ? '<span class="badge-active">Active</span>' 
            : '<span class="badge-inactive">Inactive</span>';

        html += `<tr>
            <td style="color:#666;">${(currentPage - 1) * 10 + i + 1}</td>
            <td>${img}</td>
            <td><div style="font-weight:600;">${p.name}</div></td>
            <td><span style="background:rgba(224,16,32,0.1);color:var(--primary-red);padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">${p.category_name || '-'}</span></td>
            <td>
                <div style="font-size:11px;color:#999;">MRP: ₹${parseFloat(p.mrp).toFixed(2)}</div>
                <div style="color:#4caf50;font-weight:600;">Price: ₹${parseFloat(p.price).toFixed(2)}</div>
            </td>
            <td><span style="${stockColor}">${p.stock}</span></td>
            <td>${status}</td>
            <td>
                <a href="<?= site_url('product/edit/') ?>${p.id}" class="action-btn edit" title="Edit"><i class="fas fa-pencil-alt"></i></a>
                <a href="<?= site_url('product/delete/') ?>${p.id}" class="action-btn delete" title="Delete" onclick="return confirm('Delete this product?')"><i class="fas fa-trash-alt"></i></a>
            </td>
        </tr>`;
    });

    html += `</tbody></table>`;
    $('#productTableContainer').html(html);
}

function renderPagination(pagination) {
    const { current_page, total_pages } = pagination;
    if (total_pages <= 1) {
        $('#paginationContainer').html('');
        return;
    }

    let html = '<div style="display:flex;gap:10px;justify-content:center;align-items:center;">';

    // Previous button
    if (current_page > 1) {
        html += `<button onclick="loadProducts(${current_page - 1})" style="padding:8px 15px;border:1px solid var(--border-color);border-radius:6px;background:var(--card-bg);color:#fff;cursor:pointer;"><i class="fas fa-chevron-left"></i></button>`;
    }

    // Show max 3 page buttons
    let startPage = Math.max(1, current_page - 1);
    let endPage = Math.min(total_pages, startPage + 2);
    
    if (endPage - startPage < 2) {
        startPage = Math.max(1, endPage - 2);
    }

    for (let i = startPage; i <= endPage; i++) {
        const active = i === current_page ? 'background:var(--primary-red);' : '';
        html += `<button onclick="loadProducts(${i})" style="padding:8px 15px;border:1px solid var(--border-color);border-radius:6px;${active}background:var(--card-bg);color:#fff;cursor:pointer;font-weight:${i === current_page ? '700' : '400'};">${i}</button>`;
    }

    // Next button
    if (current_page < total_pages) {
        html += `<button onclick="loadProducts(${current_page + 1})" style="padding:8px 15px;border:1px solid var(--border-color);border-radius:6px;background:var(--card-bg);color:#fff;cursor:pointer;"><i class="fas fa-chevron-right"></i></button>`;
    }

    html += '</div>';
    $('#paginationContainer').html(html);
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

    $('#searchProduct').on('keyup', function() {
        searchTerm = $(this).val();
        loadProducts(1);
    });

    $('#filterCategory').on('change', function() {
        categoryFilter = $(this).val();
        loadProducts(1);
    });
});
</script>
<style>
    /* ===== MOBILE RESPONSIVE FOR ADD PRODUCT PAGE ===== */
    @media (max-width: 768px) {

        /* Make columns stack vertically */
        .row.g-4 {
            display: block;
        }

        .col-lg-8,
        .col-lg-4 {
            width: 100%;
            float: none;
            margin-bottom: 20px;
        }

        /* Fix clearfix for row */
        .row:after {
            content: "";
            display: table;
            clear: both;
        }

        /* Page header - stack vertically */
        .page-header {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        /* Back button on mobile - inline style fix */
        .page-header .btn-outline-light-custom {
            display: inline-flex;
            width: auto;
        }

        /* Make form inputs full width */
        .form-control,
        .form-select {
            width: 100%;
            box-sizing: border-box;
        }

        /* Stack pricing fields vertically */
        .row.g-3 {
            display: block;
        }

        .row.g-3 .col-md-4,
        .row.g-3 .col-md-6 {
            width: 100%;
            margin-bottom: 15px;
            float: none;
        }

        /* Clearfix for inner rows */
        .row.g-3:after {
            content: "";
            display: table;
            clear: both;
        }

        /* Fix card margins */
        .card-dark {
            width: 100%;
            margin-bottom: 20px;
            overflow: visible;
        }

        /* Make buttons full width on mobile */
        .btn-red,
        .btn-outline-light-custom {
            width: 100%;
            display: flex;
            justify-content: center;
        }

        /* Fix button spacing */
        .card-dark.mt-4 {
            margin-top: 0;
        }

        /* Responsive image preview */
        #imagePreview {
            width: 100%;
            height: auto;
            max-height: 250px;
            object-fit: cover;
        }

        /* Adjust textarea height */
        textarea.form-control {
            min-height: 100px;
        }

        /* Small text adjustments */
        .form-label {
            display: block;
            margin-bottom: 5px;
        }

        /* Ensure proper spacing between elements */
        .mb-3 {
            margin-bottom: 16px;
        }

        .card-body-dark {
            padding: 16px;
        }

        .card-header-dark {
            padding: 12px 16px;
        }
    }

    /* Extra small devices (phones under 480px) */
    @media (max-width: 480px) {
        .card-body-dark {
            padding: 12px;
        }

        .form-control,
        .form-select {
            font-size: 16px;
            /* Prevents zoom on iOS */
            padding: 8px 12px;
        }

        .btn-red,
        .btn-outline-light-custom {
            padding: 10px;
            font-size: 14px;
        }

        .page-header h4 {
            font-size: 20px;
            margin-bottom: 5px;
        }

        .page-header p {
            font-size: 12px;
        }
    }
</style>