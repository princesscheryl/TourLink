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
    <style>
        :root {
            --primary: #0f766e;
            --primary-dark: #0d5a54;
            --primary-light: #14b8a6;
            --accent: #f59e0b;
            --bg-main: #f8fafc;
            --bg-card: #ffffff;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --border: #e2e8f0;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-main);
            color: var(--text-primary);
            min-height: 100vh;
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            min-height: 100vh;
        }

        .top-bar {
            background: var(--bg-card);
            border-bottom: 1px solid var(--border);
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .page-title h1 { font-size: 1.25rem; font-weight: 600; margin: 0; }
        .page-title p { font-size: 0.85rem; color: var(--text-secondary); margin: 4px 0 0 0; }

        .content-area { padding: 24px 32px; }

        /* Stats Row */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .stat-icon {
            width: 46px;
            height: 46px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .stat-icon.total { background: rgba(15, 118, 110, 0.1); color: var(--primary); }
        .stat-icon.rating { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
        .stat-icon.unanswered { background: rgba(239, 68, 68, 0.1); color: var(--danger); }
        .stat-icon.answered { background: rgba(16, 185, 129, 0.1); color: var(--success); }

        .stat-details h3 { font-size: 1.5rem; font-weight: 700; margin: 0; line-height: 1; }
        .stat-details span { font-size: 0.8rem; color: var(--text-secondary); }

        /* Filter Tabs */
        .filter-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            gap: 16px;
            flex-wrap: wrap;
        }

        .filter-tabs {
            display: flex;
            gap: 4px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 4px;
            flex-wrap: wrap;
        }

        .filter-tab {
            padding: 8px 14px;
            border: none;
            background: none;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-secondary);
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .filter-tab:hover { color: var(--text-primary); }

        .filter-tab.active {
            background: var(--primary);
            color: white;
        }

        .filter-tab .count {
            background: rgba(0,0,0,0.08);
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.75rem;
        }

        .filter-tab.active .count {
            background: rgba(255,255,255,0.25);
        }

        /* Review Cards */
        .reviews-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .review-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            transition: all 0.2s;
        }

        .review-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .reviewer-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .reviewer-avatar {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .reviewer-details h6 {
            font-weight: 600;
            font-size: 0.9rem;
            margin: 0 0 2px 0;
        }

        .reviewer-details small {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        .review-rating {
            color: var(--warning);
            font-size: 0.9rem;
        }

        .review-service {
            margin: 12px 0;
            padding: 8px 12px;
            background: var(--bg-main);
            border-radius: 6px;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .review-service a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .review-title {
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 8px;
        }

        .review-text {
            font-size: 0.9rem;
            color: var(--text-primary);
            line-height: 1.6;
            margin-bottom: 12px;
        }

        .provider-response {
            margin-top: 16px;
            padding: 16px;
            background: #f0fdfa;
            border-left: 3px solid var(--primary);
            border-radius: 6px;
        }

        .provider-response-label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--primary);
            margin-bottom: 8px;
        }

        .provider-response-text {
            font-size: 0.9rem;
            color: var(--text-primary);
            line-height: 1.6;
            margin-bottom: 4px;
        }

        .response-date {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }

        .response-form {
            margin-top: 16px;
            padding: 16px;
            background: var(--bg-main);
            border-radius: 8px;
        }

        .response-form textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 0.9rem;
            font-family: inherit;
            resize: vertical;
            margin-bottom: 10px;
        }

        .response-form button {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .response-form button:hover {
            background: var(--primary-dark);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
        }

        .empty-icon {
            width: 80px;
            height: 80px;
            background: var(--bg-main);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .empty-icon i { font-size: 2rem; color: var(--text-secondary); }
        .empty-state h3 { font-size: 1.1rem; font-weight: 600; margin: 0 0 8px 0; }
        .empty-state p { font-size: 0.9rem; color: var(--text-secondary); margin: 0; }

        /* Responsive */
        @media (max-width: 1200px) {
            .stats-row { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 1024px) {
            .main-content { margin-left: 80px; }
        }

        @media (max-width: 768px) {
            .main-content { margin-left: 0; }
            .content-area { padding: 16px; }
            .stats-row { grid-template-columns: 1fr 1fr; gap: 12px; }
        }
    </style>
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
    <script>
    $(document).ready(function() {
        $('.reply-form').on('submit', function(e) {
            e.preventDefault();
            
            const form = $(this);
            const reviewId = form.data('review-id');
            const serviceId = form.data('service-id');
            const responseText = form.find('textarea').val().trim();
            
            if (!responseText) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Please enter a response',
                    confirmButtonColor: '#0f766e'
                });
                return;
            }

            // Disable form
            form.find('button').prop('disabled', true).text('Posting...');

            $.ajax({
                url: '../../actions/respond_to_review_action.php',
                method: 'POST',
                data: {
                    review_id: reviewId,
                    service_id: serviceId,
                    provider_response: responseText
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message || 'Your response has been posted successfully',
                            confirmButtonColor: '#0f766e',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Failed to post response. Please try again.',
                            confirmButtonColor: '#0f766e'
                        });
                        form.find('button').prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Post Response');
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred. Please try again.',
                        confirmButtonColor: '#0f766e'
                    });
                    form.find('button').prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Post Response');
                }
            });
        });
    });
    </script>
</body>
</html>

