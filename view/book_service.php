<?php
session_start();
require_once '../settings/core.php';
require_once '../controllers/service_controller.php';

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tourist') {
    header('Location: ../login/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Get service ID
$service_id = isset($_GET['service_id']) ? (int)$_GET['service_id'] : 0;
if (!$service_id) {
    header('Location: all_services.php');
    exit();
}

// Get service details
$service = get_service_by_id_ctr($service_id);
if (!$service) {
    header('Location: all_services.php?error=service_not_found');
    exit();
}

// Get booking parameters from URL
$service_date = isset($_GET['date']) ? $_GET['date'] : '';
$service_time = isset($_GET['time']) ? $_GET['time'] : '';
$number_of_people = isset($_GET['guests']) ? (int)$_GET['guests'] : 1;
$service_duration = isset($_GET['duration']) ? (int)$_GET['duration'] : 1;

// Calculate pricing
$base_price = (float)$service['base_price'];
$pricing_unit = $service['pricing_unit'];

switch ($pricing_unit) {
    case 'per_person':
        $subtotal = $base_price * $number_of_people;
        break;
    case 'per_hour':
        $subtotal = $base_price * $service_duration;
        break;
    case 'per_day':
        $subtotal = $base_price * $service_duration;
        break;
    case 'flat_rate':
    default:
        $subtotal = $base_price;
        break;
}

// Get user details for prefilling
$user_first_name = $_SESSION['first_name'] ?? '';
$user_last_name = $_SESSION['last_name'] ?? '';
$user_email = $_SESSION['email'] ?? '';
$user_phone = $_SESSION['phone'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Booking - TourLink</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../css/book_service.css" rel="stylesheet">
    <script>
        // Pass PHP variables to JavaScript
        window.serviceBookingData = {
            serviceId: <?php echo $service_id; ?>,
            serviceDate: '<?php echo htmlspecialchars($service_date); ?>',
            serviceTime: '<?php echo htmlspecialchars($service_time); ?>',
            numberOfPeople: <?php echo $number_of_people; ?>,
            serviceDuration: <?php echo $service_duration; ?>,
            totalAmount: <?php echo $subtotal; ?>,
            originalAmount: <?php echo $subtotal; ?>
        };
    </script>
</head>
<body>
    <div class="container">
        <a href="single_service.php?service_id=<?php echo $service_id; ?>" style="display: inline-block; margin-bottom: 20px; color: #1b4332; text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Back to service
        </a>

        <div class="booking-grid">
            <!-- Main Content -->
            <div class="main-content">
                <h1 class="page-title">Complete your booking</h1>

                <div id="alertBox"></div>

                <form id="bookingForm">
                    <input type="hidden" name="service_id" value="<?php echo $service_id; ?>">
                    <input type="hidden" name="service_date" value="<?php echo htmlspecialchars($service_date); ?>">
                    <input type="hidden" name="service_time" value="<?php echo htmlspecialchars($service_time); ?>">
                    <input type="hidden" name="number_of_people" value="<?php echo $number_of_people; ?>">
                    <input type="hidden" name="service_duration" value="<?php echo $service_duration; ?>">

                    <!-- Personal Details -->
                    <div class="section">
                        <h2 class="section-title">Your details</h2>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">First name <span class="required">*</span></label>
                                <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($user_first_name); ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Last name <span class="required">*</span></label>
                                <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($user_last_name); ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email address <span class="required">*</span></label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user_email); ?>" required>
                            <small class="form-help">Confirmation email goes to this address</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Phone number <span class="required">*</span></label>
                            <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user_phone); ?>" placeholder="+233 XX XXX XXXX" required>
                            <small class="form-help">To verify your booking, and for the provider to contact if needed</small>
                        </div>
                    </div>

                    <!-- Booking Details -->
                    <div class="section">
                        <h2 class="section-title">Booking details</h2>

                        <div class="form-group">
                            <label class="form-label">Who are you booking for? <span class="optional">(optional)</span></label>
                            <div class="radio-group">
                                <div class="radio-option">
                                    <input type="radio" id="main_guest" name="booking_for" value="main_guest" checked>
                                    <label for="main_guest">I am the main guest</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" id="someone_else" name="booking_for" value="someone_else">
                                    <label for="someone_else">Booking is for someone else</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Are you travelling for work? <span class="optional">(optional)</span></label>
                            <div class="radio-group">
                                <div class="radio-option">
                                    <input type="radio" id="work_yes" name="travelling_for_work" value="yes">
                                    <label for="work_yes">Yes</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" id="work_no" name="travelling_for_work" value="no" checked>
                                    <label for="work_no">No</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Special Requests -->
                    <div class="section">
                        <h2 class="section-title">Special requests</h2>
                        <p style="color: #6c757d; margin-bottom: 16px; font-size: 14px;">
                            Special requests cannot be guaranteed – but the provider will do their best to meet your needs.
                        </p>
                        <div class="form-group">
                            <label class="form-label">Please write your requests <span class="optional">(optional)</span></label>
                            <textarea name="special_requests" class="form-control" placeholder="e.g., Dietary restrictions, accessibility needs, preferred language..."></textarea>
                        </div>
                    </div>

                    <!-- Arrival Time -->
                    <div class="section">
                        <h2 class="section-title">Your arrival time</h2>

                        <div class="info-box">
                            <i class="fas fa-check-circle"></i>
                            <p>Please arrive at the scheduled time: <?php echo date('g:i A', strtotime($service_time)); ?></p>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Add your estimated arrival time <span class="optional">(optional)</span></label>
                            <select name="arrival_time" class="form-select">
                                <option value="">Please select</option>
                                <option value="early_morning">Early morning (5:00 AM - 8:00 AM)</option>
                                <option value="morning">Morning (8:00 AM - 12:00 PM)</option>
                                <option value="afternoon">Afternoon (12:00 PM - 5:00 PM)</option>
                                <option value="evening">Evening (5:00 PM - 9:00 PM)</option>
                                <option value="night">Night (9:00 PM onwards)</option>
                            </select>
                            <small class="form-help">Time is for Ghana time zone</small>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit" id="submitBtn">
                        Proceed to Payment
                    </button>
                </form>
            </div>

            <!-- Sidebar -->
            <div class="sidebar">
                <div class="booking-summary">
                    <h3 class="summary-title">Booking summary</h3>

                    <div class="service-preview">
                        <?php 
                        require_once '../classes/hosted_upload_class.php';
                        if ($service['service_images']):
                            $images = json_decode($service['service_images'], true);
                            $first_image = $images[0] ?? null;
                            if ($first_image):
                                $image_url = HostedUpload::getImageUrl($first_image, '../');
                        ?>
                            <img src="<?php echo htmlspecialchars($image_url); ?>" alt="Service" class="service-thumb" onerror="this.style.display='none';">
                        <?php 
                            endif;
                        endif; 
                        ?>
                        <div class="service-info">
                            <h4><?php echo htmlspecialchars($service['service_title']); ?></h4>
                            <p><?php echo htmlspecialchars($service['category_name']); ?></p>
                        </div>
                    </div>

                    <div class="booking-detail">
                        <span>Date:</span>
                        <strong><?php echo date('D, M j, Y', strtotime($service_date)); ?></strong>
                    </div>
                    <div class="booking-detail">
                        <span>Time:</span>
                        <strong><?php echo date('g:i A', strtotime($service_time)); ?></strong>
                    </div>
                    <div class="booking-detail">
                        <span>Guests:</span>
                        <strong><?php echo $number_of_people; ?> <?php echo $number_of_people == 1 ? 'person' : 'people'; ?></strong>
                    </div>
                    <?php if ($service_duration > 1): ?>
                    <div class="booking-detail">
                        <span>Duration:</span>
                        <strong><?php echo $service_duration; ?>
                            <?php
                                if ($pricing_unit == 'per_hour') echo ($service_duration == 1 ? 'hour' : 'hours');
                                else if ($pricing_unit == 'per_day') echo ($service_duration == 1 ? 'day' : 'days');
                            ?>
                        </strong>
                    </div>
                    <?php endif; ?>

                    <!-- Discount Code Section -->
                    <div class="discount-section" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e9ecef;">
                        <label class="form-label" style="font-size: 14px; font-weight: 600; margin-bottom: 8px;">Have a discount code?</label>
                        <div style="display: flex; gap: 8px;">
                            <input type="text" id="discountCode" placeholder="Enter code" style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;" maxlength="50">
                            <button type="button" id="applyDiscountBtn" style="padding: 10px 16px; background: #1b4332; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600;">Apply</button>
                            <button type="button" id="removeDiscountBtn" style="display: none; padding: 10px 16px; background: #dc3545; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600;">Remove</button>
                        </div>
                        <div id="discountMessage" style="margin-top: 8px; font-size: 13px;"></div>
                    </div>

                    <div class="price-breakdown" style="margin-top: 20px;">
                        <div class="price-row">
                            <span>Subtotal:</span>
                            <span id="subtotalAmount">GH₵ <?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="price-row" id="discountRow" style="display: none; color: #28a745;">
                            <span>Discount:</span>
                            <span id="discountAmount">- GH₵ 0.00</span>
                        </div>
                        <div class="price-row total">
                            <span>Total:</span>
                            <span id="totalAmount">GH₵ <?php echo number_format($subtotal, 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/book_service.js"></script>
</body>
</html>
