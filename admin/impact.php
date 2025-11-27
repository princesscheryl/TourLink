<?php
require_once 'includes_platform/auth_check.php';
require_privilege('view_dashboard');

// Sample impact metrics data
// In production, this would be calculated from database queries
$impact_metrics = [
    'jobs_created' => 127,
    'provider_income_total' => 456780,
    'total_bookings' => 1843,
    'active_tourists' => 2156,
    'avg_provider_income' => 6420,
    'platform_revenue' => 68517,
    'success_rate' => 94.2,
    'repeat_booking_rate' => 37.8
];

// Regional impact breakdown
$regional_impact = [
    ['region' => 'Greater Accra', 'providers' => 45, 'bookings' => 687, 'revenue' => 187650, 'growth' => 12.5],
    ['region' => 'Ashanti', 'providers' => 38, 'bookings' => 512, 'revenue' => 142340, 'growth' => 18.3],
    ['region' => 'Central', 'providers' => 22, 'bookings' => 298, 'revenue' => 78920, 'growth' => 8.7],
    ['region' => 'Eastern', 'providers' => 18, 'bookings' => 186, 'revenue' => 52430, 'growth' => 15.2],
    ['region' => 'Western', 'providers' => 15, 'bookings' => 124, 'revenue' => 34180, 'growth' => 6.4],
    ['region' => 'Volta', 'providers' => 12, 'bookings' => 98, 'revenue' => 28450, 'growth' => 22.1]
];

// Monthly growth data for last 6 months
$monthly_growth = [
    ['month' => 'Jun', 'bookings' => 245, 'revenue' => 68420, 'new_providers' => 8],
    ['month' => 'Jul', 'bookings' => 278, 'revenue' => 74850, 'new_providers' => 12],
    ['month' => 'Aug', 'bookings' => 312, 'revenue' => 89230, 'new_providers' => 15],
    ['month' => 'Sep', 'bookings' => 289, 'revenue' => 81340, 'new_providers' => 9],
    ['month' => 'Oct', 'bookings' => 334, 'revenue' => 95680, 'new_providers' => 18],
    ['month' => 'Nov', 'bookings' => 385, 'revenue' => 107260, 'new_providers' => 21]
];

// Service category performance
$category_performance = [
    ['category' => 'Tour Guides', 'providers' => 52, 'bookings' => 734, 'avg_rating' => 4.7],
    ['category' => 'Drivers', 'providers' => 38, 'bookings' => 521, 'avg_rating' => 4.5],
    ['category' => 'Artisans', 'providers' => 28, 'bookings' => 312, 'avg_rating' => 4.8],
    ['category' => 'Cultural Guides', 'providers' => 15, 'bookings' => 187, 'avg_rating' => 4.9],
    ['category' => 'Accommodation', 'providers' => 12, 'bookings' => 89, 'avg_rating' => 4.6]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impact Metrics - TourLink Admin</title>
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

        /* Impact metrics grid */
        .impact-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 32px; }
        .impact-card { background: white; border-radius: 12px; padding: 24px; border: 1px solid #e2e8f0; position: relative; overflow: hidden; }
        .impact-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, #1b4332, #2d6a4f); }
        .impact-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px; font-size: 20px; }
        .impact-icon.blue { background: #dbeafe; color: #1e40af; }
        .impact-icon.green { background: #dcfce7; color: #166534; }
        .impact-icon.purple { background: #f3e8ff; color: #7c3aed; }
        .impact-icon.orange { background: #fed7aa; color: #c2410c; }
        .impact-value { font-size: 32px; font-weight: 700; color: #1e293b; margin-bottom: 4px; }
        .impact-label { font-size: 13px; color: #64748b; margin-bottom: 8px; }
        .impact-trend { font-size: 12px; font-weight: 600; color: #166534; display: flex; align-items: center; gap: 4px; }
        .impact-trend.positive { color: #166534; }
        .impact-trend.neutral { color: #64748b; }

        /* Section styling */
        .section { background: white; border-radius: 12px; padding: 24px; border: 1px solid #e2e8f0; margin-bottom: 24px; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #e2e8f0; }
        .section-title { font-size: 16px; font-weight: 600; color: #1e293b; }
        .section-subtitle { font-size: 12px; color: #64748b; margin-top: 4px; }

        /* Tables */
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table thead th { text-align: left; padding: 12px; background: #f8fafc; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #e2e8f0; }
        .data-table tbody td { padding: 16px 12px; font-size: 13px; border-bottom: 1px solid #f1f5f9; }
        .data-table tbody tr:hover { background: #f8fafc; }

        /* Growth chart placeholder */
        .growth-chart { height: 200px; display: flex; align-items: flex-end; gap: 12px; padding: 20px 0; }
        .chart-bar { flex: 1; background: linear-gradient(to top, #1b4332, #2d6a4f); border-radius: 6px 6px 0 0; position: relative; min-height: 20px; display: flex; align-items: flex-end; justify-content: center; padding-bottom: 8px; }
        .chart-label { font-size: 11px; color: #64748b; text-align: center; margin-top: 8px; }
        .chart-value { font-size: 10px; font-weight: 600; color: white; }

        /* Metric boxes */
        .metric-box { background: #f8fafc; border-radius: 8px; padding: 16px; margin-bottom: 12px; }
        .metric-row { display: flex; justify-content: space-between; align-items: center; }
        .metric-name { font-size: 13px; color: #64748b; }
        .metric-value { font-size: 18px; font-weight: 700; color: #1e293b; }
        .metric-bar { height: 6px; background: #e2e8f0; border-radius: 3px; margin-top: 8px; overflow: hidden; }
        .metric-bar-fill { height: 100%; background: linear-gradient(90deg, #1b4332, #2d6a4f); border-radius: 3px; transition: width 0.3s ease; }

        /* Badge styling */
        .rating-badge { background: #fef3c7; color: #92400e; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
        .growth-badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
        .growth-badge.positive { background: #dcfce7; color: #166534; }
        .growth-badge.moderate { background: #fef3c7; color: #92400e; }

        @media (max-width: 1400px) { .impact-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; }
            .impact-grid { grid-template-columns: 1fr; }
            .growth-chart { height: 150px; }
        }
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
            <a href="impact.php" class="nav-link active"><i class="fas fa-chart-line"></i> Impact Metrics</a>
        </div>
        <div class="nav-section">
            <div class="nav-section-title">Management</div>
            <a href="users.php" class="nav-link"><i class="fas fa-users"></i> Users</a>
            <a href="manage_providers.php" class="nav-link"><i class="fas fa-store"></i> Providers</a>
            <a href="manage_bookings.php" class="nav-link"><i class="fas fa-calendar-check"></i> Bookings</a>
            <a href="services.php" class="nav-link"><i class="fas fa-concierge-bell"></i> Services</a>
            <a href="manage_discounts.php" class="nav-link"><i class="fas fa-tags"></i> Discount Codes</a>
        </div>
        <div class="nav-section">
            <div class="nav-section-title">Content</div>
            <a href="manage_festivals.php" class="nav-link"><i class="fas fa-drum"></i> Festivals</a>
            <a href="regions.php" class="nav-link"><i class="fas fa-map-marked-alt"></i> Regions</a>
            <a href="stories.php" class="nav-link"><i class="fas fa-book-open"></i> Success Stories</a>
        </div>
        <div class="nav-section" style="margin-top: auto;">
            <a href="platform_logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="top-bar">
            <h1 class="page-title">Impact Metrics</h1>
            <div class="user-menu">
                <div class="user-info">
                    <div class="user-name">System Admin</div>
                    <div class="user-role">Super Admin</div>
                </div>
                <div class="user-avatar">S</div>
            </div>
        </div>

        <div class="content-area">
            <!-- Key Impact Metrics -->
            <div class="impact-grid">
                <div class="impact-card">
                    <div class="impact-icon blue">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="impact-value"><?php echo number_format($impact_metrics['jobs_created']); ?></div>
                    <div class="impact-label">Jobs Created</div>
                    <div class="impact-trend positive">
                        <i class="fas fa-arrow-up"></i> 23% this quarter
                    </div>
                </div>
                <div class="impact-card">
                    <div class="impact-icon green">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="impact-value">GH₵<?php echo number_format($impact_metrics['provider_income_total']); ?></div>
                    <div class="impact-label">Provider Income Generated</div>
                    <div class="impact-trend positive">
                        <i class="fas fa-arrow-up"></i> 18% from last month
                    </div>
                </div>
                <div class="impact-card">
                    <div class="impact-icon purple">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="impact-value"><?php echo number_format($impact_metrics['total_bookings']); ?></div>
                    <div class="impact-label">Total Bookings Completed</div>
                    <div class="impact-trend positive">
                        <i class="fas fa-arrow-up"></i> 15% growth rate
                    </div>
                </div>
                <div class="impact-card">
                    <div class="impact-icon orange">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="impact-value"><?php echo number_format($impact_metrics['active_tourists']); ?></div>
                    <div class="impact-label">Active Tourists</div>
                    <div class="impact-trend positive">
                        <i class="fas fa-arrow-up"></i> 27% engagement rate
                    </div>
                </div>
            </div>

            <!-- Monthly Growth Trends -->
            <div class="section">
                <div class="section-header">
                    <div>
                        <div class="section-title">Monthly Growth Trends</div>
                        <div class="section-subtitle">Booking and revenue trends over the last 6 months</div>
                    </div>
                </div>
                <div class="growth-chart">
                    <?php foreach ($monthly_growth as $month):
                        $maxBookings = 400;
                        $heightPercent = ($month['bookings'] / $maxBookings) * 100;
                    ?>
                    <div style="flex: 1; text-align: center;">
                        <div class="chart-bar" style="height: <?php echo $heightPercent; ?>%;">
                            <span class="chart-value"><?php echo $month['bookings']; ?></span>
                        </div>
                        <div class="chart-label"><?php echo $month['month']; ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                <!-- Regional Impact -->
                <div class="section">
                    <div class="section-header">
                        <div>
                            <div class="section-title">Regional Impact Breakdown</div>
                            <div class="section-subtitle">Performance across Ghana's regions</div>
                        </div>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Region</th>
                                <th>Providers</th>
                                <th>Bookings</th>
                                <th>Growth</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($regional_impact as $region): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($region['region']); ?></strong></td>
                                <td><?php echo $region['providers']; ?></td>
                                <td><?php echo number_format($region['bookings']); ?></td>
                                <td>
                                    <span class="growth-badge <?php echo $region['growth'] > 15 ? 'positive' : 'moderate'; ?>">
                                        <i class="fas fa-arrow-up"></i> <?php echo number_format($region['growth'], 1); ?>%
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Category Performance -->
                <div class="section">
                    <div class="section-header">
                        <div>
                            <div class="section-title">Service Category Performance</div>
                            <div class="section-subtitle">Top performing service categories</div>
                        </div>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Providers</th>
                                <th>Bookings</th>
                                <th>Rating</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($category_performance as $category): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($category['category']); ?></strong></td>
                                <td><?php echo $category['providers']; ?></td>
                                <td><?php echo number_format($category['bookings']); ?></td>
                                <td>
                                    <span class="rating-badge">
                                        <i class="fas fa-star"></i> <?php echo number_format($category['avg_rating'], 1); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Economic Impact Metrics -->
            <div class="section">
                <div class="section-header">
                    <div>
                        <div class="section-title">Economic Impact Metrics</div>
                        <div class="section-subtitle">Platform contribution to local economy</div>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                    <div class="metric-box">
                        <div class="metric-row">
                            <span class="metric-name">Average Provider Income</span>
                            <span class="metric-value">GH₵<?php echo number_format($impact_metrics['avg_provider_income']); ?></span>
                        </div>
                        <div class="metric-bar">
                            <div class="metric-bar-fill" style="width: 75%;"></div>
                        </div>
                    </div>
                    <div class="metric-box">
                        <div class="metric-row">
                            <span class="metric-name">Platform Revenue</span>
                            <span class="metric-value">GH₵<?php echo number_format($impact_metrics['platform_revenue']); ?></span>
                        </div>
                        <div class="metric-bar">
                            <div class="metric-bar-fill" style="width: 85%;"></div>
                        </div>
                    </div>
                    <div class="metric-box">
                        <div class="metric-row">
                            <span class="metric-name">Success Rate</span>
                            <span class="metric-value"><?php echo number_format($impact_metrics['success_rate'], 1); ?>%</span>
                        </div>
                        <div class="metric-bar">
                            <div class="metric-bar-fill" style="width: <?php echo $impact_metrics['success_rate']; ?>%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
