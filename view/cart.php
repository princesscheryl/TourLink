<?php
session_start();
require_once(__DIR__ . '/../controllers/cart_controller.php');

// Get customer ID and IP address
$customer_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$ip_address = $_SERVER['REMOTE_ADDR'];

// Get cart items
$cart_items = get_user_cart_ctr($customer_id, $ip_address);

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['subtotal'];
}
$tax = $subtotal * 0.0;
$total = $subtotal + $tax;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Shopify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../css/index.css" rel="stylesheet">
    <style>
        body {
            background: var(--gray-50);
        }

        .cart-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            padding: 60px 0 40px;
            margin-bottom: 40px;
        }

        .cart-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .cart-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 60px;
        }

        .cart-card {
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow-md);
            padding: 32px;
            margin-bottom: 24px;
        }

        .cart-item {
            display: grid;
            grid-template-columns: 120px 1fr auto;
            gap: 24px;
            padding: 24px 0;
            border-bottom: 1px solid var(--gray-200);
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 12px;
            background: var(--gray-100);
        }

        .cart-item-details {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .cart-item-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 8px;
        }

        .cart-item-meta {
            color: var(--gray-600);
            font-size: 0.95rem;
            margin-bottom: 12px;
        }

        .cart-item-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }

        .cart-item-actions {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            justify-content: space-between;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--gray-50);
            border-radius: 8px;
            padding: 8px 16px;
        }

        .quantity-input {
            width: 60px;
            text-align: center;
            border: 1px solid var(--gray-300);
            border-radius: 6px;
            padding: 6px;
            font-weight: 600;
        }

        .btn-remove {
            background: transparent;
            border: none;
            color: var(--danger, #e74c3c);
            font-size: 1.25rem;
            cursor: pointer;
            transition: all 0.3s;
            padding: 8px;
        }

        .btn-remove:hover {
            color: #c0392b;
            transform: scale(1.1);
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

        .btn-checkout {
            width: 100%;
            background: var(--primary);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            margin-top: 24px;
            transition: all 0.3s;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-checkout:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(103, 146, 103, 0.3);
            color: white;
            text-decoration: none;
        }

        .btn-continue {
            background: var(--gray-200);
            color: var(--gray-700);
            border: none;
            padding: 14px 32px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-continue:hover {
            background: var(--gray-300);
            color: var(--gray-900);
        }

        .btn-empty {
            background: transparent;
            border: 2px solid var(--danger, #e74c3c);
            color: var(--danger, #e74c3c);
            padding: 14px 32px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-empty:hover {
            background: var(--danger, #e74c3c);
            color: white;
        }

        .empty-cart {
            text-align: center;
            padding: 80px 20px;
        }

        .empty-cart i {
            font-size: 5rem;
            color: var(--gray-300);
            margin-bottom: 24px;
        }

        .empty-cart h3 {
            color: var(--gray-700);
            margin-bottom: 16px;
        }

        .promo-section {
            background: var(--gray-50);
            border-radius: 12px;
            padding: 24px;
            margin-top: 24px;
        }

        .promo-section h6 {
            font-weight: 700;
            margin-bottom: 12px;
            color: var(--gray-900);
        }

        .promo-input-group {
            display: flex;
            gap: 8px;
        }

        .promo-input {
            flex: 1;
            border: 1px solid var(--gray-300);
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 0.95rem;
        }

        .promo-input:focus {
            outline: none;
            border-color: var(--primary);
        }

        .btn-apply-promo {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s;
            height: fit-content;
        }

        .btn-apply-promo:hover {
            background: var(--primary-dark);
        }

        .help-text {
            font-size: 0.85rem;
            color: var(--gray-600);
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid var(--gray-200);
        }

        .help-text i {
            color: var(--primary);
            margin-right: 6px;
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

        .progress-step.inactive i, .progress-step.inactive p {
            color: var(--gray-400);
        }

        .progress-arrow {
            color: var(--gray-400);
            font-size: 1.5rem;
        }

        @media (max-width: 768px) {
            .cart-item {
                grid-template-columns: 80px 1fr;
                gap: 16px;
            }

            .cart-item-actions {
                grid-column: 1 / -1;
                flex-direction: row;
                justify-content: space-between;
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
                <?php if(isset($_SESSION['user_id'])): ?>
                    <span class="nav-user">
                        <i class="fa fa-user-circle"></i>
                        <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </span>
                    <a href="../login/logout.php" class="btn-nav btn-nav-logout">Logout</a>
                <?php else: ?>
                    <a href="../login/login.php" class="nav-link">Sign in</a>
                    <a href="../login/register.php" class="btn-nav btn-nav-join">Join</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Cart Header -->
    <div class="cart-header">
        <div class="container text-center">
            <h1><i class="fa fa-shopping-cart"></i> Shopping Cart</h1>
            <p><?php echo count($cart_items); ?> item<?php echo count($cart_items) != 1 ? 's' : ''; ?> in your cart</p>
        </div>
    </div>

    <!-- Cart Content -->
    <div class="cart-container">
        <!-- Progress Steps -->
        <div class="progress-steps">
            <div class="progress-step active">
                <i class="fa fa-shopping-cart"></i>
                <p>Cart</p>
            </div>
            <i class="fa fa-chevron-right progress-arrow"></i>
            <div class="progress-step inactive">
                <i class="fa fa-credit-card"></i>
                <p>Checkout</p>
            </div>
            <i class="fa fa-chevron-right progress-arrow"></i>
            <div class="progress-step inactive">
                <i class="fa fa-check-circle"></i>
                <p>Complete</p>
            </div>
        </div>
        <?php if (empty($cart_items)): ?>
            <!-- Empty Cart -->
            <div class="cart-card">
                <div class="empty-cart">
                    <i class="fa fa-shopping-cart"></i>
                    <h3>Your cart is empty</h3>
                    <p class="text-muted mb-4">Start shopping to add items to your cart!</p>
                    <a href="all_product.php" class="btn-checkout">
                        <i class="fa fa-arrow-left"></i> Continue Shopping
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <!-- Cart Items -->
                <div class="col-lg-8">
                    <div class="cart-card">
                        <h4 class="mb-4" style="font-weight: 700; color: var(--gray-900);">Cart Items</h4>

                        <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item">
                                <!-- Product Image -->
                                <div>
                                    <?php if (!empty($item['product_image'])): ?>
                                        <img src="../<?php echo htmlspecialchars($item['product_image']); ?>"
                                             alt="<?php echo htmlspecialchars($item['product_title']); ?>"
                                             class="cart-item-image"
                                             onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22120%22 height=%22120%22%3E%3Crect fill=%22%23f0f0f0%22 width=%22120%22 height=%22120%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22%23999%22 font-size=%2214%22%3ENo Image%3C/text%3E%3C/svg%3E';">
                                    <?php else: ?>
                                        <div class="cart-item-image" style="display: flex; align-items: center; justify-content: center;">
                                            <i class="fa fa-image" style="font-size: 2rem; color: var(--gray-400);"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Product Details -->
                                <div class="cart-item-details">
                                    <div>
                                        <div class="cart-item-title"><?php echo htmlspecialchars($item['product_title']); ?></div>
                                        <div class="cart-item-meta">
                                            <?php if ($item['cat_name']): ?>
                                                <i class="fa fa-tag"></i> <?php echo htmlspecialchars($item['cat_name']); ?>
                                            <?php endif; ?>
                                            <?php if ($item['brand_name']): ?>
                                                &nbsp;•&nbsp; <i class="fa fa-bookmark"></i> <?php echo htmlspecialchars($item['brand_name']); ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="cart-item-price">GHS <?php echo number_format($item['product_price'], 2); ?></div>
                                </div>

                                <!-- Quantity & Actions -->
                                <div class="cart-item-actions">
                                    <button class="btn-remove remove-item-btn" data-product-id="<?php echo $item['p_id']; ?>">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                    <div class="quantity-control">
                                        <label style="font-size: 0.9rem; color: var(--gray-600);">Qty:</label>
                                        <input type="number"
                                               class="quantity-input"
                                               value="<?php echo $item['qty']; ?>"
                                               min="1"
                                               max="99"
                                               data-product-id="<?php echo $item['p_id']; ?>">
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between mt-4">
                            <a href="all_product.php" class="btn-continue">
                                <i class="fa fa-arrow-left"></i> Continue Shopping
                            </a>
                            <button class="btn-empty" id="empty-cart-btn">
                                <i class="fa fa-trash-alt"></i> Empty Cart
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="col-lg-4">
                    <div class="summary-card">
                        <h4 style="font-weight: 700; margin-bottom: 24px;">Order Summary</h4>

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

                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="checkout.php" class="btn-checkout">
                                <i class="fa fa-lock"></i>
                                <span>Proceed to Checkout</span>
                            </a>
                        <?php else: ?>
                            <div class="alert alert-warning mt-3" style="font-size: 0.9rem;">
                                Please <a href="../login/login.php" style="font-weight: 600;">login</a> to checkout
                            </div>
                            <a href="../login/login.php" class="btn-checkout">
                                <i class="fa fa-sign-in-alt"></i>
                                <span>Login to Checkout</span>
                            </a>
                        <?php endif; ?>

                        <!-- Promo Code Section -->
                        <div class="promo-section">
                            <h6><i class="fa fa-tag"></i> Have a promo code?</h6>
                            <div class="promo-input-group">
                                <input type="text"
                                       class="promo-input"
                                       placeholder="Enter promo code"
                                       id="promo-code-input">
                                <button class="btn-apply-promo" onclick="alert('Promo code feature coming soon!')">
                                    Apply
                                </button>
                            </div>
                            <div class="help-text">
                                <i class="fa fa-info-circle"></i>
                                Need help? <a href="#" style="color: var(--primary); font-weight: 600;">Contact Support</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/cart.js"></script>
</body>
</html>
