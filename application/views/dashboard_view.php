<?php if ($this->session->flashdata('success')): ?>
    <div class="alert-success">
        <i class="fas fa-check-circle"></i>
        <?= $this->session->flashdata('success') ?>
    </div>
<?php endif; ?>

<div class="page-header">
    <div>
        <h4>Dashboard</h4>
        <p>Welcome back, <strong><?= htmlspecialchars($this->session->userdata('admin_name')) ?></strong>! Here's your shop overview.</p>
    </div>
    <div class="header-actions">
        <a href="<?= site_url('product/add') ?>" class="btn-primary">
            <i class="fas fa-plus"></i> Add Product
        </a>
        <a href="<?= site_url('order') ?>" class="btn-secondary">
            <i class="fas fa-shopping-cart"></i> Orders
        </a>
    </div>
</div>

<!-- REVENUE STATS -->
<div class="stats-grid">
    <div class="stat-card revenue">
        <div class="stat-icon">
            <i class="fas fa-rupee-sign"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Total Revenue</p>
            <h3 class="stat-value">₹<?= number_format($total_revenue, 2) ?></h3>
            <span class="stat-meta">All time</span>
        </div>
    </div>

    <div class="stat-card today">
        <div class="stat-icon">
            <i class="fas fa-calendar-day"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Today's Revenue</p>
            <h3 class="stat-value">₹<?= number_format($today_revenue, 2) ?></h3>
            <span class="stat-meta"><?= date('d M Y') ?></span>
        </div>
    </div>

    <div class="stat-card month">
        <div class="stat-icon">
            <i class="fas fa-calendar-alt"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">This Month</p>
            <h3 class="stat-value">₹<?= number_format($month_revenue, 2) ?></h3>
            <span class="stat-meta"><?= date('F Y') ?></span>
        </div>
    </div>

    <div class="stat-card customers">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Total Customers</p>
            <h3 class="stat-value"><?= number_format($total_customers) ?></h3>
            <span class="stat-meta">Registered users</span>
        </div>
    </div>
</div>

<!-- ORDER & PRODUCT STATS -->
<div class="secondary-stats">
    <div class="stat-mini">
        <i class="fas fa-shopping-bag"></i>
        <div>
            <strong><?= number_format($total_orders) ?></strong>
            <span>Total Orders</span>
        </div>
    </div>
    <div class="stat-mini pending">
        <i class="fas fa-clock"></i>
        <div>
            <strong><?= number_format($pending_orders) ?></strong>
            <span>Pending Orders</span>
        </div>
    </div>
    <div class="stat-mini processing">
        <i class="fas fa-sync-alt"></i>
        <div>
            <strong><?= number_format($processing_orders) ?></strong>
            <span>Processing</span>
        </div>
    </div>
    <div class="stat-mini completed">
        <i class="fas fa-check-circle"></i>
        <div>
            <strong><?= number_format($completed_orders) ?></strong>
            <span>Completed</span>
        </div>
    </div>
    <div class="stat-mini products">
        <i class="fas fa-boxes"></i>
        <div>
            <strong><?= number_format($total_products) ?></strong>
            <span>Total Products</span>
        </div>
    </div>
    <div class="stat-mini low-stock">
        <i class="fas fa-exclamation-triangle"></i>
        <div>
            <strong><?= number_format($low_stock_count) ?></strong>
            <span>Low Stock</span>
        </div>
    </div>
</div>

<!-- CHARTS ROW -->
<div class="charts-grid">
    <div class="chart-card">
        <div class="chart-header">
            <h6><i class="fas fa-chart-line"></i> Revenue Trend (Last 7 Days)</h6>
        </div>
        <div class="chart-body">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <div class="chart-card">
        <div class="chart-header">
            <h6><i class="fas fa-chart-pie"></i> Orders by Status</h6>
        </div>
        <div class="chart-body">
            <canvas id="orderStatusChart"></canvas>
        </div>
    </div>
</div>

<!-- RECENT ORDERS & LOW STOCK -->
<div class="tables-grid">
    <!-- RECENT ORDERS -->
    <div class="table-card">
        <div class="table-card-header">
            <h6><i class="fas fa-shopping-cart"></i> Recent Orders</h6>
            <a href="<?= site_url('order') ?>" class="link-primary">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="table-responsive">
            <?php if (!empty($recent_orders)): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td>
                                    <strong class="order-number">#<?= $order['order_number'] ?></strong>
                                </td>
                                <td>
                                    <div class="customer-info">
                                        <span class="customer-name"><?= htmlspecialchars($order['customer_name']) ?></span>
                                        <span class="customer-mobile"><?= $order['mobile'] ?? '' ?></span>
                                    </div>
                                </td>
                                <td class="amount">₹<?= number_format($order['total_amount'], 2) ?></td>
                                <td>
                                    <?php
                                    $status_class = [
                                        'pending' => 'badge-warning',
                                        'processing' => 'badge-info',
                                        'shipped' => 'badge-primary',
                                        'delivered' => 'badge-success',
                                        'cancelled' => 'badge-danger'
                                    ];
                                    $class = $status_class[$order['status']] ?? 'badge-secondary';
                                    ?>
                                    <span class="badge <?= $class ?>"><?= ucfirst($order['status']) ?></span>
                                </td>
                                <td class="date"><?= date('d M, h:i A', strtotime($order['created_at'])) ?></td>
                                <td>
                                    <a href="<?= site_url('orders/view/' . $order['id']) ?>" 
                                       class="btn-action" 
                                       title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-shopping-cart"></i>
                    <p>No orders yet</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- LOW STOCK ALERT -->
    <div class="table-card alert-card">
        <div class="table-card-header">
            <h6><i class="fas fa-exclamation-triangle"></i> Low Stock Alert</h6>
            <a href="<?= site_url('product') ?>" class="link-danger">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="low-stock-list">
            <?php if (!empty($low_stock_products)): ?>
                <?php foreach ($low_stock_products as $product): ?>
                    <div class="low-stock-item">
                        <div class="product-thumb">
                            <?php if (!empty($product['image'])): ?>
                                <img src="<?= base_url('uploads/products/' . $product['image']) ?>" 
                                     alt="<?= htmlspecialchars($product['name']) ?>">
                            <?php else: ?>
                                <div class="thumb-placeholder">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <strong><?= htmlspecialchars($product['name']) ?></strong>
                            <span class="stock-warning">
                                <i class="fas fa-box"></i> Only <?= $product['stock'] ?> left
                            </span>
                        </div>
                        <a href="<?= site_url('product/edit/' . $product['id']) ?>" class="btn-restock">
                            <i class="fas fa-plus"></i> Restock
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state small">
                    <i class="fas fa-check-circle"></i>
                    <p>All products are well stocked!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Revenue Chart
const revenueData = <?= $revenue_chart ?>;
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: revenueData.map(d => d.date),
        datasets: [{
            label: 'Revenue (₹)',
            data: revenueData.map(d => d.amount),
            borderColor: '#E01020',
            backgroundColor: 'rgba(224, 16, 32, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: '#2a2a2a' }, ticks: { color: '#999' } },
            x: { grid: { display: false }, ticks: { color: '#999' } }
        }
    }
});

// Order Status Chart
const orderStatus = <?= $order_status_chart ?>;
const orderCtx = document.getElementById('orderStatusChart').getContext('2d');
new Chart(orderCtx, {
    type: 'doughnut',
    data: {
        labels: ['Pending', 'Processing', 'Completed'],
        datasets: [{
            data: [orderStatus.pending, orderStatus.processing, orderStatus.completed],
            backgroundColor: ['#ff9800', '#2196F3', '#4caf50'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', labels: { color: '#fff', padding: 15 } }
        }
    }
});
</script>
<style>
    /* ========== DASHBOARD STYLES ========== */

/* Page Header */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 16px;
}

.page-header h4 {
    margin: 0;
    font-size: 1.75rem;
    font-weight: 700;
    color: #fff;
}

.page-header p {
    margin: 4px 0 0 0;
    color: #999;
    font-size: 0.9rem;
}

.header-actions {
    display: flex;
    gap: 12px;
}

.btn-primary, .btn-secondary {
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
}

.btn-primary {
    background: var(--primary-red);
    color: #fff;
    border: none;
}

.btn-primary:hover {
    background: #c00d1a;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(224, 16, 32, 0.3);
}

.btn-secondary {
    background: transparent;
    color: #fff;
    border: 1px solid var(--border-color);
}

.btn-secondary:hover {
    border-color: var(--primary-red);
    color: var(--primary-red);
}

/* Alert */
.alert-success {
    background: rgba(76, 175, 80, 0.1);
    border: 1px solid #4caf50;
    color: #4caf50;
    padding: 14px 18px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Main Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 24px;
}

.stat-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: all 0.3s;
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: var(--primary-red);
    opacity: 0;
    transition: opacity 0.3s;
}

.stat-card:hover::before {
    opacity: 1;
}

.stat-card:hover {
    border-color: var(--primary-red);
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.3);
}

.stat-icon {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    flex-shrink: 0;
}

.stat-card.revenue .stat-icon { background: rgba(76, 175, 80, 0.1); color: #4caf50; }
.stat-card.today .stat-icon { background: rgba(33, 150, 243, 0.1); color: #2196F3; }
.stat-card.month .stat-icon { background: rgba(255, 152, 0, 0.1); color: #ff9800; }
.stat-card.customers .stat-icon { background: rgba(156, 39, 176, 0.1); color: #9c27b0; }

.stat-content {
    flex: 1;
}

.stat-label {
    margin: 0 0 4px 0;
    font-size: 12px;
    color: #999;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-value {
    margin: 0 0 4px 0;
    font-size: 28px;
    font-weight: 700;
    color: #fff;
}

.stat-meta {
    font-size: 12px;
    color: #666;
}

/* Secondary Stats */
.secondary-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.stat-mini {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: all 0.3s;
}

.stat-mini:hover {
    border-color: var(--primary-red);
}

.stat-mini i {
    font-size: 20px;
    color: #999;
}

.stat-mini.pending i { color: #ff9800; }
.stat-mini.processing i { color: #2196F3; }
.stat-mini.completed i { color: #4caf50; }
.stat-mini.low-stock i { color: #f44336; }

.stat-mini div {
    display: flex;
    flex-direction: column;
}

.stat-mini strong {
    font-size: 20px;
    color: #fff;
    font-weight: 700;
}

.stat-mini span {
    font-size: 11px;
    color: #999;
    text-transform: uppercase;
}

/* Charts Grid */
.charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
    margin-bottom: 24px;
}

.chart-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    overflow: hidden;
}

.chart-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--border-color);
}

.chart-header h6 {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 8px;
}

.chart-header i {
    color: var(--primary-red);
}

.chart-body {
    padding: 20px;
    height: 280px;
}

/* Tables Grid */
.tables-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
}

.table-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    overflow: hidden;
}

.table-card.alert-card {
    border-color: rgba(244, 67, 54, 0.3);
}

.table-card-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.table-card-header h6 {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 8px;
}

.link-primary, .link-danger {
    font-size: 13px;
    text-decoration: none;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 4px;
    transition: all 0.3s;
}

.link-primary { color: var(--primary-red); }
.link-danger { color: #f44336; }

.link-primary:hover, .link-danger:hover {
    gap: 8px;
}

/* Data Table */
.table-responsive {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table thead {
    background: rgba(255,255,255,0.02);
}

.data-table th {
    padding: 12px 16px;
    text-align: left;
    font-size: 12px;
    font-weight: 600;
    color: #999;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 1px solid var(--border-color);
}

.data-table td {
    padding: 14px 16px;
    border-bottom: 1px solid var(--border-color);
    color: #fff;
    font-size: 13px;
}

.data-table tbody tr {
    transition: background 0.2s;
}

.data-table tbody tr:hover {
    background: rgba(255,255,255,0.02);
}

.order-number {
    color: var(--primary-red);
    font-weight: 600;
}

.customer-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.customer-name {
    font-weight: 500;
    color: #fff;
}

.customer-mobile {
    font-size: 11px;
    color: #666;
}

.amount {
    font-weight: 600;
    color: #4caf50;
}

.date {
    color: #999;
    font-size: 12px;
}

/* Badges */
.badge {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.badge-warning { background: rgba(255, 152, 0, 0.1); color: #ff9800; }
.badge-info { background: rgba(33, 150, 243, 0.1); color: #2196F3; }
.badge-primary { background: rgba(156, 39, 176, 0.1); color: #9c27b0; }
.badge-success { background: rgba(76, 175, 80, 0.1); color: #4caf50; }
.badge-danger { background: rgba(244, 67, 54, 0.1); color: #f44336; }
.badge-secondary { background: rgba(158, 158, 158, 0.1); color: #999; }

.btn-action {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    background: rgba(224, 16, 32, 0.1);
    color: var(--primary-red);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.3s;
}

.btn-action:hover {
    background: var(--primary-red);
    color: #fff;
}

/* Low Stock List */
.low-stock-list {
    padding: 16px;
}

.low-stock-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 12px;
    background: rgba(244, 67, 54, 0.05);
    border: 1px solid rgba(244, 67, 54, 0.2);
}

.low-stock-item:last-child {
    margin-bottom: 0;
}

.product-thumb img {
    width: 48px;
    height: 48px;
    object-fit: cover;
    border-radius: 8px;
}

.thumb-placeholder {
    width: 48px;
    height: 48px;
    background: #222;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #666;
}

.product-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.product-info strong {
    color: #fff;
    font-size: 13px;
}

.stock-warning {
    font-size: 11px;
    color: #f44336;
    display: flex;
    align-items: center;
    gap: 4px;
}

.btn-restock {
    padding: 6px 12px;
    background: var(--primary-red);
    color: #fff;
    border-radius: 6px;
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
    transition: all 0.3s;
}

.btn-restock:hover {
    background: #c00d1a;
}

/* Empty State */
.empty-state {
    padding: 60px 20px;
    text-align: center;
    color: #666;
}

.empty-state.small {
    padding: 40px 20px;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 16px;
    display: block;
    opacity: 0.5;
}

.empty-state p {
    margin: 0;
    font-size: 14px;
}

/* ========== RESPONSIVE ========== */

@media (max-width: 1200px) {
    .tables-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .header-actions {
        width: 100%;
    }
    
    .header-actions a {
        flex: 1;
        justify-content: center;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .secondary-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .charts-grid {
        grid-template-columns: 1fr;
    }
    
    /* Mobile Table to Cards */
    .data-table thead {
        display: none;
    }
    
    .data-table, .data-table tbody, .data-table tr, .data-table td {
        display: block;
        width: 100%;
    }
    
    .data-table tr {
        margin-bottom: 16px;
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 12px;
        background: rgba(255,255,255,0.02);
    }
    
    .data-table td {
        padding: 8px 0;
        border: none;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .data-table td:before {
        content: attr(data-label);
        font-weight: 600;
        color: #999;
        font-size: 11px;
        text-transform: uppercase;
    }
    
    .low-stock-item {
        flex-wrap: wrap;
    }
    
    .btn-restock {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .secondary-stats {
        grid-template-columns: 1fr;
    }
    
    .stat-value {
        font-size: 24px;
    }
    
    .stat-mini strong {
        font-size: 18px;
    }
}
</style>