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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f8f9fa;
            color: #1a1a1a;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .booking-grid {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 32px;
        }

        .main-content {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 32px;
            color: #1a1a1a;
        }

        .section {
            margin-bottom: 40px;
            padding-bottom: 32px;
            border-bottom: 1px solid #e9ecef;
        }

        .section:last-child {
            border-bottom: none;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 24px;
            color: #1a1a1a;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 6px;
            color: #1a1a1a;
        }

        .form-label .required {
            color: #dc3545;
            margin-left: 2px;
        }

        .form-label .optional {
            color: #6c757d;
            font-weight: 400;
        }

        .form-control, .form-select {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.2s;
        }

        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: #1b4332;
            box-shadow: 0 0 0 3px rgba(27, 67, 50, 0.1);
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        .form-help {
            font-size: 12px;
            color: #6c757d;
            margin-top: 4px;
        }

        .radio-group, .checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .radio-option, .checkbox-option {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .radio-option input[type="radio"],
        .checkbox-option input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .radio-option label,
        .checkbox-option label {
            cursor: pointer;
            font-size: 14px;
        }

        .info-box {
            background: #e7f3ef;
            border: 1px solid #b8dac8;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 20px;
        }

        .info-box i {
            color: #1b4332;
            margin-right: 8px;
        }

        .info-box p {
            font-size: 13px;
            color: #1a1a1a;
            margin: 0;
        }

        /* Sidebar */
        .sidebar {
            position: sticky;
            top: 20px;
            height: fit-content;
        }

        .booking-summary {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .summary-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .service-preview {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e9ecef;
        }

        .service-thumb {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            object-fit: cover;
        }

        .service-info h4 {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .service-info p {
            font-size: 13px;
            color: #6c757d;
            margin: 0;
        }

        .booking-detail {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 14px;
        }

        .booking-detail strong {
            font-weight: 600;
        }

        .price-breakdown {
            border-top: 2px solid #e9ecef;
            padding-top: 16px;
            margin-top: 16px;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
        }

        .price-row.total {
            border-top: 1px solid #e9ecef;
            margin-top: 8px;
            padding-top: 16px;
            font-size: 18px;
            font-weight: 700;
            color: #1b4332;
        }

        .btn-submit {
            width: 100%;
            padding: 16px;
            background: #1b4332;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 20px;
        }

        .btn-submit:hover {
            background: #143729;
        }

        .btn-submit:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .alert {
            padding: 14px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-danger {
            background: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
        }

        .alert-success {
            background: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }

        @media (max-width: 968px) {
            .booking-grid {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: static;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
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
                        <?php if ($service['service_images']):
                            $images = json_decode($service['service_images'], true);
                            $first_image = $images[0] ?? 'default.jpg';
                        ?>
                            <img src="../uploads/services/<?php echo htmlspecialchars($first_image); ?>" alt="Service" class="service-thumb">
                        <?php endif; ?>
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

    <script>
        const serviceId = <?php echo $service_id; ?>;
        const serviceDate = '<?php echo htmlspecialchars($service_date); ?>';
        const serviceTime = '<?php echo htmlspecialchars($service_time); ?>';
        const numberOfPeople = <?php echo $number_of_people; ?>;
        let totalAmount = <?php echo $subtotal; ?>;
        let originalAmount = <?php echo $subtotal; ?>;
        let discountAmount = 0;
        let appliedDiscountCode = '';

        // Discount code functionality
        function showDiscountMessage(message, type) {
            const messageEl = document.getElementById('discountMessage');
            messageEl.textContent = message;
            messageEl.style.color = type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#6c757d';
            messageEl.style.fontWeight = type === 'error' ? '600' : '400';
        }

        document.getElementById('applyDiscountBtn').addEventListener('click', async function() {
            const code = document.getElementById('discountCode').value.trim().toUpperCase();
            const applyBtn = document.getElementById('applyDiscountBtn');

            if (!code) {
                showDiscountMessage('Please enter a discount code', 'error');
                return;
            }

            applyBtn.disabled = true;
            applyBtn.textContent = 'Checking...';

            try {
                const response = await fetch('../actions/validate_discount_booking.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        discount_code: code,
                        booking_amount: originalAmount,
                        service_id: serviceId
                    })
                });

                const data = await response.json();

                if (data.status === 'success') {
                    discountAmount = parseFloat(data.discount_amount);
                    appliedDiscountCode = code;
                    totalAmount = originalAmount - discountAmount;

                    // Update UI
                    document.getElementById('discountAmount').textContent = `- GH₵ ${discountAmount.toFixed(2)}`;
                    document.getElementById('discountRow').style.display = 'flex';
                    document.getElementById('totalAmount').textContent = `GH₵ ${totalAmount.toFixed(2)}`;

                    showDiscountMessage(`Discount applied! You save GH₵ ${discountAmount.toFixed(2)}`, 'success');
                    document.getElementById('discountCode').disabled = true;
                    applyBtn.style.display = 'none';
                } else {
                    showDiscountMessage(data.message || 'Invalid discount code', 'error');
                    applyBtn.disabled = false;
                    applyBtn.textContent = 'Apply';
                }
            } catch (error) {
                console.error('Discount validation error:', error);
                showDiscountMessage('Error validating discount code. Please try again.', 'error');
                applyBtn.disabled = false;
                applyBtn.textContent = 'Apply';
            }
        });

        // Allow Enter key to apply discount
        document.getElementById('discountCode').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('applyDiscountBtn').click();
            }
        });

        document.getElementById('bookingForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submitBtn');
            const alertBox = document.getElementById('alertBox');

            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.textContent = 'Initializing payment...';

            // Clear previous alerts
            alertBox.innerHTML = '';

            try {
                const formData = new FormData(this);

                // Collect guest details
                const guestDetails = {
                    first_name: formData.get('first_name'),
                    last_name: formData.get('last_name'),
                    email: formData.get('email'),
                    phone: formData.get('phone'),
                    booking_for: formData.get('booking_for'),
                    travelling_for_work: formData.get('travelling_for_work') === 'yes',
                    special_requests: formData.get('special_requests'),
                    arrival_time: formData.get('arrival_time')
                };

                // Prepare payment data
                const paymentData = {
                    service_id: serviceId,
                    service_date: serviceDate,
                    service_time: serviceTime,
                    number_of_people: numberOfPeople,
                    service_duration: <?php echo $service_duration; ?>,
                    total_amount: totalAmount,
                    original_amount: originalAmount,
                    discount_code: appliedDiscountCode,
                    discount_amount: discountAmount,
                    email: formData.get('email'),
                    guest_details: guestDetails
                };

                // Initialize Paystack payment
                const response = await fetch('../actions/paystack_init_booking.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(paymentData)
                });

                const result = await response.json();

                if (result.status === 'success' && result.authorization_url) {
                    // Redirect to Paystack payment gateway
                    window.location.href = result.authorization_url;
                } else {
                    alertBox.innerHTML = `<div class="alert alert-danger">${result.message || 'Failed to initialize payment'}</div>`;
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Proceed to Payment';
                    window.scrollTo(0, 0);
                }
            } catch (error) {
                console.error('Payment initialization error:', error);
                alertBox.innerHTML = '<div class="alert alert-danger">An error occurred. Please try again.</div>';
                submitBtn.disabled = false;
                submitBtn.textContent = 'Proceed to Payment';
                window.scrollTo(0, 0);
            }
        });
    </script>
</body>
</html>
