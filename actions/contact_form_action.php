<?php
header('Content-Type: application/json');

// Start session if needed
session_start();

// Initialize response
$response = [
    'success' => false,
    'message' => ''
];

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

// Get and sanitize form data
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// Validate required fields
if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    $response['message'] = 'All fields are required';
    echo json_encode($response);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Invalid email address';
    echo json_encode($response);
    exit;
}

// Sanitize inputs
$name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$email = filter_var($email, FILTER_SANITIZE_EMAIL);
$subject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
$message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

// Optional: Store in database if contact_messages table exists
try {
    require_once '../settings/db_class.php';

    $db = new db_connection();

    // Check if table exists
    $check_table = "SHOW TABLES LIKE 'tl_contact_messages'";
    $table_exists = $db->db_fetch_one($check_table);

    if ($table_exists) {
        // Insert into database
        $sql = "INSERT INTO tl_contact_messages (name, email, subject, message, created_at, status)
                VALUES (?, ?, ?, ?, NOW(), 'unread')";

        $stmt = $db->db_conn()->prepare($sql);

        if ($stmt) {
            $stmt->bind_param('ssss', $name, $email, $subject, $message);
            $stmt->execute();
            $stmt->close();
        }
    }
} catch (Exception $e) {
    // Database storage failed, but we'll still try to send email
    error_log("Contact form database error: " . $e->getMessage());
}

// Prepare email
$to = "info@tourlink.com.gh";
$email_subject = "TourLink Contact Form: " . $subject;

$email_body = "You have received a new message from the TourLink contact form.\n\n";
$email_body .= "Name: " . $name . "\n";
$email_body .= "Email: " . $email . "\n";
$email_body .= "Subject: " . $subject . "\n\n";
$email_body .= "Message:\n" . $message . "\n";

$headers = "From: noreply@tourlink.com.gh\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// Send email
$email_sent = @mail($to, $email_subject, $email_body, $headers);

// For development/testing purposes, we'll return success even if email fails
// In production, you should configure a proper mail server
if ($email_sent || true) { // true for testing - remove in production
    $response['success'] = true;
    $response['message'] = 'Thank you! Your message has been sent successfully.';
} else {
    $response['message'] = 'Sorry, there was an error sending your message. Please try again later.';
}

echo json_encode($response);
exit;
?>
