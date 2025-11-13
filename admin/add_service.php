<?php
require_once '../settings/core.php';
require_once '../classes/service_provider_class.php';
require_once '../controllers/service_category_controller.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

// Check if user is a provider
$provider_class = new ServiceProvider();
$provider = $provider_class->get_provider_by_user_id($_SESSION['user_id']);

if (!$provider) {
    header("Location: become_provider.php");
    exit();
}

// Get categories
$categories = get_all_service_categories_ctr();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Service - TourLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
        }

        /* Navigation */
        .main-nav {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }

        .nav-left, .nav-right {
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 800;
            color: #2d6a4f;
            text-decoration: none;
            transition: all 0.3s;
        }

        .logo:hover {
            color: #1b4332;
        }

        .logo-dot {
            color: #ffd700;
            font-size: 2rem;
        }

        .nav-link {
            color: #333;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: color 0.3s;
        }

        .nav-link:hover {
            color: #2d6a4f;
        }

        .btn-nav {
            padding: 10px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .btn-nav-logout {
            background: #dc3545;
            color: white;
        }

        .btn-nav-logout:hover {
            background: #c82333;
        }

        /* Main Container */
        .main-container {
            max-width: 900px;
            margin: 100px auto 60px;
            padding: 0 30px;
        }

        /* Form Card */
        .form-card {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }

        .form-card h2 {
            font-weight: 800;
            color: #1a1a1a;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid #2d6a4f;
        }

        /* Form Elements */
        .form-label {
            font-weight: 600;
            color: #1b4332;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 16px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s;
        }

        .form-control:focus, .form-select:focus {
            border-color: #2d6a4f;
            box-shadow: 0 0 0 0.2rem rgba(45, 106, 79, 0.15);
        }

        textarea.form-control {
            resize: vertical;
        }

        .form-check {
            padding: 10px 0;
        }

        .form-check-input {
            width: 20px;
            height: 20px;
            border: 2px solid #2d6a4f;
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: #2d6a4f;
            border-color: #2d6a4f;
        }

        .form-check-input:focus {
            box-shadow: 0 0 0 0.2rem rgba(45, 106, 79, 0.15);
        }

        .form-check-label {
            font-weight: 500;
            color: #333;
            cursor: pointer;
            margin-left: 8px;
        }

        /* Alert */
        .alert-info {
            background: #e8f4f1;
            border-left: 4px solid #2d6a4f;
            color: #1b4332;
            border-radius: 8px;
        }

        .alert-info i {
            color: #2d6a4f;
        }

        /* Buttons */
        .btn-primary {
            background: #2d6a4f;
            border: none;
            padding: 14px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            font-size: 1rem;
        }

        .btn-primary:hover {
            background: #1b4332;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(45, 106, 79, 0.3);
        }

        .btn-outline-secondary {
            border: 2px solid #6c757d;
            color: #6c757d;
            padding: 12px 28px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-outline-secondary:hover {
            background: #6c757d;
            color: white;
        }

        /* Help Text */
        small.text-muted {
            color: #666 !important;
            font-size: 0.85rem;
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
                <a href="provider_dashboard.php" class="nav-link">Dashboard</a>
                <a href="manage_services.php" class="nav-link">My Services</a>
                <a href="../login/logout.php" class="btn-nav btn-nav-logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="main-container">
        <div class="form-card">
            <h2>Add New Service</h2>

                        <form action="../actions/add_service_action.php" method="POST" id="addServiceForm">
                            <!-- Service Title -->
                            <div class="mb-3">
                                <label for="service_title" class="form-label">Service Title *</label>
                                <input type="text" class="form-control" id="service_title" name="service_title" required>
                                <small class="text-muted">Give your service a clear, descriptive title</small>
                            </div>

                            <!-- Category -->
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Category *</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Select a category</option>
                                    <?php if ($categories): ?>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['category_id']; ?>">
                                                <?php echo htmlspecialchars($category['category_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <label for="service_description" class="form-label">Description *</label>
                                <textarea class="form-control" id="service_description" name="service_description" rows="5" required></textarea>
                                <small class="text-muted">Describe what your service includes, what makes it special, and what tourists can expect</small>
                            </div>

                            <!-- Pricing -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="base_price" class="form-label">Price (GHS) *</label>
                                    <input type="number" class="form-control" id="base_price" name="base_price" step="0.01" min="0" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="pricing_unit" class="form-label">Pricing Unit *</label>
                                    <select class="form-select" id="pricing_unit" name="pricing_unit" required>
                                        <option value="per_hour">Per Hour</option>
                                        <option value="per_day">Per Day</option>
                                        <option value="per_person">Per Person</option>
                                        <option value="flat_rate">Flat Rate</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Location -->
                            <div class="mb-3">
                                <label for="service_location" class="form-label">Service Location *</label>
                                <input type="text" class="form-control" id="service_location" name="service_location" required>
                                <small class="text-muted">Where is this service provided? (e.g., "Accra", "Cape Coast", "Kumasi")</small>
                            </div>

                            <!-- Available Regions -->
                            <div class="mb-3">
                                <label class="form-label">Available Regions</label>
                                <div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="regions[]" value="Greater Accra" id="region1">
                                        <label class="form-check-label" for="region1">Greater Accra</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="regions[]" value="Central" id="region2">
                                        <label class="form-check-label" for="region2">Central</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="regions[]" value="Ashanti" id="region3">
                                        <label class="form-check-label" for="region3">Ashanti</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="regions[]" value="Northern" id="region4">
                                        <label class="form-check-label" for="region4">Northern</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Max Capacity -->
                            <div class="mb-3">
                                <label for="max_capacity" class="form-label">Maximum Capacity (Optional)</label>
                                <input type="number" class="form-control" id="max_capacity" name="max_capacity" min="1">
                                <small class="text-muted">How many people can you serve at once?</small>
                            </div>

                            <!-- Service Images (Placeholder for now) -->
                            <div class="mb-3">
                                <label for="service_images" class="form-label">Service Image URLs (Optional)</label>
                                <textarea class="form-control" id="service_images" name="service_images" rows="3" placeholder="https://example.com/image1.jpg&#10;https://example.com/image2.jpg"></textarea>
                                <small class="text-muted">Enter image URLs, one per line. These should be publicly accessible images.</small>
                            </div>

                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i>
                                Your service will be marked as "pending approval" until verified by our team.
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-check"></i> Add Service
                                </button>
                                <a href="provider_dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
