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
    <link href="../css/admin_dashboard.css" rel="stylesheet">
    <link href="../css/platform_dashboard.css" rel="stylesheet">
    <script>
        // Pass PHP variables to JavaScript
        window.dashboardData = {
            bookingTrends: <?php echo json_encode($booking_trends); ?>,
            statusBreakdown: <?php echo json_encode($status_breakdown); ?>,
            regionalStats: <?php echo json_encode($regional_stats); ?>,
            categoryPerformance: <?php echo json_encode($category_performance); ?>,
            regionalRevenue: <?php echo json_encode($regional_revenue); ?>
        };
    </script>
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

    <script src="../js/admin_dashboard.js"></script>
    <script>
        // Initialize charts when dashboard data is available
        if (window.dashboardData && window.dashboardData.bookingTrends) {
            const bookingData = window.dashboardData.bookingTrends;
            const bookingCtx = document.getElementById('bookingChart').getContext('2d');
            
            const labels = bookingData.map(item => {
                const [year, month] = item.month.split('-');
            const date = new Date(year, month - 1);
            return date.toLocaleDateString('en-US', { month: 'short', year: '2-digit' });
        });

                    new Chart(bookingCtx.getContext('2d'), {
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
                    const revenueCtx = document.getElementById('revenueChart');
                    if (revenueCtx) {
                        const revenue = bookingData.map(item => parseFloat(item.revenue || 0));

                        new Chart(revenueCtx.getContext('2d'), {
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
                    const statusCtx = document.getElementById('statusChart');
                    if (statusCtx) {
                        const statusData = window.dashboardData.statusBreakdown;
        
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

                        new Chart(statusCtx.getContext('2d'), {
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
                    const regionalCtx = document.getElementById('regionalChart');
                    if (regionalCtx) {
                        const regionalData = window.dashboardData.regionalStats;
        
                        const regionalLabels = regionalData.map(r => r.region);
                        const regionalCounts = regionalData.map(r => parseInt(r.provider_count));
                        const totalRegional = regionalCounts.reduce((a, b) => a + b, 0);
                        const regionalColors = ['#1b4332', '#2d6a4f', '#40916c', '#52b788', '#74c69d'];

                        new Chart(regionalCtx.getContext('2d'), {
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
                    const categoryCtx = document.getElementById('categoryChart');
                    if (categoryCtx) {
                        const categoryData = window.dashboardData.categoryPerformance;
        
                        const categoryLabels = categoryData.map(c => c.category_name);
                        const categoryBookings = categoryData.map(c => parseInt(c.booking_count || 0));
                        const categoryServices = categoryData.map(c => parseInt(c.service_count || 0));
                        const categoryRevenue = categoryData.map(c => parseFloat(c.revenue || 0));

                        new Chart(categoryCtx.getContext('2d'), {
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
                    }

                    // Regional Revenue Breakdown Chart
                    const regionalRevenueCtx = document.getElementById('regionalRevenueChart');
                    if (regionalRevenueCtx) {
                        const regionalRevenueData = window.dashboardData.regionalRevenue;
        
                        const regionalRevenueLabels = regionalRevenueData.map(r => r.region);
                        const regionalRevenueAmounts = regionalRevenueData.map(r => parseFloat(r.revenue || 0));
                        const regionalBookingCounts = regionalRevenueData.map(r => parseInt(r.booking_count || 0));
                        const regionalProviderCounts = regionalRevenueData.map(r => parseInt(r.provider_count || 0));
                        const totalRevenue = regionalRevenueAmounts.reduce((a, b) => a + b, 0);

                        new Chart(regionalRevenueCtx.getContext('2d'), {
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
                    });
                }
            }
        }
    });
    </script>
</body>
</html>
