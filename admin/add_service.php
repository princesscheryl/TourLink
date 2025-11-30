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

// Set current page for sidebar
$current_page = 'add_service';
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
    <link href="../css/provider_sidebar.css" rel="stylesheet">
    <link href="../css/add_service.css" rel="stylesheet">
</head>
<body>
    <?php
    // Include reusable sidebar component
    include '../includes/provider_sidebar.php';
    ?>

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
