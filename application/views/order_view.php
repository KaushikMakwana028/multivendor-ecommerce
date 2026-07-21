<?php if ($this->session->flashdata('success')): ?>
    <div class="alert-custom alert-success-custom">
        <i class="fas fa-check-circle"></i>
        <?= $this->session->flashdata('success') ?>
    </div>
<?php endif; ?>

<div class="card-dark" style="margin-bottom: 20px;">
    <div class="card-body-dark">
        <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
            
            <!-- Search -->
            <div style="flex: 1; min-width: 250px;">
                <input type="text" 
                       id="searchInput" 
                       class="form-control-dark" 
                       placeholder="Search by order number, customer name, phone, email..."
                       autocomplete="off">
            </div>

            <!-- Status Filter -->
            <div>
                <select id="statusFilter" class="form-control-dark" style="min-width: 150px;">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="processing">Processing</option>
                    <option value="shipped">Shipped</option>
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <!-- Payment Status Filter -->
            <div>
                <select id="paymentStatusFilter" class="form-control-dark" style="min-width: 150px;">
                    <option value="">All Payments</option>
                    <option value="pending">Pending</option>
                    <option value="paid">Paid</option>
                    <option value="failed">Failed</option>
                </select>
            </div>

            <!-- Clear Filters -->
            <button id="clearFilters" class="btn-clear" style="display: none;">
                <i class="fas fa-times"></i> Clear
            </button>

            <!-- Results Info -->
            <div style="margin-left: auto; color: #999; font-size: 14px;">
                <span id="resultsInfo">Loading...</span>
            </div>

        </div>
    </div>
</div>

<!-- Loading Spinner -->
<div id="loadingSpinner" style="display: none; text-align: center; padding: 50px;">
    <div class="spinner-border" style="color: var(--primary-red);" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <p style="color: #999; margin-top: 15px;">Loading orders...</p>
</div>

<!-- Orders Table -->
<div class="card-dark" id="ordersTableContainer">
    <div class="card-body-dark" style="padding:0;">
        <div class="table-responsive">
            <table class="table-dark-custom">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Order Number</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="ordersTableBody">
                    <!-- Loaded via AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination Container -->
<div id="paginationContainer" style="margin-top: 20px;"></div>
<style>
/* Order Status Badges */
.order-status-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    display: inline-block;
    letter-spacing: 0.3px;
}

.badge-pending {
    background: rgba(255, 193, 7, 0.15);
    color: #ffc107;
    border: 1px solid rgba(255, 193, 7, 0.3);
}

.badge-confirmed {
    background: rgba(33, 150, 243, 0.15);
    color: #42a5f5;
    border: 1px solid rgba(33, 150, 243, 0.3);
}

.badge-processing {
    background: rgba(156, 39, 176, 0.15);
    color: #ab47bc;
    border: 1px solid rgba(156, 39, 176, 0.3);
}

.badge-shipped {
    background: rgba(255, 152, 0, 0.15);
    color: #ffa726;
    border: 1px solid rgba(255, 152, 0, 0.3);
}

.badge-delivered {
    background: rgba(76, 175, 80, 0.15);
    color: #81c784;
    border: 1px solid rgba(76, 175, 80, 0.3);
}

.badge-cancelled {
    background: rgba(244, 67, 54, 0.15);
    color: #e57373;
    border: 1px solid rgba(244, 67, 54, 0.3);
}

/* Dropdown Menu Dark Theme */
.dropdown-menu-dark {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 5px;
    min-width: 180px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.5);
}

.dropdown-menu-dark .dropdown-item {
    color: #ccc;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 13px;
    transition: all 0.2s;
}

.dropdown-menu-dark .dropdown-item:hover {
    background: rgba(255,255,255,0.05);
    color: var(--white-text);
}

.dropdown-menu-dark .dropdown-item.text-danger {
    color: #e57373;
}

.dropdown-menu-dark .dropdown-item.text-danger:hover {
    background: rgba(244, 67, 54, 0.1);
    color: #ff6b6b;
}

.dropdown-divider {
    border-top: 1px solid var(--border-color);
    margin: 5px 0;
}

/* Table Responsive */
.table-responsive {
    overflow-x: auto;
}

/* Mobile Responsive */
@media (max-width: 1200px) {
    .table-dark-custom {
        font-size: 12px;
    }
    
    .table-dark-custom thead th,
    .table-dark-custom tbody td {
        padding: 10px 8px;
    }
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        gap: 15px;
    }
    
    .page-header > div:last-child {
        width: 100%;
    }
    
    .page-header .form-select {
        width: 100% !important;
    }
    
    /* Convert table to card layout on mobile */
    .table-responsive {
        overflow-x: auto;
    }
    
    .table-dark-custom {
        min-width: 900px;
    }
}
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
    border: 1px solid var(--border-color);
    background: var(--card-bg);
    color: var(--white-text);
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 12px;
}

.pagination-btn:hover:not(.disabled) {
    background: var(--primary-red);
    border-color: var(--primary-red);
    transform: translateY(-2px);
}

.pagination-btn.active {
    background: var(--primary-red);
    border-color: var(--primary-red);
    font-weight: 600;
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

.form-control-dark {
    background: var(--light-gray);
    border: 1px solid var(--border-color);
    color: var(--white-text);
    padding: 10px 15px;
    border-radius: 6px;
    width: 100%;
}

.form-control-dark:focus {
    outline: none;
    border-color: var(--primary-red);
    background: var(--card-bg);
}

.btn-clear {
    background: var(--light-gray);
    border: 1px solid var(--border-color);
    color: var(--white-text);
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-clear:hover {
    background: var(--primary-red);
    border-color: var(--primary-red);
}

#ordersTableContainer {
    min-height: 400px;
    transition: opacity 0.3s;
}

.spinner-border {
    width: 3rem;
    height: 3rem;
    border: 0.3em solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    animation: spinner-border 0.75s linear infinite;
}

@keyframes spinner-border {
    to { transform: rotate(360deg); }
}
</style>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(document).ready(function() {
    let currentPage     = 1;
    let searchQuery     = '';
    let statusFilter    = '';
    let paymentFilter   = '';
    let searchTimeout   = null;
    let csrfToken       = '<?= $this->security->get_csrf_hash(); ?>';

    // Initial load
    loadOrders();

    // Search with debounce
    $('#searchInput').on('keyup', function() {
        clearTimeout(searchTimeout);
        const value = $(this).val().trim();
        searchTimeout = setTimeout(function() {
            searchQuery  = value;
            currentPage  = 1;
            loadOrders();
            updateClearButton();
        }, 500);
    });

    // Status filter
    $('#statusFilter').on('change', function() {
        statusFilter = $(this).val();
        currentPage  = 1;
        loadOrders();
        updateClearButton();
    });

    // Payment status filter
    $('#paymentStatusFilter').on('change', function() {
        paymentFilter = $(this).val();
        currentPage   = 1;
        loadOrders();
        updateClearButton();
    });

    // Clear filters
    $('#clearFilters').on('click', function() {
        $('#searchInput').val('');
        $('#statusFilter').val('');
        $('#paymentStatusFilter').val('');
        searchQuery   = '';
        statusFilter  = '';
        paymentFilter = '';
        currentPage   = 1;
        $(this).hide();
        loadOrders();
    });

    // Pagination click
    $(document).on('click', '.pagination-btn:not(.disabled)', function() {
        const page = $(this).data('page');
        if (page) {
            currentPage = page;
            loadOrders();
            $('html, body').animate({
                scrollTop: $('#ordersTableContainer').offset().top - 100
            }, 300);
        }
    });

    // Load orders function
    function loadOrders() {
        showLoading();

        $.ajax({
            url: '<?= site_url("order/get_orders") ?>',
            type: 'POST',
            data: {
                page: currentPage,
                search: searchQuery,
                status: statusFilter,
                payment_status: paymentFilter,
                csrf_test_name: csrfToken
            },
            dataType: 'json',
            success: function(response) {
                if (response.csrf_hash) {
                    csrfToken = response.csrf_hash;
                }
                if (response.status) {
                    $('#ordersTableBody').html(response.html);
                    $('#paginationContainer').html(response.pagination);

                    // Update results info
                    if (response.total_records > 0) {
                        const start = ((response.current_page - 1) * 20) + 1;
                        const end   = Math.min(response.current_page * 20, response.total_records);
                        let info    = `Showing ${start}-${end} of ${response.total_records} orders`;

                        let filters = [];
                        if (searchQuery)   filters.push(`search: "${searchQuery}"`);
                        if (statusFilter)  filters.push(`status: ${statusFilter}`);
                        if (paymentFilter) filters.push(`payment: ${paymentFilter}`);
                        if (filters.length) info += ` (${filters.join(', ')})`;

                        $('#resultsInfo').text(info);
                    } else {
                        $('#resultsInfo').text('No orders found');
                    }
                } else {
                    showError('Failed to load orders');
                }
            },
            error: function(xhr) {
                console.error('AJAX Error:', xhr);
                showError('Something went wrong. Please refresh the page.');
            },
            complete: function() {
                hideLoading();
            }
        });
    }

    // Update clear button visibility
    function updateClearButton() {
        (searchQuery || statusFilter || paymentFilter) 
            ? $('#clearFilters').show() 
            : $('#clearFilters').hide();
    }

    // Show loading
    function showLoading() {
        $('#loadingSpinner').show();
        $('#ordersTableContainer').css('opacity', '0.5');
    }

    // Hide loading
    function hideLoading() {
        $('#loadingSpinner').hide();
        $('#ordersTableContainer').css('opacity', '1');
    }

    // Show error
    function showError(message) {
        $('#ordersTableBody').html(`
            <tr>
                <td colspan="9" style="text-align:center; padding:50px; color:#E01020;">
                    <i class="fas fa-exclamation-triangle" style="font-size:42px; margin-bottom:14px; display:block;"></i>
                    <p>${message}</p>
                    <button onclick="location.reload()" class="btn-clear" style="margin-top:15px;">
                        <i class="fas fa-sync"></i> Reload Page
                    </button>
                </td>
            </tr>
        `);
        $('#paginationContainer').html('');
        $('#resultsInfo').text('Error loading data');
    }
});
function updateStatus(orderId, status) {
    if (!confirm('Are you sure you want to update this order status to ' + status + '?')) return false;
    window.location.href = '<?= site_url("order/update_status/") ?>' + orderId + '/' + status;
}

let currentOrderId = null;

function openCourierModal(orderId) {
    currentOrderId = orderId;
    document.getElementById('courierList').innerHTML = 'Loading couriers...';
    const modal = new bootstrap.Modal(document.getElementById('courierModal'));
    modal.show();

    fetch('<?= site_url("order/get_couriers/") ?>' + orderId)
        .then(res => res.json())
        .then(res => {
            let html = '';
            if (res.status == 200 && res.data?.available_courier_companies?.length) {
                res.data.available_courier_companies.forEach(c => {
                    const isRecommended = c.courier_company_id === res.data.recommended_courier_company_id;
                    html += `<div class="d-flex justify-content-between align-items-center border border-secondary p-2 mb-2 rounded">
                        <div><strong>${c.courier_name}</strong> ${isRecommended ? '<span class="badge bg-success">Recommended</span>' : ''}<br>
                        <small>Rate: ₹${c.rate} | ETD: ${c.etd} | Rating: ${c.rating}⭐</small></div>
<button class="btn btn-sm btn-primary" onclick="selectCourier(${c.courier_company_id}, '${c.etd}')">Select</button>                    </div>`;
                });
            } else {
                html = '<p class="text-danger">No couriers available.</p>';
            }
            document.getElementById('courierList').innerHTML = html;
        });
}

function selectCourier(courierId, etd) {
    if (!confirm('Assign this courier and generate AWB?')) return;

    fetch('<?= site_url("order/assign_courier/") ?>' + currentOrderId, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'courier_id=' + courierId + '&etd=' + encodeURIComponent(etd)
    })
    .then(res => res.json())
    .then(res => {
        if (res.status) { alert('Courier assigned! AWB: ' + res.awb_code); location.reload(); }
        else alert('Failed: ' + res.message);
    });
}

function refreshTracking(orderId) {
    fetch('<?= site_url("order/refresh_tracking/") ?>' + orderId)
        .then(res => res.json()).then(() => location.reload());
}

function schedulePickup(orderId) {
    if (!confirm('Schedule pickup for this shipment?')) return;
    fetch('<?= site_url("order/schedule_pickup/") ?>' + orderId)
        .then(res => res.json()).then(res => {
            alert(res.message);
            if (res.status) location.reload();
        });
}

function downloadLabel(orderId) {
    fetch('<?= site_url("order/download_label/") ?>' + orderId)
        .then(res => res.json()).then(res => {
            if (res.status) window.open(res.url, '_blank');
            else alert('Failed: ' + res.message);
        });
}

function downloadInvoice(orderId) {
    fetch('<?= site_url("order/download_invoice/") ?>' + orderId)
        .then(res => res.json()).then(res => {
            if (res.status) window.open(res.url, '_blank');
            else alert('Failed: ' + res.message);
        });
}
</script>