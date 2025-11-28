<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes_platform/auth_check.php';
require_once '../settings/db_class.php';

// Check privilege
require_privilege('view_providers');

// Get providers
$db = new db_connection();
$db->db_connect();
$providers = $db->db_fetch_all("
    SELECT sp.*, u.email, u.account_status as user_active,
           (SELECT COUNT(*) FROM tl_services WHERE provider_id = sp.provider_id) as service_count,
           (SELECT COUNT(*) FROM tl_bookings b JOIN tl_services s ON b.service_id = s.service_id WHERE s.provider_id = sp.provider_id) as booking_count
    FROM tl_service_providers sp
    JOIN tl_users u ON sp.user_id = u.user_id
    ORDER BY sp.created_at DESC
");

// Handle actions
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $provider_id = (int)($_POST['provider_id'] ?? 0);

    if ($action === 'approve' && has_privilege('approve_providers')) {
        $stmt = $db->db->prepare("UPDATE tl_service_providers SET verification_status = 'verified' WHERE provider_id = ?");
        $stmt->bind_param("i", $provider_id);
        if ($stmt->execute()) {
            $message = 'Provider approved successfully!';
            log_admin_action('approve_provider', ['provider_id' => $provider_id]);
        }
    }

    if ($action === 'suspend' && has_privilege('suspend_providers')) {
        $stmt = $db->db->prepare("UPDATE tl_service_providers SET verification_status = 'suspended' WHERE provider_id = ?");
        $stmt->bind_param("i", $provider_id);
        if ($stmt->execute()) {
            $message = 'Provider suspended.';
            log_admin_action('suspend_provider', ['provider_id' => $provider_id]);
        }
    }

    // Refresh data
    $providers = $db->db_fetch_all("
        SELECT sp.*, u.email, u.account_status as user_active,
               (SELECT COUNT(*) FROM tl_services WHERE provider_id = sp.provider_id) as service_count,
               (SELECT COUNT(*) FROM tl_bookings b JOIN tl_services s ON b.service_id = s.service_id WHERE s.provider_id = sp.provider_id) as booking_count
        FROM tl_service_providers sp
        JOIN tl_users u ON sp.user_id = u.user_id
        ORDER BY sp.created_at DESC
    ");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Providers - TourLink Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f5f5f5; }
        .admin-layout { display: flex; min-height: 100vh; }

        .sidebar {
            width: 260px;
            background: #1b4332;
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header { padding: 24px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-logo { font-size: 24px; font-weight: 700; color: white; text-decoration: none; }
        .sidebar-logo span { color: #d4a017; }
        .sidebar-nav { padding: 16px 0; }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 24px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: all 0.2s;
            font-size: 14px;
        }

        .nav-item:hover, .nav-item.active { background: rgba(255,255,255,0.1); color: white; }
        .nav-item i { width: 20px; }

        .nav-section {
            padding: 16px 24px 8px;
            font-size: 11px;
            text-transform: uppercase;
            color: rgba(255,255,255,0.4);
            letter-spacing: 1px;
        }

        .main-content { flex: 1; margin-left: 260px; padding: 32px; }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }

        .page-header h1 { font-size: 24px; font-weight: 700; color: #111827; }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .stat-card h3 { font-size: 28px; font-weight: 700; color: #1b4332; }
        .stat-card p { font-size: 13px; color: #6b7280; margin-top: 4px; }

        .data-table {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .data-table table { width: 100%; border-collapse: collapse; }

        .data-table th {
            background: #f9fafb;
            padding: 14px 16px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e5e7eb;
        }

        .data-table td {
            padding: 16px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
            color: #374151;
        }

        .data-table tr:hover { background: #f9fafb; }

        .provider-info { display: flex; align-items: center; gap: 12px; }

        .provider-avatar {
            width: 40px;
            height: 40px;
            background: #1b4332;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-verified { background: #dcfce7; color: #166534; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-suspended { background: #fee2e2; color: #991b1b; }

        .action-btn {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            margin-right: 4px;
        }

        .btn-approve { background: #dcfce7; color: #166534; }
        .btn-suspend { background: #fee2e2; color: #991b1b; }
        .btn-view { background: #e0e7ff; color: #4338ca; }

        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 24px; font-size: 14px; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="../index_tourlink.php" class="sidebar-logo">TourLink<span>.</span></a>
                <p style="font-size: 12px; opacity: 0.7; margin-top: 4px;">Admin Portal</p>
            </div>
            <nav class="sidebar-nav">
                <a href="platform_dashboard.php" class="nav-item"><i class="fas fa-th-large"></i> Dashboard</a>
                <div class="nav-section">Management</div>
                <a href="manage_providers.php" class="nav-item active"><i class="fas fa-store"></i> Providers</a>
                <a href="manage_bookings.php" class="nav-item"><i class="fas fa-calendar-check"></i> Bookings</a>
                <a href="manage_festivals.php" class="nav-item"><i class="fas fa-drum"></i> Festivals</a>
                <a href="manage_users.php" class="nav-item"><i class="fas fa-users"></i> Users</a>
                <?php if (has_privilege('view_admins')): ?>
                <div class="nav-section">Administration</div>
                <a href="manage_admins.php" class="nav-item"><i class="fas fa-user-shield"></i> Admins</a>
                <?php endif; ?>
                <div class="nav-section">Account</div>
                <a href="platform_logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <div>
                    <h1>Provider Management</h1>
                    <p style="color: #6b7280; font-size: 14px;">Manage service providers on the platform</p>
                </div>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $message; ?></div>
            <?php endif; ?>

            <?php
            $total = count($providers ?? []);
            $verified = count(array_filter($providers ?? [], fn($p) => $p['verification_status'] === 'verified'));
            $pending = count(array_filter($providers ?? [], fn($p) => $p['verification_status'] === 'pending'));
            $suspended = count(array_filter($providers ?? [], fn($p) => $p['verification_status'] === 'suspended'));
            ?>

            <div class="stats-row">
                <div class="stat-card">
                    <h3><?php echo $total; ?></h3>
                    <p>Total Providers</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $verified; ?></h3>
                    <p>Verified</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $pending; ?></h3>
                    <p>Pending Approval</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $suspended; ?></h3>
                    <p>Suspended</p>
                </div>
            </div>

            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>Provider</th>
                            <th>Business</th>
                            <th>Region</th>
                            <th>Services</th>
                            <th>Bookings</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($providers): foreach ($providers as $provider): ?>
                        <tr>
                            <td>
                                <div class="provider-info">
                                    <div class="provider-avatar">
                                        <?php echo strtoupper(substr($provider['business_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <strong><?php echo htmlspecialchars($provider['business_name']); ?></strong>
                                        <div style="font-size: 12px; color: #6b7280;"><?php echo htmlspecialchars($provider['email']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($provider['service_type'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($provider['region'] ?? 'N/A'); ?></td>
                            <td><?php echo $provider['service_count']; ?></td>
                            <td><?php echo $provider['booking_count']; ?></td>
                            <td>
                                <span class="badge badge-<?php echo $provider['verification_status']; ?>">
                                    <?php echo ucfirst($provider['verification_status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if (has_privilege('approve_providers') && $provider['verification_status'] !== 'verified'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="approve">
                                    <input type="hidden" name="provider_id" value="<?php echo $provider['provider_id']; ?>">
                                    <button type="submit" class="action-btn btn-approve"><i class="fas fa-check"></i> Approve</button>
                                </form>
                                <?php endif; ?>

                                <?php if (has_privilege('suspend_providers') && $provider['verification_status'] !== 'suspended'): ?>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Suspend this provider?');">
                                    <input type="hidden" name="action" value="suspend">
                                    <input type="hidden" name="provider_id" value="<?php echo $provider['provider_id']; ?>">
                                    <button type="submit" class="action-btn btn-suspend"><i class="fas fa-ban"></i></button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: #6b7280;">
                                No providers found.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
