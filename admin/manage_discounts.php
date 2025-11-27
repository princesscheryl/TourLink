<?php
session_start();
require_once '../settings/core.php';
require_once '../controllers/discount_controller.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: platform_login.php');
    exit();
}

// Get all discount codes
$discounts = get_all_discount_codes_ctr();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Discount Codes - TourLink Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="../css/navigation.css" rel="stylesheet">
    <link href="../css/dark-mode.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
        }

        .container {
            max-width: 1400px;
            margin: 100px auto 60px;
            padding: 0 20px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .page-header h1 {
            font-size: 1.8rem;
            color: #1a1a1a;
        }

        .btn-add {
            padding: 12px 24px;
            background: #2d6a4f;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-add:hover {
            background: #1b4332;
            transform: translateY(-2px);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .stat-card .stat-label {
            color: #666;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #2d6a4f;
        }

        .stat-card .stat-icon {
            font-size: 24px;
            margin-bottom: 12px;
            color: #2d6a4f;
        }

        .discounts-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: #1a1a1a;
        }

        .search-filter {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .search-input {
            flex: 1;
            min-width: 250px;
            padding: 10px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
        }

        .search-input:focus {
            outline: none;
            border-color: #2d6a4f;
        }

        .filter-select {
            padding: 10px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            background: white;
        }

        .filter-select:focus {
            outline: none;
            border-color: #2d6a4f;
        }

        .discounts-table {
            width: 100%;
            border-collapse: collapse;
            overflow-x: auto;
            display: block;
        }

        .discounts-table thead {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .discounts-table tbody {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .discounts-table th {
            text-align: left;
            padding: 12px;
            background: #f8f9fa;
            font-weight: 600;
            color: #1a1a1a;
            font-size: 14px;
            border-bottom: 2px solid #e0e0e0;
        }

        .discounts-table td {
            padding: 16px 12px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
            color: #333;
        }

        .discounts-table tr:hover {
            background: #f8f9fa;
        }

        .code-badge {
            background: #2d6a4f;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            display: inline-block;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-block;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .status-expired {
            background: #fff3cd;
            color: #856404;
        }

        .discount-type {
            padding: 4px 10px;
            background: #e7f5ff;
            color: #0056b3;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-action {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-edit {
            background: #0056b3;
            color: white;
        }

        .btn-edit:hover {
            background: #004494;
        }

        .btn-toggle {
            background: #ffc107;
            color: #1a1a1a;
        }

        .btn-toggle:hover {
            background: #e0a800;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .btn-delete:hover {
            background: #c82333;
        }

        .usage-bar {
            width: 100%;
            height: 6px;
            background: #e0e0e0;
            border-radius: 3px;
            overflow: hidden;
            margin-top: 6px;
        }

        .usage-fill {
            height: 100%;
            background: #2d6a4f;
            border-radius: 3px;
            transition: width 0.3s;
        }

        .usage-text {
            font-size: 12px;
            color: #666;
            margin-top: 4px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state i {
            font-size: 64px;
            color: #ccc;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: #666;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #999;
            margin-bottom: 20px;
        }

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
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 30px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .modal-header h2 {
            font-size: 1.4rem;
            color: #1a1a1a;
        }

        .btn-close {
            background: none;
            border: none;
            font-size: 24px;
            color: #666;
            cursor: pointer;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s;
        }

        .btn-close:hover {
            background: #f0f0f0;
            color: #1a1a1a;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #1a1a1a;
            font-size: 14px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #2d6a4f;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-help {
            font-size: 12px;
            color: #666;
            margin-top: 4px;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: #2d6a4f;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            background: #1b4332;
        }

        .btn-submit:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .alert {
            padding: 14px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .alert.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            display: block;
        }

        .alert.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            display: block;
        }

        @media (max-width: 968px) {
            .discounts-table {
                font-size: 13px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/navigation.php'; ?>

    <div class="container">
        <div class="page-header">
            <h1>Discount Code Management</h1>
            <button class="btn-add" onclick="openCreateModal()">
                <i class="fas fa-plus"></i>
                Create Discount Code
            </button>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-tags"></i>
                </div>
                <div class="stat-label">Total Codes</div>
                <div class="stat-value"><?php echo count($discounts); ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-label">Active Codes</div>
                <div class="stat-value">
                    <?php
                    $active_count = 0;
                    foreach ($discounts as $d) {
                        if ($d['is_active'] && strtotime($d['valid_until']) >= time()) {
                            $active_count++;
                        }
                    }
                    echo $active_count;
                    ?>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-label">Total Usage</div>
                <div class="stat-value">
                    <?php
                    $total_usage = 0;
                    foreach ($discounts as $d) {
                        $total_usage += $d['usage_count'];
                    }
                    echo $total_usage;
                    ?>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-label">Total Savings</div>
                <div class="stat-value">
                    <?php
                    $total_savings = 0;
                    foreach ($discounts as $d) {
                        $total_savings += floatval($d['total_discount_given'] ?? 0);
                    }
                    echo 'GH₵ ' . number_format($total_savings, 0);
                    ?>
                </div>
            </div>
        </div>

        <div class="discounts-section">
            <h2 class="section-title">All Discount Codes</h2>

            <div class="search-filter">
                <input type="text" class="search-input" id="searchInput" placeholder="Search by code or description...">
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

            <?php if (empty($discounts)): ?>
            <div class="empty-state">
                <i class="fas fa-tags"></i>
                <h3>No discount codes yet</h3>
                <p>Create your first discount code to start offering promotions to customers</p>
                <button class="btn-add" onclick="openCreateModal()">
                    <i class="fas fa-plus"></i>
                    Create Discount Code
                </button>
            </div>
            <?php else: ?>
            <table class="discounts-table">
                <thead>
                    <tr>
                        <th style="width: 15%;">Code</th>
                        <th style="width: 15%;">Type</th>
                        <th style="width: 12%;">Value</th>
                        <th style="width: 18%;">Valid Period</th>
                        <th style="width: 15%;">Usage</th>
                        <th style="width: 10%;">Status</th>
                        <th style="width: 15%;">Actions</th>
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
                            <span class="discount-type">
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
                            <div style="font-size: 13px;">
                                <?php echo date('M j, Y', strtotime($discount['valid_from'])); ?>
                                <br>
                                to <?php echo date('M j, Y', strtotime($discount['valid_until'])); ?>
                            </div>
                        </td>
                        <td>
                            <div>
                                <?php
                                if ($discount['usage_limit']) {
                                    echo "{$discount['usage_count']} / {$discount['usage_limit']}";
                                } else {
                                    echo "{$discount['usage_count']} / Unlimited";
                                }
                                ?>
                            </div>
                            <?php if ($discount['usage_limit']): ?>
                            <div class="usage-bar">
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
                                <button class="btn-action btn-edit" onclick="editDiscount(<?php echo $discount['discount_id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-action btn-toggle" onclick="toggleDiscount(<?php echo $discount['discount_id']; ?>, <?php echo $discount['is_active'] ? 'false' : 'true'; ?>)">
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

    <!-- Create/Edit Modal -->
    <div class="modal" id="discountModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Create Discount Code</h2>
                <button class="btn-close" onclick="closeModal()">&times;</button>
            </div>

            <div class="alert" id="modalAlert"></div>

            <form id="discountForm">
                <input type="hidden" id="discountId" name="discount_id">

                <div class="form-group">
                    <label for="code">Discount Code *</label>
                    <input type="text" id="code" name="code" required maxlength="50" style="text-transform: uppercase;">
                    <div class="form-help">Use uppercase letters and numbers only (e.g., SUMMER2025)</div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="discountType">Discount Type *</label>
                        <select id="discountType" name="discount_type" required onchange="updateValueLabel()">
                            <option value="percentage">Percentage</option>
                            <option value="fixed">Fixed Amount</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="discountValue" id="valueLabel">Discount Value (%) *</label>
                        <input type="number" id="discountValue" name="discount_value" required min="0" step="0.01">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="minAmount">Minimum Booking Amount</label>
                        <input type="number" id="minAmount" name="min_booking_amount" min="0" step="0.01" value="0">
                        <div class="form-help">GH₵ (Leave 0 for no minimum)</div>
                    </div>

                    <div class="form-group">
                        <label for="maxDiscount">Max Discount Amount</label>
                        <input type="number" id="maxDiscount" name="max_discount_amount" min="0" step="0.01">
                        <div class="form-help">GH₵ (Optional cap)</div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="usageLimit">Total Usage Limit</label>
                        <input type="number" id="usageLimit" name="usage_limit" min="1">
                        <div class="form-help">Leave empty for unlimited</div>
                    </div>

                    <div class="form-group">
                        <label for="perUserLimit">Per User Limit</label>
                        <input type="number" id="perUserLimit" name="per_user_limit" min="1" value="1">
                        <div class="form-help">Uses per customer</div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="validFrom">Valid From *</label>
                        <input type="date" id="validFrom" name="valid_from" required>
                    </div>

                    <div class="form-group">
                        <label for="validUntil">Valid Until *</label>
                        <input type="date" id="validUntil" name="valid_until" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3" placeholder="Internal notes about this discount..."></textarea>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    <i class="fas fa-check"></i> Create Discount Code
                </button>
            </form>
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
            document.getElementById('submitBtn').innerHTML = '<i class="fas fa-check"></i> Create Discount Code';
            document.getElementById('discountModal').classList.add('active');

            // Set default dates
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('validFrom').value = today;
        }

        function closeModal() {
            document.getElementById('discountModal').classList.remove('active');
            document.getElementById('modalAlert').style.display = 'none';
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
                    submitBtn.innerHTML = isEdit ? '<i class="fas fa-check"></i> Update Discount Code' : '<i class="fas fa-check"></i> Create Discount Code';
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('error', 'Connection error. Please try again.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = isEdit ? '<i class="fas fa-check"></i> Update Discount Code' : '<i class="fas fa-check"></i> Create Discount Code';
            }
        });

        function showAlert(type, message) {
            const alert = document.getElementById('modalAlert');
            alert.className = `alert ${type}`;
            alert.textContent = message;
            alert.style.display = 'block';
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
                    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-check"></i> Update Discount Code';

                    updateValueLabel();
                    document.getElementById('discountModal').classList.add('active');
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
