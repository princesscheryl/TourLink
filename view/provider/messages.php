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
$conversations = get_user_conversations_ctr($user_id);
if (!$conversations) {
    $conversations = [];
}

// Get unread count
$unread_count = get_unread_message_count_ctr($user_id);

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
    <link href="../../css/provider_bookings.css" rel="stylesheet">
    <link href="../../css/messages.css" rel="stylesheet">
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
                <p>Communicate with tourists about bookings and services</p>
            </div>
        </div>

        <div class="content-area">
            <div class="messages-content">
            <?php if (empty($conversations)): ?>
                <div class="empty-state">
                    <i class="fas fa-comments"></i>
                    <h3>No messages yet</h3>
                    <p>When tourists contact you about your services, conversations will appear here.</p>
                </div>
            <?php else: ?>
                <div class="conversations-list">
                    <?php foreach ($conversations as $conv): 
                        $other_user = $conv['other_user'];
                        $profile_img_url = !empty($other_user['profile_image']) 
                            ? HostedUpload::getImageUrl($other_user['profile_image'], '../../') 
                            : null;
                        $last_message_time = strtotime($conv['last_message_time']);
                        $time_ago = '';
                        $diff = time() - $last_message_time;
                        if ($diff < 3600) {
                            $time_ago = floor($diff / 60) . 'm ago';
                        } elseif ($diff < 86400) {
                            $time_ago = floor($diff / 3600) . 'h ago';
                        } elseif ($diff < 604800) {
                            $time_ago = floor($diff / 86400) . 'd ago';
                        } else {
                            $time_ago = date('M j', $last_message_time);
                        }
                    ?>
                        <a href="message_thread.php?conversation_id=<?php echo urlencode($conv['conversation_id']); ?>" class="conversation-item <?php echo $conv['unread_count'] > 0 ? 'unread' : ''; ?>">
                            <div class="conversation-avatar">
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
                                <?php if ($conv['unread_count'] > 0): ?>
                                    <span class="unread-badge"><?php echo $conv['unread_count']; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="conversation-info">
                                <div class="conversation-header">
                                    <h4><?php echo htmlspecialchars($other_user['first_name'] . ' ' . $other_user['last_name']); ?></h4>
                                    <span class="conversation-time"><?php echo $time_ago; ?></span>
                                </div>
                                <p class="conversation-preview"><?php echo htmlspecialchars(mb_substr($conv['last_message'], 0, 60)); ?><?php echo mb_strlen($conv['last_message']) > 60 ? '...' : ''; ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            </div>
        </div>
    </main>

    <script src="../../js/messages.js"></script>
</body>
</html>

