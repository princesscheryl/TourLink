<?php
require_once '../settings/core.php';
require_once '../controllers/service_controller.php';

$service_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$service_id) {
    header("Location: all_services.php");
    exit();
}

$service = get_service_by_id_ctr($service_id);

if (!$service) {
    header("Location: all_services.php");
    exit();
}

// Increment views
increment_service_views_ctr($service_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($service['service_title']); ?> - TourLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../css/index.css" rel="stylesheet">
</head>
<body>
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-left">
                <a href="../index_tourlink.php" class="logo">TourLink<span class="logo-dot">.</span></a>
            </div>
            <div class="nav-right">
                <a href="all_services.php" class="nav-link">Browse Services</a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="../login/logout.php" class="btn-nav btn-nav-logout">Logout</a>
                <?php else: ?>
                    <a href="../login/login.php" class="nav-link">Sign in</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 100px;">
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <span class="badge bg-primary mb-2"><?php echo htmlspecialchars($service['category_name']); ?></span>
                        <h1><?php echo htmlspecialchars($service['service_title']); ?></h1>

                        <div class="mb-3">
                            <i class="fa fa-user"></i>
                            <strong><?php echo htmlspecialchars($service['provider_name'] ?: ($service['provider_first_name'] . ' ' . $service['provider_last_name'])); ?></strong>
                            <span class="text-muted"> | </span>
                            <i class="fa fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($service['provider_region']); ?>
                            <?php if ($service['provider_rating'] > 0): ?>
                                <span class="text-muted"> | </span>
                                <i class="fa fa-star text-warning"></i>
                                <?php echo number_format($service['provider_rating'], 1); ?>/5.0
                            <?php endif; ?>
                        </div>

                        <hr>

                        <h5>About This Service</h5>
                        <p><?php echo nl2br(htmlspecialchars($service['service_description'])); ?></p>

                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fa fa-map-marker-alt"></i> Location</h6>
                                <p><?php echo htmlspecialchars($service['service_location']); ?></p>
                            </div>
                            <?php if ($service['max_capacity']): ?>
                            <div class="col-md-6">
                                <h6><i class="fa fa-users"></i> Max Capacity</h6>
                                <p><?php echo $service['max_capacity']; ?> people</p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($service['languages_spoken']): ?>
                        <div class="mt-3">
                            <h6><i class="fa fa-language"></i> Languages</h6>
                            <p><?php echo htmlspecialchars($service['languages_spoken']); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card sticky-top" style="top: 100px;">
                    <div class="card-body">
                        <h3 class="text-primary">GHS <?php echo number_format($service['base_price'], 2); ?></h3>
                        <p class="text-muted"><?php echo str_replace('_', ' ', $service['pricing_unit']); ?></p>

                        <hr>

                        <?php if (isset($_SESSION['user_id'])): ?>
                            <button class="btn btn-primary w-100 mb-2" onclick="bookService()">
                                <i class="fa fa-calendar-check"></i> Book Now
                            </button>
                            <button class="btn btn-outline-primary w-100">
                                <i class="fa fa-heart"></i> Add to Favorites
                            </button>
                        <?php else: ?>
                            <a href="../login/login.php" class="btn btn-primary w-100">
                                Sign in to Book
                            </a>
                        <?php endif; ?>

                        <hr>

                        <h6>Contact Provider</h6>
                        <?php if ($service['provider_phone']): ?>
                        <p><i class="fa fa-phone"></i> <?php echo htmlspecialchars($service['provider_phone']); ?></p>
                        <?php endif; ?>
                        <?php if ($service['provider_email']): ?>
                        <p><i class="fa fa-envelope"></i> <?php echo htmlspecialchars($service['provider_email']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function bookService() {
            alert('Booking functionality coming soon!');
        }
    </script>
</body>
</html>
