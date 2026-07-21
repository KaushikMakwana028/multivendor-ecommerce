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
        <a href="<?= site_url('order') ?>" class="btn-outline-light-custom" style="margin-bottom: 10px;">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>
        <h4>Order Details</h4>
        <p><?= htmlspecialchars($order['order_number']) ?> - <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></p>
    </div>
    <div style="display: flex; gap: 10px; align-items: flex-start;">
        <?php if ($order['status'] === 'delivered'): ?>
            <a href="<?= site_url('order/invoice/' . $order['id']) ?>" class="btn-red">
                <i class="fas fa-file-invoice"></i> Generate Invoice
            </a>
        <?php endif; ?>

        <div class="dropdown">
            <button class="btn-red" data-bs-toggle="dropdown">
                <i class="fas fa-edit"></i> Update Status
            </button>
            <ul class="dropdown-menu dropdown-menu-dark">
                <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $order['id'] ?>, 'confirmed')">Mark Confirmed</a></li>
                <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $order['id'] ?>, 'processing')">Mark Processing</a></li>
                <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $order['id'] ?>, 'delivered')">Mark Delivered</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="#" onclick="updateStatus(<?= $order['id'] ?>, 'cancelled')">Cancel Order</a></li>
            </ul>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column -->
    <div class="col-lg-8">

        <!-- Order Status Card -->
        <div class="card-dark mb-4">
            <div class="card-header-dark">
                <h6><i class="fas fa-info-circle"></i> Order Status</h6>
            </div>
            <div class="card-body-dark">
                <div class="status-timeline">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="status-info-box">
                                <div class="status-info-label">Order Status</div>
                                <?php
                                $status_badges = [
                                    'pending' => 'badge-pending',
                                    'confirmed' => 'badge-confirmed',
                                    'processing' => 'badge-processing',
                                    'shipped' => 'badge-shipped',
                                    'delivered' => 'badge-delivered',
                                    'cancelled' => 'badge-cancelled'
                                ];
                                $status_class = $status_badges[$order['status']] ?? 'badge-pending';
                                ?>
                                <span class="order-status-badge <?= $status_class ?>" style="font-size: 14px; padding: 8px 16px;">
                                    <?= ucfirst($order['status']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="status-info-box">
                                <div class="status-info-label">Payment Status</div>
                                <?php
                                $payment_badges = [
                                    'pending' => ['class' => 'badge-inactive', 'icon' => 'clock'],
                                    'paid' => ['class' => 'badge-active', 'icon' => 'check-circle'],
                                    'failed' => ['class' => 'badge-cancelled', 'icon' => 'times-circle']
                                ];
                                $payment_badge = $payment_badges[$order['payment_status']] ?? $payment_badges['pending'];
                                ?>
                                <span class="<?= $payment_badge['class'] ?>" style="font-size: 14px; padding: 8px 16px;">
                                    <i class="fas fa-<?= $payment_badge['icon'] ?>"></i> <?= ucfirst($order['payment_status']) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items Card -->
        <div class="card-dark mb-4">
            <div class="card-header-dark">
                <h6><i class="fas fa-shopping-bag"></i> Order Items (<?= count($order['items']) ?>)</h6>
            </div>
            <div class="card-body-dark" style="padding: 0;">
                <div class="order-items-list">
                    <?php foreach ($order['items'] as $item): ?>
                        <div class="order-item-row">
                            <div class="order-item-image">
                                <?php if (!empty($item['image'])): ?>
    <img src="<?= base_url('uploads/products/' . $item['image']) ?>"
         alt="<?= htmlspecialchars($item['product_name'] ?? '') ?>">
<?php else: ?>
    <div class="no-image">
        <i class="fas fa-image"></i>
    </div>
<?php endif; ?>
                            </div>
                            <div class="order-item-details">
                                <div class="order-item-name">
                                    <?= htmlspecialchars($item['product_name'] ?? '') ?>
                                </div>
                                <div class="order-item-meta">
                                    Price: ₹<?= number_format($item['price'], 2) ?> × <?= $item['quantity'] ?>
                                </div>
                            </div>
                            <div class="order-item-total">
                                ₹<?= number_format($item['price'] * $item['quantity'], 2) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Order Summary -->
                <div class="order-summary-section">
                    <div class="order-summary-row">
                        <span>Subtotal</span>
                        <span>₹<?= number_format($order['subtotal'], 2) ?></span>
                    </div>
                    <?php if ($order['delivery_charge'] > 0): ?>
                        <div class="order-summary-row">
                            <span>Delivery Charge</span>
                            <span>₹<?= number_format($order['delivery_charge'], 2) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($order['discount'] > 0): ?>
                        <div class="order-summary-row discount">
                            <span>Discount</span>
                            <span>-₹<?= number_format($order['discount'], 2) ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="order-summary-row total">
                        <span>Total Amount</span>
                        <span>₹<?= number_format($order['total_amount'], 2) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status History Card -->
        <?php if (!empty($order['status_history'])): ?>
            <div class="card-dark">
                <div class="card-header-dark">
                    <h6><i class="fas fa-history"></i> Status History</h6>
                </div>
                <div class="card-body-dark">
                    <div class="timeline">
                        <?php foreach ($order['status_history'] as $history): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker">
                                    <i class="fas fa-circle"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-status">
                                        <?= ucfirst($history['status']) ?>
                                    </div>
                                    <?php if (!empty($history['comment'])): ?>
                                        <div class="timeline-comment">
                                            <?= htmlspecialchars($history['comment']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="timeline-date">
                                        <i class="far fa-clock"></i>
                                        <?= date('d M Y, h:i A', strtotime($history['created_at'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <!-- Right Column -->
    <div class="col-lg-4">

        <!-- Customer Info Card -->
        <div class="card-dark mb-4">
            <div class="card-header-dark">
                <h6><i class="fas fa-user"></i> Customer Information</h6>
            </div>
            <div class="card-body-dark">
                <div class="info-group">
                    <div class="info-label">Name</div>
                    <div class="info-value"><?= htmlspecialchars($order['customer_name'] ?? '') ?></div>
                </div>
                <div class="info-group">
                    <div class="info-label">Email</div>
                    <div class="info-value">
                        <a href="mailto:<?= htmlspecialchars($order['customer_email'] ?? '') ?>" style="color: var(--primary-red);">
                            <?= htmlspecialchars($order['customer_email'] ?? '') ?>
                        </a>
                    </div>
                </div>
                <div class="info-group">
                    <div class="info-label">Phone</div>
                    <div class="info-value">
                        <a href="tel:<?= htmlspecialchars($order['customer_phone'] ?? '') ?>" style="color: var(--primary-red);">
                            <?= htmlspecialchars($order['customer_phone'] ?? '') ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delivery Address Card -->
        <div class="card-dark mb-4">
            <div class="card-header-dark">
                <h6><i class="fas fa-map-marker-alt"></i> Delivery Address</h6>
            </div>
            <div class="card-body-dark">
                <div class="address-box">
                    <?= htmlspecialchars($order['address_line1'] ?? '') ?><br>
                    <?php if (!empty($order['address_line2'])): ?>
                        <?= htmlspecialchars($order['address_line2']) ?><br>
                    <?php endif; ?>
                    <?= htmlspecialchars($order['city'] ?? '') ?>, <?= htmlspecialchars($order['state'] ?? '') ?><br>
                    PIN: <?= htmlspecialchars($order['pincode'] ?? '') ?><br>
                    <div style="margin-top: 8px; color: var(--primary-red);">
                        <i class="fas fa-phone"></i> <?= htmlspecialchars($order['delivery_mobile'] ?? '') ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Info Card -->
        <div class="card-dark mb-4">
            <div class="card-header-dark">
                <h6><i class="fas fa-credit-card"></i> Payment Information</h6>
            </div>
            <div class="card-body-dark">
                <div class="info-group">
                    <div class="info-label">Payment Method</div>
                    <div class="info-value" style="text-transform: uppercase;">
                        <?= $order['payment_method'] === 'cod' ? 'Cash on Delivery' : 'Online Payment' ?>
                    </div>
                </div>

                <?php if ($order['payment_method'] === 'online'): ?>
                    <?php if (!empty($order['razorpay_order_id'])): ?>
                        <div class="info-group">
                            <div class="info-label">Razorpay Order ID</div>
                            <div class="info-value" style="font-size: 11px; word-break: break-all;">
                                <?= htmlspecialchars($order['razorpay_order_id']) ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($order['razorpay_payment_id'])): ?>
                        <div class="info-group">
                            <div class="info-label">Payment ID</div>
                            <div class="info-value" style="font-size: 11px; word-break: break-all;">
                                <?= htmlspecialchars($order['razorpay_payment_id']) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Shipping / Shiprocket Card -->
        <div class="card-dark mb-4">
            <div class="card-header-dark">
                <h6><i class="fas fa-truck"></i> Shipping Details</h6>
            </div>
            <div class="card-body-dark">

                <?php if (!empty($order['awb_code'])): ?>
                    <div class="info-group">
                        <div class="info-label">AWB Number</div>
                        <div class="info-value"><?= htmlspecialchars($order['awb_code']) ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Courier Partner</div>
                        <div class="info-value"><?= htmlspecialchars($order['courier_name']) ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Tracking Status</div>
                        <div class="info-value" id="trackingStatusText">
                            <?= htmlspecialchars($order['tracking_status'] ?? 'Not updated yet') ?>
                        </div>
                    </div>

                    <div style="display:flex; flex-direction:column; gap:8px; margin-top:15px;">
                        <button class="btn-outline-light-custom" onclick="refreshTracking(<?= $order['id'] ?>)">
                            <i class="fas fa-sync"></i> Refresh Tracking
                        </button>

                        <?php if (empty($order['pickup_scheduled'])): ?>
                            <button class="btn-red" onclick="schedulePickup(<?= $order['id'] ?>)">
                                <i class="fas fa-calendar-check"></i> Schedule Pickup
                            </button>
                        <?php else: ?>
                            <span class="badge-active" style="padding:8px; text-align:center;">Pickup Scheduled</span>
                        <?php endif; ?>

                        <button class="btn-outline-light-custom" onclick="downloadLabel(<?= $order['id'] ?>)">
                            <i class="fas fa-tag"></i> Download Label
                        </button>

                        <button class="btn-outline-light-custom" onclick="downloadInvoice(<?= $order['id'] ?>)">
                            <i class="fas fa-file-invoice"></i> Download Shiprocket Invoice
                        </button>
                    </div>

                <?php elseif (!empty($order['shiprocket_shipment_id'])): ?>
                    <p style="color:#999; font-size:13px;">Courier not assigned yet.</p>
                    <button class="btn-red" onclick="openCourierModal(<?= $order['id'] ?>)">Assign Courier</button>
                <?php else: ?>
                    <p style="color:#999; font-size:13px;">Not yet synced with Shiprocket.</p>
                <?php endif; ?>

            </div>
        </div>

        <!-- Notes Card -->
        <?php if (!empty($order['notes'])): ?>
            <div class="card-dark">
                <div class="card-header-dark">
                    <h6><i class="fas fa-sticky-note"></i> Order Notes</h6>
                </div>
                <div class="card-body-dark">
                    <div class="notes-box">
                        <?= nl2br(htmlspecialchars($order['notes'])) ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<!-- Courier Selection Modal -->
<div class="modal fade" id="courierModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content" style="background:#1a1a1a; color:#fff;">
      <div class="modal-header">
        <h5 class="modal-title">Select Courier Partner</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="courierList">Loading couriers...</div>
    </div>
  </div>
</div>

<script>
function updateStatus(orderId, status) {
    if (!confirm('Are you sure you want to update this order status to ' + status + '?')) {
        return false;
    }
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
            if (res.status == 200 && res.data && res.data.available_courier_companies && res.data.available_courier_companies.length) {
                res.data.available_courier_companies.forEach(c => {
                    const isRecommended = c.courier_company_id === res.data.recommended_courier_company_id;
                    html += `
                    <div class="d-flex justify-content-between align-items-center border border-secondary p-2 mb-2 rounded">
                        <div>
                            <strong>${c.courier_name}</strong> ${isRecommended ? '<span class="badge bg-success">Recommended</span>' : ''}<br>
                            <small>Rate: ₹${c.rate} | ETD: ${c.etd} | Rating: ${c.rating}⭐</small>
                        </div>
                        <button class="btn btn-sm btn-primary" onclick="selectCourier(${c.courier_company_id})">Select</button>
                    </div>`;
                });
            } else {
                html = '<p class="text-danger">No couriers available for this shipment.</p>';
            }
            document.getElementById('courierList').innerHTML = html;
        });
}

function selectCourier(courierId) {
    if (!confirm('Assign this courier and generate AWB?')) return;

    fetch('<?= site_url("order/assign_courier/") ?>' + currentOrderId, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'courier_id=' + courierId
    })
    .then(res => res.json())
    .then(res => {
        if (res.status) {
            alert('Courier assigned! AWB: ' + res.awb_code);
            location.reload();
        } else {
            alert('Failed: ' + res.message);
        }
    });
}

function refreshTracking(orderId) {
    fetch('<?= site_url("order/refresh_tracking/") ?>' + orderId)
        .then(res => res.json())
        .then(() => location.reload());
}

function schedulePickup(orderId) {
    if (!confirm('Schedule pickup for this shipment?')) return;

    fetch('<?= site_url("order/schedule_pickup/") ?>' + orderId)
        .then(res => res.json())
        .then(res => {
            alert(res.message);
            if (res.status) location.reload();
        });
}

function downloadLabel(orderId) {
    fetch('<?= site_url("order/download_label/") ?>' + orderId)
        .then(res => res.json())
        .then(res => {
            if (res.status) window.open(res.url, '_blank');
            else alert('Failed: ' + res.message);
        });
}

function downloadInvoice(orderId) {
    fetch('<?= site_url("order/download_invoice/") ?>' + orderId)
        .then(res => res.json())
        .then(res => {
            if (res.status) window.open(res.url, '_blank');
            else alert('Failed: ' + res.message);
        });
}
</script>

<style>
/* Status Info Box */
.status-info-box {
    background: rgba(255, 255, 255, 0.03);
    padding: 15px;
    border-radius: 8px;
    border: 1px solid var(--border-color);
}

.status-info-label {
    font-size: 11px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
    font-weight: 600;
}

/* Order Items List */
.order-items-list {
    padding: 20px;
    border-bottom: 1px solid var(--border-color);
}

.order-item-row {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
}

.order-item-row:last-child {
    border-bottom: none;
}

.order-item-row:first-child {
    padding-top: 0;
}

.order-item-image img,
.order-item-image .no-image {
    width: 70px;
    height: 70px;
    border-radius: 8px;
    object-fit: cover;
    border: 1px solid var(--border-color);
}

.order-item-image .no-image {
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--light-gray);
    color: #555;
    font-size: 24px;
}

.order-item-details {
    flex: 1;
}

.order-item-name {
    font-size: 15px;
    font-weight: 600;
    color: var(--white-text);
    margin-bottom: 5px;
}

.order-item-meta {
    font-size: 12px;
    color: #999;
}

.order-item-total {
    font-size: 16px;
    font-weight: 700;
    color: #4caf50;
}

/* Order Summary Section */
.order-summary-section {
    padding: 20px;
    background: rgba(0, 0, 0, 0.2);
}

.order-summary-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    font-size: 14px;
    color: #999;
}

.order-summary-row.discount {
    color: #ff9800;
}

.order-summary-row.total {
    margin-top: 10px;
    padding-top: 15px;
    border-top: 1px solid var(--border-color);
    font-size: 18px;
    font-weight: 700;
    color: var(--white-text);
}

/* Info Groups */
.info-group {
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.info-group:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.info-label {
    font-size: 11px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 6px;
    font-weight: 600;
}

.info-value {
    font-size: 14px;
    color: var(--white-text);
    line-height: 1.6;
}

/* Address Box */
.address-box {
    background: rgba(255, 255, 255, 0.03);
    padding: 15px;
    border-radius: 8px;
    border: 1px solid var(--border-color);
    line-height: 1.8;
    font-size: 13px;
    color: #ccc;
}

/* Notes Box */
.notes-box {
    background: rgba(255, 255, 255, 0.03);
    padding: 15px;
    border-radius: 8px;
    border: 1px solid var(--border-color);
    line-height: 1.8;
    font-size: 13px;
    color: #ccc;
}

/* Timeline */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    padding-bottom: 25px;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-item:last-child::before {
    display: none;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -25px;
    top: 20px;
    bottom: -5px;
    width: 2px;
    background: var(--border-color);
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 0;
    width: 12px;
    height: 12px;
    background: var(--primary-red);
    border-radius: 50%;
    border: 2px solid var(--card-bg);
}

.timeline-marker i {
    font-size: 6px;
    color: var(--primary-red);
}

.timeline-content {
    background: rgba(255, 255, 255, 0.03);
    padding: 12px 15px;
    border-radius: 8px;
    border: 1px solid var(--border-color);
}

.timeline-status {
    font-size: 14px;
    font-weight: 600;
    color: var(--white-text);
    margin-bottom: 5px;
    text-transform: capitalize;
}

.timeline-comment {
    font-size: 12px;
    color: #999;
    margin-bottom: 8px;
}

.timeline-date {
    font-size: 11px;
    color: #666;
}

/* Responsive */
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        gap: 15px;
    }

    .page-header > div:last-child {
        width: 100%;
        flex-direction: column;
    }

    .order-item-row {
        gap: 10px;
    }

    .order-item-image img,
    .order-item-image .no-image {
        width: 50px;
        height: 50px;
    }

    .order-item-name {
        font-size: 13px;
    }

    .order-item-total {
        font-size: 14px;
    }

    .timeline {
        padding-left: 25px;
    }
}
</style>

