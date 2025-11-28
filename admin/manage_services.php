<?php
require_once '../settings/core.php';
require_once '../classes/service_provider_class.php';
require_once '../classes/service_class.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

// Check if user is a provider
$provider_class = new ServiceProvider();
$provider = $provider_class->get_provider_by_user_id($_SESSION['user_id']);

if (!$provider) {
    header("Location: become_provider.php");
    exit();
}

// Get provider's services
$service_class = new Service();
$services = $service_class->get_services_by_provider($provider['provider_id']);

// Count by status
$active_count = 0;
$pending_count = 0;
$inactive_count = 0;
if ($services) {
    foreach ($services as $s) {
        if ($s['service_status'] === 'active') $active_count++;
        elseif ($s['service_status'] === 'pending_approval') $pending_count++;
        else $inactive_count++;
    }
}
$total_count = $services ? count($services) : 0;

// Set current page for sidebar
$current_page = 'services';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Services - TourLink Provider</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0f766e;
            --primary-dark: #0d5a54;
            --primary-light: #14b8a6;
            --accent: #f59e0b;
            --bg-main: #f8fafc;
            --bg-card: #ffffff;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --border: #e2e8f0;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-main);
            color: var(--text-primary);
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: 260px;
            background: var(--bg-card);
            border-right: 1px solid var(--border);
            z-index: 100;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 24px;
            border-bottom: 1px solid var(--border);
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            text-decoration: none;
            gap: 12px;
        }

        .sidebar-logo-text {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 2px;
        }

        .sidebar-logo-dot {
            width: 6px;
            height: 6px;
            background: var(--accent);
            border-radius: 50%;
            margin-top: 4px;
            margin-left: 1px;
        }

        .sidebar-logo-badge {
            background: var(--primary);
            color: white;
            font-size: 0.65rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 6px;
            letter-spacing: 0.3px;
        }

        .sidebar-nav {
            flex: 1;
            padding: 16px 0;
            overflow-y: auto;
        }

        .nav-section {
            padding: 0 16px;
            margin-bottom: 24px;
        }

        .nav-section-title {
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0 12px;
            margin-bottom: 8px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-radius: 8px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s;
            margin-bottom: 2px;
        }

        .nav-item:hover {
            background: var(--bg-main);
            color: var(--text-primary);
        }

        .nav-item.active {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
        }

        .nav-item i {
            width: 20px;
            text-align: center;
            font-size: 1rem;
        }

        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid var(--border);
        }

        .provider-badge {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: var(--bg-main);
            border-radius: 10px;
        }

        .provider-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1rem;
        }

        .provider-info h4 {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }

        .provider-info span {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            min-height: 100vh;
        }

        /* Top Bar */
        .top-bar {
            background: var(--bg-card);
            border-bottom: 1px solid var(--border);
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .page-title h1 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }

        .page-title p {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin: 4px 0 0 0;
        }

        .btn-add {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-add:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(15, 118, 110, 0.3);
            color: white;
        }

        /* Content Area */
        .content-area {
            padding: 24px 32px;
        }

        /* Stats Row */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-item {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .stat-icon.total { background: rgba(15, 118, 110, 0.1); color: var(--primary); }
        .stat-icon.active { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .stat-icon.pending { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
        .stat-icon.inactive { background: rgba(239, 68, 68, 0.1); color: var(--danger); }

        .stat-details h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
            line-height: 1;
        }

        .stat-details span {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        /* Filter Bar */
        .filter-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            gap: 16px;
        }

        .filter-tabs {
            display: flex;
            gap: 4px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 4px;
        }

        .filter-tab {
            padding: 8px 16px;
            border: none;
            background: none;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-secondary);
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .filter-tab:hover {
            color: var(--text-primary);
        }

        .filter-tab.active {
            background: var(--primary);
            color: white;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding: 10px 16px 10px 40px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.875rem;
            width: 280px;
            background: var(--bg-card);
            transition: all 0.2s;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.1);
        }

        .search-box i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        /* Services Table */
        .services-table {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
        }

        .table-header {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 100px 120px;
            padding: 14px 24px;
            background: var(--bg-main);
            border-bottom: 1px solid var(--border);
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 100px 120px;
            padding: 16px 24px;
            border-bottom: 1px solid var(--border);
            align-items: center;
            transition: background 0.2s;
        }

        .table-row:last-child {
            border-bottom: none;
        }

        .table-row:hover {
            background: var(--bg-main);
        }

        .service-cell {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .service-thumb {
            width: 52px;
            height: 52px;
            border-radius: 8px;
            object-fit: cover;
            background: var(--bg-main);
            border: 1px solid var(--border);
        }

        .service-thumb-placeholder {
            width: 52px;
            height: 52px;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }

        .service-info h4 {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0 0 4px 0;
        }

        .service-info span {
            font-size: 0.8rem;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .category-cell {
            font-size: 0.85rem;
            color: var(--text-primary);
        }

        .price-cell {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .price-cell span {
            display: block;
            font-size: 0.75rem;
            font-weight: 400;
            color: var(--text-secondary);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-badge.active {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
        }

        .status-badge.pending {
            background: rgba(245, 158, 11, 0.1);
            color: #d97706;
        }

        .status-badge.inactive {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
        }

        .status-badge::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
        }

        .views-cell {
            font-size: 0.85rem;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .actions-cell {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            width: 34px;
            height: 34px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--bg-card);
            color: var(--text-secondary);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            text-decoration: none;
        }

        .action-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(15, 118, 110, 0.05);
        }

        .action-btn.delete:hover {
            border-color: var(--danger);
            color: var(--danger);
            background: rgba(239, 68, 68, 0.05);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 40px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
        }

        .empty-icon {
            width: 80px;
            height: 80px;
            background: var(--bg-main);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .empty-icon i {
            font-size: 2rem;
            color: var(--text-secondary);
        }

        .empty-state h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0 0 8px 0;
        }

        .empty-state p {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin: 0 0 24px 0;
        }

        /* Alert Messages */
        .alert-toast {
            position: fixed;
            top: 24px;
            right: 24px;
            padding: 16px 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.875rem;
            font-weight: 500;
            z-index: 1000;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .alert-toast.success {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-toast.error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-close {
            background: none;
            border: none;
            color: inherit;
            cursor: pointer;
            padding: 0;
            margin-left: 8px;
            opacity: 0.7;
        }

        .alert-close:hover {
            opacity: 1;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .stats-row { grid-template-columns: repeat(2, 1fr); }
            .table-header, .table-row {
                grid-template-columns: 2fr 1fr 1fr 100px 100px;
            }
            .table-header > div:nth-child(4),
            .table-row > div:nth-child(4) { display: none; }
        }

        @media (max-width: 1024px) {
            .sidebar { width: 80px; }
            .sidebar-logo-text, .sidebar-logo-badge, .nav-section-title, .nav-item span, .provider-info { display: none; }
            .sidebar-header { padding: 16px; justify-content: center; }
            .sidebar-logo { justify-content: center; }
            .nav-item { justify-content: center; padding: 14px; }
            .nav-item i { margin: 0; font-size: 1.2rem; }
            .provider-badge { justify-content: center; padding: 12px 8px; }
            .main-content { margin-left: 80px; }
        }

        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; }
            .content-area { padding: 16px; }
            .stats-row { grid-template-columns: repeat(2, 1fr); gap: 12px; }
            .filter-bar { flex-direction: column; align-items: stretch; }
            .search-box input { width: 100%; }
            .table-header { display: none; }
            .table-row {
                display: flex;
                flex-direction: column;
                gap: 12px;
                padding: 20px;
            }
            .service-cell { width: 100%; }
            .actions-cell { justify-content: flex-end; }
        }
    </style>
</head>
<body>
    <?php
    // Include reusable sidebar component
    include '../includes/provider_sidebar.php';
    ?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="top-bar">
            <div class="page-title">
                <h1>My Services</h1>
                <p>Manage your service listings</p>
            </div>
            <a href="add_service.php" class="btn-add">
                <i class="fas fa-plus"></i>
                Add Service
            </a>
        </div>

        <div class="content-area">
            <!-- Stats Row -->
            <div class="stats-row">
                <div class="stat-item">
                    <div class="stat-icon total">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $total_count; ?></h3>
                        <span>Total Services</span>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon active">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $active_count; ?></h3>
                        <span>Active</span>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $pending_count; ?></h3>
                        <span>Pending Review</span>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon inactive">
                        <i class="fas fa-pause-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $inactive_count; ?></h3>
                        <span>Inactive</span>
                    </div>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <div class="filter-tabs">
                    <button class="filter-tab active" data-filter="all">All Services</button>
                    <button class="filter-tab" data-filter="active">Active</button>
                    <button class="filter-tab" data-filter="pending_approval">Pending</button>
                    <button class="filter-tab" data-filter="inactive">Inactive</button>
                </div>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search services...">
                </div>
            </div>

            <?php if ($services && count($services) > 0): ?>
            <!-- Services Table -->
            <div class="services-table">
                <div class="table-header">
                    <div>Service</div>
                    <div>Category</div>
                    <div>Price</div>
                    <div>Status</div>
                    <div>Views</div>
                    <div>Actions</div>
                </div>

                <?php foreach ($services as $service): ?>
                <div class="table-row" data-status="<?php echo $service['service_status']; ?>">
                    <div class="service-cell">
                        <?php
                        $images = json_decode($service['service_images'], true);
                        $thumb = null;
                        if ($images && count($images) > 0) {
                            // Handle both old format (filename only) and new format (full relative path)
                            $img = $images[0];
                            $thumb = (strpos($img, 'uploads/') === 0) ? '../' . $img : '../uploads/services/' . $img;
                        }
                        ?>
                        <?php if ($thumb): ?>
                            <img src="<?php echo htmlspecialchars($thumb); ?>" alt="" class="service-thumb" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="service-thumb-placeholder" style="display:none;">
                                <i class="fas fa-image"></i>
                            </div>
                        <?php else: ?>
                            <div class="service-thumb-placeholder">
                                <i class="fas fa-image"></i>
                            </div>
                        <?php endif; ?>
                        <div class="service-info">
                            <h4><?php echo htmlspecialchars($service['service_title']); ?></h4>
                            <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($service['service_location']); ?></span>
                        </div>
                    </div>
                    <div class="category-cell">
                        <?php echo htmlspecialchars($service['category_name']); ?>
                    </div>
                    <div class="price-cell">
                        GHS <?php echo number_format($service['base_price'], 2); ?>
                        <span><?php echo ucfirst(str_replace('_', ' ', $service['pricing_unit'])); ?></span>
                    </div>
                    <div>
                        <span class="status-badge <?php
                            echo $service['service_status'] === 'active' ? 'active' :
                                 ($service['service_status'] === 'pending_approval' ? 'pending' : 'inactive');
                        ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $service['service_status'])); ?>
                        </span>
                    </div>
                    <div class="views-cell">
                        <i class="fas fa-eye"></i>
                        <?php echo number_format($service['views_count']); ?>
                    </div>
                    <div class="actions-cell">
                        <a href="../view/single_service.php?id=<?php echo $service['service_id']; ?>"
                           class="action-btn" title="View" target="_blank">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                        <a href="edit_service.php?id=<?php echo $service['service_id']; ?>"
                           class="action-btn" title="Edit">
                            <i class="fas fa-pen"></i>
                        </a>
                        <button class="action-btn delete" title="Delete"
                                onclick="deleteService(<?php echo $service['service_id']; ?>)">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-concierge-bell"></i>
                </div>
                <h3>No services yet</h3>
                <p>Create your first service listing to start receiving bookings from tourists</p>
                <a href="add_service.php" class="btn-add">
                    <i class="fas fa-plus"></i>
                    Add Your First Service
                </a>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <?php if (isset($_SESSION['success'])): ?>
    <div class="alert-toast success">
        <i class="fas fa-check-circle"></i>
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        <button class="alert-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert-toast error">
        <i class="fas fa-exclamation-circle"></i>
        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        <button class="alert-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <?php endif; ?>

    <script>
        // Filter tabs
        document.querySelectorAll('.filter-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                const filter = this.dataset.filter;
                document.querySelectorAll('.table-row').forEach(row => {
                    if (filter === 'all' || row.dataset.status === filter) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });

        // Search
        document.getElementById('searchInput').addEventListener('input', function() {
            const query = this.value.toLowerCase();
            document.querySelectorAll('.table-row').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });

        // Delete service
        function deleteService(serviceId) {
            if (confirm('Are you sure you want to delete this service? This action cannot be undone.')) {
                window.location.href = '../actions/delete_service_action.php?id=' + serviceId;
            }
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            document.querySelectorAll('.alert-toast').forEach(alert => alert.remove());
        }, 5000);
    </script>
</body>
</html>
