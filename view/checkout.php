<?php
session_start();
require_once(__DIR__ . '/../settings/core.php');
require_once(__DIR__ . '/../controllers/cart_controller.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

$customer_id = $_SESSION['user_id'];
$ip_address = $_SERVER['REMOTE_ADDR'];

// Get cart items
$cart_items = get_user_cart_ctr($customer_id, $ip_address);

// Redirect to cart if empty
if (empty($cart_items)) {
    header("Location: cart.php");
    exit();
}

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['subtotal'];
}
$tax = $subtotal * 0.0; // Adjust tax rate as needed
$total = $subtotal + $tax;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Shopify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../css/index.css" rel="stylesheet">
    <style>
        body {
            background: var(--gray-50);
        }

        .checkout-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            padding: 60px 0 40px;
            margin-bottom: 40px;
        }

        .checkout-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .progress-steps {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-bottom: 40px;
        }

        .progress-step {
            text-align: center;
        }

        .progress-step i {
            font-size: 2.5rem;
            margin-bottom: 8px;
        }

        .progress-step p {
            font-size: 0.9rem;
            font-weight: 600;
            margin: 0;
        }

        .progress-step.active i {
            color: var(--primary);
        }

        .progress-step.active p {
            color: var(--primary);
        }

        .progress-step.completed i {
            color: var(--success, #28a745);
        }

        .progress-step.completed p {
            color: var(--success, #28a745);
        }

        .progress-step.inactive i, .progress-step.inactive p {
            color: var(--gray-400);
        }

        .progress-arrow {
            color: var(--gray-400);
            font-size: 1.5rem;
        }

        .checkout-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 60px;
        }

        .checkout-card {
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow-md);
            padding: 32px;
            margin-bottom: 24px;
        }

        .checkout-card h4 {
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 24px;
        }

        .order-table {
            width: 100%;
            margin-bottom: 0;
        }

        .order-table thead th {
            border-bottom: 2px solid var(--gray-200);
            padding: 12px 8px;
            font-weight: 700;
            color: var(--gray-900);
        }

        .order-table tbody td {
            padding: 16px 8px;
            border-bottom: 1px solid var(--gray-200);
            vertical-align: middle;
        }

        .order-table tbody tr:last-child td {
            border-bottom: none;
        }

        .product-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .product-thumbnail {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
        }

        .product-info strong {
            display: block;
            color: var(--gray-900);
            margin-bottom: 4px;
        }

        .product-info small {
            color: var(--gray-600);
        }

        .customer-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
        }

        .info-row {
            margin-bottom: 16px;
        }

        .info-row strong {
            color: var(--gray-700);
            font-weight: 600;
        }

        .summary-card {
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow-md);
            padding: 32px;
            position: sticky;
            top: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            font-size: 1.1rem;
        }

        .summary-total {
            border-top: 2px solid var(--gray-200);
            margin-top: 16px;
            padding-top: 16px;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .btn-payment {
            width: 100%;
            background: var(--primary);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            margin-top: 24px;
            margin-bottom: 16px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-payment:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(103, 146, 103, 0.3);
        }

        .info-alert {
            background: #e3f2fd;
            border: 1px solid #90caf9;
            border-radius: 8px;
            padding: 12px;
            font-size: 0.9rem;
            color: #1976d2;
            margin-bottom: 20px;
        }

        .info-alert i {
            margin-right: 6px;
        }

        .security-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .security-list li {
            padding: 8px 0;
            color: var(--gray-700);
        }

        .security-list i {
            color: var(--success, #28a745);
            margin-right: 8px;
        }

        .btn-back {
            background: var(--gray-200);
            color: var(--gray-700);
            border: none;
            padding: 14px 32px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-back:hover {
            background: var(--gray-300);
            color: var(--gray-900);
        }

        @media (max-width: 768px) {
            .customer-info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-left">
                <a href="../index.php" class="logo">shopify<span class="logo-dot">.</span></a>
            </div>
            <div class="nav-right">
                <a href="all_product.php" class="nav-link">All Products</a>
                <a href="cart.php" class="nav-link" style="position: relative; margin-right: 20px;">
                    <i class="fa fa-shopping-cart"></i> Cart
                    <span class="cart-count" style="position: absolute; top: -8px; right: -10px; background: #e74c3c; color: white; border-radius: 50%; padding: 2px 6px; font-size: 12px; font-weight: bold;"><?php echo count($cart_items); ?></span>
                </a>
                <span class="nav-user">
                    <i class="fa fa-user-circle"></i>
                    <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                </span>
                <a href="../login/logout.php" class="btn-nav btn-nav-logout">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Checkout Header -->
    <div class="checkout-header">
        <div class="container text-center">
            <h1><i class="fa fa-credit-card"></i> Checkout</h1>
            <p>Review your order and complete payment</p>
        </div>
    </div>

    <div class="checkout-container">
        <!-- Progress Steps -->
        <div class="progress-steps">
            <div class="progress-step completed">
                <i class="fa fa-shopping-cart"></i>
                <p>Cart</p>
            </div>
            <i class="fa fa-chevron-right progress-arrow"></i>
            <div class="progress-step active">
                <i class="fa fa-credit-card"></i>
                <p>Checkout</p>
            </div>
            <i class="fa fa-chevron-right progress-arrow"></i>
            <div class="progress-step inactive">
                <i class="fa fa-check-circle"></i>
                <p>Complete</p>
            </div>
        </div>

        <div class="row">
            <!-- Order Summary -->
            <div class="col-lg-8">
                <div class="checkout-card">
                    <h4><i class="fa fa-shopping-bag"></i> Order Summary</h4>
                    <div class="table-responsive">
                        <table class="order-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th style="text-align: right;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="product-cell">
                                                <?php if (!empty($item['product_image'])): ?>
                                                    <img src="../<?php echo htmlspecialchars($item['product_image']); ?>"
                                                         alt="<?php echo htmlspecialchars($item['product_title']); ?>"
                                                         class="product-thumbnail">
                                                <?php else: ?>
                                                    <div class="product-thumbnail" style="background: var(--gray-100); display: flex; align-items: center; justify-content: center;">
                                                        <i class="fa fa-image" style="color: var(--gray-400);"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="product-info">
                                                    <strong><?php echo htmlspecialchars($item['product_title']); ?></strong>
                                                    <?php if ($item['cat_name']): ?>
                                                        <small><?php echo htmlspecialchars($item['cat_name']); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>GHS <?php echo number_format($item['product_price'], 2); ?></td>
                                        <td><?php echo $item['qty']; ?></td>
                                        <td style="text-align: right;"><strong>GHS <?php echo number_format($item['subtotal'], 2); ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="checkout-card">
                    <h4><i class="fa fa-user"></i> Customer Information</h4>
                    <div class="customer-info-grid">
                        <div>
                            <div class="info-row">
                                <strong>Name:</strong> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </div>
                            <div class="info-row">
                                <strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['user_email']); ?>
                            </div>
                        </div>
                        <div>
                            <div class="info-row">
                                <strong>Country:</strong> <?php echo htmlspecialchars($_SESSION['user_country']); ?>
                            </div>
                            <div class="info-row">
                                <strong>City:</strong> <?php echo htmlspecialchars($_SESSION['user_city']); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Section -->
            <div class="col-lg-4">
                <div class="summary-card">
                    <h4 style="font-weight: 700; margin-bottom: 24px;">Payment Details</h4>

                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <strong>GHS <?php echo number_format($subtotal, 2); ?></strong>
                    </div>
                    <div class="summary-row">
                        <span>Tax:</span>
                        <strong>GHS <?php echo number_format($tax, 2); ?></strong>
                    </div>

                    <div class="summary-row summary-total">
                        <span>Total:</span>
                        <span style="color: var(--primary);">GHS <?php echo number_format($total, 2); ?></span>
                    </div>

                    <button class="btn-payment" id="proceed-to-checkout-btn">
                        <i class="fa fa-lock"></i>
                        <span>Simulate Payment</span>
                    </button>

                    <div class="info-alert">
                        <i class="fa fa-info-circle"></i>
                        This is a simulated payment for demonstration purposes only.
                    </div>

                    <h6 style="font-weight: 700; margin-bottom: 12px; color: var(--gray-900);">Secure Checkout</h6>
                    <ul class="security-list">
                        <li>
                            <i class="fa fa-lock"></i> SSL Encrypted
                        </li>
                        <li>
                            <i class="fa fa-shield-alt"></i> Buyer Protection
                        </li>
                        <li>
                            <i class="fa fa-undo"></i> Easy Returns
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Back to Cart Button -->
        <div class="mt-4">
            <a href="cart.php" class="btn-back">
                <i class="fa fa-arrow-left"></i> Back to Cart
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/checkout.js"></script>
</body>
</html>
