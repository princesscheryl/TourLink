<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../settings/core.php';

// Check if user is logged in - redirect if not
if (!isLoggedIn()) {
    header("Location: ../login/login.php");
    exit();
}

$user_id = getUserID();

// Get user's favorites - with error handling
$favorites = [];
$debug_info = '';
try {
    require_once '../controllers/favorite_controller.php';
    $favorites = get_user_favorites_ctr($user_id);

    // Debug: Check if we got results
    if ($favorites === false) {
        $debug_info = "Query returned false - check database query";
        $favorites = [];
    } elseif (!is_array($favorites)) {
        $debug_info = "Query returned non-array: " . gettype($favorites);
        $favorites = [];
    } elseif (empty($favorites)) {
        $debug_info = "Query returned empty array";
    }
} catch (Exception $e) {
    error_log("Favorites error: " . $e->getMessage());
    $debug_info = "Exception: " . $e->getMessage();
    $favorites = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-i18n="favorites.page_title">My Favorites - TourLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="../css/navigation.css" rel="stylesheet">
    <link href="../css/footer.css" rel="stylesheet">
    <link href="../css/dark-mode.css" rel="stylesheet">
    <link href="../css/accessibility.css" rel="stylesheet">
    <link href="../css/my_favorites.css" rel="stylesheet">
    <script src="../js/dark-mode.js"></script>
    <script src="../js/accessibility.js"></script>
</head>
<body>
    <a href="#main-content" class="skip-link" data-i18n="accessibility.skip_to_content">Skip to main content</a>

    <!-- Navigation -->
    <?php include '../includes/navigation.php'; ?>

    <!-- Page Header -->
    <div class="page-header" id="main-content" role="main">
        <div class="main-container">
            <h1 data-i18n="favorites.my_favorites">My Favorites</h1>
            <p data-i18n="favorites.page_description">Services you've saved for later</p>
        </div>
    </div>

    <div class="main-container">
        <?php if (!empty($debug_info)): ?>
            <div class="alert alert-warning" style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <strong>Debug Info:</strong> <?php echo htmlspecialchars($debug_info); ?>
                <br><small>User ID: <?php echo $user_id; ?></small>
            </div>
        <?php endif; ?>

        <?php if ($favorites && count($favorites) > 0): ?>
            <p class="services-count">
                <strong><?php echo count($favorites); ?></strong>
                <span data-i18n="favorites.services_saved">service(s) saved</span>
            </p>
            <div class="row favorites-grid">
                <?php foreach ($favorites as $favorite): ?>
                    <div class="col-md-4">
                        <div class="service-card" data-service-id="<?php echo $favorite['service_id']; ?>">
                            <!-- Favorite Button -->
                            <button class="favorite-btn active"
                                    data-favorite-btn
                                    data-service-id="<?php echo $favorite['service_id']; ?>"
                                    aria-label="Remove from favorites"
                                    title="Remove from favorites">
                                <i class="fas fa-heart"></i>
                            </button>

                            <?php
                            // Handle service_image (JSON array or single path)
                            $service_image = $favorite['service_image'] ?? null;
                            require_once '../classes/hosted_upload_class.php';
                            $image_url = null;

                            if ($service_image) {
                                // Check if it's a JSON array (multiple images)
                                $images = json_decode($service_image, true);
                                if (is_array($images) && count($images) > 0) {
                                    // Use first image from array
                                    $image_url = HostedUpload::getImageUrl($images[0], '../');
                                } elseif (!empty($service_image)) {
                                    // Single image path
                                    $image_url = HostedUpload::getImageUrl($service_image, '../');
                                }
                            }
                            ?>

                            <?php if ($image_url): ?>
                                <img src="<?php echo htmlspecialchars($image_url); ?>"
                                     alt="<?php echo htmlspecialchars($favorite['service_name']); ?>"
                                     class="service-image"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="service-image" style="background: linear-gradient(135deg, #2d6a4f 0%, #1b4332 100%); display: none; align-items: center; justify-content: center;">
                                    <i class="fa fa-image fa-3x" style="color: white; opacity: 0.5;"></i>
                                </div>
                            <?php else: ?>
                                <div class="service-image" style="background: linear-gradient(135deg, #2d6a4f 0%, #1b4332 100%); display: flex; align-items: center; justify-content: center;">
                                    <i class="fa fa-image fa-3x" style="color: white; opacity: 0.5;"></i>
                                </div>
                            <?php endif; ?>

                            <div class="service-content">
                                <span class="badge bg-primary mb-2"><?php echo htmlspecialchars($favorite['category_name']); ?></span>
                                <h5><?php echo htmlspecialchars($favorite['service_name']); ?></h5>
                                <p class="text-muted small">
                                    <i class="fa fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($favorite['location'] . ', ' . $favorite['region']); ?>
                                </p>
                                <p class="text-muted small">
                                    <i class="fa fa-user"></i>
                                    <?php echo htmlspecialchars($favorite['business_name']); ?>
                                </p>
                                <p class="text-muted small mb-3">
                                    <?php echo htmlspecialchars(substr($favorite['service_description'], 0, 100)); ?>...
                                </p>
                                <div class="d-flex justify-content-between align-items-center mt-auto">
                                    <div>
                                        <strong class="text-primary">GHS <?php echo number_format($favorite['base_price'], 2); ?></strong>
                                        <br><small class="text-muted"><?php echo ucfirst(str_replace('_', ' ', $favorite['pricing_type'])); ?></small>
                                    </div>
                                    <a href="single_service.php?id=<?php echo $favorite['service_id']; ?>"
                                       class="btn btn-sm btn-primary"
                                       data-i18n="service.view_details">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-favorites">
                <i class="far fa-heart fa-4x"></i>
                <h3 data-i18n="favorites.no_favorites">No favorites yet</h3>
                <p data-i18n="favorites.start_adding">Start adding services to your favorites to see them here.</p>
                <a href="all_services.php" class="btn btn-primary" data-i18n="favorites.browse_services">Browse Services</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/favorites.js"></script>
    <script src="../js/accessibility.js"></script>
</body>
</html>
