<?php
require_once '../settings/core.php';
require_once '../controllers/support_ticket_controller.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$tickets = get_user_tickets_ctr($user_id);
if (!$tickets) {
    $tickets = [];
}

// Group tickets by status
$new_tickets = array_filter($tickets, fn($t) => $t['status'] === 'new');
$open_tickets = array_filter($tickets, fn($t) => $t['status'] === 'open');
$in_progress_tickets = array_filter($tickets, fn($t) => $t['status'] === 'in_progress');
$resolved_tickets = array_filter($tickets, fn($t) => $t['status'] === 'resolved');
$closed_tickets = array_filter($tickets, fn($t) => $t['status'] === 'closed');

// Get current filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Support Tickets - TourLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="../css/navigation.css" rel="stylesheet">
    <link href="../css/footer.css" rel="stylesheet">
    <link href="../css/dark-mode.css" rel="stylesheet">
    <link href="../css/my_tickets.css" rel="stylesheet">
    <script src="../js/dark-mode.js"></script>
</head>
<body>
    <?php include '../includes/navigation.php'; ?>

    <div class="tickets-container">
        <div class="tickets-header">
            <div>
                <h1><i class="fas fa-ticket-alt"></i> My Support Tickets</h1>
                <p>View and manage your support requests</p>
            </div>
            <a href="create_ticket.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Ticket
            </a>
        </div>

        <?php if (isset($_SESSION['ticket_success'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['ticket_success']); unset($_SESSION['ticket_success']); ?>
            </div>
        <?php endif; ?>

        <div class="tickets-filters">
            <a href="?filter=all" class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">
                All (<?php echo count($tickets); ?>)
            </a>
            <a href="?filter=new" class="filter-btn <?php echo $filter === 'new' ? 'active' : ''; ?>">
                New (<?php echo count($new_tickets); ?>)
            </a>
            <a href="?filter=open" class="filter-btn <?php echo $filter === 'open' ? 'active' : ''; ?>">
                Open (<?php echo count($open_tickets); ?>)
            </a>
            <a href="?filter=in_progress" class="filter-btn <?php echo $filter === 'in_progress' ? 'active' : ''; ?>">
                In Progress (<?php echo count($in_progress_tickets); ?>)
            </a>
            <a href="?filter=resolved" class="filter-btn <?php echo $filter === 'resolved' ? 'active' : ''; ?>">
                Resolved (<?php echo count($resolved_tickets); ?>)
            </a>
            <a href="?filter=closed" class="filter-btn <?php echo $filter === 'closed' ? 'active' : ''; ?>">
                Closed (<?php echo count($closed_tickets); ?>)
            </a>
        </div>

        <div class="tickets-list">
            <?php 
            $display_tickets = [];
            if ($filter === 'all') {
                $display_tickets = $tickets;
            } elseif ($filter === 'new') {
                $display_tickets = $new_tickets;
            } elseif ($filter === 'open') {
                $display_tickets = $open_tickets;
            } elseif ($filter === 'in_progress') {
                $display_tickets = $in_progress_tickets;
            } elseif ($filter === 'resolved') {
                $display_tickets = $resolved_tickets;
            } elseif ($filter === 'closed') {
                $display_tickets = $closed_tickets;
            }

            if (empty($display_tickets)): 
            ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>No tickets found</h3>
                    <p><?php echo $filter === 'all' ? "You haven't created any support tickets yet." : "No tickets with this status."; ?></p>
                    <?php if ($filter === 'all'): ?>
                        <a href="create_ticket.php" class="btn btn-primary">Create Your First Ticket</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach ($display_tickets as $ticket): 
                    $status_class = 'status-' . $ticket['status'];
                    $priority_class = 'priority-' . $ticket['priority'];
                    $created_date = date('M d, Y', strtotime($ticket['created_at']));
                    $created_time = date('g:i A', strtotime($ticket['created_at']));
                ?>
                    <div class="ticket-card">
                        <div class="ticket-header-row">
                            <div class="ticket-number">
                                <strong><?php echo htmlspecialchars($ticket['ticket_number']); ?></strong>
                            </div>
                            <div class="ticket-meta">
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                                </span>
                                <span class="priority-badge <?php echo $priority_class; ?>">
                                    <?php echo ucfirst($ticket['priority']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <h3 class="ticket-subject">
                            <a href="ticket_details.php?id=<?php echo $ticket['ticket_id']; ?>">
                                <?php echo htmlspecialchars($ticket['subject']); ?>
                            </a>
                        </h3>
                        
                        <div class="ticket-info">
                            <span class="ticket-category">
                                <i class="fas fa-tag"></i> <?php echo ucfirst($ticket['category']); ?>
                            </span>
                            <span class="ticket-date">
                                <i class="fas fa-clock"></i> <?php echo $created_date . ' at ' . $created_time; ?>
                            </span>
                            <?php if ($ticket['reply_count'] > 0): ?>
                                <span class="ticket-replies">
                                    <i class="fas fa-comments"></i> <?php echo $ticket['reply_count']; ?> <?php echo $ticket['reply_count'] == 1 ? 'reply' : 'replies'; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="ticket-actions">
                            <a href="ticket_details.php?id=<?php echo $ticket['ticket_id']; ?>" class="btn btn-sm btn-outline-primary">
                                View Details
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script src="../js/my_tickets.js"></script>
</body>
</html>

