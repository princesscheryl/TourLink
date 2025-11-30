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
    <link href="../css/provider_sidebar.css" rel="stylesheet">
    <link href="../css/edit_service.css" rel="stylesheet">
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
                                <?php 
                                require_once '../classes/hosted_upload_class.php';
                                foreach ($existing_images as $img): 
                                    $img_path = HostedUpload::getImageUrl($img, '../');
                                ?>
                                <div class="existing-image-item">
                                    <img src="<?php echo htmlspecialchars($img_path); ?>" alt="Service image" onerror="this.parentElement.innerHTML='<div style=\'display:flex;align-items:center;justify-content:center;height:100%;background:#f1f5f9;color:#94a3b8;\'><i class=\'fas fa-image\'></i></div>'">
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
