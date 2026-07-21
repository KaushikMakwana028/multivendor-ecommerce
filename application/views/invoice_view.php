<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Tax Invoice') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            max-width: 760px;
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

        /* Invoice Container (A4 Printable Layout) */
        .invoice-container {
            width: 760px;
            max-width: 100%;
            background: #ffffff;
            border: 2px solid #000000;
            padding: 0;
            box-shadow: 0 6px 18px rgba(0,0,0,0.1);
            font-size: 12px;
            line-height: 1.4;
            overflow: hidden;
        }

        /* Header */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 2px solid #000000;
            background: #fafafa;
        }
        .logo-img {
            max-height: 42px;
            width: auto;
            object-fit: contain;
        }
        .invoice-title-box {
            text-align: right;
        }
        .invoice-title {
            font-size: 20px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #000000;
        }

        /* Two Column Address Grid */
        .address-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            border-bottom: 2px solid #000000;
        }
        .address-box {
            padding: 14px 18px;
        }
        .address-box:first-child {
            border-right: 2px solid #000000;
        }
        .box-title {
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            color: #444444;
            margin-bottom: 6px;
            padding-bottom: 4px;
            border-bottom: 1px solid #dddddd;
        }
        .party-name {
            font-size: 14px;
            font-weight: 800;
            color: #000000;
            margin-bottom: 4px;
        }

        /* Invoice Meta Section */
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: #fdfdfd;
            border-bottom: 2px solid #000000;
            padding: 10px 18px;
            font-size: 11px;
        }
        .meta-row {
            display: flex;
            margin-bottom: 3px;
        }
        .meta-label {
            width: 110px;
            color: #555555;
            font-weight: 600;
        }
        .meta-val {
            color: #000000;
            font-weight: 700;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #000000;
        }
        .items-table th {
            background: #f0f0f0;
            color: #000000;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            padding: 8px 10px;
            border-bottom: 2px solid #000000;
            border-right: 1px solid #cccccc;
            text-align: left;
        }
        .items-table th:last-child {
            border-right: none;
        }
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #e0e0e0;
            border-right: 1px solid #e0e0e0;
            font-size: 12px;
            color: #222222;
        }
        .items-table td:last-child {
            border-right: none;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }

        /* Totals Area */
        .totals-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 12px 18px;
            border-bottom: 2px solid #000000;
            background: #fafafa;
        }
        .amount-words-box {
            flex: 1;
            padding-right: 20px;
        }
        .totals-table {
            width: 280px;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 4px 0;
            font-size: 12px;
        }
        .totals-table tr.grand-total td {
            font-size: 14px;
            font-weight: 800;
            color: #000000;
            padding-top: 8px;
            border-top: 2px solid #000000;
        }

        /* Footer & Signature */
        .invoice-footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding: 14px 18px;
            background: #ffffff;
        }
        .terms-text {
            font-size: 10px;
            color: #666666;
            max-width: 380px;
            line-height: 1.4;
        }
        .signature-box {
            text-align: center;
            border-top: 1px solid #000000;
            padding-top: 6px;
            min-width: 200px;
        }
        .signature-title {
            font-size: 11px;
            font-weight: 800;
            color: #000000;
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
            .invoice-container {
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
            <?php if (!empty($order['invoice_url'])): ?>
                <a href="<?= htmlspecialchars($order['invoice_url']) ?>" target="_blank" class="btn-action btn-secondary" title="Official Shiprocket PDF">
                    <i class="fas fa-file-pdf"></i> Shiprocket PDF
                </a>
            <?php endif; ?>
            <button onclick="window.print()" class="btn-action">
                <i class="fas fa-print"></i> Print Invoice
            </button>
        </div>
    </div>

    <!-- Tax Invoice Sheet -->
    <div class="invoice-container" id="printableInvoice">
        
        <!-- Header -->
        <div class="invoice-header">
            <div>
                <img src="<?= base_url('uploads/ghanshyam-murti-logo.png') ?>" 
                     alt="Ghanshyam Murti Bhandar" 
                     class="logo-img"
                     onerror="this.src='<?= base_url('uploads/ghanshyam-murti-logo-dark-bg.png') ?>'">
            </div>
            <div class="invoice-title-box">
                <div class="invoice-title">Tax Invoice</div>
                <div style="font-size: 11px; color: #555; font-weight: 700; margin-top: 2px;">
                    Invoice #: INV-<?= htmlspecialchars($order['order_number']) ?>
                </div>
            </div>
        </div>

        <!-- Address Grid: Sold By vs Delivered To -->
        <div class="address-grid">
            
            <!-- Sold By (Seller) -->
            <div class="address-box">
                <div class="box-title">Sold By (Seller):</div>
                <div class="party-name">Ghanshyam Murti Bhandar</div>
                <div style="font-size: 11px; color: #333; margin-top: 3px; line-height: 1.3;">
                    Pujara Plot Main Rd, near chirag medical Lakshmi wadi, Bhakti Nagar, Rajkot, Gujarat 360002, India
                </div>
                <div style="font-size: 11px; color: #333; margin-top: 4px;">
                    <strong>State Code:</strong> 24 (Gujarat)
                </div>
                <div style="font-size: 11px; color: #333; margin-top: 2px;">
                    <strong>Ph:</strong> +91 9909289536
                </div>
            </div>

            <!-- Delivered To (Customer) -->
            <div class="address-box">
                <div class="box-title">Delivered To (Customer):</div>
                <div class="party-name">
                    <?= htmlspecialchars(($order['full_name'] ?? '') ?: (($order['customer_name'] ?? '') ?: 'Valued Customer')) ?>
                </div>
                <div style="font-size: 11px; color: #333; margin-top: 3px; line-height: 1.3;">
                    <?= htmlspecialchars($order['address_line1'] ?? '') ?>
                    <?php if (!empty($order['address_line2'])): ?>
                        , <?= htmlspecialchars($order['address_line2']) ?>
                    <?php endif; ?>
                    <?php if (!empty($order['landmark'])): ?>
                        (Near <?= htmlspecialchars($order['landmark']) ?>)
                    <?php endif; ?>
                </div>
                <div style="font-size: 11px; color: #333; margin-top: 2px;">
                    <?= htmlspecialchars($order['city'] ?? '') ?><?= !empty($order['state']) ? ', ' . htmlspecialchars($order['state']) : '' ?>, <?= htmlspecialchars($order['country'] ?? 'India') ?> - <strong><?= htmlspecialchars($order['pincode'] ?? '') ?></strong>
                </div>
                <?php if (!empty($order['delivery_mobile']) || !empty($order['customer_phone'])): ?>
                    <div style="font-size: 11px; color: #333; margin-top: 3px;">
                        <strong>Ph:</strong> <?= htmlspecialchars(($order['delivery_mobile'] ?? '') ?: ($order['customer_phone'] ?? '')) ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <!-- Meta Info Grid -->
        <div class="meta-grid">
            <div>
                <div class="meta-row">
                    <span class="meta-label">Invoice No.:</span>
                    <span class="meta-val">INV-<?= htmlspecialchars($order['order_number']) ?></span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Invoice Date:</span>
                    <span class="meta-val"><?= date('d/m/Y', strtotime($order['created_at'] ?? 'now')) ?></span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Order No.:</span>
                    <span class="meta-val"><?= htmlspecialchars($order['order_number']) ?></span>
                </div>
            </div>
            <div>
                <div class="meta-row">
                    <span class="meta-label">Payment Method:</span>
                    <span class="meta-val" style="text-transform: uppercase;">
                        <?= htmlspecialchars($order['payment_method'] ?? 'ONLINE') ?> (<?= htmlspecialchars($order['payment_status'] ?? 'PAID') ?>)
                    </span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Courier:</span>
                    <span class="meta-val"><?= htmlspecialchars(($order['courier_name'] ?? '') ?: (($order['courier_company_name'] ?? '') ?: 'Xpressbees Air')) ?></span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">AWB No.:</span>
                    <span class="meta-val"><?= htmlspecialchars(($order['awb_code'] ?? '') ?: '1319460490758') ?></span>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 40px;" class="text-center">#</th>
                    <th>Description</th>
                    <th style="width: 60px;" class="text-center">Qty</th>
                    <th style="width: 100px;" class="text-right">Unit Price</th>
                    <th style="width: 110px;" class="text-right">Total (₹)</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $calc_subtotal = 0;
                if (!empty($order['items'])): 
                    foreach ($order['items'] as $i => $item): 
                        $item_price = floatval($item['price'] ?? 0);
                        $item_qty   = intval($item['quantity'] ?? 1);
                        $item_total = $item_price * $item_qty;
                        $calc_subtotal += $item_total;
                ?>
                    <tr>
                        <td class="text-center" style="color:#666;"><?= $i + 1 ?></td>
                        <td>
                            <div style="font-weight: 700; color: #000;"><?= htmlspecialchars($item['product_name'] ?? 'Product') ?></div>
                        </td>
                        <td class="text-center" style="font-weight: 700;"><?= $item_qty ?></td>
                        <td class="text-right">₹<?= number_format($item_price, 2) ?></td>
                        <td class="text-right" style="font-weight: 700;">₹<?= number_format($item_total, 2) ?></td>
                    </tr>
                <?php 
                    endforeach; 
                else: 
                ?>
                    <tr>
                        <td colspan="5" class="text-center" style="padding: 20px; color: #999;">No items found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Totals & Amount in Words Section -->
        <div class="totals-section">
            
            <div class="amount-words-box">
                <div style="font-size: 11px; font-weight: 800; color: #444; text-transform: uppercase;">
                    Net Amount Payable (In Words):
                </div>
                <div style="font-size: 13px; font-weight: 700; color: #000; margin-top: 4px; background: #eee; padding: 6px 10px; border-radius: 4px; display: inline-block;">
                    <?php
                    $grand_total = floatval($order['total_amount'] ?? $calc_subtotal);
                    
                    function convertNumberToWords($num) {
                        $ones = array(
                            0 => "Zero", 1 => "One", 2 => "Two", 3 => "Three", 4 => "Four",
                            5 => "Five", 6 => "Six", 7 => "Seven", 8 => "Eight", 9 => "Nine",
                            10 => "Ten", 11 => "Eleven", 12 => "Twelve", 13 => "Thirteen", 14 => "Fourteen",
                            15 => "Fifteen", 16 => "Sixteen", 17 => "Seventeen", 18 => "Eighteen", 19 => "Nineteen"
                        );
                        $tens = array(
                            0 => "", 1 => "", 2 => "Twenty", 3 => "Thirty", 4 => "Forty",
                            5 => "Fifty", 6 => "Sixty", 7 => "Seventy", 8 => "Eighty", 9 => "Ninety"
                        );
                        
                        if ($num < 20) return $ones[$num];
                        if ($num < 100) return $tens[floor($num / 10)] . (($num % 10 != 0) ? " " . $ones[$num % 10] : "");
                        if ($num < 1000) return $ones[floor($num / 100)] . " Hundred" . (($num % 100 != 0) ? " " . convertNumberToWords($num % 100) : "");
                        if ($num < 100000) return convertNumberToWords(floor($num / 1000)) . " Thousand" . (($num % 1000 != 0) ? " " . convertNumberToWords($num % 1000) : "");
                        if ($num < 10000000) return convertNumberToWords(floor($num / 100000)) . " Lakh" . (($num % 100000 != 0) ? " " . convertNumberToWords($num % 100000) : "");
                        return convertNumberToWords(floor($num / 10000000)) . " Crore" . (($num % 10000000 != 0) ? " " . convertNumberToWords($num % 10000000) : "");
                    }

                    $rupees = floor($grand_total);
                    $paise = round(($grand_total - $rupees) * 100);
                    $words = convertNumberToWords($rupees) . " Rupees";
                    if ($paise > 0) {
                        $words .= " and " . convertNumberToWords($paise) . " Paise";
                    }
                    $words .= " Only";
                    echo htmlspecialchars($words);
                    ?>
                </div>
            </div>

            <table class="totals-table">
                <tr>
                    <td style="color:#555; font-weight:600;">Subtotal:</td>
                    <td class="text-right" style="font-weight:700;">₹<?= number_format(floatval($order['subtotal'] ?? $calc_subtotal), 2) ?></td>
                </tr>
                <?php if (!empty($order['delivery_charge']) && floatval($order['delivery_charge']) > 0): ?>
                    <tr>
                        <td style="color:#555; font-weight:600;">Delivery Charges:</td>
                        <td class="text-right" style="font-weight:700;">₹<?= number_format(floatval($order['delivery_charge']), 2) ?></td>
                    </tr>
                <?php endif; ?>
                <?php if (!empty($order['discount']) && floatval($order['discount']) > 0): ?>
                    <tr>
                        <td style="color:#555; font-weight:600;">Discount:</td>
                        <td class="text-right" style="color:#e01020; font-weight:700;">-₹<?= number_format(floatval($order['discount']), 2) ?></td>
                    </tr>
                <?php endif; ?>
                <tr class="grand-total">
                    <td>Grand Total:</td>
                    <td class="text-right">₹<?= number_format($grand_total, 2) ?></td>
                </tr>
            </table>

        </div>

        <!-- Footer Terms & Signature -->
        <div class="invoice-footer">
            <div class="terms-text">
                <strong>Terms & Conditions:</strong><br>
                1. All disputes are subject to Gujarat jurisdiction only.<br>
                2. Goods once sold will only be taken back or exchanged as per the store's exchange/return policy.<br>
                3. This is a computer-generated tax invoice.
            </div>

            <div class="signature-box">
                <div style="font-family: 'Brush Script MT', cursive, sans-serif; font-size: 20px; color: #333; margin-bottom: 4px;">
                    Ghanshyam
                </div>
                <div class="signature-title">
                    Authorised Signature for<br>
                    <strong>Ghanshyam Murti Bhandar</strong>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
