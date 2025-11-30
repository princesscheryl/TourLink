<?php
require_once '../settings/core.php';
require_once '../controllers/support_ticket_controller.php';
require_once '../classes/tourlink_user_class.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'admin') {
    header("Location: ../login/login.php");
    exit();
}

// Get filters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$priority_filter = isset($_GET['priority']) ? $_GET['priority'] : '';
$assigned_filter = isset($_GET['assigned']) ? $_GET['assigned'] : '';

$filters = [
    'status' => $status_filter,
    'category' => $category_filter,
    'priority' => $priority_filter,
    'assigned_to' => $assigned_filter
];

$tickets = get_all_tickets_ctr($filters);
if (!$tickets) {
    $tickets = [];
}

// Get statistics
$stats = get_ticket_stats_ctr();

// Get all admins for assignment
$user_class = new TourLinkUser();
$admins = $user_class->get_users_by_type('admin');

// Set current page for sidebar
$current_page = 'tickets';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Support Tickets - TourLink Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../css/admin_sidebar.css" rel="stylesheet">
    <link href="../css/manage_tickets.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/admin_sidebar.php'; ?>

    <main class="main-content">
        <div class="top-bar">
            <div class="page-title">
                <h1><i class="fas fa-headset"></i> Support Tickets</h1>
                <p>Manage and respond to customer support requests</p>
            </div>
        </div>

        <div class="content-area">
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon total">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['total_tickets'] ?? 0; ?></h3>
                        <span>Total Tickets</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon new">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['new_tickets'] ?? 0; ?></h3>
                        <span>New Tickets</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon open">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['open_tickets'] ?? 0; ?></h3>
                        <span>Open Tickets</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon urgent">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['urgent_tickets'] ?? 0; ?></h3>
                        <span>Urgent</span>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-section">
                <form method="GET" class="filters-form">
                    <div class="filter-group">
                        <label>Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="new" <?php echo $status_filter === 'new' ? 'selected' : ''; ?>>New</option>
                            <option value="open" <?php echo $status_filter === 'open' ? 'selected' : ''; ?>>Open</option>
                            <option value="in_progress" <?php echo $status_filter === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="resolved" <?php echo $status_filter === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                            <option value="closed" <?php echo $status_filter === 'closed' ? 'selected' : ''; ?>>Closed</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Category</label>
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            <option value="payment" <?php echo $category_filter === 'payment' ? 'selected' : ''; ?>>Payment</option>
                            <option value="booking" <?php echo $category_filter === 'booking' ? 'selected' : ''; ?>>Booking</option>
                            <option value="account" <?php echo $category_filter === 'account' ? 'selected' : ''; ?>>Account</option>
                            <option value="technical" <?php echo $category_filter === 'technical' ? 'selected' : ''; ?>>Technical</option>
                            <option value="service" <?php echo $category_filter === 'service' ? 'selected' : ''; ?>>Service</option>
                            <option value="provider" <?php echo $category_filter === 'provider' ? 'selected' : ''; ?>>Provider</option>
                            <option value="other" <?php echo $category_filter === 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Priority</label>
                        <select name="priority" class="form-select">
                            <option value="">All Priorities</option>
                            <option value="low" <?php echo $priority_filter === 'low' ? 'selected' : ''; ?>>Low</option>
                            <option value="medium" <?php echo $priority_filter === 'medium' ? 'selected' : ''; ?>>Medium</option>
                            <option value="high" <?php echo $priority_filter === 'high' ? 'selected' : ''; ?>>High</option>
                            <option value="urgent" <?php echo $priority_filter === 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Assigned To</label>
                        <select name="assigned" class="form-select">
                            <option value="">All</option>
                            <option value="unassigned" <?php echo $assigned_filter === 'unassigned' ? 'selected' : ''; ?>>Unassigned</option>
                            <?php if ($admins): ?>
                                <?php foreach ($admins as $admin): ?>
                                    <option value="<?php echo $admin['user_id']; ?>" <?php echo $assigned_filter == $admin['user_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="manage_tickets.php" class="btn btn-secondary">Clear</a>
                    </div>
                </form>
            </div>

            <!-- Tickets Table -->
            <div class="tickets-table-wrapper">
                <?php if (empty($tickets)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>No tickets found</h3>
                        <p>No tickets match the selected filters.</p>
                    </div>
                <?php else: ?>
                    <table class="tickets-table">
                        <thead>
                            <tr>
                                <th>Ticket #</th>
                                <th>Subject</th>
                                <th>User</th>
                                <th>Category</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Assigned To</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tickets as $ticket): 
                                $status_class = 'status-' . $ticket['status'];
                                $priority_class = 'priority-' . $ticket['priority'];
                                $created_date = date('M d, Y', strtotime($ticket['created_at']));
                            ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($ticket['ticket_number']); ?></strong>
                                    </td>
                                    <td>
                                        <a href="ticket_details.php?id=<?php echo $ticket['ticket_id']; ?>" class="ticket-link">
                                            <?php echo htmlspecialchars($ticket['subject']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars(($ticket['user_first_name'] ?? '') . ' ' . ($ticket['user_last_name'] ?? '')); ?>
                                        <br>
                                        <small class="text-muted"><?php echo ucfirst($ticket['user_type']); ?></small>
                                    </td>
                                    <td>
                                        <span class="category-badge"><?php echo ucfirst($ticket['category']); ?></span>
                                    </td>
                                    <td>
                                        <span class="priority-badge <?php echo $priority_class; ?>">
                                            <?php echo ucfirst($ticket['priority']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $status_class; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($ticket['assigned_to']): ?>
                                            <?php echo htmlspecialchars(($ticket['assigned_first_name'] ?? '') . ' ' . ($ticket['assigned_last_name'] ?? '')); ?>
                                        <?php else: ?>
                                            <span class="text-muted">Unassigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $created_date; ?></td>
                                    <td>
                                        <a href="ticket_details.php?id=<?php echo $ticket['ticket_id']; ?>" class="btn btn-sm btn-primary">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script src="../js/manage_tickets.js"></script>
</body>
</html>

