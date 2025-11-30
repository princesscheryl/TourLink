<?php
require_once '../settings/core.php';
require_once '../controllers/support_ticket_controller.php';
require_once '../classes/email_class.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'] ?? 'tourist';

// Get form data
$subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
$category = isset($_POST['category']) ? $_POST['category'] : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$priority = isset($_POST['priority']) ? $_POST['priority'] : 'medium';
$related_booking_id = isset($_POST['related_booking_id']) && $_POST['related_booking_id'] ? (int)$_POST['related_booking_id'] : null;
$related_service_id = isset($_POST['related_service_id']) && $_POST['related_service_id'] ? (int)$_POST['related_service_id'] : null;

// Validate input
$errors = [];

if (empty($subject)) {
    $errors[] = 'Subject is required';
}

if (empty($category)) {
    $errors[] = 'Category is required';
}

if (empty($description)) {
    $errors[] = 'Description is required';
}

if (strlen($description) < 10) {
    $errors[] = 'Description must be at least 10 characters';
}

if (!empty($errors)) {
    $_SESSION['ticket_errors'] = $errors;
    $_SESSION['ticket_form_data'] = $_POST;
    header("Location: ../view/create_ticket.php");
    exit();
}

// Create ticket
$ticket_id = create_ticket_ctr($user_id, $user_type, $subject, $category, $description, $priority, $related_booking_id, $related_service_id);

if ($ticket_id) {
    // Get ticket details for email
    $ticket = get_ticket_by_id_ctr($ticket_id);
    
    if ($ticket) {
        // Send confirmation email to user
        $user_email = $_SESSION['email'] ?? '';
        $user_name = $_SESSION['first_name'] ?? 'User';
        
        if ($user_email) {
            $email_subject = "Support Ticket Created - " . $ticket['ticket_number'];
            $email_body = "
                <h2>Your Support Ticket Has Been Created</h2>
                <p>Hello {$user_name},</p>
                <p>Thank you for contacting TourLink support. We have received your ticket and will respond as soon as possible.</p>
                <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <p><strong>Ticket Number:</strong> {$ticket['ticket_number']}</p>
                    <p><strong>Subject:</strong> {$subject}</p>
                    <p><strong>Category:</strong> " . ucfirst($category) . "</p>
                    <p><strong>Priority:</strong> " . ucfirst($priority) . "</p>
                </div>
                <p>You can view your ticket and track its status by visiting your support tickets page.</p>
                <p>Best regards,<br>TourLink Support Team</p>
            ";
            
            // Send email (assuming email class exists)
            if (class_exists('Email')) {
                $email = new Email();
                $email->send_email($user_email, $email_subject, $email_body);
            }
        }
        
        // Notify admins (optional - can be implemented later)
        // send_admin_notification($ticket);
    }
    
    $_SESSION['ticket_success'] = "Your ticket #{$ticket['ticket_number']} has been created successfully!";
    header("Location: ../view/ticket_details.php?id=" . $ticket_id);
    exit();
} else {
    $_SESSION['ticket_errors'] = ['Failed to create ticket. Please try again.'];
    $_SESSION['ticket_form_data'] = $_POST;
    header("Location: ../view/create_ticket.php");
    exit();
}

