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

// Get additional analytics data for visualizations
require_once '../settings/db_class.php';
$db = new db_connection();
$db->db_connect();

// Booking status breakdown
$status_breakdown = $db->db_fetch_all("SELECT booking_status, COUNT(*) as count FROM tl_bookings GROUP BY booking_status");
$status_breakdown_array = [];
if ($status_breakdown) {
    foreach ($status_breakdown as $row) {
        $status_breakdown_array[$row['booking_status']] = $row['count'];
    }
}
$status_breakdown = $status_breakdown_array;

// Service category performance
$category_performance = $db->db_fetch_all("
    SELECT sc.category_name, COUNT(s.service_id) as service_count, 
           COUNT(b.booking_id) as booking_count,
           COALESCE(SUM(b.total_amount), 0) as revenue
    FROM tl_service_categories sc
    LEFT JOIN tl_services s ON sc.category_id = s.category_id AND s.service_status = 'active'
    LEFT JOIN tl_bookings b ON s.service_id = b.service_id AND b.booking_status IN ('confirmed', 'completed')
    GROUP BY sc.category_id, sc.category_name
    ORDER BY booking_count DESC
    LIMIT 7
");
if (!$category_performance) {
    $category_performance = [];
}

// Regional revenue breakdown
$regional_revenue = $db->db_fetch_all("
    SELECT sp.region, 
           COUNT(DISTINCT sp.provider_id) as provider_count,
           COUNT(b.booking_id) as booking_count,
           COALESCE(SUM(b.total_amount), 0) as revenue
    FROM tl_service_providers sp
    LEFT JOIN tl_services s ON sp.provider_id = s.provider_id
    LEFT JOIN tl_bookings b ON s.service_id = b.service_id AND b.booking_status IN ('confirmed', 'completed')
    WHERE sp.verification_status = 'verified'
    GROUP BY sp.region
    ORDER BY revenue DESC
");
if (!$regional_revenue) {
    $regional_revenue = [];
}
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

        .chart-container-large {
            height: 300px;
        }

        /* Analytics Grid */
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
            margin-bottom: 32px;
        }

        .analytics-card {
            background: white;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .analytics-card-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e2e8f0;
        }

        .analytics-card-title {
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
        }

        .analytics-card-body {
            padding: 24px;
        }

        /* Mini Stat Cards */
        .mini-stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .mini-stat-card {
            background: white;
            border-radius: 10px;
            padding: 16px;
            border: 1px solid #e2e8f0;
            text-align: center;
        }

        .mini-stat-value {
            font-size: 20px;
            font-weight: 700;
            color: #1b4332;
            margin-bottom: 4px;
        }

        .mini-stat-label {
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .mini-stat-trend {
            font-size: 10px;
            color: #22c55e;
            margin-top: 4px;
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
            .analytics-grid {
                grid-template-columns: 1fr;
            }
            .mini-stat-grid {
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
            .stats-grid, .impact-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php
    // Set current page for sidebar highlighting
    $current_page = 'dashboard';
    // Include reusable admin sidebar component
    include '../includes/admin_sidebar.php';
    ?>

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

            <!-- Analytics Charts Row -->
            <div class="analytics-grid">
                <!-- Booking Trends Chart -->
                <div class="analytics-card">
                    <div class="analytics-card-header">
                        <div>
                            <h3 class="analytics-card-title">Booking Trends (Last 6 Months)</h3>
                            <p style="font-size: 12px; color: #64748b; margin-top: 4px;">
                                Total: <?php echo array_sum(array_column($booking_trends, 'bookings')); ?> bookings
                            </p>
                        </div>
                    </div>
                    <div class="analytics-card-body">
                        <div class="chart-container-large">
                            <canvas id="bookingChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Revenue Trends Chart -->
                <div class="analytics-card">
                    <div class="analytics-card-header">
                        <div>
                            <h3 class="analytics-card-title">Revenue Trends (Last 6 Months)</h3>
                            <p style="font-size: 12px; color: #64748b; margin-top: 4px;">
                                Total: GHS <?php echo number_format(array_sum(array_column($booking_trends, 'revenue')), 2); ?>
                            </p>
                        </div>
                    </div>
                    <div class="analytics-card-body">
                        <div class="chart-container-large">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Analytics Row -->
            <div class="analytics-grid">
                <!-- Booking Status Breakdown -->
                <div class="analytics-card">
                    <div class="analytics-card-header">
                        <div>
                            <h3 class="analytics-card-title">Booking Status Distribution</h3>
                            <p style="font-size: 12px; color: #64748b; margin-top: 4px;">
                                Breakdown of all bookings by current status
                            </p>
                        </div>
                    </div>
                    <div class="analytics-card-body">
                        <div class="chart-container">
                            <canvas id="statusChart"></canvas>
                        </div>
                        <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #e2e8f0;">
                            <?php 
                            $totalBookings = array_sum($status_breakdown);
                            foreach ($status_breakdown as $status => $count): 
                                $percent = $totalBookings > 0 ? round(($count / $totalBookings) * 100, 1) : 0;
                            ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; font-size: 13px;">
                                <span style="color: #64748b;">
                                    <strong style="color: #1e293b;"><?php echo ucfirst($status); ?>:</strong> 
                                    <?php echo $count; ?> bookings
                                </span>
                                <span style="color: #1b4332; font-weight: 600;"><?php echo $percent; ?>%</span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Regional Distribution -->
                <div class="analytics-card">
                    <div class="analytics-card-header">
                        <div>
                            <h3 class="analytics-card-title">Regional Provider Distribution</h3>
                            <p style="font-size: 12px; color: #64748b; margin-top: 4px;">
                                Verified providers across TourLink's operational regions
                            </p>
                        </div>
                    </div>
                    <div class="analytics-card-body">
                        <div class="chart-container">
                            <canvas id="regionalChart"></canvas>
                        </div>
                        <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #e2e8f0;">
                            <?php 
                            $totalProviders = array_sum(array_column($regional_stats, 'provider_count'));
                            foreach ($regional_stats as $region): 
                                $percent = $totalProviders > 0 ? round(($region['provider_count'] / $totalProviders) * 100, 1) : 0;
                            ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; font-size: 13px;">
                                <span style="color: #64748b;">
                                    <strong style="color: #1e293b;"><?php echo htmlspecialchars($region['region']); ?>:</strong> 
                                    <?php echo $region['provider_count']; ?> providers
                                </span>
                                <span style="color: #1b4332; font-weight: 600;"><?php echo $percent; ?>%</span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Service Category Performance -->
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Service Category Performance</h3>
                        <p style="font-size: 12px; color: #64748b; margin-top: 4px;">
                            Booking activity by service category - hover over bars for detailed metrics
                        </p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container-large">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Regional Revenue Breakdown -->
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Regional Revenue Breakdown</h3>
                        <p style="font-size: 12px; color: #64748b; margin-top: 4px;">
                            Revenue generated by region - hover over bars for provider and booking details
                        </p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container-large">
                        <canvas id="regionalRevenueChart"></canvas>
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
        const bookingCtx = document.getElementById('bookingChart').getContext('2d');
        const bookingData = <?php echo json_encode($booking_trends); ?>;

        const labels = bookingData.map(item => {
            const [year, month] = item.month.split('-');
            const date = new Date(year, month - 1);
            return date.toLocaleDateString('en-US', { month: 'short', year: '2-digit' });
        });

        const bookings = bookingData.map(item => item.bookings);

        new Chart(bookingCtx, {
            type: 'line',
            data: {
                labels: labels.length ? labels : ['No data'],
                datasets: [{
                    label: 'Bookings',
                    data: bookings.length ? bookings : [0],
                    borderColor: '#1b4332',
                    backgroundColor: 'rgba(27, 67, 50, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Bookings: ' + context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        },
                        title: {
                            display: true,
                            text: 'Number of Bookings'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    }
                }
            }
        });

        // Revenue Trends Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenue = bookingData.map(item => parseFloat(item.revenue || 0));

        new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: labels.length ? labels : ['No data'],
                datasets: [{
                    label: 'Revenue (GHS)',
                    data: revenue.length ? revenue : [0],
                    backgroundColor: 'rgba(27, 67, 50, 0.8)',
                    borderColor: '#1b4332',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Revenue: GHS ' + context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'GHS ' + value.toLocaleString();
                            }
                        },
                        title: {
                            display: true,
                            text: 'Revenue (GHS)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    }
                }
            }
        });

        // Booking Status Breakdown Pie Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusData = <?php echo json_encode($status_breakdown); ?>;
        
        const statusLabels = Object.keys(statusData);
        const statusCounts = Object.values(statusData);
        const totalStatus = statusCounts.reduce((a, b) => a + b, 0);
        const statusColors = {
            'pending': '#fef3c7',
            'confirmed': '#dbeafe',
            'completed': '#dcfce7',
            'cancelled': '#fee2e2',
            'in_progress': '#f3e8ff'
        };

        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusLabels.map(s => s.charAt(0).toUpperCase() + s.slice(1)),
                datasets: [{
                    data: statusCounts,
                    backgroundColor: statusLabels.map(s => statusColors[s] || '#e2e8f0'),
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 12,
                            font: {
                                size: 12
                            },
                            generateLabels: function(chart) {
                                const data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    return data.labels.map((label, i) => {
                                        const value = data.datasets[0].data[i];
                                        const percent = totalStatus > 0 ? ((value / totalStatus) * 100).toFixed(1) : 0;
                                        return {
                                            text: label + ': ' + value + ' (' + percent + '%)',
                                            fillStyle: data.datasets[0].backgroundColor[i],
                                            strokeStyle: data.datasets[0].borderColor,
                                            lineWidth: data.datasets[0].borderWidth,
                                            hidden: false,
                                            index: i
                                        };
                                    });
                                }
                                return [];
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const percent = totalStatus > 0 ? ((value / totalStatus) * 100).toFixed(1) : 0;
                                return label + ': ' + value + ' bookings (' + percent + '% of total)';
                            }
                        }
                    },
                }
            }
        });

        // Regional Provider Distribution Chart
        const regionalCtx = document.getElementById('regionalChart').getContext('2d');
        const regionalData = <?php echo json_encode($regional_stats); ?>;
        
        const regionalLabels = regionalData.map(r => r.region);
        const regionalCounts = regionalData.map(r => parseInt(r.provider_count));
        const totalRegional = regionalCounts.reduce((a, b) => a + b, 0);
        const regionalColors = ['#1b4332', '#2d6a4f', '#40916c', '#52b788', '#74c69d'];

        new Chart(regionalCtx, {
            type: 'pie',
            data: {
                labels: regionalLabels,
                datasets: [{
                    data: regionalCounts,
                    backgroundColor: regionalColors.slice(0, regionalLabels.length),
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 12,
                            font: {
                                size: 12
                            },
                            generateLabels: function(chart) {
                                const data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    return data.labels.map((label, i) => {
                                        const value = data.datasets[0].data[i];
                                        const percent = totalRegional > 0 ? ((value / totalRegional) * 100).toFixed(1) : 0;
                                        return {
                                            text: label + ': ' + value + ' (' + percent + '%)',
                                            fillStyle: data.datasets[0].backgroundColor[i],
                                            strokeStyle: data.datasets[0].borderColor,
                                            lineWidth: data.datasets[0].borderWidth,
                                            hidden: false,
                                            index: i
                                        };
                                    });
                                }
                                return [];
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const percent = totalRegional > 0 ? ((value / totalRegional) * 100).toFixed(1) : 0;
                                return label + ': ' + value + ' providers (' + percent + '% of total)';
                            }
                        }
                    },
                }
            }
        });

        // Service Category Performance Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        const categoryData = <?php echo json_encode($category_performance); ?>;
        
        const categoryLabels = categoryData.map(c => c.category_name);
        const categoryBookings = categoryData.map(c => parseInt(c.booking_count || 0));
        const categoryServices = categoryData.map(c => parseInt(c.service_count || 0));
        const categoryRevenue = categoryData.map(c => parseFloat(c.revenue || 0));

        new Chart(categoryCtx, {
            type: 'bar',
            data: {
                labels: categoryLabels,
                datasets: [{
                    label: 'Bookings',
                    data: categoryBookings,
                    backgroundColor: 'rgba(27, 67, 50, 0.8)',
                    borderColor: '#1b4332',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            afterLabel: function(context) {
                                const index = context.dataIndex;
                                return [
                                    'Services: ' + categoryServices[index],
                                    'Revenue: GHS ' + categoryRevenue[index].toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})
                                ];
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            callback: function(value) {
                                return value + ' bookings';
                            }
                        },
                        title: {
                            display: true,
                            text: 'Number of Bookings'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Service Categories'
                        }
                    }
                }
            }
        });

        // Regional Revenue Breakdown Chart
        const regionalRevenueCtx = document.getElementById('regionalRevenueChart').getContext('2d');
        const regionalRevenueData = <?php echo json_encode($regional_revenue); ?>;
        
        const regionalRevenueLabels = regionalRevenueData.map(r => r.region);
        const regionalRevenueAmounts = regionalRevenueData.map(r => parseFloat(r.revenue || 0));
        const regionalBookingCounts = regionalRevenueData.map(r => parseInt(r.booking_count || 0));
        const regionalProviderCounts = regionalRevenueData.map(r => parseInt(r.provider_count || 0));
        const totalRevenue = regionalRevenueAmounts.reduce((a, b) => a + b, 0);

        new Chart(regionalRevenueCtx, {
            type: 'bar',
            data: {
                labels: regionalRevenueLabels,
                datasets: [{
                    label: 'Revenue (GHS)',
                    data: regionalRevenueAmounts,
                    backgroundColor: 'rgba(27, 67, 50, 0.8)',
                    borderColor: '#1b4332',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            afterLabel: function(context) {
                                const index = context.dataIndex;
                                const percent = totalRevenue > 0 ? ((regionalRevenueAmounts[index] / totalRevenue) * 100).toFixed(1) : 0;
                                return [
                                    'Providers: ' + regionalProviderCounts[index],
                                    'Bookings: ' + regionalBookingCounts[index],
                                    'Share: ' + percent + '% of total revenue'
                                ];
                            },
                            label: function(context) {
                                return 'Revenue: GHS ' + context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'GHS ' + value.toLocaleString();
                            }
                        },
                        title: {
                            display: true,
                            text: 'Revenue (GHS)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Regions'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
