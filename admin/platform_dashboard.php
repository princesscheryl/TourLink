<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes_platform/auth_check.php';
require_once '../classes/admin_class.php';

// Check dashboard access
require_privilege('view_dashboard');

$admin = new Admin();
$stats = $admin->get_platform_stats();
$impact = $admin->get_impact_metrics();
$regional_stats = $admin->get_regional_stats();
$recent_bookings = $admin->get_recent_bookings(5);
$booking_trends = $admin->get_booking_trends();

// Calculate north star metric growth
$north_star = $stats['total_revenue'] ?? 0;
$monthly_growth = $impact['growth_rate'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - TourLink Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .admin-avatar {
            width: 36px;
            height: 36px;
            background: #1b4332;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 13px;
        }

        .admin-info {
            text-align: right;
        }

        .admin-name {
            font-size: 13px;
            font-weight: 600;
            color: #1e293b;
        }

        .admin-role {
            font-size: 11px;
            color: #64748b;
        }

        /* Dashboard Content */
        .dashboard-content {
            padding: 32px;
        }

        /* North Star Metric */
        .north-star-card {
            background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%);
            border-radius: 16px;
            padding: 32px;
            color: white;
            margin-bottom: 32px;
        }

        .north-star-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.8;
            margin-bottom: 8px;
        }

        .north-star-value {
            font-size: 48px;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 8px;
        }

        .north-star-description {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 16px;
        }

        .north-star-growth {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.2);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
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
            padding: 24px;
            border: 1px solid #e2e8f0;
        }

        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            margin-bottom: 16px;
        }

        .stat-icon.blue { background: #eff6ff; color: #3b82f6; }
        .stat-icon.green { background: #f0fdf4; color: #22c55e; }
        .stat-icon.amber { background: #fffbeb; color: #f59e0b; }
        .stat-icon.purple { background: #faf5ff; color: #a855f7; }

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

        /* Impact Section */
        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title i {
            color: #d4a017;
        }

        .impact-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 32px;
        }

        .impact-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            border: 1px solid #e2e8f0;
            text-align: center;
        }

        .impact-value {
            font-size: 32px;
            font-weight: 700;
            color: #1b4332;
            margin-bottom: 8px;
        }

        .impact-label {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 12px;
        }

        .impact-badge {
            display: inline-block;
            background: #f0fdf4;
            color: #166534;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }

        /* Content Row */
        .content-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
            margin-bottom: 32px;
        }

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
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
        }

        .card-body {
            padding: 24px;
        }

        /* Table */
        .data-table {
            width: 100%;
        }

        .data-table th {
            text-align: left;
            padding: 12px 0;
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e2e8f0;
        }

        .data-table td {
            padding: 14px 0;
            font-size: 13px;
            border-bottom: 1px solid #f1f5f9;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }

        .status-badge.pending { background: #fef3c7; color: #92400e; }
        .status-badge.confirmed { background: #dbeafe; color: #1e40af; }
        .status-badge.completed { background: #dcfce7; color: #166534; }
        .status-badge.cancelled { background: #fee2e2; color: #991b1b; }

        /* Regional Stats */
        .region-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .region-item:last-child {
            border-bottom: none;
        }

        .region-name {
            font-size: 13px;
            font-weight: 500;
            color: #1e293b;
        }

        .region-count {
            font-size: 13px;
            font-weight: 600;
            color: #1b4332;
        }

        /* Chart Container */
        .chart-container {
            height: 250px;
        }

        /* Footer */
        .dashboard-footer {
            padding: 24px 32px;
            border-top: 1px solid #e2e8f0;
            background: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .footer-text {
            font-size: 12px;
            color: #94a3b8;
        }

        .footer-brand {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: #64748b;
        }

        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .impact-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .content-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .main-content {
                margin-left: 0;
            }
            .stats-grid, .impact-grid {
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
            <a href="platform_dashboard.php" class="nav-link active">
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
            <h1 class="page-title">Dashboard</h1>
            <div class="admin-profile">
                <div class="admin-info">
                    <div class="admin-name"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></div>
                    <div class="admin-role"><?php echo ucfirst($_SESSION['admin_role']); ?></div>
                </div>
                <div class="admin-avatar">
                    <?php echo strtoupper(substr($_SESSION['admin_name'], 0, 1)); ?>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="dashboard-content">
            <!-- North Star Metric -->
            <div class="north-star-card">
                <div class="north-star-label">North Star Metric</div>
                <div class="north-star-value">GHS <?php echo number_format($north_star, 2); ?></div>
                <div class="north-star-description">Total Revenue Generated for Local Communities</div>
                <div class="north-star-growth">
                    <i class="fas fa-arrow-<?php echo $monthly_growth >= 0 ? 'up' : 'down'; ?>"></i>
                    <?php echo abs($monthly_growth); ?>% from last month
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($stats['total_bookings']); ?></div>
                    <div class="stat-label">Total Bookings</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-store"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($stats['total_providers']); ?></div>
                    <div class="stat-label">Active Providers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon amber">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($stats['total_tourists']); ?></div>
                    <div class="stat-label">Registered Tourists</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple">
                        <i class="fas fa-concierge-bell"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($stats['total_services']); ?></div>
                    <div class="stat-label">Active Services</div>
                </div>
            </div>

            <!-- Impact Metrics Section -->
            <h2 class="section-title">
                <i class="fas fa-seedling"></i>
                ADF Impact Metrics
            </h2>
            <div class="impact-grid">
                <div class="impact-card">
                    <div class="impact-value"><?php echo number_format($impact['jobs_supported']); ?></div>
                    <div class="impact-label">Jobs Supported</div>
                    <span class="impact-badge">Economic Impact</span>
                </div>
                <div class="impact-card">
                    <div class="impact-value">GHS <?php echo number_format($impact['provider_income'], 0); ?></div>
                    <div class="impact-label">Provider Income Generated</div>
                    <span class="impact-badge">Direct to Communities</span>
                </div>
                <div class="impact-card">
                    <div class="impact-value"><?php echo number_format($impact['communities_reached']); ?></div>
                    <div class="impact-label">Regions Reached</div>
                    <span class="impact-badge">Geographic Spread</span>
                </div>
            </div>

            <!-- Content Row -->
            <div class="content-row">
                <!-- Recent Bookings -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Bookings</h3>
                        <a href="bookings.php" style="font-size: 12px; color: #1b4332; text-decoration: none;">View All</a>
                    </div>
                    <div class="card-body">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th>Tourist</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recent_bookings): ?>
                                    <?php foreach ($recent_bookings as $booking): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars(substr($booking['service_title'], 0, 25)); ?>...</td>
                                            <td><?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name'][0] . '.'); ?></td>
                                            <td>GHS <?php echo number_format($booking['total_amount'], 2); ?></td>
                                            <td>
                                                <span class="status-badge <?php echo $booking['booking_status']; ?>">
                                                    <?php echo ucfirst($booking['booking_status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" style="text-align: center; color: #94a3b8;">No bookings yet</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Regional Distribution -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Providers by Region</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($regional_stats): ?>
                            <?php foreach ($regional_stats as $region): ?>
                                <div class="region-item">
                                    <span class="region-name"><?php echo htmlspecialchars($region['region']); ?></span>
                                    <span class="region-count"><?php echo $region['provider_count']; ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="text-align: center; color: #94a3b8; font-size: 13px;">No regional data yet</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Booking Trends Chart -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Booking Trends (Last 6 Months)</h3>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="bookingChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="dashboard-footer">
            <span class="footer-text">&copy; 2025 TourLink. All rights reserved.</span>
            <div class="footer-brand">
                <i class="fas fa-globe-africa"></i>
                Proudly Ghanaian
            </div>
        </div>
    </main>

    <script>
        // Booking Trends Chart
        const ctx = document.getElementById('bookingChart').getContext('2d');
        const bookingData = <?php echo json_encode($booking_trends); ?>;

        const labels = bookingData.map(item => {
            const [year, month] = item.month.split('-');
            const date = new Date(year, month - 1);
            return date.toLocaleDateString('en-US', { month: 'short', year: '2-digit' });
        });

        const bookings = bookingData.map(item => item.bookings);
        const revenue = bookingData.map(item => parseFloat(item.revenue));

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels.length ? labels : ['No data'],
                datasets: [{
                    label: 'Bookings',
                    data: bookings.length ? bookings : [0],
                    borderColor: '#1b4332',
                    backgroundColor: 'rgba(27, 67, 50, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
