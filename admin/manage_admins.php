<?php
require_once 'includes_platform/auth_check.php';
require_once '../classes/admin_class.php';

// Super admin only
require_privilege('view_admins');

$admin_class = new Admin();

// Get all admins
$db = new db_connection();
$db->db_connect();
$admins = $db->db_fetch_all("SELECT * FROM tl_admins ORDER BY created_at DESC");

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && has_privilege('add_admins')) {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $role = $_POST['role'];

        // Validate
        if (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->db->prepare("INSERT INTO tl_admins (email, password, first_name, last_name, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $email, $hashed, $first_name, $last_name, $role);

            if ($stmt->execute()) {
                $message = 'Admin created successfully!';
                log_admin_action('create_admin', ['email' => $email, 'role' => $role]);
                $admins = $db->db_fetch_all("SELECT * FROM tl_admins ORDER BY created_at DESC");
            } else {
                $error = 'Failed to create admin. Email may already exist.';
            }
        }
    }

    if ($action === 'delete' && has_privilege('delete_admins')) {
        $admin_id = (int)$_POST['admin_id'];

        // Prevent self-deletion
        if ($admin_id === $_SESSION['admin_id']) {
            $error = 'You cannot delete your own account.';
        } else {
            $stmt = $db->db->prepare("DELETE FROM tl_admins WHERE admin_id = ?");
            $stmt->bind_param("i", $admin_id);
            if ($stmt->execute()) {
                $message = 'Admin deleted.';
                log_admin_action('delete_admin', ['admin_id' => $admin_id]);
                $admins = $db->db_fetch_all("SELECT * FROM tl_admins ORDER BY created_at DESC");
            }
        }
    }

    if ($action === 'toggle_status') {
        $admin_id = (int)$_POST['admin_id'];
        $new_status = (int)$_POST['new_status'];

        if ($admin_id === $_SESSION['admin_id']) {
            $error = 'You cannot deactivate your own account.';
        } else {
            $stmt = $db->db->prepare("UPDATE tl_admins SET is_active = ? WHERE admin_id = ?");
            $stmt->bind_param("ii", $new_status, $admin_id);
            if ($stmt->execute()) {
                $message = $new_status ? 'Admin activated.' : 'Admin deactivated.';
                $admins = $db->db_fetch_all("SELECT * FROM tl_admins ORDER BY created_at DESC");
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admins - TourLink Admin</title>
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
            font-size: 14px;
        }

        .nav-item:hover, .nav-item.active { background: rgba(255,255,255,0.1); color: white; }
        .nav-item i { width: 20px; }

        .nav-section {
            padding: 16px 24px 8px;
            font-size: 11px;
            text-transform: uppercase;
            color: rgba(255,255,255,0.4);
        }

        .main-content { flex: 1; margin-left: 260px; padding: 32px; }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }

        .page-header h1 { font-size: 24px; font-weight: 700; }

        .btn-add {
            background: #1b4332;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
            cursor: pointer;
        }

        .btn-add:hover { background: #143728; color: white; }

        .warning-box {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .warning-box i { color: #f59e0b; font-size: 20px; margin-top: 2px; }

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
            border-bottom: 1px solid #e5e7eb;
        }

        .data-table td {
            padding: 16px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
        }

        .data-table tr:hover { background: #f9fafb; }

        .badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-super_admin { background: #fee2e2; color: #991b1b; }
        .badge-admin { background: #dbeafe; color: #1e40af; }
        .badge-moderator { background: #e5e7eb; color: #374151; }

        .action-btn {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            border: none;
            cursor: pointer;
            margin-right: 4px;
        }

        .btn-activate { background: #dcfce7; color: #166534; }
        .btn-deactivate { background: #fef3c7; color: #92400e; }
        .btn-delete { background: #fee2e2; color: #991b1b; }

        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 24px; font-size: 14px; }

        .modal-content { border-radius: 12px; }
        .modal-header { border-bottom: 1px solid #e5e7eb; padding: 20px 24px; }
        .modal-body { padding: 24px; }
        .modal-footer { border-top: 1px solid #e5e7eb; padding: 16px 24px; }

        .form-label { font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px; }

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

        .you-badge {
            background: #d4a017;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            margin-left: 8px;
        }
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
                <a href="manage_bookings.php" class="nav-item"><i class="fas fa-calendar-check"></i> Bookings</a>
                <a href="manage_festivals.php" class="nav-item"><i class="fas fa-drum"></i> Festivals</a>
                <a href="manage_users.php" class="nav-item"><i class="fas fa-users"></i> Users</a>
                <div class="nav-section">Administration</div>
                <a href="manage_admins.php" class="nav-item active"><i class="fas fa-user-shield"></i> Admins</a>
                <div class="nav-section">Account</div>
                <a href="platform_logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <div>
                    <h1>Admin Management</h1>
                    <p style="color: #6b7280; font-size: 14px;">Manage platform administrators (Super Admin only)</p>
                </div>
                <?php if (has_privilege('add_admins')): ?>
                <button class="btn-add" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                    <i class="fas fa-plus"></i> Add Admin
                </button>
                <?php endif; ?>
            </div>

            <div class="warning-box">
                <i class="fas fa-shield-alt"></i>
                <div>
                    <strong>Security Notice</strong>
                    <p style="margin: 4px 0 0; font-size: 13px; color: #92400e;">
                        Admin accounts have elevated privileges. Only create accounts for trusted staff members.
                        All admin actions are logged for security purposes.
                    </p>
                </div>
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
                            <th>Admin</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Last Login</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($admins): foreach ($admins as $admin): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']); ?></strong>
                                <?php if ($admin['admin_id'] == $_SESSION['admin_id']): ?>
                                <span class="you-badge">You</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($admin['email']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $admin['role']; ?>">
                                    <?php echo get_role_display_name($admin['role']); ?>
                                </span>
                            </td>
                            <td>
                                <?php echo $admin['last_login'] ? date('M d, Y H:i', strtotime($admin['last_login'])) : 'Never'; ?>
                            </td>
                            <td>
                                <?php if ($admin['is_active']): ?>
                                <span style="color: #059669;"><i class="fas fa-check-circle"></i> Active</span>
                                <?php else: ?>
                                <span style="color: #dc2626;"><i class="fas fa-times-circle"></i> Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($admin['admin_id'] != $_SESSION['admin_id']): ?>
                                    <?php if ($admin['is_active']): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="admin_id" value="<?php echo $admin['admin_id']; ?>">
                                        <input type="hidden" name="new_status" value="0">
                                        <button type="submit" class="action-btn btn-deactivate"><i class="fas fa-pause"></i></button>
                                    </form>
                                    <?php else: ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="admin_id" value="<?php echo $admin['admin_id']; ?>">
                                        <input type="hidden" name="new_status" value="1">
                                        <button type="submit" class="action-btn btn-activate"><i class="fas fa-play"></i></button>
                                    </form>
                                    <?php endif; ?>

                                    <?php if (has_privilege('delete_admins')): ?>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Permanently delete this admin?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="admin_id" value="<?php echo $admin['admin_id']; ?>">
                                        <button type="submit" class="action-btn btn-delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                    <?php endif; ?>
                                <?php else: ?>
                                <span style="color: #6b7280; font-size: 12px;">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Add Admin Modal -->
    <div class="modal fade" id="addAdminModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Admin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">First Name *</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Last Name *</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password *</label>
                            <input type="password" name="password" class="form-control" required minlength="8">
                            <small class="text-muted">Minimum 8 characters</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role *</label>
                            <select name="role" class="form-select" required>
                                <option value="moderator">Moderator (View only)</option>
                                <option value="admin">Admin (Manage content)</option>
                                <option value="super_admin">Super Admin (Full access)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Create Admin</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
