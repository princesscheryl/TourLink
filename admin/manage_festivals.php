<?php
require_once 'includes_platform/auth_check.php';
require_once '../classes/festival_class.php';

// Check privilege
require_privilege('view_festivals');

$festival_class = new Festival();
$festivals = $festival_class->get_all_festivals();

// Handle actions
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && has_privilege('add_festivals')) {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $data = [
            'festival_name' => trim($_POST['festival_name']),
            'description' => trim($_POST['description']),
            'region' => trim($_POST['region']),
            'location' => trim($_POST['location']),
            'start_date' => $_POST['start_date'],
            'end_date' => $_POST['end_date'] ?: $_POST['start_date'],
            'month_typically' => $_POST['month_typically'] ?: null,
            'festival_type' => $_POST['festival_type'],
            'image_url' => '',
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0
        ];

        if ($festival_class->add_festival($data)) {
            $message = 'Festival added successfully!';
            log_admin_action('add_festival', $data);
            $festivals = $festival_class->get_all_festivals();
        } else {
            $error = 'Failed to add festival.';
        }
    }

    if ($action === 'delete' && has_privilege('delete_festivals')) {
        $festival_id = (int)$_POST['festival_id'];
        if ($festival_class->delete_festival($festival_id)) {
            $message = 'Festival deleted successfully!';
            log_admin_action('delete_festival', ['festival_id' => $festival_id]);
            $festivals = $festival_class->get_all_festivals();
        } else {
            $error = 'Failed to delete festival.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Festivals - TourLink Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f5f5f5; }

        .admin-layout { display: flex; min-height: 100vh; }

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
            flex: 1;
            margin-left: 260px;
            padding: 32px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }

        .page-header h1 {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
        }

        .btn-add {
            background: #1b4332;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
            cursor: pointer;
        }

        .btn-add:hover { background: #143728; color: white; }

        /* Table */
        .data-table {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .data-table table {
            width: 100%;
            border-collapse: collapse;
        }

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

        .data-table tr:last-child td { border-bottom: none; }

        .data-table tr:hover { background: #f9fafb; }

        .badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-traditional { background: #dbeafe; color: #1e40af; }
        .badge-cultural { background: #fef3c7; color: #92400e; }
        .badge-music { background: #fce7f3; color: #9d174d; }
        .badge-art { background: #e0e7ff; color: #4338ca; }
        .badge-food { background: #dcfce7; color: #166534; }
        .badge-religious { background: #f3e8ff; color: #7c3aed; }
        .badge-national { background: #fee2e2; color: #991b1b; }
        .badge-featured { background: #d4a017; color: white; }

        .action-btn {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }

        .btn-edit { background: #e0e7ff; color: #4338ca; }
        .btn-delete { background: #fee2e2; color: #991b1b; }

        /* Modal */
        .modal-content { border-radius: 12px; }
        .modal-header { border-bottom: 1px solid #e5e7eb; padding: 20px 24px; }
        .modal-body { padding: 24px; }
        .modal-footer { border-top: 1px solid #e5e7eb; padding: 16px 24px; }

        .form-label {
            font-size: 13px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-control, .form-select {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 14px;
        }

        .form-control:focus, .form-select:focus {
            border-color: #1b4332;
            box-shadow: 0 0 0 3px rgba(27, 67, 50, 0.1);
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 14px;
        }

        .role-badge {
            font-size: 11px;
            padding: 4px 8px;
            border-radius: 4px;
            background: #1b4332;
            color: white;
        }
    </style>
</head>
<body>
    <div class="admin-layout">
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
                <a href="manage_festivals.php" class="nav-link active">
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
            <div class="page-header">
                <div>
                    <h1>Festival Management</h1>
                    <p style="color: #6b7280; font-size: 14px;">Manage Ghana's festival calendar</p>
                </div>
                <?php if (has_privilege('add_festivals')): ?>
                <button class="btn-add" data-bs-toggle="modal" data-bs-target="#addFestivalModal">
                    <i class="fas fa-plus"></i> Add Festival
                </button>
                <?php endif; ?>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $message; ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
            <?php endif; ?>

            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>Festival</th>
                            <th>Location</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($festivals): foreach ($festivals as $fest): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($fest['festival_name']); ?></strong>
                                <?php if ($fest['is_featured']): ?>
                                <span class="badge badge-featured" style="margin-left: 8px;">Featured</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($fest['location'] . ', ' . $fest['region']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($fest['start_date'])); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $fest['festival_type']; ?>">
                                    <?php echo ucfirst($fest['festival_type']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($fest['is_active']): ?>
                                <span style="color: #059669;"><i class="fas fa-check-circle"></i> Active</span>
                                <?php else: ?>
                                <span style="color: #dc2626;"><i class="fas fa-times-circle"></i> Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (has_privilege('edit_festivals')): ?>
                                <button class="action-btn btn-edit" onclick="editFestival(<?php echo $fest['festival_id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php endif; ?>
                                <?php if (has_privilege('delete_festivals')): ?>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this festival?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="festival_id" value="<?php echo $fest['festival_id']; ?>">
                                    <button type="submit" class="action-btn btn-delete"><i class="fas fa-trash"></i></button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: #6b7280;">
                                No festivals found. Add your first festival to get started.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Add Festival Modal -->
    <div class="modal fade" id="addFestivalModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Festival</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Festival Name *</label>
                                <input type="text" name="festival_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Festival Type *</label>
                                <select name="festival_type" class="form-select" required>
                                    <option value="traditional">Traditional</option>
                                    <option value="cultural">Cultural</option>
                                    <option value="music">Music</option>
                                    <option value="art">Art</option>
                                    <option value="food">Food</option>
                                    <option value="religious">Religious</option>
                                    <option value="national">National</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Region *</label>
                                <select name="region" class="form-select" required>
                                    <option value="Greater Accra">Greater Accra</option>
                                    <option value="Ashanti">Ashanti</option>
                                    <option value="Central">Central</option>
                                    <option value="Northern">Northern</option>
                                    <option value="Volta">Volta</option>
                                    <option value="Eastern">Eastern</option>
                                    <option value="Western">Western</option>
                                    <option value="Upper East">Upper East</option>
                                    <option value="Upper West">Upper West</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Location/Town *</label>
                                <input type="text" name="location" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Start Date *</label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Typical Month</label>
                                <select name="month_typically" class="form-select">
                                    <option value="">Variable</option>
                                    <option value="1">January</option>
                                    <option value="2">February</option>
                                    <option value="3">March</option>
                                    <option value="4">April</option>
                                    <option value="5">May</option>
                                    <option value="6">June</option>
                                    <option value="7">July</option>
                                    <option value="8">August</option>
                                    <option value="9">September</option>
                                    <option value="10">October</option>
                                    <option value="11">November</option>
                                    <option value="12">December</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" name="is_featured" class="form-check-input" id="isFeatured">
                                    <label class="form-check-label" for="isFeatured">Feature this festival on homepage</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Add Festival</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
