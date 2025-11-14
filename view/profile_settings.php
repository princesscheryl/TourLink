<?php
require_once '../settings/core.php';
require_once '../classes/tourlink_user_class.php';

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
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
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
            max-width: 800px;
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

        /* Profile Picture Section */
        .profile-picture-section {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px;
            background: #f8f9fa;
            border-radius: 12px;
        }

        .profile-picture-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            margin: 0 auto 20px;
            border: 5px solid #2d6a4f;
            overflow: hidden;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .profile-picture-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-picture-preview i {
            font-size: 4rem;
            color: #ccc;
        }

        .file-upload-wrapper {
            position: relative;
            display: inline-block;
        }

        .file-upload-input {
            display: none;
        }

        .file-upload-label {
            background: #2d6a4f;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-block;
        }

        .file-upload-label:hover {
            background: #1b4332;
        }

        .file-info {
            margin-top: 15px;
            color: #666;
            font-size: 0.9rem;
        }

        /* Form Elements */
        .form-label {
            font-weight: 600;
            color: #1b4332;
            margin-bottom: 8px;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 16px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: #2d6a4f;
            box-shadow: 0 0 0 0.2rem rgba(45, 106, 79, 0.15);
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

        .btn-danger {
            background: #dc3545;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-danger:hover {
            background: #c82333;
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
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-left">
                <a href="../index_tourlink.php" class="logo">TourLink<span class="logo-dot">.</span></a>
            </div>
            <div class="nav-right">
                <a href="all_services.php" class="nav-link">Browse Services</a>
                <?php if ($user['user_type'] === 'provider'): ?>
                    <a href="../admin/provider_dashboard.php" class="nav-link">Dashboard</a>
                <?php endif; ?>
                <a href="../login/logout.php" class="btn-nav btn-nav-logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="main-container">
        <div class="form-card">
            <h2><i class="fa fa-user-circle"></i> Profile Settings</h2>

            <!-- Profile Picture Section -->
            <div class="profile-picture-section">
                <div class="profile-picture-preview" id="profilePicturePreview">
                    <?php if (!empty($user['profile_image'])): ?>
                        <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile Picture">
                    <?php else: ?>
                        <i class="fa fa-user"></i>
                    <?php endif; ?>
                </div>
                <h5>Profile Picture</h5>
                <form id="uploadPictureForm" enctype="multipart/form-data">
                    <div class="file-upload-wrapper">
                        <input type="file" id="profilePicture" name="profile_picture" class="file-upload-input" accept="image/jpeg,image/png,image/jpg">
                        <label for="profilePicture" class="file-upload-label">
                            <i class="fa fa-upload"></i> Choose Picture
                        </label>
                    </div>
                    <div class="file-info">
                        <small>Accepted formats: JPG, JPEG, PNG. Max size: 2MB</small>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary" id="uploadBtn" style="display:none;">
                            <i class="fa fa-save"></i> Upload Picture
                        </button>
                        <?php if (!empty($user['profile_image'])): ?>
                            <button type="button" class="btn btn-danger btn-sm mt-2" onclick="removeProfilePicture()">
                                <i class="fa fa-trash"></i> Remove Picture
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <hr>

            <!-- User Info Form -->
            <form id="userInfoForm">
                <h5 class="mb-3">Personal Information</h5>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="first_name" class="form-label">First Name *</label>
                        <input type="text" class="form-control" id="first_name" name="first_name"
                               value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="last_name" class="form-label">Last Name *</label>
                        <input type="text" class="form-control" id="last_name" name="last_name"
                               value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" id="phone" name="phone"
                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                    <small class="text-muted">Email cannot be changed. Contact support if needed.</small>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Save Changes
                    </button>
                    <a href="<?php echo $user['user_type'] === 'provider' ? '../admin/provider_dashboard.php' : '../index_tourlink.php'; ?>" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Profile picture preview
        $('#profilePicture').on('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file type
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!validTypes.includes(file.type)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid File Type',
                        text: 'Please upload a JPG, JPEG, or PNG image',
                        confirmButtonColor: '#2d6a4f'
                    });
                    this.value = '';
                    return;
                }

                // Validate file size (2MB)
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

                // Preview image
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#profilePicturePreview').html('<img src="' + e.target.result + '" alt="Profile Picture">');
                    $('#uploadBtn').show();
                };
                reader.readAsDataURL(file);
            }
        });

        // Upload profile picture
        $('#uploadPictureForm').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

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
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        // Show debug info if available
                        let errorMessage = response.message;
                        if (response.debug) {
                            errorMessage += '\n\nDebug Info:\n' + JSON.stringify(response.debug, null, 2);
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            html: '<pre style="text-align: left; max-height: 300px; overflow-y: auto; font-size: 12px;">' + errorMessage + '</pre>',
                            confirmButtonColor: '#2d6a4f',
                            width: 600
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        html: '<p>An error occurred. Please try again.</p><pre style="text-align: left; font-size: 12px;">Status: ' + status + '\nError: ' + error + '\nResponse: ' + xhr.responseText + '</pre>',
                        confirmButtonColor: '#2d6a4f',
                        width: 600
                    });
                }
            });
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
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            confirmButtonColor: '#2d6a4f'
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

        // Remove profile picture
        function removeProfilePicture() {
            Swal.fire({
                title: 'Remove Profile Picture?',
                text: 'Are you sure you want to remove your profile picture?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
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
                                }).then(() => {
                                    location.reload();
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
                }
            });
        }
    </script>
</body>
</html>
