<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $this->security->get_csrf_hash() ?>">
    <title><?= isset($page_title) ? $page_title . ' - ' : '' ?><?= $this->session->userdata('shop_name') ?: 'Admin Panel' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-red: #E01020;
            --dark-red: #C02020;
            --black: #000000;
            --dark-gray: #111111;
            --card-bg: #1A1A1A;
            --white-text: #FFFFFF;
            --border-color: #333333;
            --light-gray: #2A2A2A;
        }

        body {
            background: var(--dark-gray);
            color: var(--white-text);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }

        /* PAGE HEADER & DASHBOARD GRID STYLES (Loaded in <head> to prevent FOUC) */
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        .secondary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        .tables-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        /* SIDEBAR */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            height: 100vh;
            background: var(--black);
            border-right: 1px solid var(--border-color);
            overflow-y: auto;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .sidebar::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: var(--primary-red);
        }

        .sidebar-logo {
            padding: 20px 20px 25px;
            border-bottom: 1px solid var(--border-color);
        }

        .sidebar-logo-content {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--white-text);
        }

        .sidebar-logo-icon {
            font-size: 30px;
            color: var(--primary-red);
        }

        .sidebar-logo-text h5 {
            font-size: 15px;
            font-weight: 700;
            margin: 0;
        }

        .sidebar-logo-text p {
            font-size: 11px;
            color: #999;
            margin: 0;
        }

        .sidebar-menu {
            padding: 15px;
        }

        .sidebar-section-title {
            font-size: 10px;
            font-weight: 700;
            color: #555;
            padding: 12px 10px 6px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sidebar-menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            margin-bottom: 3px;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            color: #999;
            transition: all 0.25s ease;
            font-size: 13.5px;
            font-weight: 500;
        }

        .sidebar-menu-item:hover {
            background: rgba(224, 16, 32, 0.1);
            color: var(--primary-red);
            padding-left: 18px;
        }

        .sidebar-menu-item.active {
            background: var(--primary-red);
            color: var(--white-text);
        }

        .sidebar-menu-item i {
            font-size: 15px;
            width: 18px;
            text-align: center;
        }

        /* NAVBAR */
        .navbar-top {
            position: fixed;
            top: 0;
            left: 260px;
            right: 0;
            background: var(--card-bg);
            border-bottom: 1px solid var(--border-color);
            padding: 0 25px;
            height: 65px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 999;
        }

        .navbar-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .sidebar-toggle {
            background: none;
            border: none;
            color: #999;
            font-size: 18px;
            cursor: pointer;
            padding: 6px;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .sidebar-toggle:hover {
            color: var(--primary-red);
            background: rgba(224, 16, 32, 0.1);
        }

        .page-breadcrumb {
            font-size: 14px;
            color: #999;
        }

        .page-breadcrumb span {
            color: var(--white-text);
            font-weight: 600;
        }

        .navbar-admin {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 6px 12px;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .navbar-admin:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .navbar-admin-avatar {
            width: 36px;
            height: 36px;
            background: var(--primary-red);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 700;
            overflow: hidden;
        }

        .navbar-admin-info .name {
            font-size: 13px;
            font-weight: 600;
        }

        .navbar-admin-info .role {
            font-size: 11px;
            color: #999;
        }

        /* MAIN */
        .main-content {
            margin-left: 260px;
            padding-top: 65px;
            min-height: 100vh;
        }

        .page-inner {
            padding: 25px;
        }

        /* CARDS */
        .card-dark {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
        }

        .card-dark .card-header-dark {
            padding: 18px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-dark .card-header-dark h6 {
            margin: 0;
            font-size: 15px;
            font-weight: 600;
        }

        .card-dark .card-body-dark {
            padding: 20px;
        }

        /* FORM CONTROLS */
        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--white-text);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .form-control,
        .form-select {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13.5px;
            color: var(--white-text);
            transition: all 0.25s;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }

        .form-control:focus,
        .form-select:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--primary-red);
            box-shadow: 0 0 0 3px rgba(224, 16, 32, 0.1);
            color: var(--white-text);
            outline: none;
        }

        .form-control:hover,
        .form-select:hover {
            border-color: #555;
        }

        .form-select option {
            background: var(--card-bg);
            color: var(--white-text);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        /* BUTTONS */
        .btn-red {
            background: linear-gradient(135deg, var(--primary-red), var(--dark-red));
            color: #fff;
            border: none;
            padding: 10px 22px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.25s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .btn-red:hover {
            background: linear-gradient(135deg, var(--dark-red), #a01818);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 5px 20px rgba(224, 16, 32, 0.3);
        }

        .btn-outline-light-custom {
            background: transparent;
            border: 1px solid var(--border-color);
            color: #999;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.25s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .btn-outline-light-custom:hover {
            border-color: #666;
            color: var(--white-text);
        }

        /* TABLE */
        .table-dark-custom {
            width: 100%;
            color: var(--white-text);
            border-collapse: separate;
            border-spacing: 0;
        }

        .table-dark-custom thead th {
            padding: 12px 15px;
            font-size: 11px;
            font-weight: 700;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: 1px solid var(--border-color);
        }

        .table-dark-custom tbody td {
            padding: 14px 15px;
            font-size: 13.5px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            vertical-align: middle;
        }

        .table-dark-custom tbody tr:hover td {
            background: rgba(255, 255, 255, 0.03);
        }

        .table-dark-custom tbody tr:last-child td {
            border-bottom: none;
        }

        /* BADGE */
        .badge-active {
            background: rgba(76, 175, 80, 0.15);
            color: #81c784;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-inactive {
            background: rgba(255, 152, 0, 0.15);
            color: #ffb74d;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        /* ALERTS */
        .alert-custom {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13.5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success-custom {
            background: rgba(76, 175, 80, 0.1);
            border: 1px solid rgba(76, 175, 80, 0.3);
            color: #81c784;
        }

        .alert-danger-custom {
            background: rgba(224, 16, 32, 0.1);
            border: 1px solid rgba(224, 16, 32, 0.3);
            color: #ff6b6b;
        }

        /* ACTION BUTTONS */
        .action-btn {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            background: transparent;
            color: #999;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            font-size: 13px;
        }

        .action-btn:hover {
            color: var(--white-text);
            border-color: #555;
        }

        .action-btn.edit:hover {
            border-color: #2196f3;
            color: #2196f3;
            background: rgba(33, 150, 243, 0.1);
        }

        .action-btn.delete:hover {
            border-color: var(--primary-red);
            color: var(--primary-red);
            background: rgba(224, 16, 32, 0.1);
        }

        .action-btn.view:hover {
            border-color: #4caf50;
            color: #4caf50;
            background: rgba(76, 175, 80, 0.1);
        }

        /* PAGE HEADER */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
        }

        .page-header h4 {
            font-size: 20px;
            font-weight: 700;
            margin: 0;
        }

        .page-header p {
            font-size: 13px;
            color: #999;
            margin: 3px 0 0;
        }

        .alert-success-custom {
            background: rgba(25, 135, 84, .15);
            border: 1px solid rgba(25, 135, 84, .4);
            color: #75ffb1;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .sidebar {
                left: -260px;
            }

            .sidebar.mobile-open {
                left: 0;
            }

            .navbar-top {
                left: 0;
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>

    <!-- SIDEBAR -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <a href="<?= base_url('dashboard') ?>" class="sidebar-logo-content">
                <img src="<?php echo base_url('uploads/ghanshyam-murti-logo-dark-bg.png'); ?>" alt="Ghanshyam Murti Bhandar" class="sidebar-logo-img">
            </a>
        </div>
        <style>
            .sidebar-logo {
                text-align: center;
                padding: 15px 10px;
            }

            .sidebar-logo-img {
                max-width: 230px;
                width: 100%;
                height: auto;
                display: block;
                margin: 0 auto;
            }
        </style>

        <div class="sidebar-menu">

            <!-- Main -->
            <div class="sidebar-section-title">Main</div>

            <a href="<?= base_url('dashboard') ?>" class="sidebar-menu-item <?= ($this->uri->segment(1) == 'dashboard' || $this->uri->segment(1) == '') ? 'active' : '' ?>">
                <i class="fas fa-chart-pie"></i> Dashboard
            </a>

            <!-- Catalogue -->
            <div class="sidebar-section-title">Catalogue</div>

            <a href="<?= base_url('category') ?>" class="sidebar-menu-item <?= $this->uri->segment(1) == 'category' ? 'active' : '' ?>">
                <i class="fas fa-list"></i> Categories
            </a>

            <a href="<?= base_url('product') ?>" class="sidebar-menu-item <?= $this->uri->segment(1) == 'product' ? 'active' : '' ?>">
                <i class="fas fa-boxes"></i> Products
            </a>

            <a href="<?= base_url('product/add') ?>" class="sidebar-menu-item <?= ($this->uri->segment(1) == 'product' && $this->uri->segment(2) == 'add') ? 'active' : '' ?>">
                <i class="fas fa-plus-circle"></i> Add Product
            </a>

            <!-- Orders -->
            <div class="sidebar-section-title">Sales</div>

            <a href="<?= base_url('order') ?>" class="sidebar-menu-item <?= $this->uri->segment(1) == 'order' ? 'active' : '' ?>">
                <i class="fas fa-shopping-bag"></i> Orders
            </a>

            <!-- Marketing -->
            <div class="sidebar-section-title">Marketing</div>

            <a href="<?= base_url('home_banners') ?>" class="sidebar-menu-item <?= ($this->uri->segment(1) == 'home_banners' || $this->uri->segment(1) == 'home_banner') ? 'active' : '' ?>">
                <i class="fas fa-images"></i> Home Banners
            </a>

            <a href="<?= base_url('offers') ?>" class="sidebar-menu-item <?= ($this->uri->segment(1) == 'offers' || $this->uri->segment(1) == 'offer') ? 'active' : '' ?>">
                <i class="fas fa-tags"></i> Offers
            </a>

            <!-- Content -->
            <div class="sidebar-section-title">Content</div>

            <a href="<?= base_url('admin/pages') ?>" class="sidebar-menu-item <?= ($this->uri->segment(1) == 'admin' && $this->uri->segment(2) == 'pages' && $this->uri->segment(3) != 'help-support') ? 'active' : '' ?>">
                <i class="fas fa-file-alt"></i> Policy Pages
            </a>

            <a href="<?= base_url('admin/pages/help-support') ?>" class="sidebar-menu-item <?= ($this->uri->segment(1) == 'admin' && $this->uri->segment(2) == 'pages' && $this->uri->segment(3) == 'help-support') ? 'active' : '' ?>">
                <i class="fas fa-headset"></i> Help & Support
            </a>

            <!-- Account -->
            <div class="sidebar-section-title">Account</div>

            <a href="<?= base_url('logout') ?>" class="sidebar-menu-item">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>

        </div>
    </nav>

    <!-- NAVBAR -->
    <header class="navbar-top">
        <div class="navbar-left">
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="page-breadcrumb">
                <span><?= isset($page_title) ? $page_title : 'Dashboard' ?></span>
            </div>
        </div>
        <div class="navbar-right">
            <div class="navbar-admin dropdown">
                <div data-bs-toggle="dropdown" style="display:flex;align-items:center;gap:10px;">
                    <div class="navbar-admin-avatar">
                        <?php if ($this->session->userdata('admin_image')): ?>
                            <img src="<?= base_url('uploads/profile/' . $this->session->userdata('admin_image')) ?>" style="width:100%;height:100%;object-fit:cover;">
                        <?php else: ?>
                            <?= strtoupper(substr($this->session->userdata('admin_name') ?: 'A', 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <div class="navbar-admin-info">
                        <div class="name"><?= $this->session->userdata('admin_name') ?></div>
                        <div class="role">Admin</div>
                    </div>
                    <i class="fas fa-chevron-down" style="font-size:11px;color:#999;"></i>
                </div>
                <ul class="dropdown-menu dropdown-menu-end" style="background:var(--card-bg);border:1px solid var(--border-color);min-width:180px;">
                    <li><a class="dropdown-item" href="<?= base_url('profile') ?>" style="color:#999;font-size:13px;padding:10px 16px;">
                            <i class="fas fa-user me-2" style="color:var(--primary-red);"></i> Profile
                        </a></li>
                    <li>
                        <hr class="dropdown-divider" style="border-color:var(--border-color);">
                    </li>
                    <li><a class="dropdown-item" href="<?= base_url('logout') ?>" style="color:#999;font-size:13px;padding:10px 16px;">
                            <i class="fas fa-sign-out-alt me-2" style="color:var(--primary-red);"></i> Logout
                        </a></li>
                </ul>
            </div>
        </div>
    </header>

    <!-- MAIN -->
    <main class="main-content">
        <div class="page-inner">