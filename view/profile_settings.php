<?php
require_once '../settings/core.php';
require_once '../classes/tourlink_user_class.php';
require_once '../controllers/booking_controller.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

// Get user info
$user_class = new TourlinkUser();
$user = $user_class->get_user_by_id($_SESSION['user_id']);

if (!$user) {
    header("Location: ../login/login.php");
    exit();
}

// Get recent bookings
$recent_bookings = [];
if ($_SESSION['user_type'] !== 'provider') {
    $all_bookings = get_tourist_bookings_ctr($_SESSION['user_id']);
    $recent_bookings = array_slice($all_bookings ?: [], 0, 5);
}

// Get active tab
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'profile';

// Countries list
$countries = [
    'GH' => 'Ghana',
    'NG' => 'Nigeria',
    'KE' => 'Kenya',
    'ZA' => 'South Africa',
    'US' => 'United States',
    'GB' => 'United Kingdom',
    'CA' => 'Canada',
    'AU' => 'Australia',
    'DE' => 'Germany',
    'FR' => 'France',
    'NL' => 'Netherlands',
    'IT' => 'Italy',
    'ES' => 'Spain',
    'BR' => 'Brazil',
    'IN' => 'India',
    'CN' => 'China',
    'JP' => 'Japan',
    'AE' => 'United Arab Emirates',
    'OTHER' => 'Other'
];

// Languages list
$languages = [
    'en' => 'English',
    'fr' => 'Français',
    'es' => 'Español',
    'tw' => 'Twi',
    'ga' => 'Ga',
    'de' => 'Deutsch',
    'pt' => 'Português'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings - TourLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="../css/navigation.css" rel="stylesheet">
    <link href="../css/footer.css" rel="stylesheet">
    <link href="../css/dark-mode.css" rel="stylesheet">
    <script src="../js/dark-mode.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f5f6fa; min-height: 100vh; }

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, #2d6a4f 0%, #1b4332 100%);
            color: white;
            padding: 120px 0 50px;
        }
        .page-header h1 { font-size: 2rem; font-weight: 700; margin-bottom: 8px; }
        .page-header p { opacity: 0.9; }

        .main-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* Profile Layout */
        .profile-layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 24px;
            margin-top: -40px;
            padding-bottom: 60px;
        }

        /* Sidebar */
        .profile-sidebar {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .profile-card {
            padding: 30px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 0 auto 16px;
            border: 4px solid #2d6a4f;
            overflow: hidden;
            background: #f0f0f0;
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-avatar-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #2d6a4f 0%, #1b4332 100%);
            color: white;
            font-size: 2.5rem;
            font-weight: 700;
        }

        .profile-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 4px;
        }

        .profile-email {
            color: #666;
            font-size: 0.9rem;
        }

        .profile-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 10px;
            background: #e8f5e9;
            color: #2d6a4f;
        }

        /* Tab Navigation */
        .tab-nav {
            padding: 10px 0;
        }

        .tab-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 24px;
            color: #555;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .tab-nav a:hover {
            background: #f8f9fa;
            color: #2d6a4f;
        }

        .tab-nav a.active {
            background: #e8f5e9;
            color: #2d6a4f;
            border-left-color: #2d6a4f;
        }

        .tab-nav a i {
            width: 20px;
            text-align: center;
        }

        /* Main Content */
        .profile-content {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }

        .content-header {
            padding: 24px 30px;
            border-bottom: 1px solid #eee;
        }

        .content-header h2 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1a1a1a;
            margin: 0;
        }

        .content-header p {
            color: #666;
            font-size: 0.9rem;
            margin: 4px 0 0;
        }

        .content-body {
            padding: 30px;
        }

        /* Profile Picture Section */
        .picture-section {
            display: flex;
            align-items: center;
            gap: 24px;
            padding: 24px;
            background: #f8f9fa;
            border-radius: 12px;
            margin-bottom: 30px;
        }

        .picture-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid #2d6a4f;
            overflow: hidden;
            flex-shrink: 0;
            background: white;
        }

        .picture-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .picture-preview-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #2d6a4f 0%, #1b4332 100%);
            color: white;
            font-size: 2.5rem;
            font-weight: 600;
        }

        .picture-actions h4 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .picture-actions p {
            color: #666;
            font-size: 0.85rem;
            margin-bottom: 12px;
        }

        .picture-actions .btn-group {
            display: flex;
            gap: 10px;
        }

        /* Form Styles */
        .form-section {
            margin-bottom: 30px;
        }

        .form-section-title {
            font-size: 1rem;
            font-weight: 600;
            color: #1b4332;
            margin-bottom: 16px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e8f5e9;
        }

        .form-label {
            font-weight: 500;
            color: #333;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px 16px;
            transition: border-color 0.2s;
        }

        .form-control:focus, .form-select:focus {
            border-color: #2d6a4f;
            box-shadow: 0 0 0 3px rgba(45, 106, 79, 0.1);
        }

        .btn-primary {
            background: #2d6a4f;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
        }

        .btn-primary:hover {
            background: #1b4332;
        }

        .btn-outline-danger {
            border: 2px solid #dc3545;
            color: #dc3545;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
        }

        .btn-outline-danger:hover {
            background: #dc3545;
            color: white;
        }

        /* Security Section */
        .security-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 16px;
        }

        .security-item-info h5 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .security-item-info p {
            color: #666;
            font-size: 0.85rem;
            margin: 0;
        }

        .security-item .btn {
            white-space: nowrap;
        }

        /* Bookings Mini List */
        .booking-mini {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 12px;
        }

        .booking-mini-info h6 {
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .booking-mini-info p {
            color: #666;
            font-size: 0.8rem;
            margin: 0;
        }

        .booking-mini-status {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending { background: #fef3c7; color: #92400e; }
        .status-confirmed { background: #d1fae5; color: #065f46; }
        .status-completed { background: #d1fae5; color: #059669; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        .status-in_progress { background: #dbeafe; color: #1e40af; }

        /* Responsive */
        @media (max-width: 768px) {
            .profile-layout {
                grid-template-columns: 1fr;
            }
            .profile-sidebar {
                position: static;
            }
            .picture-section {
                flex-direction: column;
                text-align: center;
            }
        }

        /* Hide file input */
        .file-input-hidden { display: none; }
    </style>
</head>
<body>
    <?php include '../includes/navigation.php'; ?>

    <div class="page-header">
        <div class="main-container">
            <h1><i class="fa fa-user-circle me-2"></i>Account Settings</h1>
            <p>Manage your profile, preferences, and security settings</p>
        </div>
    </div>

    <div class="main-container">
        <div class="profile-layout">
            <!-- Sidebar -->
            <div class="profile-sidebar">
                <div class="profile-card">
                    <div class="profile-avatar">
                        <?php if (!empty($user['profile_image'])): ?>
                            <img src="../<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile">
                        <?php else: ?>
                            <div class="profile-avatar-placeholder">
                                <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="profile-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                    <div class="profile-email"><?php echo htmlspecialchars($user['email']); ?></div>
                    <span class="profile-badge">
                        <?php echo $user['user_type'] === 'provider' ? 'Service Provider' : 'Tourist'; ?>
                    </span>
                </div>

                <nav class="tab-nav">
                    <a href="?tab=profile" class="<?php echo $active_tab === 'profile' ? 'active' : ''; ?>">
                        <i class="fa fa-user"></i> Profile
                    </a>
                    <a href="?tab=preferences" class="<?php echo $active_tab === 'preferences' ? 'active' : ''; ?>">
                        <i class="fa fa-sliders-h"></i> Preferences
                    </a>
                    <?php if ($user['user_type'] !== 'provider'): ?>
                    <a href="?tab=bookings" class="<?php echo $active_tab === 'bookings' ? 'active' : ''; ?>">
                        <i class="fa fa-calendar-check"></i> My Bookings
                    </a>
                    <?php endif; ?>
                    <a href="?tab=security" class="<?php echo $active_tab === 'security' ? 'active' : ''; ?>">
                        <i class="fa fa-shield-alt"></i> Security
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="profile-content">
                <?php if ($active_tab === 'profile'): ?>
                <!-- Profile Tab -->
                <div class="content-header">
                    <h2>Profile Information</h2>
                    <p>Update your personal details and profile picture</p>
                </div>
                <div class="content-body">
                    <!-- Profile Picture -->
                    <div class="picture-section">
                        <div class="picture-preview" id="picturePreview">
                            <?php if (!empty($user['profile_image'])): ?>
                                <img src="../<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile" id="previewImg">
                            <?php else: ?>
                                <div class="picture-preview-placeholder" id="previewPlaceholder">
                                    <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="picture-actions">
                            <h4>Profile Picture</h4>
                            <p>JPG, PNG or GIF. Max size 2MB.</p>
                            <form id="uploadPictureForm" enctype="multipart/form-data">
                                <input type="file" id="profilePicture" name="profile_picture" class="file-input-hidden" accept="image/*">
                                <div class="btn-group">
                                    <label for="profilePicture" class="btn btn-primary">
                                        <i class="fa fa-upload me-1"></i> Upload
                                    </label>
                                    <?php if (!empty($user['profile_image'])): ?>
                                    <button type="button" class="btn btn-outline-danger" onclick="removeProfilePicture()">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Personal Info Form -->
                    <form id="userInfoForm">
                        <div class="form-section">
                            <h5 class="form-section-title">Personal Information</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">First Name</label>
                                    <input type="text" class="form-control" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" class="form-control" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="+233 XX XXX XXXX">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                <small class="text-muted">To change your email, go to Security settings</small>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save me-2"></i>Save Changes
                        </button>
                    </form>
                </div>

                <?php elseif ($active_tab === 'preferences'): ?>
                <!-- Preferences Tab -->
                <div class="content-header">
                    <h2>Preferences</h2>
                    <p>Customize your TourLink experience</p>
                </div>
                <div class="content-body">
                    <form id="preferencesForm">
                        <div class="form-section">
                            <h5 class="form-section-title">Language & Region</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Preferred Language</label>
                                    <select class="form-select" name="preferred_language">
                                        <?php foreach ($languages as $code => $name): ?>
                                            <option value="<?php echo $code; ?>" <?php echo (isset($user['preferred_language']) && $user['preferred_language'] === $code) ? 'selected' : ''; ?>>
                                                <?php echo $name; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Country</label>
                                    <select class="form-select" name="country">
                                        <option value="">Select country...</option>
                                        <?php foreach ($countries as $code => $name): ?>
                                            <option value="<?php echo $code; ?>" <?php echo (isset($user['country']) && $user['country'] === $code) ? 'selected' : ''; ?>>
                                                <?php echo $name; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h5 class="form-section-title">Notifications</h5>
                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" id="emailBookings" name="notify_bookings" checked>
                                <label class="form-check-label" for="emailBookings">Email me about booking updates</label>
                            </div>
                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" id="emailPromo" name="notify_promo">
                                <label class="form-check-label" for="emailPromo">Email me about promotions and offers</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save me-2"></i>Save Preferences
                        </button>
                    </form>
                </div>

                <?php elseif ($active_tab === 'bookings'): ?>
                <!-- Bookings Tab -->
                <div class="content-header">
                    <h2>My Bookings</h2>
                    <p>View your recent booking activity</p>
                </div>
                <div class="content-body">
                    <?php if (empty($recent_bookings)): ?>
                        <div class="text-center py-5">
                            <i class="far fa-calendar-alt fa-3x text-muted mb-3"></i>
                            <h5>No bookings yet</h5>
                            <p class="text-muted">You haven't made any bookings yet.</p>
                            <a href="all_services.php" class="btn btn-primary">Browse Services</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_bookings as $booking): ?>
                            <div class="booking-mini">
                                <div class="booking-mini-info">
                                    <h6><?php echo htmlspecialchars($booking['service_title']); ?></h6>
                                    <p>
                                        <?php echo date('M d, Y', strtotime($booking['service_date'])); ?>
                                        &bull; <?php echo $booking['number_of_people']; ?> guest(s)
                                        &bull; GHS <?php echo number_format($booking['total_amount'], 2); ?>
                                    </p>
                                </div>
                                <span class="booking-mini-status status-<?php echo $booking['booking_status']; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $booking['booking_status'])); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>

                        <div class="mt-4">
                            <a href="my_bookings.php" class="btn btn-primary">
                                <i class="fa fa-calendar-alt me-2"></i>View All Bookings
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <?php elseif ($active_tab === 'security'): ?>
                <!-- Security Tab -->
                <div class="content-header">
                    <h2>Security Settings</h2>
                    <p>Manage your account security</p>
                </div>
                <div class="content-body">
                    <div class="security-item">
                        <div class="security-item-info">
                            <h5><i class="fa fa-key me-2 text-success"></i>Password</h5>
                            <p>Change your account password</p>
                        </div>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                            Change Password
                        </button>
                    </div>

                    <div class="security-item">
                        <div class="security-item-info">
                            <h5><i class="fa fa-envelope me-2 text-success"></i>Email Address</h5>
                            <p>Current: <?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#changeEmailModal">
                            Change Email
                        </button>
                    </div>

                    <div class="security-item">
                        <div class="security-item-info">
                            <h5><i class="fa fa-clock me-2 text-success"></i>Last Login</h5>
                            <p><?php echo isset($user['last_login']) ? date('F j, Y g:i A', strtotime($user['last_login'])) : 'Not available'; ?></p>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="security-item" style="background: #fff5f5;">
                        <div class="security-item-info">
                            <h5 class="text-danger"><i class="fa fa-exclamation-triangle me-2"></i>Delete Account</h5>
                            <p>Permanently delete your account and all data</p>
                        </div>
                        <button type="button" class="btn btn-outline-danger" onclick="confirmDeleteAccount()">
                            Delete Account
                        </button>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-key me-2"></i>Change Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="changePasswordForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="new_password" id="newPassword" required minlength="8">
                            <small class="text-muted">Minimum 8 characters</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" name="confirm_password" id="confirmPassword" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Change Email Modal -->
    <div class="modal fade" id="changeEmailModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-envelope me-2"></i>Change Email</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="changeEmailForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">New Email Address</label>
                            <input type="email" class="form-control" name="new_email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" class="form-control" name="password" required>
                            <small class="text-muted">Enter your password to confirm this change</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Email</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Profile picture preview and upload
        $('#profilePicture').on('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    Swal.fire({
                        icon: 'error',
                        title: 'File Too Large',
                        text: 'Image must be less than 2MB',
                        confirmButtonColor: '#2d6a4f'
                    });
                    this.value = '';
                    return;
                }

                // Auto-upload immediately
                const formData = new FormData($('#uploadPictureForm')[0]);

                $.ajax({
                    url: '../actions/upload_profile_picture_action.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message,
                                confirmButtonColor: '#2d6a4f'
                            }).then(() => location.reload());
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message,
                                confirmButtonColor: '#2d6a4f'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred. Please try again.',
                            confirmButtonColor: '#2d6a4f'
                        });
                    }
                });
            }
        });

        // Update user info
        $('#userInfoForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: '../actions/update_user_info_action.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    Swal.fire({
                        icon: response.status === 'success' ? 'success' : 'error',
                        title: response.status === 'success' ? 'Success!' : 'Error',
                        text: response.message,
                        confirmButtonColor: '#2d6a4f'
                    });
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred.',
                        confirmButtonColor: '#2d6a4f'
                    });
                }
            });
        });

        // Save preferences
        $('#preferencesForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: '../actions/update_preferences_action.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    Swal.fire({
                        icon: response.status === 'success' ? 'success' : 'error',
                        title: response.status === 'success' ? 'Saved!' : 'Error',
                        text: response.message,
                        confirmButtonColor: '#2d6a4f'
                    });
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred.',
                        confirmButtonColor: '#2d6a4f'
                    });
                }
            });
        });

        // Change password
        $('#changePasswordForm').on('submit', function(e) {
            e.preventDefault();

            if ($('#newPassword').val() !== $('#confirmPassword').val()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Password Mismatch',
                    text: 'New passwords do not match',
                    confirmButtonColor: '#2d6a4f'
                });
                return;
            }

            $.ajax({
                url: '../actions/change_password_action.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        bootstrap.Modal.getInstance(document.getElementById('changePasswordModal')).hide();
                        Swal.fire({
                            icon: 'success',
                            title: 'Password Changed',
                            text: response.message,
                            confirmButtonColor: '#2d6a4f'
                        });
                        $('#changePasswordForm')[0].reset();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message,
                            confirmButtonColor: '#2d6a4f'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred.',
                        confirmButtonColor: '#2d6a4f'
                    });
                }
            });
        });

        // Change email
        $('#changeEmailForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: '../actions/change_email_action.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        bootstrap.Modal.getInstance(document.getElementById('changeEmailModal')).hide();
                        Swal.fire({
                            icon: 'success',
                            title: 'Email Updated',
                            text: response.message,
                            confirmButtonColor: '#2d6a4f'
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message,
                            confirmButtonColor: '#2d6a4f'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred.',
                        confirmButtonColor: '#2d6a4f'
                    });
                }
            });
        });

        // Remove profile picture
        function removeProfilePicture() {
            Swal.fire({
                title: 'Remove Profile Picture?',
                text: 'Are you sure?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Yes, remove it'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '../actions/remove_profile_picture_action.php',
                        type: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Removed!',
                                    text: response.message,
                                    confirmButtonColor: '#2d6a4f'
                                }).then(() => location.reload());
                            }
                        }
                    });
                }
            });
        }

        // Delete account
        function confirmDeleteAccount() {
            Swal.fire({
                title: 'Delete Account?',
                html: '<p>This action <strong>cannot be undone</strong>. All your data will be permanently deleted.</p><input type="password" id="deletePassword" class="swal2-input" placeholder="Enter your password">',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Delete My Account',
                preConfirm: () => {
                    const password = document.getElementById('deletePassword').value;
                    if (!password) {
                        Swal.showValidationMessage('Please enter your password');
                        return false;
                    }
                    return password;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '../actions/delete_account_action.php',
                        type: 'POST',
                        data: { password: result.value },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Account Deleted',
                                    text: 'Your account has been deleted.',
                                    confirmButtonColor: '#2d6a4f'
                                }).then(() => {
                                    window.location.href = '../index_tourlink.php';
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message,
                                    confirmButtonColor: '#2d6a4f'
                                });
                            }
                        }
                    });
                }
            });
        }
    </script>
</body>
</html>
