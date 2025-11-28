<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes_platform/auth_check.php';
require_once '../settings/db_class.php';

// Check admin access
require_privilege('view_dashboard');

// Get all users with their details
$db = new db_connection();
$conn = $db->db_conn();

// Fetch users with pagination
$users = $db->db_fetch_all("
    SELECT u.*, sp.business_name, sp.verification_status, sp.total_earnings
    FROM tl_users u
    LEFT JOIN tl_service_providers sp ON u.user_id = sp.user_id
    ORDER BY u.date_created DESC
    LIMIT 100
");

// Calculate statistics
$total_users = 0;
$tourists = 0;
$providers = 0;
$verified_providers = 0;

if ($users) {
    $total_users = count($users);
    foreach ($users as $user) {
        if ($user['user_type'] === 'tourist') {
            $tourists++;
        } elseif ($user['user_type'] === 'provider') {
            $providers++;
            if ($user['verification_status'] === 'verified') {
                $verified_providers++;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - TourLink Admin</title>
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

        /* Sidebar */
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

        /* Card */
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

        /* User Info Cell */
        .user-info-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar-small {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 13px;
            color: #64748b;
        }

        .user-details {
            flex: 1;
        }

        .user-name-text {
            font-weight: 600;
            color: #1e293b;
            display: block;
            margin-bottom: 2px;
        }

        .user-email-text {
            font-size: 12px;
            color: #64748b;
        }

        /* Badges */
        .badge {
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-tourist {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-provider {
            background: #f3e8ff;
            color: #7c3aed;
        }

        .badge-admin {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-verified {
            background: #dcfce7;
            color: #166534;
        }

        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Action Buttons */
        .btn-action {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            font-size: 13px;
        }

        .btn-view {
            background: #dbeafe;
            color: #1e40af;
        }

        .btn-view:hover {
            background: #bfdbfe;
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
            <a href="users.php" class="nav-link active">
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
            <a href="manage_discounts.php" class="nav-link">
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
            <h1 class="page-title">Users</h1>
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
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value"><?php echo $total_users; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="stat-value"><?php echo $tourists; ?></div>
                    <div class="stat-label">Tourists</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon purple">
                        <i class="fas fa-store"></i>
                    </div>
                    <div class="stat-value"><?php echo $providers; ?></div>
                    <div class="stat-label">Providers</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-value"><?php echo $verified_providers; ?></div>
                    <div class="stat-label">Verified Providers</div>
                </div>
            </div>

            <!-- Main Card -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">All Users</h2>
                </div>

                <div class="card-body">
                    <!-- Toolbar -->
                    <div class="toolbar">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInput" placeholder="Search users by name or email...">
                        </div>
                        <select class="filter-select" id="typeFilter">
                            <option value="all">All Types</option>
                            <option value="tourist">Tourists</option>
                            <option value="provider">Providers</option>
                            <option value="admin">Admins</option>
                        </select>
                        <select class="filter-select" id="statusFilter">
                            <option value="all">All Status</option>
                            <option value="verified">Verified</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>

                    <!-- Table -->
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Type</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            <?php foreach ($users as $user): ?>
                            <tr data-type="<?php echo $user['user_type']; ?>" data-status="<?php echo $user['verification_status'] ?? 'verified'; ?>">
                                <td>
                                    <div class="user-info-cell">
                                        <div class="user-avatar-small">
                                            <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                                        </div>
                                        <div class="user-details">
                                            <span class="user-name-text">
                                                <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                            </span>
                                            <span class="user-email-text"><?php echo htmlspecialchars($user['email']); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $user['user_type']; ?>">
                                        <?php echo ucfirst($user['user_type']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php if ($user['user_type'] === 'provider'): ?>
                                        <?php if ($user['verification_status'] === 'verified'): ?>
                                            <span class="badge badge-verified">Verified</span>
                                        <?php elseif ($user['verification_status'] === 'pending'): ?>
                                            <span class="badge badge-pending">Pending</span>
                                        <?php else: ?>
                                            <span class="badge badge-rejected">Rejected</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge badge-verified">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span style="font-size: 12px; color: #64748b;">
                                        <?php echo date('M j, Y', strtotime($user['date_created'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn-action btn-view" onclick="viewUser(<?php echo $user['user_id']; ?>)" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', filterTable);
        document.getElementById('typeFilter').addEventListener('change', filterTable);
        document.getElementById('statusFilter').addEventListener('change', filterTable);

        function filterTable() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const typeFilter = document.getElementById('typeFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            const rows = document.querySelectorAll('#usersTableBody tr');

            rows.forEach(row => {
                const nameEmail = row.querySelector('.user-info-cell').textContent.toLowerCase();
                const type = row.getAttribute('data-type');
                const status = row.getAttribute('data-status');

                const matchesSearch = nameEmail.includes(searchTerm);
                const matchesType = typeFilter === 'all' || type === typeFilter;
                const matchesStatus = statusFilter === 'all' || status === statusFilter;

                if (matchesSearch && matchesType && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // View user details
        function viewUser(userId) {
            // Navigate to user detail page or show modal
            alert('User details view coming soon for user ID: ' + userId);
        }
    </script>
</body>
</html>
