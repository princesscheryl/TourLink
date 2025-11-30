<?php
require_once '../settings/core.php';
require_once '../controllers/booking_controller.php';
require_once '../controllers/service_controller.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'] ?? 'tourist';

// Get user's bookings for linking
$user_bookings = [];
if ($user_type === 'tourist') {
    $all_bookings = get_tourist_bookings_ctr($user_id);
    if ($all_bookings) {
        $user_bookings = $all_bookings;
    }
}

// Get pre-filled data from URL if available
$related_booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : null;
$related_service_id = isset($_GET['service_id']) ? (int)$_GET['service_id'] : null;
$category = isset($_GET['category']) ? $_GET['category'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Support - TourLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="../css/navigation.css" rel="stylesheet">
    <link href="../css/footer.css" rel="stylesheet">
    <link href="../css/dark-mode.css" rel="stylesheet">
    <link href="../css/create_ticket.css" rel="stylesheet">
    <script src="../js/dark-mode.js"></script>
</head>
<body>
    <?php include '../includes/navigation.php'; ?>

    <div class="create-ticket-container">
        <?php if (isset($_SESSION['ticket_errors'])): ?>
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($_SESSION['ticket_errors'] as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php unset($_SESSION['ticket_errors']); ?>
        <?php endif; ?>

        <div class="ticket-header">
            <h1><i class="fas fa-headset"></i> Contact Support</h1>
            <p>We're here to help! Describe your issue and we'll get back to you as soon as possible.</p>
        </div>

        <div class="ticket-form-wrapper">
            <form id="ticketForm" class="ticket-form" method="POST" action="../actions/create_ticket_action.php">
                <div class="form-section">
                    <h3>Issue Details</h3>
                    
                    <div class="form-group">
                        <label for="category" class="form-label">Category <span class="required">*</span></label>
                        <select class="form-control" id="category" name="category" required>
                            <option value="">Select a category</option>
                            <option value="payment" <?php echo $category === 'payment' ? 'selected' : ''; ?>>Payment Issue</option>
                            <option value="booking" <?php echo $category === 'booking' ? 'selected' : ''; ?>>Booking Problem</option>
                            <option value="account" <?php echo $category === 'account' ? 'selected' : ''; ?>>Account Issue</option>
                            <option value="technical" <?php echo $category === 'technical' ? 'selected' : ''; ?>>Technical Problem</option>
                            <option value="service" <?php echo $category === 'service' ? 'selected' : ''; ?>>Service Related</option>
                            <?php if ($user_type === 'provider'): ?>
                            <option value="provider" <?php echo $category === 'provider' ? 'selected' : ''; ?>>Provider Support</option>
                            <?php endif; ?>
                            <option value="other" <?php echo $category === 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="subject" class="form-label">Subject <span class="required">*</span></label>
                        <input type="text" class="form-control" id="subject" name="subject" placeholder="Brief description of your issue" required maxlength="255">
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">Description <span class="required">*</span></label>
                        <textarea class="form-control" id="description" name="description" rows="8" placeholder="Please provide as much detail as possible about your issue..." required></textarea>
                        <small class="form-text">Include any error messages, steps to reproduce, or relevant information.</small>
                    </div>

                    <div class="form-group">
                        <label for="priority" class="form-label">Priority</label>
                        <select class="form-control" id="priority" name="priority">
                            <option value="low">Low - General inquiry</option>
                            <option value="medium" selected>Medium - Standard issue</option>
                            <option value="high">High - Urgent issue</option>
                            <option value="urgent">Urgent - Critical problem</option>
                        </select>
                    </div>
                </div>

                <?php if ($user_type === 'tourist' && !empty($user_bookings)): ?>
                <div class="form-section">
                    <h3>Related Information (Optional)</h3>
                    
                    <div class="form-group">
                        <label for="related_booking" class="form-label">Related Booking</label>
                        <select class="form-control" id="related_booking" name="related_booking_id">
                            <option value="">None</option>
                            <?php foreach ($user_bookings as $booking): ?>
                                <option value="<?php echo $booking['booking_id']; ?>" <?php echo $related_booking_id == $booking['booking_id'] ? 'selected' : ''; ?>>
                                    Booking #<?php echo $booking['booking_reference']; ?> - <?php echo htmlspecialchars($booking['service_title']); ?> (<?php echo date('M d, Y', strtotime($booking['service_date'])); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <?php endif; ?>

                <div class="form-actions">
                    <a href="my_tickets.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Submit Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script src="../js/toast.js"></script>
    <script src="../js/create_ticket.js"></script>
</body>
</html>

