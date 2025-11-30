<?php
require_once '../settings/core.php';
require_once '../controllers/support_ticket_controller.php';

// Check if platform admin OR regular admin
$is_platform_admin = isset($_SESSION['admin_id']);
$is_regular_admin = isset($_SESSION['user_id']) && ($_SESSION['user_type'] ?? '') === 'admin';

if (!$is_platform_admin && !$is_regular_admin) {
    if ($is_platform_admin) {
        header("Location: ../admin/platform_login.php");
    } else {
        header("Location: ../login/login.php");
    }
    exit();
}

$ticket_id = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;
$status = isset($_POST['status']) ? $_POST['status'] : '';
$priority = isset($_POST['priority']) ? $_POST['priority'] : '';
$assigned_to = isset($_POST['assigned_to']) && $_POST['assigned_to'] ? (int)$_POST['assigned_to'] : null;

if (!$ticket_id || !$status) {
    $_SESSION['ticket_error'] = 'Invalid request';
    header("Location: ../view/ticket_details.php?id=" . $ticket_id);
    exit();
}

// Update ticket (status, priority, and assignment)
$result = update_ticket_status_ctr($ticket_id, $status, $assigned_to, $priority);

if ($result) {
    // If assigning, also assign via assign method
    if ($assigned_to) {
        assign_ticket_ctr($ticket_id, $assigned_to);
    }
    
    $_SESSION['ticket_success'] = 'Ticket updated successfully';
} else {
    $_SESSION['ticket_error'] = 'Failed to update ticket';
}

header("Location: ../view/ticket_details.php?id=" . $ticket_id);
exit();

