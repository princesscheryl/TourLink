<?php
/**
 * Provider Reviews Management Page
 * Allows service providers to view and reply to reviews for their services
 */
require_once '../../settings/core.php';
require_once '../../controllers/review_controller.php';
require_once '../../classes/service_provider_class.php';

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

$provider_id = $provider['provider_id'];

// Get all reviews for this provider
$reviews = get_provider_reviews_ctr($provider_id);
if (!$reviews) {
    $reviews = [];
}

// Get review statistics
$stats = get_provider_review_stats_ctr($provider_id);

// Filter reviews
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$unanswered = array_filter($reviews, fn($r) => empty($r['response_from_provider']));
$answered = array_filter($reviews, fn($r) => !empty($r['response_from_provider']));

// Apply filter
$filtered_reviews = $reviews;
switch ($filter) {
    case 'unanswered': $filtered_reviews = $unanswered; break;
    case 'answered': $filtered_reviews = $answered; break;
}

// Set current page for sidebar
$current_page = 'reviews';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reviews - TourLink Provider</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="../../css/provider_reviews.css" rel="stylesheet">
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
                <h1>Manage Reviews</h1>
                <p>View and respond to customer reviews for your services</p>
            </div>
        </div>

        <div class="content-area">
            <!-- Stats Row -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-icon total">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['total_reviews'] ?? 0; ?></h3>
                        <span>Total Reviews</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon rating">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo number_format($stats['average_rating'] ?? 0, 1); ?></h3>
                        <span>Average Rating</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon unanswered">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['unanswered_reviews'] ?? 0; ?></h3>
                        <span>Unanswered</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon answered">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo count($answered); ?></h3>
                        <span>Answered</span>
                    </div>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <div class="filter-tabs">
                    <a href="?filter=all" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">
                        All <span class="count"><?php echo count($reviews); ?></span>
                    </a>
                    <a href="?filter=unanswered" class="filter-tab <?php echo $filter === 'unanswered' ? 'active' : ''; ?>">
                        Unanswered <span class="count"><?php echo count($unanswered); ?></span>
                    </a>
                    <a href="?filter=answered" class="filter-tab <?php echo $filter === 'answered' ? 'active' : ''; ?>">
                        Answered <span class="count"><?php echo count($answered); ?></span>
                    </a>
                </div>
            </div>

            <?php if (empty($filtered_reviews)): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h3>No reviews found</h3>
                    <p>
                        <?php if ($filter === 'unanswered'): ?>
                            All reviews have been answered!
                        <?php elseif ($filter === 'answered'): ?>
                            No answered reviews yet.
                        <?php else: ?>
                            You haven't received any reviews yet.
                        <?php endif; ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="reviews-list">
                    <?php foreach ($filtered_reviews as $review): ?>
                        <?php
                        $initials = strtoupper(substr($review['tourist_first_name'], 0, 1) . substr($review['tourist_last_name'], 0, 1));
                        ?>
                        <div class="review-card">
                            <div class="review-header">
                                <div class="reviewer-info">
                                    <div class="reviewer-avatar"><?php echo $initials; ?></div>
                                    <div class="reviewer-details">
                                        <h6><?php echo htmlspecialchars($review['tourist_first_name'] . ' ' . $review['tourist_last_name']); ?></h6>
                                        <small><?php echo date('M d, Y', strtotime($review['review_date'])); ?></small>
                                    </div>
                                </div>
                                <div class="review-rating">
                                    <?php
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo $i <= $review['rating'] ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                    }
                                    ?>
                                </div>
                            </div>

                            <div class="review-service">
                                <i class="fas fa-map-marker-alt"></i> 
                                <a href="../../view/single_service.php?id=<?php echo $review['service_id']; ?>">
                                    <?php echo htmlspecialchars($review['service_title']); ?>
                                </a>
                                <?php if ($review['service_date']): ?>
                                    <span style="margin-left: 8px; color: var(--text-secondary);">
                                        • <?php echo date('M d, Y', strtotime($review['service_date'])); ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($review['review_title'])): ?>
                                <div class="review-title"><?php echo htmlspecialchars($review['review_title']); ?></div>
                            <?php endif; ?>

                            <div class="review-text"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></div>

                            <!-- Provider Response -->
                            <?php if (!empty($review['response_from_provider'])): ?>
                                <div class="provider-response">
                                    <div class="provider-response-label">
                                        <i class="fas fa-reply"></i> Your Response
                                    </div>
                                    <p class="provider-response-text">
                                        <?php echo nl2br(htmlspecialchars($review['response_from_provider'])); ?>
                                    </p>
                                    <small class="response-date">
                                        Replied on <?php echo date('F j, Y', strtotime($review['response_date'])); ?>
                                    </small>
                                </div>
                            <?php else: ?>
                                <!-- Provider can respond -->
                                <div class="response-form">
                                    <form class="reply-form" data-review-id="<?php echo $review['review_id']; ?>" data-service-id="<?php echo $review['service_id']; ?>">
                                        <label style="font-weight: 600; font-size: 0.85rem; margin-bottom: 8px; display: block;">
                                            <i class="fas fa-reply"></i> Respond to this review
                                        </label>
                                        <textarea 
                                            name="provider_response"
                                            rows="3"
                                            placeholder="Thank your customer and address their feedback..."
                                            required></textarea>
                                        <button type="submit">
                                            <i class="fas fa-paper-plane"></i> Post Response
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../js/review_reply.js"></script>
</body>
</html>

