<?php
require_once '../settings/core.php';
require_once '../classes/service_provider_class.php';
require_once '../controllers/service_category_controller.php';
require_once '../classes/festival_class.php';

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

// Get festivals
$festival_class = new Festival();
$festivals = $festival_class->get_all_festivals();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Service - TourLink Provider</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-main);
            color: var(--text-primary);
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: 260px;
            background: var(--bg-card);
            border-right: 1px solid var(--border);
            z-index: 100;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 24px;
            border-bottom: 1px solid var(--border);
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            text-decoration: none;
            gap: 12px;
        }

        .sidebar-logo-text {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 2px;
        }

        .sidebar-logo-dot {
            width: 6px;
            height: 6px;
            background: var(--accent);
            border-radius: 50%;
            margin-top: 4px;
            margin-left: 1px;
        }

        .sidebar-logo-badge {
            background: var(--primary);
            color: white;
            font-size: 0.65rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 6px;
            letter-spacing: 0.3px;
        }

        .sidebar-nav {
            flex: 1;
            padding: 16px 0;
            overflow-y: auto;
        }

        .nav-section {
            padding: 0 16px;
            margin-bottom: 24px;
        }

        .nav-section-title {
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0 12px;
            margin-bottom: 8px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-radius: 8px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s;
            margin-bottom: 2px;
        }

        .nav-item:hover {
            background: var(--bg-main);
            color: var(--text-primary);
        }

        .nav-item.active {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
        }

        .nav-item i {
            width: 20px;
            text-align: center;
            font-size: 1rem;
        }

        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid var(--border);
        }

        .provider-badge {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: var(--bg-main);
            border-radius: 10px;
        }

        .provider-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1rem;
        }

        .provider-info h4 {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }

        .provider-info span {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            min-height: 100vh;
        }

        /* Top Bar */
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

        .page-title h1 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }

        .page-title p {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin: 4px 0 0 0;
        }

        .top-bar-actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .btn-back {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: var(--bg-main);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text-primary);
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-back:hover {
            background: var(--border);
            color: var(--text-primary);
        }

        /* Form Container */
        .form-container {
            padding: 32px;
        }

        .form-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            padding: 24px 32px;
            color: white;
            border-radius: 12px 12px 0 0;
            margin-bottom: 0;
        }

        .form-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
        }

        .form-header p {
            font-size: 0.875rem;
            opacity: 0.9;
            margin: 4px 0 0 0;
        }

        .form-body {
            background: var(--bg-card);
            padding: 32px;
            border: 1px solid var(--border);
            border-top: none;
            border-radius: 0 0 12px 12px;
        }

        /* Form Sections */
        .form-section {
            margin-bottom: 32px;
            padding-bottom: 32px;
            border-bottom: 1px solid var(--border);
        }

        .form-section:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .section-title {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title i {
            font-size: 0.9rem;
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .form-label .required {
            color: var(--danger);
        }

        .form-control, .form-select {
            width: 100%;
            padding: 12px 16px;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--bg-card);
            color: var(--text-primary);
            transition: all 0.2s;
        }

        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.1);
        }

        .form-control::placeholder {
            color: var(--text-secondary);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }

        .form-hint {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-top: 6px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        /* Checkboxes */
        .checkbox-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            background: var(--bg-main);
            border: 1px solid var(--border);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .checkbox-item:hover {
            border-color: var(--primary);
        }

        .checkbox-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .checkbox-item label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-primary);
            cursor: pointer;
            margin: 0;
        }

        .checkbox-item.checked {
            background: rgba(15, 118, 110, 0.05);
            border-color: var(--primary);
        }

        /* Image Upload */
        .upload-area {
            border: 2px dashed var(--border);
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: var(--bg-main);
        }

        .upload-area:hover {
            border-color: var(--primary);
            background: rgba(15, 118, 110, 0.02);
        }

        .upload-area.dragover {
            border-color: var(--primary);
            background: rgba(15, 118, 110, 0.05);
        }

        .upload-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }

        .upload-icon i {
            font-size: 1.5rem;
            color: white;
        }

        .upload-text h4 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0 0 4px 0;
        }

        .upload-text p {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin: 0;
        }

        .image-preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 16px;
            margin-top: 20px;
        }

        .preview-item {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            border: 2px solid var(--border);
            aspect-ratio: 1;
        }

        .preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .preview-item.main {
            border-color: var(--primary);
            border-width: 3px;
        }

        .preview-badge {
            position: absolute;
            top: 8px;
            left: 8px;
            background: var(--primary);
            color: white;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .preview-actions {
            position: absolute;
            top: 8px;
            right: 8px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .preview-btn {
            width: 28px;
            height: 28px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            transition: all 0.2s;
        }

        .preview-btn.remove {
            background: var(--danger);
            color: white;
        }

        .preview-btn.set-main {
            background: var(--primary);
            color: white;
        }

        .preview-btn:hover {
            transform: scale(1.1);
        }

        /* Info Alert */
        .info-alert {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 16px 20px;
            background: rgba(15, 118, 110, 0.05);
            border: 1px solid rgba(15, 118, 110, 0.2);
            border-radius: 10px;
            margin-bottom: 24px;
        }

        .info-alert i {
            color: var(--primary);
            font-size: 1.1rem;
            margin-top: 2px;
        }

        .info-alert p {
            font-size: 0.875rem;
            color: var(--text-primary);
            margin: 0;
            line-height: 1.5;
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            gap: 12px;
            padding-top: 24px;
            border-top: 1px solid var(--border);
            margin-top: 32px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            font-size: 0.9rem;
            font-weight: 500;
            font-family: 'Inter', sans-serif;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(15, 118, 110, 0.3);
        }

        .btn-secondary {
            background: var(--bg-main);
            color: var(--text-primary);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background: var(--border);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar { width: 80px; }
            .sidebar-logo-text, .nav-section-title, .nav-item span, .provider-info { display: none; }
            .sidebar-header { padding: 16px; justify-content: center; }
            .sidebar-logo { justify-content: center; }
            .nav-item { justify-content: center; padding: 14px; }
            .nav-item i { margin: 0; font-size: 1.2rem; }
            .provider-badge { justify-content: center; padding: 12px 8px; }
            .main-content { margin-left: 80px; }
        }

        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; }
            .form-container { padding: 16px; }
            .form-body { padding: 20px; }
            .form-row { grid-template-columns: 1fr; }
            .checkbox-grid { grid-template-columns: 1fr; }
            .form-header { padding: 20px; }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="provider_dashboard.php" class="sidebar-logo">
                <span class="sidebar-logo-text">TourLink<span class="sidebar-logo-dot"></span></span>
                <span class="sidebar-logo-badge">Provider</span>
            </a>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section">
                <div class="nav-section-title">Main</div>
                <a href="provider_dashboard.php" class="nav-item">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </a>
                <a href="../view/provider/manage_bookings.php" class="nav-item">
                    <i class="fas fa-calendar-check"></i>
                    <span>Bookings</span>
                </a>
                <a href="manage_services.php" class="nav-item">
                    <i class="fas fa-concierge-bell"></i>
                    <span>My Services</span>
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Management</div>
                <a href="add_service.php" class="nav-item active">
                    <i class="fas fa-plus-circle"></i>
                    <span>Add Service</span>
                </a>
                <a href="provider_profile.php" class="nav-item">
                    <i class="fas fa-user-tie"></i>
                    <span>Business Profile</span>
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Other</div>
                <a href="../index_tourlink.php" class="nav-item">
                    <i class="fas fa-external-link-alt"></i>
                    <span>View Site</span>
                </a>
                <a href="account_settings.php" class="nav-item">
                    <i class="fas fa-cog"></i>
                    <span>Account Settings</span>
                </a>
                <a href="../login/logout.php" class="nav-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </nav>

        <div class="sidebar-footer">
            <div class="provider-badge">
                <div class="provider-avatar">
                    <?php echo strtoupper(substr($provider['business_name'] ?? $_SESSION['first_name'], 0, 1)); ?>
                </div>
                <div class="provider-info">
                    <h4><?php echo htmlspecialchars($provider['business_name'] ?? $_SESSION['first_name']); ?></h4>
                    <span><?php echo ucfirst($provider['verification_status'] ?? 'Pending'); ?></span>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="top-bar">
            <div class="page-title">
                <h1>Add New Service</h1>
                <p>Create a new service listing for tourists</p>
            </div>
            <div class="top-bar-actions">
                <a href="manage_services.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i>
                    Back to Services
                </a>
            </div>
        </div>

        <div class="form-container">
            <div class="form-header">
                <h2><i class="fas fa-plus-circle"></i> Service Details</h2>
                <p>Fill in the information below to create your service listing</p>
            </div>

            <div class="form-body">
                    <form action="../actions/add_service_action.php" method="POST" enctype="multipart/form-data" id="addServiceForm">

                        <!-- Basic Information -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class="fas fa-info-circle"></i>
                                Basic Information
                            </div>

                            <div class="form-group">
                                <label class="form-label">Service Title <span class="required">*</span></label>
                                <input type="text" class="form-control" id="service_title" name="service_title" placeholder="e.g., Accra City Walking Tour" required>
                                <p class="form-hint">Give your service a clear, descriptive title that tourists will easily understand</p>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Category <span class="required">*</span></label>
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

                            <div class="form-group">
                                <label class="form-label">Link to Festival (Optional)</label>
                                <select class="form-select" id="festival_id" name="festival_id">
                                    <option value="">Not linked to any festival</option>
                                    <?php if ($festivals): ?>
                                        <?php foreach ($festivals as $festival): ?>
                                            <option value="<?php echo $festival['festival_id']; ?>">
                                                <?php echo htmlspecialchars($festival['festival_name']); ?>
                                                - <?php echo date('M d, Y', strtotime($festival['start_date'])); ?>
                                                (<?php echo htmlspecialchars($festival['region']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <p class="form-hint">
                                    <i class="fas fa-calendar-alt"></i> Link your service to a Ghanaian festival to boost visibility during festival season
                                </p>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Description <span class="required">*</span></label>
                                <textarea class="form-control" id="service_description" name="service_description" rows="5" placeholder="Describe what your service includes, what makes it special, and what tourists can expect..." required></textarea>
                                <p class="form-hint">Be detailed - tourists want to know exactly what they'll experience</p>
                            </div>
                        </div>

                        <!-- Pricing -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class="fas fa-tag"></i>
                                Pricing
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Price (GHS) <span class="required">*</span></label>
                                    <input type="number" class="form-control" id="base_price" name="base_price" step="0.01" min="0" placeholder="0.00" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Pricing Unit <span class="required">*</span></label>
                                    <select class="form-select" id="pricing_unit" name="pricing_unit" required>
                                        <option value="per_hour">Per Hour</option>
                                        <option value="per_day">Per Day</option>
                                        <option value="per_person">Per Person</option>
                                        <option value="flat_rate">Flat Rate</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class="fas fa-map-marker-alt"></i>
                                Location & Coverage
                            </div>

                            <div class="form-group">
                                <label class="form-label">Service Location <span class="required">*</span></label>
                                <input type="text" class="form-control" id="service_location" name="service_location" placeholder="e.g., Accra, Cape Coast, Kumasi" required>
                                <p class="form-hint">Where is this service primarily provided?</p>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Available Regions</label>
                                <div class="checkbox-grid">
                                    <div class="checkbox-item" onclick="toggleCheckbox(this)">
                                        <input type="checkbox" name="regions[]" value="Greater Accra" id="region1">
                                        <label for="region1">Greater Accra</label>
                                    </div>
                                    <div class="checkbox-item" onclick="toggleCheckbox(this)">
                                        <input type="checkbox" name="regions[]" value="Central" id="region2">
                                        <label for="region2">Central</label>
                                    </div>
                                    <div class="checkbox-item" onclick="toggleCheckbox(this)">
                                        <input type="checkbox" name="regions[]" value="Ashanti" id="region3">
                                        <label for="region3">Ashanti</label>
                                    </div>
                                    <div class="checkbox-item" onclick="toggleCheckbox(this)">
                                        <input type="checkbox" name="regions[]" value="Northern" id="region4">
                                        <label for="region4">Northern</label>
                                    </div>
                                    <div class="checkbox-item" onclick="toggleCheckbox(this)">
                                        <input type="checkbox" name="regions[]" value="Eastern" id="region5">
                                        <label for="region5">Eastern</label>
                                    </div>
                                    <div class="checkbox-item" onclick="toggleCheckbox(this)">
                                        <input type="checkbox" name="regions[]" value="Western" id="region6">
                                        <label for="region6">Western</label>
                                    </div>
                                    <div class="checkbox-item" onclick="toggleCheckbox(this)">
                                        <input type="checkbox" name="regions[]" value="Volta" id="region7">
                                        <label for="region7">Volta</label>
                                    </div>
                                    <div class="checkbox-item" onclick="toggleCheckbox(this)">
                                        <input type="checkbox" name="regions[]" value="Upper East" id="region8">
                                        <label for="region8">Upper East</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Capacity -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class="fas fa-users"></i>
                                Capacity
                            </div>

                            <div class="form-group">
                                <label class="form-label">Maximum Capacity</label>
                                <input type="number" class="form-control" id="max_capacity" name="max_capacity" min="1" placeholder="How many people can you serve at once?">
                                <p class="form-hint">Leave empty if there's no limit</p>
                            </div>
                        </div>

                        <!-- Images -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class="fas fa-images"></i>
                                Service Images
                            </div>

                            <div class="upload-area" id="uploadArea">
                                <div class="upload-icon">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                                <div class="upload-text">
                                    <h4>Upload Images</h4>
                                    <p>Drag and drop or click to select (3-5 images recommended)</p>
                                    <p style="font-size: 0.8rem; margin-top: 8px;">JPG, PNG or WEBP - Max 5MB each</p>
                                </div>
                                <input type="file" id="service_images" name="service_images[]" accept="image/jpeg,image/jpg,image/png,image/webp" multiple style="display: none;">
                            </div>
                            <div class="image-preview-grid" id="previewGrid"></div>
                        </div>

                        <!-- Info Alert -->
                        <div class="info-alert">
                            <i class="fas fa-info-circle"></i>
                            <p>Your service will be reviewed by our team before going live. This typically takes 24-48 hours. You'll receive a notification once approved.</p>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check"></i>
                                Create Service
                            </button>
                            <a href="provider_dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
        </div>
    </main>

    <script>
        // Toggle checkbox styling
        function toggleCheckbox(element) {
            const checkbox = element.querySelector('input[type="checkbox"]');
            checkbox.checked = !checkbox.checked;
            element.classList.toggle('checked', checkbox.checked);
        }

        // Image upload functionality
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('service_images');
        const previewGrid = document.getElementById('previewGrid');
        let selectedFiles = [];
        let mainImageIndex = 0;
        const MAX_FILES = 5;
        const MAX_FILE_SIZE = 5 * 1024 * 1024;

        uploadArea.addEventListener('click', () => fileInput.click());

        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            handleFiles(Array.from(e.dataTransfer.files));
        });

        fileInput.addEventListener('change', (e) => {
            handleFiles(Array.from(e.target.files));
        });

        function handleFiles(files) {
            const validFiles = files.filter(file => {
                if (!file.type.startsWith('image/')) {
                    alert(`${file.name} is not a valid image file`);
                    return false;
                }
                if (file.size > MAX_FILE_SIZE) {
                    alert(`${file.name} is too large (max 5MB)`);
                    return false;
                }
                return true;
            });

            if (selectedFiles.length + validFiles.length > MAX_FILES) {
                alert(`Maximum ${MAX_FILES} images allowed`);
                return;
            }

            selectedFiles = [...selectedFiles, ...validFiles];
            displayPreviews();
        }

        function displayPreviews() {
            previewGrid.innerHTML = '';

            selectedFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const item = document.createElement('div');
                    item.className = 'preview-item' + (index === mainImageIndex ? ' main' : '');
                    item.innerHTML = `
                        <img src="${e.target.result}" alt="Preview">
                        ${index === mainImageIndex ? '<span class="preview-badge">Main</span>' : ''}
                        <div class="preview-actions">
                            <button type="button" class="preview-btn remove" onclick="removeImage(${index})" title="Remove">
                                <i class="fas fa-times"></i>
                            </button>
                            ${index !== mainImageIndex ? `<button type="button" class="preview-btn set-main" onclick="setMainImage(${index})" title="Set as main"><i class="fas fa-star"></i></button>` : ''}
                        </div>
                    `;
                    previewGrid.appendChild(item);
                };
                reader.readAsDataURL(file);
            });

            // Update file input
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => dataTransfer.items.add(file));
            fileInput.files = dataTransfer.files;
        }

        function removeImage(index) {
            selectedFiles.splice(index, 1);
            if (mainImageIndex === index) mainImageIndex = 0;
            else if (mainImageIndex > index) mainImageIndex--;
            displayPreviews();
        }

        function setMainImage(index) {
            mainImageIndex = index;
            displayPreviews();
        }
    </script>
</body>
</html>
