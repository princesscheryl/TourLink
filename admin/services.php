<?php
require_once 'includes_platform/auth_check.php';
require_once '../settings/db_class.php';

// Check admin access
require_privilege('view_dashboard');

// Get all services with provider details
$db = new db_connection();

$services = $db->db_fetch_all("
    SELECT s.*, sp.business_name, u.first_name, u.last_name, sc.category_name,
           (SELECT COUNT(*) FROM tl_bookings WHERE service_id = s.service_id) as total_bookings
    FROM tl_services s
    JOIN tl_service_providers sp ON s.provider_id = sp.provider_id
    JOIN tl_users u ON sp.user_id = u.user_id
    LEFT JOIN tl_service_categories sc ON s.category_id = sc.category_id
    ORDER BY s.date_created DESC
    LIMIT 100
");

// Calculate statistics
$total_services = count($services);
$active_services = 0;
$pending_services = 0;
$total_revenue = 0;

if ($services) {
    foreach ($services as $service) {
        if ($service['service_status'] === 'active') {
            $active_services++;
        } elseif ($service['service_status'] === 'pending_approval') {
            $pending_services++;
        }
        $total_revenue += floatval($service['service_price']) * intval($service['total_bookings']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services - TourLink Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #1e293b; }

        .sidebar { position: fixed; left: 0; top: 0; width: 260px; height: 100vh; background: #1b4332; padding: 24px 0; overflow-y: auto; z-index: 100; }
        .sidebar-brand { padding: 0 24px 24px; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 24px; }
        .sidebar-brand a { color: white; text-decoration: none; font-size: 22px; font-weight: 700; }
        .sidebar-brand span { color: #d4a017; }
        .sidebar-brand small { display: block; color: rgba(255,255,255,0.6); font-size: 11px; margin-top: 4px; }
        .nav-section { padding: 0 16px; margin-bottom: 24px; }
        .nav-section-title { color: rgba(255,255,255,0.4); font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; padding: 0 8px; margin-bottom: 8px; }
        .nav-link { display: flex; align-items: center; gap: 12px; padding: 10px 12px; color: rgba(255,255,255,0.7); text-decoration: none; border-radius: 8px; font-size: 13px; font-weight: 500; transition: all 0.2s; margin-bottom: 2px; }
        .nav-link:hover { background: rgba(255,255,255,0.1); color: white; }
        .nav-link.active { background: rgba(255,255,255,0.15); color: white; }
        .nav-link i { width: 18px; font-size: 14px; }

        .main-content { margin-left: 260px; min-height: 100vh; }
        .top-bar { background: white; padding: 16px 32px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 50; }
        .page-title { font-size: 18px; font-weight: 600; color: #1e293b; }
        .user-menu { display: flex; align-items: center; gap: 12px; }
        .user-info { text-align: right; }
        .user-name { font-size: 13px; font-weight: 600; color: #1e293b; }
        .user-role { font-size: 11px; color: #64748b; }
        .user-avatar { width: 36px; height: 36px; background: #1b4332; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px; }

        .content-area { padding: 32px; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 32px; }
        .stat-card { background: white; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0; }
        .stat-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; font-size: 18px; }
        .stat-icon.blue { background: #dbeafe; color: #1e40af; }
        .stat-icon.green { background: #dcfce7; color: #166534; }
        .stat-icon.purple { background: #f3e8ff; color: #7c3aed; }
        .stat-icon.orange { background: #fed7aa; color: #c2410c; }
        .stat-value { font-size: 28px; font-weight: 700; color: #1e293b; margin-bottom: 4px; }
        .stat-label { font-size: 13px; color: #64748b; }

        .card { background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; }
        .card-header { padding: 20px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
        .card-title { font-size: 16px; font-weight: 600; color: #1e293b; }
        .card-body { padding: 24px; }

        .toolbar { display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap; }
        .search-box { flex: 1; min-width: 280px; position: relative; }
        .search-box input { width: 100%; padding: 10px 16px 10px 40px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; transition: all 0.2s; }
        .search-box input:focus { outline: none; border-color: #1b4332; }
        .search-box i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 14px; }
        .filter-select { padding: 10px 16px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; background: white; cursor: pointer; }

        .data-table { width: 100%; border-collapse: collapse; }
        .data-table thead th { text-align: left; padding: 12px; background: #f8fafc; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #e2e8f0; }
        .data-table tbody td { padding: 16px 12px; font-size: 13px; border-bottom: 1px solid #f1f5f9; }
        .data-table tbody tr:hover { background: #f8fafc; }

        .service-info { display: flex; align-items: center; gap: 12px; }
        .service-image { width: 50px; height: 50px; border-radius: 8px; object-fit: cover; background: #f1f5f9; }
        .service-details { flex: 1; }
        .service-title { font-weight: 600; color: #1e293b; display: block; margin-bottom: 2px; }
        .service-provider { font-size: 12px; color: #64748b; }

        .badge { padding: 5px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .badge-active { background: #dcfce7; color: #166534; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-inactive { background: #fee2e2; color: #991b1b; }

        .btn-action { width: 32px; height: 32px; border-radius: 6px; border: none; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s; font-size: 13px; margin-right: 4px; }
        .btn-view { background: #dbeafe; color: #1e40af; }
        .btn-view:hover { background: #bfdbfe; }
        .btn-approve { background: #dcfce7; color: #166534; }
        .btn-approve:hover { background: #bbf7d0; }

        @media (max-width: 1200px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .main-content { margin-left: 0; } .stats-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-brand">
            <a href="platform_dashboard.php">TourLink<span>.</span></a>
            <small>Administration</small>
        </div>
        <div class="nav-section">
            <div class="nav-section-title">Overview</div>
            <a href="platform_dashboard.php" class="nav-link"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="impact.php" class="nav-link"><i class="fas fa-chart-line"></i> Impact Metrics</a>
        </div>
        <div class="nav-section">
            <div class="nav-section-title">Management</div>
            <a href="users.php" class="nav-link"><i class="fas fa-users"></i> Users</a>
            <a href="manage_providers.php" class="nav-link"><i class="fas fa-store"></i> Providers</a>
            <a href="manage_bookings.php" class="nav-link"><i class="fas fa-calendar-check"></i> Bookings</a>
            <a href="services.php" class="nav-link active"><i class="fas fa-concierge-bell"></i> Services</a>
            <a href="manage_discounts.php" class="nav-link"><i class="fas fa-tags"></i> Discount Codes</a>
        </div>
        <div class="nav-section">
            <div class="nav-section-title">Content</div>
            <a href="manage_festivals.php" class="nav-link"><i class="fas fa-drum"></i> Festivals</a>
        </div>
        <div class="nav-section" style="margin-top: auto;">
            <a href="platform_logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="top-bar">
            <h1 class="page-title">Services</h1>
            <div class="user-menu">
                <div class="user-info">
                    <div class="user-name">System Admin</div>
                    <div class="user-role">Super Admin</div>
                </div>
                <div class="user-avatar">S</div>
            </div>
        </div>

        <div class="content-area">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fas fa-concierge-bell"></i></div>
                    <div class="stat-value"><?php echo $total_services; ?></div>
                    <div class="stat-label">Total Services</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-value"><?php echo $active_services; ?></div>
                    <div class="stat-label">Active Services</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple"><i class="fas fa-clock"></i></div>
                    <div class="stat-value"><?php echo $pending_services; ?></div>
                    <div class="stat-label">Pending Approval</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange"><i class="fas fa-money-bill-wave"></i></div>
                    <div class="stat-value">GH₵<?php echo number_format($total_revenue, 0); ?></div>
                    <div class="stat-label">Total Revenue</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">All Services</h2>
                </div>
                <div class="card-body">
                    <div class="toolbar">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInput" placeholder="Search services...">
                        </div>
                        <select class="filter-select" id="statusFilter">
                            <option value="all">All Status</option>
                            <option value="active">Active</option>
                            <option value="pending_approval">Pending</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th>Price</th>
                                <th>Category</th>
                                <th>Bookings</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="servicesTableBody">
                            <?php foreach ($services as $service): ?>
                            <tr data-status="<?php echo $service['service_status']; ?>">
                                <td>
                                    <div class="service-info">
                                        <img src="<?php echo !empty($service['service_images']) ? '../uploads/services/' . $service['service_id'] . '/' . json_decode($service['service_images'])[0] : '../assets/images/placeholder.jpg'; ?>" alt="Service" class="service-image">
                                        <div class="service-details">
                                            <span class="service-title"><?php echo htmlspecialchars($service['service_name']); ?></span>
                                            <span class="service-provider"><?php echo htmlspecialchars($service['business_name']); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><strong>GH₵<?php echo number_format($service['service_price'], 2); ?></strong></td>
                                <td><?php echo htmlspecialchars($service['category_name'] ?? 'N/A'); ?></td>
                                <td><?php echo $service['total_bookings']; ?></td>
                                <td>
                                    <?php if ($service['service_status'] === 'active'): ?>
                                        <span class="badge badge-active">Active</span>
                                    <?php elseif ($service['service_status'] === 'pending_approval'): ?>
                                        <span class="badge badge-pending">Pending</span>
                                    <?php else: ?>
                                        <span class="badge badge-inactive">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn-action btn-view" onclick="viewService(<?php echo $service['service_id']; ?>)" title="View"><i class="fas fa-eye"></i></button>
                                    <?php if ($service['service_status'] === 'pending_approval'): ?>
                                    <button class="btn-action btn-approve" onclick="approveService(<?php echo $service['service_id']; ?>)" title="Approve"><i class="fas fa-check"></i></button>
                                    <?php endif; ?>
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
        document.getElementById('searchInput').addEventListener('input', filterTable);
        document.getElementById('statusFilter').addEventListener('change', filterTable);

        function filterTable() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const rows = document.querySelectorAll('#servicesTableBody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const status = row.getAttribute('data-status');
                const matchesSearch = text.includes(searchTerm);
                const matchesStatus = statusFilter === 'all' || status === statusFilter;
                row.style.display = matchesSearch && matchesStatus ? '' : 'none';
            });
        }

        function viewService(serviceId) {
            window.location.href = '../view/single_service.php?service_id=' + serviceId;
        }

        function approveService(serviceId) {
            if (confirm('Approve this service?')) {
                alert('Service approval functionality coming soon for service ID: ' + serviceId);
            }
        }
    </script>
</body>
</html>
