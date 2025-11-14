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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - TourLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="../css/dark-mode.css" rel="stylesheet">
    <link href="../css/accessibility.css" rel="stylesheet">
    <script src="../js/dark-mode.js"></script>
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
            position: relative;
        }

        .nav-link:hover {
            color: #2d6a4f;
        }

        .nav-link.active {
            color: #2d6a4f;
            font-weight: 600;
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -23px;
            left: 0;
            right: 0;
            height: 3px;
            background: #2d6a4f;
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

        .info-box {
            background: #e8f4f1;
            border-left: 4px solid #2d6a4f;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            color: #1b4332;
        }

        .info-box i {
            color: #2d6a4f;
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <a href="#main-content" class="skip-link">Skip to main content</a>
    <!-- Navigation -->
    <nav class="main-nav" role="navigation" aria-label="Provider navigation">
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

    <div class="main-container" id="main-content" role="main">
        <div class="form-card">
            <h2><i class="fa fa-user-circle" aria-hidden="true"></i> Edit Profile</h2>

            <div class="info-box">
                <i class="fa fa-info-circle" aria-hidden="true"></i>
                Complete your profile to improve your visibility and credibility with tourists.
            </div>

            <form id="profileForm" role="form" aria-label="Provider profile form">
                <!-- Business Name -->
                <div class="mb-3">
                    <label for="business_name" class="form-label">Business Name *</label>
                    <input type="text" class="form-control" id="business_name" name="business_name"
                           value="<?php echo htmlspecialchars($provider['business_name']); ?>" required aria-required="true">
                </div>

                <!-- Business Registration Number -->
                <div class="mb-3">
                    <label for="business_registration_number" class="form-label">Business Registration Number</label>
                    <input type="text" class="form-control" id="business_registration_number" name="business_registration_number"
                           value="<?php echo htmlspecialchars($provider['business_registration_number'] ?? ''); ?>"
                           placeholder="e.g., BN12345678" aria-describedby="reg-help">
                    <small class="text-muted" id="reg-help">Optional - Helps build trust with customers</small>
                </div>

                <!-- Primary Region -->
                <div class="mb-3">
                    <label for="region" class="form-label">Primary Business Region *</label>
                    <select class="form-select" id="region" name="region" required aria-required="true">
                        <option value="Greater Accra" <?php echo $provider['region'] === 'Greater Accra' ? 'selected' : ''; ?>>Greater Accra</option>
                        <option value="Central" <?php echo $provider['region'] === 'Central' ? 'selected' : ''; ?>>Central</option>
                        <option value="Ashanti" <?php echo $provider['region'] === 'Ashanti' ? 'selected' : ''; ?>>Ashanti</option>
                        <option value="Northern" <?php echo $provider['region'] === 'Northern' ? 'selected' : ''; ?>>Northern</option>
                    </select>
                </div>

                <!-- Location Details -->
                <div class="mb-3">
                    <label for="location_details" class="form-label">Location Details</label>
                    <textarea class="form-control" id="location_details" name="location_details" rows="2"
                              placeholder="e.g., Osu, Accra - Near Oxford Street"><?php echo htmlspecialchars($provider['location_details'] ?? ''); ?></textarea>
                    <small class="text-muted">Provide specific location information for tourists</small>
                </div>

                <!-- Years of Experience -->
                <div class="mb-3">
                    <label for="years_of_experience" class="form-label">Years of Experience</label>
                    <input type="number" class="form-control" id="years_of_experience" name="years_of_experience"
                           value="<?php echo htmlspecialchars($provider['years_of_experience'] ?? ''); ?>"
                           min="0" max="50" placeholder="e.g., 5">
                    <small class="text-muted">How many years have you been providing tourism services?</small>
                </div>

                <!-- Languages Spoken -->
                <div class="mb-3">
                    <label for="languages_spoken" class="form-label">Languages Spoken</label>
                    <input type="text" class="form-control" id="languages_spoken" name="languages_spoken"
                           value="<?php echo htmlspecialchars($provider['languages_spoken'] ?? ''); ?>"
                           placeholder="e.g., English, Twi, French, German">
                    <small class="text-muted">Comma-separated list of languages you can communicate in</small>
                </div>

                <hr class="my-4">

                <h5 class="mb-3">Personal Information</h5>

                <!-- First Name -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="first_name" class="form-label">First Name *</label>
                        <input type="text" class="form-control" id="first_name" name="first_name"
                               value="<?php echo htmlspecialchars($provider['first_name']); ?>" required>
                    </div>

                    <!-- Last Name -->
                    <div class="col-md-6 mb-3">
                        <label for="last_name" class="form-label">Last Name *</label>
                        <input type="text" class="form-control" id="last_name" name="last_name"
                               value="<?php echo htmlspecialchars($provider['last_name']); ?>" required>
                    </div>
                </div>

                <!-- Phone -->
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number *</label>
                    <input type="tel" class="form-control" id="phone" name="phone"
                           value="<?php echo htmlspecialchars($provider['phone']); ?>" required>
                </div>

                <!-- Email (Read-only) -->
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?php echo htmlspecialchars($provider['email']); ?>" readonly>
                    <small class="text-muted">Email cannot be changed. Contact support if needed.</small>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Save Changes
                    </button>
                    <a href="provider_dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $('#profileForm').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: '../actions/update_provider_profile_action.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            confirmButtonColor: '#2d6a4f'
                        }).then(() => {
                            window.location.href = 'provider_dashboard.php';
                        });
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
        });
    </script>
    <script src="../js/accessibility.js"></script>
</body>
</html>
