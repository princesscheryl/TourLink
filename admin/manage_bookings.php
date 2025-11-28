<?php
/**
 * Admin Bookings Management Page
 * Displays all platform bookings with statistics and filtering capabilities
 */
// Enable error reporting at the very top before any includes
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Start output buffering to catch any errors
ob_start();

try {
    require_once 'includes_platform/auth_check.php';
    require_once '../settings/db_class.php';
    
    // Verify admin has permission to view bookings
    require_privilege('view_bookings');
} catch (Exception $e) {
    ob_clean();
    die("Error loading required files: " . $e->getMessage() . "<br>File: " . $e->getFile() . "<br>Line: " . $e->getLine());
} catch (Error $e) {
    ob_clean();
    die("Fatal error: " . $e->getMessage() . "<br>File: " . $e->getFile() . "<br>Line: " . $e->getLine());
}

try {
    // Initialize database connection
    $db = new db_connection();
    if (!$db->db_connect()) {
        throw new Exception("Database connection failed. Please contact the administrator.");
    }

    // Retrieve all bookings with related user and provider information
    // Uses tourist_id to join with users table as bookings reference tourists, not general users
    $bookings = $db->db_fetch_all("
        SELECT b.*, s.service_title as service_name, s.base_price as service_price,
               u.first_name, u.last_name, u.email as user_email,
               sp.business_name
        FROM tl_bookings b
        JOIN tl_services s ON b.service_id = s.service_id
        JOIN tl_users u ON b.tourist_id = u.user_id
        JOIN tl_service_providers sp ON s.provider_id = sp.provider_id
        ORDER BY b.booking_date DESC
        LIMIT 100
    ");

    // Handle query failure gracefully
    if ($bookings === false) {
        $bookings = [];
    }
} catch (Exception $e) {
    // Clear any output and show error
    ob_clean();
    die("Error loading bookings: " . $e->getMessage() . "<br>File: " . $e->getFile() . "<br>Line: " . $e->getLine());
} catch (Error $e) {
    // Clear any output and show error
    ob_clean();
    die("Fatal error loading bookings: " . $e->getMessage() . "<br>File: " . $e->getFile() . "<br>Line: " . $e->getLine());
}

// Calculate booking statistics by status
// Aggregates revenue and counts bookings in different states for dashboard display
$total_revenue = 0;
$completed_count = 0;
$pending_count = 0;
$cancelled_count = 0;

if ($bookings) {
    foreach ($bookings as $b) {
        $status = $b['booking_status'] ?? 'unknown';
        if ($status === 'completed') {
            // Only completed bookings contribute to revenue
            $total_revenue += $b['total_amount'] ?? 0;
            $completed_count++;
        } elseif ($status === 'pending') {
            $pending_count++;
        } elseif ($status === 'cancelled') {
            $cancelled_count++;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - TourLink Admin</title>
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

        .stat-card.revenue { background: linear-gradient(135deg, #1b4332, #2d6a4f); color: white; }
        .stat-card.revenue h3 { color: white; }
        .stat-card.revenue p { color: rgba(255,255,255,0.8); }

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

        .badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-completed { background: #dcfce7; color: #166534; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-cancelled { background: #fee2e2; color: #991b1b; }
        .badge-confirmed { background: #dbeafe; color: #1e40af; }

        .booking-id {
            font-family: monospace;
            background: #f3f4f6;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }

        .amount { font-weight: 600; color: #1b4332; }
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
                <a href="manage_providers.php" class="nav-item"><i class="fas fa-store"></i> Providers</a>
                <a href="manage_bookings.php" class="nav-item active"><i class="fas fa-calendar-check"></i> Bookings</a>
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
                    <h1>Booking Management</h1>
                    <p style="color: #6b7280; font-size: 14px;">View and manage all platform bookings</p>
                </div>
            </div>

            <div class="stats-row">
                <div class="stat-card revenue">
                    <h3>GH&#8373; <?php echo number_format($total_revenue, 2); ?></h3>
                    <p>Total Revenue (Completed)</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $completed_count; ?></h3>
                    <p>Completed Bookings</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $pending_count; ?></h3>
                    <p>Pending Bookings</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $cancelled_count; ?></h3>
                    <p>Cancelled</p>
                </div>
            </div>

            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Customer</th>
                            <th>Service</th>
                            <th>Provider</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($bookings): foreach ($bookings as $booking): ?>
                        <tr>
                            <td><span class="booking-id">#<?php echo str_pad($booking['booking_id'], 6, '0', STR_PAD_LEFT); ?></span></td>
                            <td>
                                <strong><?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></strong>
                                <div style="font-size: 12px; color: #6b7280;"><?php echo htmlspecialchars($booking['user_email']); ?></div>
                            </td>
                            <td><?php echo htmlspecialchars($booking['service_name']); ?></td>
                            <td><?php echo htmlspecialchars($booking['business_name']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                            <td class="amount">GH&#8373; <?php echo number_format($booking['total_amount'], 2); ?></td>
                            <td>
                                <?php $status = $booking['booking_status'] ?? 'unknown'; ?>
                                <span class="badge badge-<?php echo $status; ?>">
                                    <?php echo ucfirst($status); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: #6b7280;">
                                No bookings found.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <p style="text-align: center; margin-top: 24px; color: #6b7280; font-size: 13px;">
                Showing last 100 bookings.
                <?php if (has_privilege('view_financial_reports')): ?>
                <a href="reports.php" style="color: #1b4332;">View full reports</a>
                <?php endif; ?>
            </p>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
// End output buffering and flush
ob_end_flush();
?>
