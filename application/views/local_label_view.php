<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Shipping Label') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f4f6f9;
            color: #111111;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .top-action-bar {
            width: 100%;
            max-width: 420px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #ffffff;
            padding: 12px 18px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .btn-action {
            background: #E01020;
            color: #ffffff;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        .btn-secondary {
            background: #f0f0f0;
            color: #333333;
            border: 1px solid #cccccc;
        }

        .label-container {
            width: 400px;
            max-width: 100%;
            background: #ffffff;
            border: 2px solid #000000;
            box-shadow: 0 6px 18px rgba(0,0,0,0.1);
            font-size: 12px;
            line-height: 1.4;
        }
        .label-section {
            padding: 12px 16px;
            border-bottom: 2px solid #000000;
        }
        .label-title {
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            color: #444444;
            margin-bottom: 6px;
        }
        .party-name {
            font-size: 15px;
            font-weight: 800;
            color: #000000;
            margin-bottom: 4px;
        }
        .order-meta-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin-bottom: 4px;
        }
        .order-meta-row span:first-child {
            color: #555;
            font-weight: 600;
        }
        .order-meta-row span:last-child {
            font-weight: 700;
            color: #000;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }
        .items-table th {
            background: #f0f0f0;
            font-size: 11px;
            text-transform: uppercase;
            padding: 6px 8px;
            border-bottom: 1px solid #ccc;
            text-align: left;
        }
        .items-table td {
            padding: 6px 8px;
            font-size: 12px;
            border-bottom: 1px solid #eee;
        }
       .cod-badge {
    display: inline-block;
    background: #000;
    color: #fff !important;
    padding: 4px 10px;
    font-weight: 800;
    font-size: 13px;
    border-radius: 4px;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}
        .footer-box {
            padding: 12px 16px;
            text-align: center;
        }
        .footer-box .party-name {
            font-size: 13px;
        }

        @media print {
            body { background: #ffffff; padding: 0; display: block; }
            .no-print { display: none !important; }
            .label-container { width: 100%; max-width: 100%; border: 2px solid #000000; box-shadow: none; page-break-inside: avoid; }
        }
    </style>
</head>
<body>

    <div class="top-action-bar no-print">
        <a href="javascript:history.back()" class="btn-action btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        <button onclick="window.print()" class="btn-action">
            <i class="fas fa-print"></i> Print Label
        </button>
    </div>

    <div class="label-container">

        <!-- Deliver To -->
        <div class="label-section">
            <div class="label-title">Deliver To:</div>
            <div class="party-name">
                <?= htmlspecialchars(($order['full_name'] ?? '') ?: (($order['customer_name'] ?? '') ?: 'Valued Customer')) ?>
            </div>
            <div style="font-size: 12px; color: #333; margin-top: 3px; line-height: 1.4;">
                <?= htmlspecialchars($order['address_line1'] ?? '') ?>
                <?php if (!empty($order['address_line2'])): ?>
                    , <?= htmlspecialchars($order['address_line2']) ?>
                <?php endif; ?>
                <?php if (!empty($order['landmark'])): ?>
                    <br>Near <?= htmlspecialchars($order['landmark']) ?>
                <?php endif; ?>
                <br>
                <?= htmlspecialchars($order['city'] ?? '') ?><?= !empty($order['state']) ? ', ' . htmlspecialchars($order['state']) : '' ?> - <strong><?= htmlspecialchars($order['pincode'] ?? '') ?></strong>
            </div>
            <div style="font-size: 13px; font-weight: 800; color: #000; margin-top: 6px;">
                <i class="fas fa-phone"></i> <?= htmlspecialchars(($order['delivery_mobile'] ?? '') ?: ($order['customer_phone'] ?? '')) ?>
            </div>
        </div>

        <!-- Order Meta -->
        <div class="label-section">
            <div class="order-meta-row">
                <span>Order #:</span>
                <span><?= htmlspecialchars($order['order_number']) ?></span>
            </div>
            <div class="order-meta-row">
                <span>Date:</span>
                <span><?= date('d/m/Y', strtotime($order['created_at'] ?? 'now')) ?></span>
            </div>
            <div class="order-meta-row">
                <span>Payment:</span>
                <span>
                    <?php if (strtolower($order['payment_method'] ?? '') === 'cod'): ?>
                        <span class="cod-badge">COD - ₹<?= number_format(floatval($order['total_amount'] ?? 0), 2) ?></span>
                    <?php else: ?>
                        PREPAID
                    <?php endif; ?>
                </span>
            </div>
        </div>
                    <!-- DEBUG: <?= htmlspecialchars($order['payment_method'] ?? 'NULL') ?> | <?= htmlspecialchars($order['total_amount'] ?? 'NULL') ?> -->


        <!-- Items -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th style="text-align:center;">Qty</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($order['items'])): ?>
                    <?php foreach ($order['items'] as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['product_name'] ?? 'Product') ?></td>
                            <td style="text-align:center;"><?= (int) ($item['quantity'] ?? 1) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="2" style="text-align:center; color:#999;">No items found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Shipped By -->
        <div class="footer-box">
            <div class="label-title" style="margin-bottom:4px;">Shipped By</div>
            <div class="party-name">Ghanshyam Murti Bhandar</div>
            <div style="font-size: 10px; color: #555; margin-top: 3px;">
                Pujara Plot Main Rd, near chirag medical, Lakshmi wadi, Bhakti Nagar, Rajkot, Gujarat 360002
            </div>
            <div style="font-size: 11px; color: #333; margin-top: 3px;">
                Ph: 9909289536
            </div>
        </div>

    </div>

</body>
</html>