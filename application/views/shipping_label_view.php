<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Shipping Label') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f4f6f9;
            color: #111111;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Top Bar Actions */
        .top-action-bar {
            width: 100%;
            max-width: 440px;
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
            transition: background 0.2s;
        }
        .btn-action:hover {
            background: #c00d1a;
            color: #ffffff;
        }
        .btn-secondary {
            background: #f0f0f0;
            color: #333333;
            border: 1px solid #cccccc;
        }
        .btn-secondary:hover {
            background: #e4e4e4;
            color: #111111;
        }

        /* Shipping Label Container (Standard 4" x 6" / Thermal Layout) */
        .label-container {
            width: 440px;
            max-width: 100%;
            background: #ffffff;
            border: 2px solid #000000;
            padding: 0;
            box-shadow: 0 6px 18px rgba(0,0,0,0.1);
            font-size: 12px;
            line-height: 1.4;
            overflow: hidden;
        }

        /* Header Bar with Logo */
        .label-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 14px;
            border-bottom: 2px solid #000000;
            background: #fafafa;
        }
        .logo-box {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .logo-img {
            max-height: 38px;
            width: auto;
            object-fit: contain;
        }
        .header-meta {
            text-align: right;
        }
        .payment-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 4px;
            font-weight: 800;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #000;
        }
        .badge-prepaid {
            background: #e8f5e9;
            color: #2e7d32;
            border-color: #2e7d32;
        }
        .badge-cod {
            background: #fff3e0;
            color: #e65100;
            border-color: #e65100;
        }

        /* Sections */
        .label-section {
            padding: 12px 14px;
            border-bottom: 2px solid #000000;
        }

        .section-title {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #444444;
            margin-bottom: 6px;
        }

        /* Ship To */
        .recipient-name {
            font-size: 15px;
            font-weight: 800;
            color: #000000;
            margin-bottom: 4px;
        }
        .address-text {
            font-size: 12px;
            color: #222222;
        }
        .pincode-highlight {
            font-weight: 800;
            font-size: 14px;
            color: #000000;
        }

        /* Split Section (Grid) */
        .label-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            border-bottom: 2px solid #000000;
        }
        .grid-cell {
            padding: 10px 12px;
            overflow: hidden;
        }
        .grid-cell:first-child {
            border-right: 2px solid #000000;
        }

        .meta-row {
            margin-bottom: 5px;
            font-size: 11px;
        }
        .meta-label {
            color: #555555;
            font-weight: 600;
        }
        .meta-value {
            color: #000000;
            font-weight: 700;
        }

        /* Barcode Area */
        .barcode-container {
            text-align: center;
            padding: 4px 0;
            overflow: hidden;
            width: 100%;
        }
        .barcode-svg {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        /* Items List */
        .items-box {
            background: #f9f9f9;
            border: 1px dashed #cccccc;
            border-radius: 4px;
            padding: 6px 10px;
            margin-top: 6px;
            font-size: 11px;
        }

        /* Shipped By Footer */
        .shipped-by-section {
            padding: 10px 14px;
            background: #fafafa;
            overflow: hidden;
        }

        /* Print Media Styles */
        @media print {
            body {
                background: #ffffff;
                padding: 0;
                display: block;
            }
            .no-print {
                display: none !important;
            }
            .label-container {
                width: 100%;
                max-width: 100%;
                border: 2px solid #000000;
                box-shadow: none;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <!-- Top Action Bar -->
    <div class="top-action-bar no-print">
        <a href="javascript:history.back()" class="btn-action btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        <div style="display: flex; gap: 8px;">
            <?php if (!empty($order['label_url'])): ?>
                <a href="<?= htmlspecialchars($order['label_url']) ?>" target="_blank" class="btn-action btn-secondary" title="Official Shiprocket PDF">
                    <i class="fas fa-file-pdf"></i> Shiprocket PDF
                </a>
            <?php endif; ?>
            <button onclick="window.print()" class="btn-action">
                <i class="fas fa-print"></i> Print Label
            </button>
        </div>
    </div>

    <!-- Shipping Label -->
    <div class="label-container" id="printableLabel">
        
        <!-- Header -->
        <div class="label-header">
            <div class="logo-box">
                <img src="<?= base_url('uploads/ghanshyam-murti-logo.png') ?>" 
                     alt="Ghanshyam Murti Bhandar" 
                     class="logo-img"
                     onerror="this.src='<?= base_url('uploads/ghanshyam-murti-logo-dark-bg.png') ?>'">
            </div>
            <div class="header-meta">
                <?php 
                $is_cod = (strtolower($order['payment_method'] ?? '') === 'cod');
                ?>
                <span class="payment-badge <?= $is_cod ? 'badge-cod' : 'badge-prepaid' ?>">
                    <?= $is_cod ? 'CASH ON DELIVERY' : 'PREPAID' ?>
                </span>
                <div style="font-size: 10px; color: #666; margin-top: 4px;">
                    Date: <?= date('d M Y', strtotime($order['created_at'] ?? 'now')) ?>
                </div>
            </div>
        </div>

        <!-- Ship To Section -->
        <div class="label-section">
            <div class="section-title"><i class="fas fa-shipping-fast"></i> Ship To:</div>
            <div class="recipient-name">
                <?= htmlspecialchars(($order['full_name'] ?? '') ?: (($order['customer_name'] ?? '') ?: 'Valued Customer')) ?>
            </div>
            <div class="address-text">
                <?= htmlspecialchars($order['address_line1'] ?? '') ?>
                <?php if (!empty($order['address_line2'])): ?>
                    , <?= htmlspecialchars($order['address_line2']) ?>
                <?php endif; ?>
                <?php if (!empty($order['landmark'])): ?>
                    (Near <?= htmlspecialchars($order['landmark']) ?>)
                <?php endif; ?>
            </div>
            <div class="address-text" style="margin-top: 2px;">
                <?= htmlspecialchars($order['city'] ?? '') ?><?= !empty($order['state']) ? ', ' . htmlspecialchars($order['state']) : '' ?>, <?= htmlspecialchars($order['country'] ?? 'India') ?>
            </div>
            <div class="pincode-highlight" style="margin-top: 4px;">
                PINCODE: <?= htmlspecialchars($order['pincode'] ?? '382330') ?>
            </div>
            <?php if (!empty($order['delivery_mobile']) || !empty($order['customer_phone'])): ?>
                <div style="margin-top: 4px; font-weight: 700; font-size: 12px; color: #000;">
                    <i class="fas fa-phone-alt" style="font-size: 10px;"></i> Phone: <?= htmlspecialchars(($order['delivery_mobile'] ?? '') ?: ($order['customer_phone'] ?? '')) ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Split Grid: Package Meta & Courier/AWB Barcode -->
        <div class="label-grid">
            
            <!-- Left: Dimensions & Items -->
            <div class="grid-cell">
                <div class="meta-row">
                    <span class="meta-label">Courier:</span>
                    <div class="meta-value" style="font-size: 12px;">
                        <?= htmlspecialchars(($order['courier_name'] ?? '') ?: (($order['courier_company_name'] ?? '') ?: 'Xpressbees Air')) ?>
                    </div>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Dimensions:</span> 
                    <span class="meta-value"><?= htmlspecialchars(($order['dimensions'] ?? '') ?: '10.00*10.00*10.00(cm)') ?></span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Weight:</span> 
                    <span class="meta-value"><?= htmlspecialchars(($order['weight'] ?? '') ?: '0.50 kg') ?></span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Payment:</span> 
                    <span class="meta-value" style="color: <?= $is_cod ? '#e65100' : '#2e7d32' ?>;">
                        <?= $is_cod ? 'COD (Collect ₹' . number_format($order['total_amount'] ?? 0, 2) . ')' : 'PREPAID (Paid ₹' . number_format($order['total_amount'] ?? 0, 2) . ')' ?>
                    </span>
                </div>

                <?php if (!empty($order['items'])): ?>
                    <div class="items-box">
                        <div style="font-weight: 700; color: #333; margin-bottom: 2px;">Item(s):</div>
                        <?php foreach ($order['items'] as $item): ?>
                            <div style="color: #444; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?= $item['quantity'] ?>x <?= htmlspecialchars($item['product_name'] ?? 'Product') ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right: AWB Code & Barcode -->
            <div class="grid-cell" style="display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;">
                <div style="font-size: 10px; color: #555; font-weight: 600;">AWB Code</div>
                <div class="barcode-container">
                    <svg id="awbBarcode" class="barcode-svg"></svg>
                </div>
                <div style="font-size: 11px; font-weight: 800; color: #000; margin-top: 2px;">
                    <?= htmlspecialchars(($order['awb_code'] ?? '') ?: (($order['shipment_id'] ?? '') ?: '1319460490758')) ?>
                </div>

                <?php if (!empty($order['routing_code'])): ?>
                    <div style="font-size: 10px; color: #333; margin-top: 4px; background: #eee; padding: 2px 6px; border-radius: 3px; font-weight: 700;">
                        Routing: <?= htmlspecialchars($order['routing_code']) ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <!-- Shipped By / Return Address Footer -->
        <div class="shipped-by-section">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px;">
                <div style="flex: 1; min-width: 0;">
                    <div class="section-title" style="margin-bottom: 3px;">
                        Shipped By <span style="font-weight: 400; font-size: 9px; text-transform: none;">(If undelivered, return to)</span>:
                    </div>
                    <div style="font-weight: 800; font-size: 12px; color: #000;">
                        Ghanshyam Murti Bhandar
                    </div>
                    <div style="font-size: 10px; color: #333; line-height: 1.3; margin-top: 2px;">
                        Pujara Plot Main Rd, near chirag medical Lakshmi wadi, Bhakti Nagar, Rajkot, Gujarat 360002
                    </div>
                    <div style="font-size: 10px; font-weight: 700; color: #000; margin-top: 3px;">
                        Phone No: +91 9909289536
                    </div>
                </div>

                <div style="width: 140px; min-width: 140px; text-align: center; flex-shrink: 0;">
                    <div style="font-size: 10px; color: #555; font-weight: 600;">Order #</div>
                    <div class="barcode-container">
                        <svg id="orderBarcode" class="barcode-svg"></svg>
                    </div>
                    <div style="font-size: 10px; font-weight: 700; color: #000; word-break: break-all;">
                        <?= htmlspecialchars($order['order_number'] ?? '') ?>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Generate Crisp SVG Barcodes -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // AWB Barcode
            const awbVal = "<?= htmlspecialchars(($order['awb_code'] ?? '') ?: (($order['shipment_id'] ?? '') ?: '1319460490758'), ENT_QUOTES, 'UTF-8') ?>";
            JsBarcode("#awbBarcode", awbVal, {
                format: "CODE128",
                displayValue: false,
                height: 40,
                width: 1.1,
                margin: 0,
                lineColor: "#000000"
            });

            // Order Number Barcode
            const orderVal = "<?= htmlspecialchars($order['order_number'] ?? '', ENT_QUOTES, 'UTF-8') ?>";
            JsBarcode("#orderBarcode", orderVal, {
                format: "CODE128",
                displayValue: false,
                height: 30,
                width: 1.0,
                margin: 0,
                lineColor: "#000000"
            });
        });
    </script>
</body>
</html>
