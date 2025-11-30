<?php
require_once '../settings/core.php';
require_once '../controllers/support_ticket_controller.php';
require_once '../classes/tourlink_user_class.php';

// Check if user is logged in OR if admin is logged in
$is_platform_admin = false;
$user_id = null;
$user_type = null;

// Check for platform admin (admin dashboard)
if (isset($_SESSION['admin_id'])) {
    $is_platform_admin = true;
    $user_id = $_SESSION['admin_id']; // For admin context
    $user_type = 'admin';
} 
// Check for regular user login
elseif (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_type = $_SESSION['user_type'] ?? 'tourist';
    $is_admin = ($user_type === 'admin');
} 
// No valid session
else {
    header("Location: ../login/login.php");
    exit();
}

$ticket_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$ticket_id) {
    if ($is_platform_admin) {
        header("Location: ../admin/manage_tickets.php");
    } else {
        header("Location: my_tickets.php");
    }
    exit();
}

$ticket = get_ticket_by_id_ctr($ticket_id);

if (!$ticket) {
    if ($is_platform_admin) {
        header("Location: ../admin/manage_tickets.php");
    } else {
        header("Location: my_tickets.php");
    }
    exit();
}

// Check if user has permission to view this ticket
// Platform admins can view all tickets, regular admins can view all tickets, users can only view their own
if (!$is_platform_admin && !$is_admin && $ticket['user_id'] != $user_id) {
    if ($is_platform_admin) {
        header("Location: ../admin/manage_tickets.php");
    } else {
        header("Location: my_tickets.php");
    }
    exit();
}

// Get ticket replies
$include_internal = $is_admin;
$replies = get_ticket_replies_ctr($ticket_id, $include_internal);

// Get all admins for assignment (admin only)
$admins = [];
if ($is_admin) {
    $user_class = new TourlinkUser();
    $admins = $user_class->get_users_by_type('admin');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket <?php echo htmlspecialchars($ticket['ticket_number']); ?> - TourLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <?php if ($is_admin): ?>
        <link href="../css/admin_sidebar.css" rel="stylesheet">
    <?php else: ?>
        <link href="../css/navigation.css" rel="stylesheet">
        <link href="../css/footer.css" rel="stylesheet">
    <?php endif; ?>
    <link href="../css/dark-mode.css" rel="stylesheet">
    <link href="../css/ticket_details.css" rel="stylesheet">
    <script src="../js/dark-mode.js"></script>
</head>
<body>
    <?php if ($is_admin): ?>
        <?php include '../includes/admin_sidebar.php'; ?>
    <?php else: ?>
        <?php include '../includes/navigation.php'; ?>
    <?php endif; ?>

    <div class="ticket-details-container">
        <?php if (isset($_SESSION['ticket_success'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['ticket_success']); unset($_SESSION['ticket_success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['ticket_error'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_SESSION['ticket_error']); unset($_SESSION['ticket_error']); ?>
            </div>
        <?php endif; ?>

        <div class="ticket-header-section">
            <div class="ticket-header-top">
                <a href="<?php echo $is_admin ? '../admin/manage_tickets.php' : 'my_tickets.php'; ?>" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Tickets
                </a>
                <div class="ticket-number-badge">
                    <?php echo htmlspecialchars($ticket['ticket_number']); ?>
                </div>
            </div>
            
            <div class="ticket-title-section">
                <h1><?php echo htmlspecialchars($ticket['subject']); ?></h1>
                <div class="ticket-meta-badges">
                    <span class="status-badge status-<?php echo $ticket['status']; ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                    </span>
                    <span class="priority-badge priority-<?php echo $ticket['priority']; ?>">
                        <?php echo ucfirst($ticket['priority']); ?>
                    </span>
                    <span class="category-badge">
                        <?php echo ucfirst($ticket['category']); ?>
                    </span>
                </div>
            </div>
        </div>

        <?php if ($is_admin): ?>
        <div class="admin-actions-section">
            <form method="POST" action="../actions/update_ticket_action.php" class="admin-controls-form">
                <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
                
                <div class="control-group">
                    <label>Status</label>
                    <select name="status" class="form-select" id="statusSelect">
                        <option value="new" <?php echo $ticket['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                        <option value="open" <?php echo $ticket['status'] === 'open' ? 'selected' : ''; ?>>Open</option>
                        <option value="in_progress" <?php echo $ticket['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="resolved" <?php echo $ticket['status'] === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                        <option value="closed" <?php echo $ticket['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                    </select>
                </div>
                
                <div class="control-group">
                    <label>Assign To</label>
                    <select name="assigned_to" class="form-select">
                        <option value="">Unassigned</option>
                        <?php if ($admins): ?>
                            <?php foreach ($admins as $admin): ?>
                                <option value="<?php echo $admin['user_id']; ?>" <?php echo $ticket['assigned_to'] == $admin['user_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">Update Ticket</button>
            </form>
        </div>
        <?php endif; ?>

        <div class="ticket-info-section">
            <div class="info-card">
                <h3>Ticket Information</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Created By:</span>
                        <span class="info-value"><?php echo htmlspecialchars(($ticket['user_first_name'] ?? '') . ' ' . ($ticket['user_last_name'] ?? '')); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">User Type:</span>
                        <span class="info-value"><?php echo ucfirst($ticket['user_type']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Created:</span>
                        <span class="info-value"><?php echo date('F d, Y g:i A', strtotime($ticket['created_at'])); ?></span>
                    </div>
                    <?php if ($ticket['updated_at'] != $ticket['created_at']): ?>
                    <div class="info-item">
                        <span class="info-label">Last Updated:</span>
                        <span class="info-value"><?php echo date('F d, Y g:i A', strtotime($ticket['updated_at'])); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($ticket['resolved_at']): ?>
                    <div class="info-item">
                        <span class="info-label">Resolved:</span>
                        <span class="info-value"><?php echo date('F d, Y g:i A', strtotime($ticket['resolved_at'])); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($is_admin && $ticket['assigned_to']): ?>
                    <div class="info-item">
                        <span class="info-label">Assigned To:</span>
                        <span class="info-value"><?php echo htmlspecialchars(($ticket['assigned_first_name'] ?? '') . ' ' . ($ticket['assigned_last_name'] ?? '')); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="ticket-description-section">
            <h3>Description</h3>
            <div class="description-content">
                <?php echo nl2br(htmlspecialchars($ticket['description'])); ?>
            </div>
        </div>

        <div class="ticket-replies-section">
            <h3>Conversation (<?php echo count($replies); ?>)</h3>
            
            <div class="replies-list">
                <?php if (empty($replies)): ?>
                    <div class="no-replies">
                        <i class="fas fa-comments"></i>
                        <p>No replies yet. <?php echo $is_admin ? 'Be the first to respond!' : 'Support will respond soon.'; ?></p>
                    </div>
                <?php else: ?>
                    <?php foreach ($replies as $reply): 
                        $is_admin_reply = ($reply['user_type'] === 'admin');
                        $is_internal = ($reply['is_internal_note'] ?? 0) == 1;
                        $reply_date = date('F d, Y', strtotime($reply['created_at']));
                        $reply_time = date('g:i A', strtotime($reply['created_at']));
                    ?>
                        <div class="reply-item <?php echo $is_admin_reply ? 'admin-reply' : 'user-reply'; ?> <?php echo $is_internal ? 'internal-note' : ''; ?>">
                            <div class="reply-header">
                                <div class="reply-author">
                                    <strong><?php echo htmlspecialchars($reply['first_name'] . ' ' . $reply['last_name']); ?></strong>
                                    <?php if ($is_admin_reply): ?>
                                        <span class="admin-badge">Support Team</span>
                                    <?php endif; ?>
                                    <?php if ($is_internal): ?>
                                        <span class="internal-badge">Internal Note</span>
                                    <?php endif; ?>
                                </div>
                                <div class="reply-date">
                                    <?php echo $reply_date . ' at ' . $reply_time; ?>
                                </div>
                            </div>
                            <div class="reply-content">
                                <?php echo nl2br(htmlspecialchars($reply['message'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php if ($ticket['status'] !== 'closed'): ?>
            <div class="reply-form-section">
                <h4><?php echo $is_admin ? 'Add Reply' : 'Add a Reply'; ?></h4>
                <form method="POST" action="../actions/add_ticket_reply_action.php" id="replyForm">
                    <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
                    
                    <?php if ($is_admin): ?>
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="internalNote" name="is_internal_note" value="1">
                        <label class="form-check-label" for="internalNote">
                            Internal note (only visible to support team)
                        </label>
                    </div>
                    <?php endif; ?>
                    
                    <textarea name="message" class="form-control" rows="5" placeholder="Type your reply here..." required></textarea>
                    <button type="submit" class="btn btn-primary mt-3">
                        <i class="fas fa-paper-plane"></i> Send Reply
                    </button>
                </form>
            </div>
            <?php else: ?>
            <div class="ticket-closed-notice">
                <i class="fas fa-lock"></i> This ticket is closed. No further replies can be added.
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$is_admin): ?>
        <?php include '../includes/footer.php'; ?>
    <?php endif; ?>
    
    <script src="../js/toast.js"></script>
    <script src="../js/ticket_details.js"></script>
</body>
</html>

