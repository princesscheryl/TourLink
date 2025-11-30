<?php
require_once '../../settings/core.php';
require_once '../../controllers/message_controller.php';
require_once '../../classes/service_provider_class.php';
require_once '../../classes/hosted_upload_class.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login/login.php");
    exit();
}

// Check if user is a provider
$provider_class = new ServiceProvider();
$provider = $provider_class->get_provider_by_user_id($_SESSION['user_id']);

if (!$provider) {
    header("Location: ../../index_tourlink.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$conversation_id = isset($_GET['conversation_id']) ? $_GET['conversation_id'] : null;

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
$messages = get_conversation_messages_ctr($conversation_id, $user_id);
if (!$messages) {
    $messages = [];
}

$profile_img_url = !empty($other_user['profile_image']) 
    ? HostedUpload::getImageUrl($other_user['profile_image'], '../../') 
    : null;

// Set current page for sidebar
$current_page = 'messages';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - TourLink Provider</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../../css/provider_sidebar.css" rel="stylesheet">
    <link href="../../css/message_thread.css" rel="stylesheet">
    <script>
        window.conversationId = '<?php echo htmlspecialchars($conversation_id); ?>';
        window.receiverId = <?php echo $other_user['user_id']; ?>;
        window.serviceId = <?php echo $conv_details['service_id'] ?: 'null'; ?>;
        window.bookingId = <?php echo $conv_details['booking_id'] ?: 'null'; ?>;
    </script>
</head>
<body>
    <?php
    // Include reusable sidebar component
    include '../../includes/provider_sidebar.php';
    ?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="top-bar">
            <div class="page-title">
                <h1><i class="fas fa-envelope"></i> Messages</h1>
                <p>Conversation with <?php echo htmlspecialchars($other_user['first_name'] . ' ' . $other_user['last_name']); ?></p>
            </div>
        </div>
        
        <div class="content-area">
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
                        <p class="user-type">Tourist</p>
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
                            ? HostedUpload::getImageUrl($msg['sender_profile_image'], '../../') 
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
        </div>
    </main>

    <script src="../../js/toast.js"></script>
    <script src="../../js/message_thread.js"></script>
</body>
</html>

