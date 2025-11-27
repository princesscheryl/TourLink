<?php
require_once 'includes_platform/auth_check.php';
require_privilege('manage_content');

// Ghana regions data
$regions = [
    ['name' => 'Greater Accra', 'providers' => 15, 'services' => 42, 'active' => true],
    ['name' => 'Ashanti', 'providers' => 12, 'services' => 35, 'active' => true],
    ['name' => 'Central', 'providers' => 8, 'services' => 24, 'active' => true],
    ['name' => 'Northern', 'providers' => 6, 'services' => 18, 'active' => true],
    ['name' => 'Western', 'providers' => 7, 'services' => 21, 'active' => true],
    ['name' => 'Eastern', 'providers' => 9, 'services' => 27, 'active' => true],
    ['name' => 'Volta', 'providers' => 5, 'services' => 15, 'active' => true],
    ['name' => 'Upper East', 'providers' => 3, 'services' => 9, 'active' => true],
    ['name' => 'Upper West', 'providers' => 2, 'services' => 6, 'active' => true],
    ['name' => 'Bono', 'providers' => 4, 'services' => 12, 'active' => true]
];

$total_regions = count($regions);
$active_regions = count(array_filter($regions, fn($r) => $r['active']));
$total_providers = array_sum(array_column($regions, 'providers'));
$total_services = array_sum(array_column($regions, 'services'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Regions - TourLink Admin</title>
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
        .regions-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .region-card { background: white; border-radius: 12px; padding: 24px; border: 1px solid #e2e8f0; transition: all 0.2s; }
        .region-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .region-header { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
        .region-icon { width: 48px; height: 48px; background: #1b4332; color: white; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        .region-name { font-size: 18px; font-weight: 600; color: #1e293b; }
        .region-stats { display: flex; gap: 24px; }
        .region-stat { flex: 1; }
        .region-stat-value { font-size: 24px; font-weight: 700; color: #1b4332; }
        .region-stat-label { font-size: 12px; color: #64748b; margin-top: 4px; }
        .badge-active { background: #dcfce7; color: #166534; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
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

    <main class="main-content">
        <div class="top-bar">
            <h1 class="page-title">Regions</h1>
            <div class="user-menu"><div class="user-info"><div class="user-name">System Admin</div><div class="user-role">Super Admin</div></div><div class="user-avatar">S</div></div>
        </div>

        <div class="content-area">
            <div class="stats-grid">
                <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-map-marked-alt"></i></div><div class="stat-value"><?php echo $total_regions; ?></div><div class="stat-label">Total Regions</div></div>
                <div class="stat-card"><div class="stat-icon green"><i class="fas fa-check-circle"></i></div><div class="stat-value"><?php echo $active_regions; ?></div><div class="stat-label">Active Regions</div></div>
                <div class="stat-card"><div class="stat-icon purple"><i class="fas fa-store"></i></div><div class="stat-value"><?php echo $total_providers; ?></div><div class="stat-label">Total Providers</div></div>
                <div class="stat-card"><div class="stat-icon orange"><i class="fas fa-concierge-bell"></i></div><div class="stat-value"><?php echo $total_services; ?></div><div class="stat-label">Total Services</div></div>
            </div>

            <div class="regions-grid">
                <?php foreach ($regions as $region): ?>
                <div class="region-card">
                    <div class="region-header">
                        <div class="region-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div>
                            <div class="region-name"><?php echo $region['name']; ?></div>
                            <?php if ($region['active']): ?><span class="badge-active">Active</span><?php endif; ?>
                        </div>
                    </div>
                    <div class="region-stats">
                        <div class="region-stat">
                            <div class="region-stat-value"><?php echo $region['providers']; ?></div>
                            <div class="region-stat-label">Providers</div>
                        </div>
                        <div class="region-stat">
                            <div class="region-stat-value"><?php echo $region['services']; ?></div>
                            <div class="region-stat-label">Services</div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</body>
</html>
