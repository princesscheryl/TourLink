<?php
require_once '../settings/core.php';
require_once '../controllers/message_controller.php';
require_once '../classes/hosted_upload_class.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$conversation_id = isset($_GET['conversation_id']) ? $_GET['conversation_id'] : null;
$service_id_param = isset($_GET['service_id']) ? (int)$_GET['service_id'] : null;

// If service_id is provided but no conversation_id, create conversation_id
if ($service_id_param && !$conversation_id) {
    require_once '../controllers/service_controller.php';
    $service = get_service_by_id_ctr($service_id_param);
    if ($service && isset($service['provider_user_id'])) {
        $provider_user_id = $service['provider_user_id'];
        $conversation_id = 'conv_' . min($user_id, $provider_user_id) . '_' . max($user_id, $provider_user_id);
        // Redirect to include conversation_id in URL
        header("Location: message_thread.php?conversation_id=" . urlencode($conversation_id) . ($service_id_param ? "&service_id=" . $service_id_param : ""));
        exit();
    }
}

if (!$conversation_id) {
    header("Location: messages.php");
    exit();
}

// Get conversation details
$conv_details = get_conversation_details_ctr($conversation_id, $user_id);
if (!$conv_details) {
    header("Location: messages.php");
    exit();
}

$other_user = $conv_details['other_user'];
if (!$other_user) {
    header("Location: messages.php");
    exit();
}

$messages = get_conversation_messages_ctr($conversation_id, $user_id);
if (!$messages) {
    $messages = [];
}

$profile_img_url = !empty($other_user['profile_image']) 
    ? HostedUpload::getImageUrl($other_user['profile_image'], '../') 
    : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - TourLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="../css/navigation.css" rel="stylesheet">
    <link href="../css/footer.css" rel="stylesheet">
    <link href="../css/dark-mode.css" rel="stylesheet">
    <link href="../css/message_thread.css" rel="stylesheet">
    <script src="../js/dark-mode.js"></script>
    <script>
        window.conversationId = '<?php echo htmlspecialchars($conversation_id); ?>';
        window.receiverId = <?php echo $other_user['user_id']; ?>;
        window.serviceId = <?php 
            $final_service_id = $conv_details['service_id'] ?: ($service_id_param ?: null);
            echo $final_service_id ?: 'null';
        ?>;
        window.bookingId = <?php echo $conv_details['booking_id'] ?: 'null'; ?>;
    </script>
</head>
<body>
    <?php include '../includes/navigation.php'; ?>

    <div class="message-thread-container">
        <div class="message-thread-header">
            <a href="messages.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Messages
            </a>
            <div class="thread-user-info">
                <div class="thread-avatar">
                    <?php if ($profile_img_url): ?>
                        <img src="<?php echo htmlspecialchars($profile_img_url); ?>" alt="<?php echo htmlspecialchars($other_user['first_name']); ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="avatar-placeholder" style="display:none;">
                            <?php echo strtoupper(substr($other_user['first_name'], 0, 1)); ?>
                        </div>
                    <?php else: ?>
                        <div class="avatar-placeholder">
                            <?php echo strtoupper(substr($other_user['first_name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div>
                    <h3><?php echo htmlspecialchars($other_user['first_name'] . ' ' . $other_user['last_name']); ?></h3>
                    <p class="user-type"><?php echo ucfirst($other_user['user_type']); ?></p>
                </div>
            </div>
        </div>

        <div class="messages-area" id="messagesArea">
            <?php if (empty($messages)): ?>
                <div class="empty-messages">
                    <i class="fas fa-comment-dots"></i>
                    <p>No messages yet. Start the conversation!</p>
                </div>
            <?php else: ?>
                <?php foreach ($messages as $msg): 
                    $is_sender = $msg['sender_id'] == $user_id;
                    $sender_profile_url = !empty($msg['sender_profile_image']) 
                        ? HostedUpload::getImageUrl($msg['sender_profile_image'], '../') 
                        : null;
                    $msg_time = date('g:i A', strtotime($msg['created_at']));
                ?>
                    <div class="message <?php echo $is_sender ? 'sent' : 'received'; ?>">
                        <?php if (!$is_sender): ?>
                            <div class="message-avatar">
                                <?php if ($sender_profile_url): ?>
                                    <img src="<?php echo htmlspecialchars($sender_profile_url); ?>" alt="<?php echo htmlspecialchars($msg['sender_first_name']); ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="avatar-placeholder" style="display:none;">
                                        <?php echo strtoupper(substr($msg['sender_first_name'], 0, 1)); ?>
                                    </div>
                                <?php else: ?>
                                    <div class="avatar-placeholder">
                                        <?php echo strtoupper(substr($msg['sender_first_name'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <div class="message-content">
                            <p><?php echo nl2br(htmlspecialchars($msg['message_text'])); ?></p>
                            <span class="message-time"><?php echo $msg_time; ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="message-input-area">
            <form id="messageForm" class="message-form">
                <textarea id="messageText" class="message-input" placeholder="Type your message..." rows="1"></textarea>
                <button type="submit" class="send-btn" id="sendBtn">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script src="../js/toast.js"></script>
    <script src="../js/message_thread.js"></script>
</body>
</html>

