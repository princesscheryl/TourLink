<?php
require_once '../settings/core.php';
require_once '../classes/service_provider_class.php';

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

// Set current page for sidebar
$current_page = 'profile';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Profile - TourLink Provider</title>
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

        .content-area { padding: 32px; }

        .profile-grid {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 24px;
        }

        .profile-sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .profile-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
            text-align: center;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 2.5rem;
            color: white;
            font-weight: 700;
        }

        .profile-card h3 {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0 0 4px 0;
        }

        .profile-card .business-type {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-bottom: 16px;
        }

        .verification-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .verification-badge.pending {
            background: rgba(245, 158, 11, 0.1);
            color: #d97706;
        }

        .verification-badge.verified {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
        }

        .completion-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
        }

        .completion-card h4 {
            font-size: 0.9rem;
            font-weight: 600;
            margin: 0 0 16px 0;
        }

        .completion-bar {
            height: 8px;
            background: var(--bg-main);
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 8px;
        }

        .completion-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
            border-radius: 4px;
            transition: width 0.3s;
        }

        .completion-text {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        .form-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
        }

        .form-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
        }

        .form-header h2 {
            font-size: 1rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-header h2 i {
            color: var(--primary);
        }

        .form-body { padding: 24px; }

        .form-section {
            margin-bottom: 32px;
        }

        .form-section:last-child { margin-bottom: 0; }

        .section-title {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--border);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-group { margin-bottom: 16px; }

        .form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 6px;
        }

        .form-label .required { color: var(--danger); }

        .form-control, .form-select {
            width: 100%;
            padding: 10px 14px;
            font-size: 0.875rem;
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

        .form-control:read-only {
            background: var(--bg-main);
            color: var(--text-secondary);
        }

        .form-hint {
            font-size: 0.75rem;
            color: var(--text-secondary);
            margin-top: 4px;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            font-size: 0.875rem;
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

        .alert-toast {
            position: fixed;
            top: 24px;
            right: 24px;
            padding: 16px 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.875rem;
            font-weight: 500;
            z-index: 1000;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .alert-toast.success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-toast.error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

        @media (max-width: 1200px) {
            .profile-grid { grid-template-columns: 1fr; }
            .profile-sidebar { flex-direction: row; }
            .profile-card, .completion-card { flex: 1; }
        }

        @media (max-width: 1024px) {
            .sidebar { width: 80px; }
            .sidebar-logo-text, .sidebar-logo-badge, .nav-section-title, .nav-item span, .provider-info { display: none; }
            .main-content { margin-left: 80px; }
        }

        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; }
            .content-area { padding: 16px; }
            .profile-sidebar { flex-direction: column; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <?php
    // Include reusable sidebar component
    include '../includes/provider_sidebar.php';
    ?>

    <main class="main-content">
        <div class="top-bar">
            <div class="page-title">
                <h1>Business Profile</h1>
                <p>Manage your provider information</p>
            </div>
        </div>

        <div class="content-area">
            <div class="profile-grid">
                <div class="profile-sidebar">
                    <div class="profile-card">
                        <div class="profile-avatar">
                            <?php echo strtoupper(substr($provider['business_name'] ?? $_SESSION['first_name'], 0, 1)); ?>
                        </div>
                        <h3><?php echo htmlspecialchars($provider['business_name'] ?? 'Your Business'); ?></h3>
                        <p class="business-type">Service Provider</p>
                        <span class="verification-badge <?php echo ($provider['verification_status'] ?? 'pending') === 'verified' ? 'verified' : 'pending'; ?>">
                            <i class="fas <?php echo ($provider['verification_status'] ?? 'pending') === 'verified' ? 'fa-check-circle' : 'fa-clock'; ?>"></i>
                            <?php echo ucfirst($provider['verification_status'] ?? 'Pending Verification'); ?>
                        </span>
                    </div>

                    <?php
                    $completion = 30;
                    if (!empty($provider['business_name'])) $completion += 15;
                    if (!empty($provider['years_of_experience'])) $completion += 15;
                    if (!empty($provider['languages_spoken'])) $completion += 15;
                    if (!empty($provider['business_registration_number'])) $completion += 15;
                    if (($provider['verification_status'] ?? '') === 'verified') $completion += 10;
                    ?>
                    <div class="completion-card">
                        <h4>Profile Completion</h4>
                        <div class="completion-bar">
                            <div class="completion-fill" style="width: <?php echo $completion; ?>%"></div>
                        </div>
                        <p class="completion-text"><?php echo $completion; ?>% complete - <?php echo $completion < 100 ? 'Add more details to improve visibility' : 'Great job!'; ?></p>
                    </div>
                </div>

                <div class="form-card">
                    <div class="form-header">
                        <h2><i class="fas fa-building"></i> Business Information</h2>
                    </div>
                    <div class="form-body">
                        <form id="profileForm">
                            <div class="form-section">
                                <div class="section-title">Business Details</div>

                                <div class="form-group">
                                    <label class="form-label">Business Name <span class="required">*</span></label>
                                    <input type="text" class="form-control" id="business_name" name="business_name"
                                           value="<?php echo htmlspecialchars($provider['business_name'] ?? ''); ?>" required>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">Registration Number</label>
                                        <input type="text" class="form-control" name="business_registration_number"
                                               value="<?php echo htmlspecialchars($provider['business_registration_number'] ?? ''); ?>"
                                               placeholder="e.g., BN12345678">
                                        <p class="form-hint">Optional - builds trust with customers</p>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Years of Experience</label>
                                        <input type="number" class="form-control" name="years_of_experience"
                                               value="<?php echo htmlspecialchars($provider['years_of_experience'] ?? ''); ?>"
                                               min="0" max="50" placeholder="e.g., 5">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">Primary Region <span class="required">*</span></label>
                                        <select class="form-select" name="region" required>
                                            <option value="Greater Accra" <?php echo ($provider['region'] ?? '') === 'Greater Accra' ? 'selected' : ''; ?>>Greater Accra</option>
                                            <option value="Central" <?php echo ($provider['region'] ?? '') === 'Central' ? 'selected' : ''; ?>>Central</option>
                                            <option value="Ashanti" <?php echo ($provider['region'] ?? '') === 'Ashanti' ? 'selected' : ''; ?>>Ashanti</option>
                                            <option value="Northern" <?php echo ($provider['region'] ?? '') === 'Northern' ? 'selected' : ''; ?>>Northern</option>
                                            <option value="Eastern" <?php echo ($provider['region'] ?? '') === 'Eastern' ? 'selected' : ''; ?>>Eastern</option>
                                            <option value="Western" <?php echo ($provider['region'] ?? '') === 'Western' ? 'selected' : ''; ?>>Western</option>
                                            <option value="Volta" <?php echo ($provider['region'] ?? '') === 'Volta' ? 'selected' : ''; ?>>Volta</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Languages Spoken</label>
                                        <input type="text" class="form-control" name="languages_spoken"
                                               value="<?php echo htmlspecialchars($provider['languages_spoken'] ?? ''); ?>"
                                               placeholder="e.g., English, Twi, French">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Location Details</label>
                                    <input type="text" class="form-control" name="location_details"
                                           value="<?php echo htmlspecialchars($provider['location_details'] ?? ''); ?>"
                                           placeholder="e.g., Osu, Accra - Near Oxford Street">
                                </div>
                            </div>

                            <div class="form-section">
                                <div class="section-title">Personal Information</div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">First Name <span class="required">*</span></label>
                                        <input type="text" class="form-control" name="first_name"
                                               value="<?php echo htmlspecialchars($provider['first_name'] ?? ''); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Last Name <span class="required">*</span></label>
                                        <input type="text" class="form-control" name="last_name"
                                               value="<?php echo htmlspecialchars($provider['last_name'] ?? ''); ?>" required>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">Phone Number <span class="required">*</span></label>
                                        <input type="tel" class="form-control" name="phone"
                                               value="<?php echo htmlspecialchars($provider['phone'] ?? ''); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Email Address</label>
                                        <input type="email" class="form-control" name="email"
                                               value="<?php echo htmlspecialchars($provider['email'] ?? ''); ?>" readonly>
                                        <p class="form-hint">Contact support to change email</p>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i>
                                    Save Changes
                                </button>
                                <a href="provider_dashboard.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div id="alertContainer"></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $('#profileForm').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: '../actions/update_provider_profile_action.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    showAlert(response.status, response.message);
                    if (response.status === 'success') {
                        setTimeout(() => location.reload(), 1500);
                    }
                },
                error: function() {
                    showAlert('error', 'An error occurred. Please try again.');
                }
            });
        });

        function showAlert(type, message) {
            const alert = document.createElement('div');
            alert.className = `alert-toast ${type}`;
            alert.innerHTML = `
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                ${message}
            `;
            document.getElementById('alertContainer').appendChild(alert);
            setTimeout(() => alert.remove(), 4000);
        }
    </script>
</body>
</html>
