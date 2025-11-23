<?php
require_once '../settings/core.php';
require_once '../classes/service_provider_class.php';
require_once '../classes/service_class.php';
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

// Get service ID
$service_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$service_id) {
    $_SESSION['error'] = 'Invalid service ID';
    header("Location: manage_services.php");
    exit();
}

// Get service details
$service_class = new Service();
$service = $service_class->get_service_by_id($service_id);

// Verify ownership
if (!$service || $service['provider_id'] != $provider['provider_id']) {
    $_SESSION['error'] = 'Service not found or access denied';
    header("Location: manage_services.php");
    exit();
}

// Get categories
$categories = get_all_service_categories_ctr();

// Parse existing data
$existing_images = json_decode($service['service_images'], true) ?: [];
$existing_regions = json_decode($service['available_regions'], true) ?: [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Service - TourLink Provider</title>
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

        .nav-item i { width: 20px; text-align: center; font-size: 1rem; }

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
        }

        .provider-info h4 { font-size: 0.85rem; font-weight: 600; margin: 0; }
        .provider-info span { font-size: 0.75rem; color: var(--text-secondary); }

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

        .btn-back:hover { background: var(--border); color: var(--text-primary); }

        .form-container { padding: 32px; }

        .form-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            padding: 24px 32px;
            color: white;
            border-radius: 12px 12px 0 0;
        }

        .form-header h2 { font-size: 1.25rem; font-weight: 600; margin: 0; }
        .form-header p { font-size: 0.875rem; opacity: 0.9; margin: 4px 0 0 0; }

        .form-body {
            background: var(--bg-card);
            padding: 32px;
            border: 1px solid var(--border);
            border-top: none;
            border-radius: 0 0 12px 12px;
        }

        .form-section {
            margin-bottom: 32px;
            padding-bottom: 32px;
            border-bottom: 1px solid var(--border);
        }

        .form-section:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }

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

        .form-group { margin-bottom: 20px; }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .form-label .required { color: var(--danger); }

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

        textarea.form-control { resize: vertical; min-height: 120px; }

        .form-hint { font-size: 0.8rem; color: var(--text-secondary); margin-top: 6px; }

        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

        .checkbox-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }

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

        .checkbox-item:hover { border-color: var(--primary); }
        .checkbox-item input[type="checkbox"] { width: 18px; height: 18px; accent-color: var(--primary); }
        .checkbox-item label { font-size: 0.875rem; font-weight: 500; cursor: pointer; margin: 0; }
        .checkbox-item.checked { background: rgba(15, 118, 110, 0.05); border-color: var(--primary); }

        .existing-images { margin-bottom: 20px; }
        .existing-images h4 { font-size: 0.9rem; font-weight: 600; margin-bottom: 12px; }

        .existing-images-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 12px;
        }

        .existing-image-item {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid var(--border);
            aspect-ratio: 1;
        }

        .existing-image-item img { width: 100%; height: 100%; object-fit: cover; }

        .upload-area {
            border: 2px dashed var(--border);
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: var(--bg-main);
        }

        .upload-area:hover { border-color: var(--primary); background: rgba(15, 118, 110, 0.02); }

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

        .upload-icon i { font-size: 1.5rem; color: white; }
        .upload-text h4 { font-size: 1rem; font-weight: 600; margin: 0 0 4px 0; }
        .upload-text p { font-size: 0.85rem; color: var(--text-secondary); margin: 0; }

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

        .preview-item img { width: 100%; height: 100%; object-fit: cover; }

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

        .info-alert i { color: var(--primary); font-size: 1.1rem; margin-top: 2px; }
        .info-alert p { font-size: 0.875rem; margin: 0; line-height: 1.5; }

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

        .btn-secondary:hover { background: var(--border); }

        @media (max-width: 1024px) {
            .sidebar { width: 80px; }
            .sidebar-logo-text, .sidebar-logo-badge, .nav-section-title, .nav-item span, .provider-info { display: none; }
            .main-content { margin-left: 80px; }
        }

        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; }
            .form-container { padding: 16px; }
            .form-body { padding: 20px; }
            .form-row { grid-template-columns: 1fr; }
            .checkbox-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
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
                <a href="manage_services.php" class="nav-item active">
                    <i class="fas fa-concierge-bell"></i>
                    <span>My Services</span>
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Management</div>
                <a href="add_service.php" class="nav-item">
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

    <main class="main-content">
        <div class="top-bar">
            <div class="page-title">
                <h1>Edit Service</h1>
                <p>Update your service listing details</p>
            </div>
            <a href="manage_services.php" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Back to Services
            </a>
        </div>

        <div class="form-container">
            <div class="form-header">
                <h2><i class="fas fa-edit"></i> Edit Service Details</h2>
                <p>Make changes to your service listing</p>
            </div>

            <div class="form-body">
                <form action="../actions/edit_service_action.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="service_id" value="<?php echo $service['service_id']; ?>">

                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-info-circle"></i>
                            Basic Information
                        </div>

                        <div class="form-group">
                            <label class="form-label">Service Title <span class="required">*</span></label>
                            <input type="text" class="form-control" name="service_title"
                                   value="<?php echo htmlspecialchars($service['service_title']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Category <span class="required">*</span></label>
                            <select class="form-select" name="category_id" required>
                                <option value="">Select a category</option>
                                <?php if ($categories): ?>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['category_id']; ?>"
                                            <?php echo ($service['category_id'] == $category['category_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['category_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Description <span class="required">*</span></label>
                            <textarea class="form-control" name="service_description" rows="5" required><?php echo htmlspecialchars($service['service_description']); ?></textarea>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-tag"></i>
                            Pricing
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Price (GHS) <span class="required">*</span></label>
                                <input type="number" class="form-control" name="base_price" step="0.01" min="0"
                                       value="<?php echo $service['base_price']; ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Pricing Unit <span class="required">*</span></label>
                                <select class="form-select" name="pricing_unit" required>
                                    <option value="per_hour" <?php echo ($service['pricing_unit'] == 'per_hour') ? 'selected' : ''; ?>>Per Hour</option>
                                    <option value="per_day" <?php echo ($service['pricing_unit'] == 'per_day') ? 'selected' : ''; ?>>Per Day</option>
                                    <option value="per_person" <?php echo ($service['pricing_unit'] == 'per_person') ? 'selected' : ''; ?>>Per Person</option>
                                    <option value="flat_rate" <?php echo ($service['pricing_unit'] == 'flat_rate') ? 'selected' : ''; ?>>Flat Rate</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-map-marker-alt"></i>
                            Location & Coverage
                        </div>

                        <div class="form-group">
                            <label class="form-label">Service Location <span class="required">*</span></label>
                            <input type="text" class="form-control" name="service_location"
                                   value="<?php echo htmlspecialchars($service['service_location']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Available Regions</label>
                            <div class="checkbox-grid">
                                <?php
                                $all_regions = ['Greater Accra', 'Central', 'Ashanti', 'Northern', 'Eastern', 'Western', 'Volta', 'Upper East'];
                                foreach ($all_regions as $index => $region):
                                    $checked = in_array($region, $existing_regions) ? 'checked' : '';
                                ?>
                                <div class="checkbox-item <?php echo $checked ? 'checked' : ''; ?>" onclick="toggleCheckbox(this)">
                                    <input type="checkbox" name="regions[]" value="<?php echo $region; ?>" id="region<?php echo $index; ?>" <?php echo $checked; ?>>
                                    <label for="region<?php echo $index; ?>"><?php echo $region; ?></label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-users"></i>
                            Capacity
                        </div>

                        <div class="form-group">
                            <label class="form-label">Maximum Capacity</label>
                            <input type="number" class="form-control" name="max_capacity" min="1"
                                   value="<?php echo $service['max_capacity']; ?>">
                            <p class="form-hint">Leave empty if there's no limit</p>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-images"></i>
                            Service Images
                        </div>

                        <?php if (!empty($existing_images)): ?>
                        <div class="existing-images">
                            <h4>Current Images</h4>
                            <div class="existing-images-grid">
                                <?php foreach ($existing_images as $img): ?>
                                <div class="existing-image-item">
                                    <img src="../uploads/services/<?php echo htmlspecialchars($img); ?>" alt="Service image">
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <p class="form-hint" style="margin-top: 12px;">Upload new images below to replace existing ones, or leave empty to keep current images.</p>
                        </div>
                        <?php endif; ?>

                        <div class="upload-area" id="uploadArea">
                            <div class="upload-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div class="upload-text">
                                <h4>Upload New Images</h4>
                                <p>Drag and drop or click to select</p>
                            </div>
                            <input type="file" id="service_images" name="service_images[]" accept="image/*" multiple style="display: none;">
                        </div>
                        <div class="image-preview-grid" id="previewGrid"></div>
                    </div>

                    <div class="info-alert">
                        <i class="fas fa-info-circle"></i>
                        <p>Changes may require re-approval if they significantly modify the service offering.</p>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Save Changes
                        </button>
                        <a href="manage_services.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        function toggleCheckbox(element) {
            const checkbox = element.querySelector('input[type="checkbox"]');
            checkbox.checked = !checkbox.checked;
            element.classList.toggle('checked', checkbox.checked);
        }

        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('service_images');
        const previewGrid = document.getElementById('previewGrid');

        uploadArea.addEventListener('click', () => fileInput.click());

        fileInput.addEventListener('change', (e) => {
            previewGrid.innerHTML = '';
            Array.from(e.target.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = (ev) => {
                    const item = document.createElement('div');
                    item.className = 'preview-item';
                    item.innerHTML = `<img src="${ev.target.result}" alt="Preview">`;
                    previewGrid.appendChild(item);
                };
                reader.readAsDataURL(file);
            });
        });
    </script>
</body>
</html>
