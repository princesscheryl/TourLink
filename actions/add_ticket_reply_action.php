<?php
require_once '../settings/core.php';
require_once '../controllers/support_ticket_controller.php';
require_once '../classes/email_class.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

$ticket_id = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;
$message = isset($_POST['message']) ? trim($_POST['message']) : '';
$is_internal_note = isset($_POST['is_internal_note']) && $_POST['is_internal_note'] == '1';

if (!$ticket_id || empty($message)) {
    $_SESSION['ticket_error'] = 'Message cannot be empty';
    header("Location: ../view/ticket_details.php?id=" . $ticket_id);
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'] ?? 'tourist';

// Get ticket to verify ownership
$ticket = get_ticket_by_id_ctr($ticket_id);
if (!$ticket) {
    $_SESSION['ticket_error'] = 'Ticket not found';
    header("Location: ../view/my_tickets.php");
    exit();
}

// Check permission (user can only reply to their own tickets, admin can reply to any)
$is_admin = ($user_type === 'admin');
if (!$is_admin && $ticket['user_id'] != $user_id) {
    $_SESSION['ticket_error'] = 'You do not have permission to reply to this ticket';
    header("Location: ../view/my_tickets.php");
    exit();
}

// Add reply
$reply_id = add_ticket_reply_ctr($ticket_id, $user_id, $user_type, $message, $is_internal_note);

if ($reply_id) {
    // Send email notification
    if (!$is_internal_note) {
        if ($is_admin) {
            // Admin replied - notify ticket creator
            $user_email = $ticket['user_email'] ?? '';
            $user_name = $ticket['user_first_name'] ?? 'User';
            
            if ($user_email) {
                $email_subject = "New Reply to Your Ticket - " . $ticket['ticket_number'];
                $email_body = "
                    <h2>New Reply to Your Support Ticket</h2>
                    <p>Hello {$user_name},</p>
                    <p>You have received a new reply to your support ticket.</p>
                    <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                        <p><strong>Ticket:</strong> {$ticket['ticket_number']}</p>
                        <p><strong>Subject:</strong> {$ticket['subject']}</p>
                    </div>
                    <p><strong>Reply:</strong></p>
                    <div style='background: #fff; padding: 15px; border-left: 4px solid #2d6a4f; margin: 15px 0;'>
                        " . nl2br(htmlspecialchars($message)) . "
                    </div>
                    <p><a href='" . (isset($_SERVER['HTTPS']) ? 'https' : 'http') . "://" . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['PHP_SELF'])) . "/view/ticket_details.php?id={$ticket_id}' style='background: #2d6a4f; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;'>View Ticket</a></p>
                    <p>Best regards,<br>TourLink Support Team</p>
                ";
                
                if (class_exists('Email')) {
                    $email = new Email();
                    $email->send_email($user_email, $email_subject, $email_body);
                }
            }
        } else {
            // User replied - notify assigned admin or all admins
            $assigned_admin_email = null;
            if ($ticket['assigned_to']) {
                require_once '../classes/tourlink_user_class.php';
                $user_class = new TourlinkUser();
                $assigned_admin = $user_class->get_user_by_id($ticket['assigned_to']);
                if ($assigned_admin) {
                    $assigned_admin_email = $assigned_admin['email'];
                }
            }
            
            if ($assigned_admin_email) {
                $email_subject = "New Reply to Ticket - " . $ticket['ticket_number'];
                $email_body = "
                    <h2>New Reply to Support Ticket</h2>
                    <p>A user has replied to ticket {$ticket['ticket_number']}.</p>
                    <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                        <p><strong>Ticket:</strong> {$ticket['ticket_number']}</p>
                        <p><strong>Subject:</strong> {$ticket['subject']}</p>
                        <p><strong>User:</strong> {$ticket['user_first_name']} {$ticket['user_last_name']}</p>
                    </div>
                    <p><strong>Reply:</strong></p>
                    <div style='background: #fff; padding: 15px; border-left: 4px solid #2d6a4f; margin: 15px 0;'>
                        " . nl2br(htmlspecialchars($message)) . "
                    </div>
                    <p><a href='" . (isset($_SERVER['HTTPS']) ? 'https' : 'http') . "://" . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['PHP_SELF'])) . "/view/ticket_details.php?id={$ticket_id}' style='background: #2d6a4f; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;'>View Ticket</a></p>
                ";
                
                if (class_exists('Email')) {
                    $email = new Email();
                    $email->send_email($assigned_admin_email, $email_subject, $email_body);
                }
            }
        }
    }
    
    $_SESSION['ticket_success'] = 'Reply sent successfully';
} else {
    $_SESSION['ticket_error'] = 'Failed to send reply';
}

header("Location: ../view/ticket_details.php?id=" . $ticket_id);
exit();

