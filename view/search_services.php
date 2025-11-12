<?php
require_once '../settings/core.php';
require_once '../controllers/service_controller.php';

$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$services = [];

if ($search_query) {
    $services = search_services_ctr($search_query);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - TourLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../css/index.css" rel="stylesheet">
    <style>
        .service-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            margin-bottom: 24px;
        }
        .service-card:hover {
            transform: translateY(-4px);
        }
        .service-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
    </style>
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
        <h1 class="mb-4">Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h1>

        <?php if ($services && count($services) > 0): ?>
            <p class="text-muted mb-4"><?php echo count($services); ?> service(s) found</p>
            <div class="row">
                <?php foreach ($services as $service): ?>
                    <div class="col-md-4">
                        <div class="service-card">
                            <?php
                            $images = json_decode($service['service_images'], true);
                            $first_image = is_array($images) && !empty($images) ? $images[0] : null;
                            ?>
                            <?php if ($first_image): ?>
                                <img src="<?php echo htmlspecialchars($first_image); ?>"
                                     alt="<?php echo htmlspecialchars($service['service_title']); ?>"
                                     class="service-image">
                            <?php else: ?>
                                <div class="service-image" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center;">
                                    <i class="fa fa-image fa-3x" style="color: white; opacity: 0.5;"></i>
                                </div>
                            <?php endif; ?>

                            <div style="padding: 20px;">
                                <span class="badge bg-primary mb-2"><?php echo htmlspecialchars($service['category_name']); ?></span>
                                <h5><?php echo htmlspecialchars($service['service_title']); ?></h5>
                                <p class="text-muted small">
                                    <i class="fa fa-user"></i>
                                    <?php echo htmlspecialchars($service['provider_name'] ?: 'Provider'); ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong class="text-primary">GHS <?php echo number_format($service['base_price'], 2); ?></strong>
                                    <a href="single_service.php?id=<?php echo $service['service_id']; ?>" class="btn btn-sm btn-primary">View</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fa fa-search fa-3x text-muted mb-3"></i>
                <h4>No services found</h4>
                <p class="text-muted">Try different keywords or browse all services</p>
                <a href="all_services.php" class="btn btn-primary">Browse All Services</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
