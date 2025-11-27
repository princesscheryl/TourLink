<?php
require_once 'includes_platform/auth_check.php';
require_once '../controllers/discount_controller.php';

// Check privilege for discount management
require_privilege('manage_discounts');

// Fetch all discount codes
$discounts = get_all_discount_codes_ctr();

// Calculate statistics
$total_codes = count($discounts);
$active_codes = 0;
$total_usage = 0;
$total_savings = 0;

foreach ($discounts as $d) {
    if ($d['is_active'] && strtotime($d['valid_until']) >= time()) {
        $active_codes++;
    }
    $total_usage += $d['usage_count'];
    $total_savings += floatval($d['total_discount_given'] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discount Codes - TourLink Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            color: #1e293b;
        }

        /* Sidebar - matches dashboard exactly */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            height: 100vh;
            background: #1b4332;
            padding: 24px 0;
            overflow-y: auto;
            z-index: 100;
        }

        .sidebar-brand {
            padding: 0 24px 24px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 24px;
        }

        .sidebar-brand a {
            color: white;
            text-decoration: none;
            font-size: 22px;
            font-weight: 700;
        }

        .sidebar-brand span {
            color: #d4a017;
        }

        .sidebar-brand small {
            display: block;
            color: rgba(255,255,255,0.6);
            font-size: 11px;
            font-weight: 400;
            margin-top: 4px;
        }

        .nav-section {
            padding: 0 16px;
            margin-bottom: 24px;
        }

        .nav-section-title {
            color: rgba(255,255,255,0.4);
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0 8px;
            margin-bottom: 8px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s;
            margin-bottom: 2px;
        }

        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        .nav-link.active {
            background: rgba(255,255,255,0.15);
            color: white;
        }

        .nav-link i {
            width: 18px;
            font-size: 14px;
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            min-height: 100vh;
        }

        /* Top Bar */
        .top-bar {
            background: white;
            padding: 16px 32px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .page-title {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-size: 13px;
            font-weight: 600;
            color: #1e293b;
        }

        .user-role {
            font-size: 11px;
            color: #64748b;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            background: #1b4332;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }

        /* Content Area */
        .content-area {
            padding: 32px;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #e2e8f0;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            font-size: 18px;
        }

        .stat-icon.blue { background: #dbeafe; color: #1e40af; }
        .stat-icon.green { background: #dcfce7; color: #166534; }
        .stat-icon.purple { background: #f3e8ff; color: #7c3aed; }
        .stat-icon.orange { background: #fed7aa; color: #c2410c; }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 13px;
            color: #64748b;
        }

        /* Main Card */
        .card {
            background: white;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .card-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
        }

        .card-body {
            padding: 24px;
        }

        /* Toolbar */
        .toolbar {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .search-box {
            flex: 1;
            min-width: 280px;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 10px 16px 10px 40px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 13px;
            transition: all 0.2s;
        }

        .search-box input:focus {
            outline: none;
            border-color: #1b4332;
        }

        .search-box i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 14px;
        }

        .filter-select {
            padding: 10px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 13px;
            background: white;
            cursor: pointer;
            transition: all 0.2s;
        }

        .filter-select:focus {
            outline: none;
            border-color: #1b4332;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: #1b4332;
            color: white;
        }

        .btn-primary:hover {
            background: #14352a;
            transform: translateY(-1px);
        }

        /* Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table thead th {
            text-align: left;
            padding: 12px;
            background: #f8fafc;
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e2e8f0;
        }

        .data-table tbody td {
            padding: 16px 12px;
            font-size: 13px;
            border-bottom: 1px solid #f1f5f9;
        }

        .data-table tbody tr:hover {
            background: #f8fafc;
        }

        /* Code Badge */
        .code-badge {
            background: #1b4332;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            display: inline-block;
        }

        /* Type Badge */
        .type-badge {
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .type-percentage {
            background: #dbeafe;
            color: #1e40af;
        }

        .type-fixed {
            background: #f3e8ff;
            color: #7c3aed;
        }

        /* Status Badge */
        .status-badge {
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-active {
            background: #dcfce7;
            color: #166534;
        }

        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-expired {
            background: #fef3c7;
            color: #92400e;
        }

        /* Progress Bar */
        .usage-progress {
            height: 6px;
            background: #e2e8f0;
            border-radius: 3px;
            overflow: hidden;
            margin-top: 6px;
        }

        .usage-fill {
            height: 100%;
            background: #1b4332;
            border-radius: 3px;
            transition: width 0.3s;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 6px;
        }

        .btn-action {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            font-size: 13px;
        }

        .btn-edit {
            background: #dbeafe;
            color: #1e40af;
        }

        .btn-edit:hover {
            background: #bfdbfe;
        }

        .btn-toggle {
            background: #fef3c7;
            color: #92400e;
        }

        .btn-toggle:hover {
            background: #fde68a;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
        }

        .btn-close {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: none;
            background: #f1f5f9;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .btn-close:hover {
            background: #e2e8f0;
        }

        .modal-body {
            padding: 24px;
        }

        /* Form */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 13px;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: #1b4332;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-help {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            display: none;
        }

        .alert.success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .alert.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert.active {
            display: block;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state i {
            font-size: 56px;
            color: #cbd5e1;
            margin-bottom: 16px;
        }

        .empty-state h3 {
            font-size: 16px;
            color: #64748b;
            margin-bottom: 8px;
        }

        .empty-state p {
            font-size: 14px;
            color: #94a3b8;
            margin-bottom: 20px;
        }

        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .main-content {
                margin-left: 0;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <a href="platform_dashboard.php">TourLink<span>.</span></a>
            <small>Administration</small>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Overview</div>
            <a href="platform_dashboard.php" class="nav-link">
                <i class="fas fa-th-large"></i>
                Dashboard
            </a>
            <a href="impact.php" class="nav-link">
                <i class="fas fa-chart-line"></i>
                Impact Metrics
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Management</div>
            <a href="users.php" class="nav-link">
                <i class="fas fa-users"></i>
                Users
            </a>
            <a href="manage_providers.php" class="nav-link">
                <i class="fas fa-store"></i>
                Providers
            </a>
            <a href="manage_bookings.php" class="nav-link">
                <i class="fas fa-calendar-check"></i>
                Bookings
            </a>
            <a href="services.php" class="nav-link">
                <i class="fas fa-concierge-bell"></i>
                Services
            </a>
            <a href="manage_discounts.php" class="nav-link active">
                <i class="fas fa-tags"></i>
                Discount Codes
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Content</div>
            <a href="manage_festivals.php" class="nav-link">
                <i class="fas fa-drum"></i>
                Festivals
            </a>
            <a href="regions.php" class="nav-link">
                <i class="fas fa-map-marked-alt"></i>
                Regions
            </a>
            <a href="stories.php" class="nav-link">
                <i class="fas fa-book-open"></i>
                Success Stories
            </a>
        </div>

        <div class="nav-section" style="margin-top: auto;">
            <a href="platform_logout.php" class="nav-link">
                <i class="fas fa-sign-out-alt"></i>
                Sign Out
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <h1 class="page-title">Discount Codes</h1>
            <div class="user-menu">
                <div class="user-info">
                    <div class="user-name">System Admin</div>
                    <div class="user-role">Super Admin</div>
                </div>
                <div class="user-avatar">S</div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="stat-value"><?php echo $total_codes; ?></div>
                    <div class="stat-label">Total Codes</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-value"><?php echo $active_codes; ?></div>
                    <div class="stat-label">Active Codes</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon purple">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-value"><?php echo $total_usage; ?></div>
                    <div class="stat-label">Total Usage</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-value">GH₵<?php echo number_format($total_savings, 0); ?></div>
                    <div class="stat-label">Total Savings</div>
                </div>
            </div>

            <!-- Main Card -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">All Discount Codes</h2>
                    <button class="btn btn-primary" onclick="openCreateModal()">
                        <i class="fas fa-plus"></i>
                        Create Discount Code
                    </button>
                </div>

                <div class="card-body">
                    <!-- Toolbar -->
                    <div class="toolbar">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInput" placeholder="Search discount codes...">
                        </div>
                        <select class="filter-select" id="statusFilter">
                            <option value="all">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="expired">Expired</option>
                        </select>
                        <select class="filter-select" id="typeFilter">
                            <option value="all">All Types</option>
                            <option value="percentage">Percentage</option>
                            <option value="fixed">Fixed Amount</option>
                        </select>
                    </div>

                    <!-- Table -->
                    <?php if (empty($discounts)): ?>
                    <div class="empty-state">
                        <i class="fas fa-tags"></i>
                        <h3>No discount codes yet</h3>
                        <p>Create your first discount code to start offering promotions</p>
                        <button class="btn btn-primary" onclick="openCreateModal()">
                            <i class="fas fa-plus"></i>
                            Create Discount Code
                        </button>
                    </div>
                    <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Type</th>
                                <th>Value</th>
                                <th>Valid Period</th>
                                <th>Usage</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="discountsTableBody">
                            <?php foreach ($discounts as $discount):
                                $is_expired = strtotime($discount['valid_until']) < time();
                                $usage_percentage = 0;
                                if ($discount['usage_limit'] > 0) {
                                    $usage_percentage = ($discount['usage_count'] / $discount['usage_limit']) * 100;
                                }
                            ?>
                            <tr data-status="<?php echo $is_expired ? 'expired' : ($discount['is_active'] ? 'active' : 'inactive'); ?>" data-type="<?php echo $discount['discount_type']; ?>">
                                <td>
                                    <span class="code-badge"><?php echo htmlspecialchars($discount['code']); ?></span>
                                </td>
                                <td>
                                    <span class="type-badge type-<?php echo $discount['discount_type']; ?>">
                                        <?php echo $discount['discount_type'] === 'percentage' ? 'Percentage' : 'Fixed'; ?>
                                    </span>
                                </td>
                                <td>
                                    <strong>
                                        <?php
                                        if ($discount['discount_type'] === 'percentage') {
                                            echo $discount['discount_value'] . '%';
                                        } else {
                                            echo 'GH₵ ' . number_format($discount['discount_value'], 2);
                                        }
                                        ?>
                                    </strong>
                                </td>
                                <td>
                                    <div style="font-size: 12px; color: #64748b;">
                                        <?php echo date('M j, Y', strtotime($discount['valid_from'])); ?> -
                                        <?php echo date('M j, Y', strtotime($discount['valid_until'])); ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size: 13px; margin-bottom: 4px;">
                                        <?php
                                        if ($discount['usage_limit']) {
                                            echo "{$discount['usage_count']} / {$discount['usage_limit']}";
                                        } else {
                                            echo "{$discount['usage_count']} / Unlimited";
                                        }
                                        ?>
                                    </div>
                                    <?php if ($discount['usage_limit']): ?>
                                    <div class="usage-progress">
                                        <div class="usage-fill" style="width: <?php echo min($usage_percentage, 100); ?>%;"></div>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($is_expired): ?>
                                        <span class="status-badge status-expired">Expired</span>
                                    <?php elseif ($discount['is_active']): ?>
                                        <span class="status-badge status-active">Active</span>
                                    <?php else: ?>
                                        <span class="status-badge status-inactive">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-action btn-edit" onclick="editDiscount(<?php echo $discount['discount_id']; ?>)" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-action btn-toggle" onclick="toggleDiscount(<?php echo $discount['discount_id']; ?>, <?php echo $discount['is_active'] ? 'false' : 'true'; ?>)" title="<?php echo $discount['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                            <i class="fas fa-<?php echo $discount['is_active'] ? 'pause' : 'play'; ?>"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Create/Edit Modal -->
    <div class="modal" id="discountModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Create Discount Code</h3>
                <button class="btn-close" onclick="closeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body">
                <div class="alert" id="modalAlert"></div>

                <form id="discountForm">
                    <input type="hidden" id="discountId" name="discount_id">

                    <div class="form-group">
                        <label class="form-label">Discount Code *</label>
                        <input type="text" class="form-control" id="code" name="code" required maxlength="50" style="text-transform: uppercase;" placeholder="e.g., SUMMER2025">
                        <div class="form-help">Use uppercase letters and numbers only</div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Discount Type *</label>
                            <select class="form-control" id="discountType" name="discount_type" required onchange="updateValueLabel()">
                                <option value="percentage">Percentage</option>
                                <option value="fixed">Fixed Amount</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label" id="valueLabel">Discount Value (%) *</label>
                            <input type="number" class="form-control" id="discountValue" name="discount_value" required min="0" step="0.01" placeholder="10">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Minimum Booking Amount</label>
                            <input type="number" class="form-control" id="minAmount" name="min_booking_amount" min="0" step="0.01" value="0" placeholder="0.00">
                            <div class="form-help">GH₵ (0 for no minimum)</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Max Discount Amount</label>
                            <input type="number" class="form-control" id="maxDiscount" name="max_discount_amount" min="0" step="0.01" placeholder="Optional cap">
                            <div class="form-help">GH₵ (Optional)</div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Total Usage Limit</label>
                            <input type="number" class="form-control" id="usageLimit" name="usage_limit" min="1" placeholder="Unlimited">
                            <div class="form-help">Leave empty for unlimited</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Per User Limit</label>
                            <input type="number" class="form-control" id="perUserLimit" name="per_user_limit" min="1" value="1" placeholder="1">
                            <div class="form-help">Uses per customer</div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Valid From *</label>
                            <input type="date" class="form-control" id="validFrom" name="valid_from" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Valid Until *</label>
                            <input type="date" class="form-control" id="validUntil" name="valid_until" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Internal notes about this discount..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;" id="submitBtn">
                        Create Discount Code
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Search and filter functionality
        document.getElementById('searchInput').addEventListener('input', filterTable);
        document.getElementById('statusFilter').addEventListener('change', filterTable);
        document.getElementById('typeFilter').addEventListener('change', filterTable);

        function filterTable() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const typeFilter = document.getElementById('typeFilter').value;
            const rows = document.querySelectorAll('#discountsTableBody tr');

            rows.forEach(row => {
                const code = row.querySelector('.code-badge').textContent.toLowerCase();
                const status = row.getAttribute('data-status');
                const type = row.getAttribute('data-type');

                const matchesSearch = code.includes(searchTerm);
                const matchesStatus = statusFilter === 'all' || status === statusFilter;
                const matchesType = typeFilter === 'all' || type === typeFilter;

                if (matchesSearch && matchesStatus && matchesType) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Modal functions
        function openCreateModal() {
            document.getElementById('modalTitle').textContent = 'Create Discount Code';
            document.getElementById('discountForm').reset();
            document.getElementById('discountId').value = '';
            document.getElementById('submitBtn').textContent = 'Create Discount Code';
            document.getElementById('discountModal').classList.add('active');
            document.getElementById('modalAlert').classList.remove('active');

            const today = new Date().toISOString().split('T')[0];
            document.getElementById('validFrom').value = today;
        }

        function closeModal() {
            document.getElementById('discountModal').classList.remove('active');
        }

        function updateValueLabel() {
            const type = document.getElementById('discountType').value;
            const label = document.getElementById('valueLabel');
            if (type === 'percentage') {
                label.textContent = 'Discount Value (%) *';
            } else {
                label.textContent = 'Discount Value (GH₵) *';
            }
        }

        // Form submission
        document.getElementById('discountForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            const isEdit = document.getElementById('discountId').value !== '';

            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

            try {
                const response = await fetch(isEdit ? '../actions/update_discount_action.php' : '../actions/create_discount_action.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.status === 'success') {
                    showAlert('success', result.message);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showAlert('error', result.message);
                    submitBtn.disabled = false;
                    submitBtn.textContent = isEdit ? 'Update Discount Code' : 'Create Discount Code';
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('error', 'Connection error. Please try again.');
                submitBtn.disabled = false;
                submitBtn.textContent = isEdit ? 'Update Discount Code' : 'Create Discount Code';
            }
        });

        function showAlert(type, message) {
            const alert = document.getElementById('modalAlert');
            alert.className = `alert ${type} active`;
            alert.textContent = message;
        }

        // Edit discount
        async function editDiscount(discountId) {
            try {
                const response = await fetch(`../actions/get_discount_action.php?id=${discountId}`);
                const result = await response.json();

                if (result.status === 'success') {
                    const discount = result.data;

                    document.getElementById('modalTitle').textContent = 'Edit Discount Code';
                    document.getElementById('discountId').value = discount.discount_id;
                    document.getElementById('code').value = discount.code;
                    document.getElementById('discountType').value = discount.discount_type;
                    document.getElementById('discountValue').value = discount.discount_value;
                    document.getElementById('minAmount').value = discount.min_booking_amount;
                    document.getElementById('maxDiscount').value = discount.max_discount_amount || '';
                    document.getElementById('usageLimit').value = discount.usage_limit || '';
                    document.getElementById('perUserLimit').value = discount.per_user_limit;
                    document.getElementById('validFrom').value = discount.valid_from;
                    document.getElementById('validUntil').value = discount.valid_until;
                    document.getElementById('description').value = discount.description || '';
                    document.getElementById('submitBtn').textContent = 'Update Discount Code';

                    updateValueLabel();
                    document.getElementById('discountModal').classList.add('active');
                    document.getElementById('modalAlert').classList.remove('active');
                } else {
                    alert(result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to load discount details');
            }
        }

        // Toggle discount active status
        async function toggleDiscount(discountId, activate) {
            if (!confirm(`Are you sure you want to ${activate ? 'activate' : 'deactivate'} this discount code?`)) {
                return;
            }

            try {
                const response = await fetch('../actions/toggle_discount_action.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        discount_id: discountId,
                        is_active: activate
                    })
                });

                const result = await response.json();

                if (result.status === 'success') {
                    window.location.reload();
                } else {
                    alert(result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Connection error. Please try again.');
            }
        }

        // Close modal when clicking outside
        document.getElementById('discountModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>
