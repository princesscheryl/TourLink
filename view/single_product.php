<?php
session_start();
require_once '../settings/core.php';
require_once '../controllers/product_controller.php';

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    header("Location: all_product.php");
    exit();
}

// Get product details
$product = view_single_product_ctr($product_id);

if (!$product) {
    header("Location: all_product.php");
    exit();
}

// Get related products from same category
$related_products = filter_products_by_category_ctr($product['product_cat']);
// Remove current product from related products
$related_products = array_filter($related_products, function($p) use ($product_id) {
    return $p['product_id'] != $product_id;
});
// Limit to 4 related products
$related_products = array_slice($related_products, 0, 4);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['product_title']); ?> - Product Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../css/index.css" rel="stylesheet">
    <style>
        body {
            background: var(--gray-50);
        }

        .breadcrumb {
            background: transparent;
            padding: 20px 0;
            margin-bottom: 0;
        }

        .breadcrumb-item a {
            color: var(--primary);
            text-decoration: none;
        }

        .breadcrumb-item.active {
            color: var(--gray-600);
        }

        .product-detail-section {
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow-md);
            padding: 40px;
            margin-bottom: 40px;
        }

        .product-image-main {
            width: 100%;
            height: 500px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: var(--shadow-md);
        }

        .product-image-placeholder {
            width: 100%;
            height: 500px;
            background: linear-gradient(135deg, var(--gray-100) 0%, var(--gray-200) 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-400);
        }

        .product-meta {
            display: inline-block;
            padding: 8px 16px;
            background: var(--gray-100);
            border-radius: 50px;
            font-size: 0.9rem;
            color: var(--gray-700);
            font-weight: 600;
            margin-bottom: 16px;
        }

        .product-title-main {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 16px;
            line-height: 1.2;
        }

        .product-brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            margin-bottom: 24px;
        }

        .product-price-main {
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 24px;
        }

        .product-description {
            font-size: 1.1rem;
            color: var(--gray-700);
            line-height: 1.8;
            margin-bottom: 32px;
        }

        .product-keywords {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 32px;
        }

        .keyword-tag {
            display: inline-block;
            padding: 8px 16px;
            background: var(--gray-100);
            border: 1px solid var(--gray-300);
            border-radius: 50px;
            font-size: 0.9rem;
            color: var(--gray-700);
        }

        .btn-add-cart-large {
            background: var(--primary);
            color: white;
            border: none;
            padding: 18px 48px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.2rem;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(103, 146, 103, 0.3);
        }

        .btn-add-cart-large:hover {
            background: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(103, 146, 103, 0.4);
        }

        .btn-back {
            background: var(--gray-200);
            color: var(--gray-700);
            border: none;
            padding: 18px 48px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.2rem;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-back:hover {
            background: var(--gray-300);
            color: var(--gray-800);
        }

        .related-products-section {
            margin-top: 60px;
        }

        .section-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 32px;
        }

        .related-product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s;
        }

        .related-product-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-4px);
        }

        .related-product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .related-product-body {
            padding: 16px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            margin-bottom: 32px;
        }

        .info-item {
            padding: 16px;
            background: var(--gray-50);
            border-radius: 8px;
            border: 1px solid var(--gray-200);
        }

        .info-label {
            font-size: 0.85rem;
            color: var(--gray-600);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 1.1rem;
            color: var(--gray-900);
            font-weight: 600;
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
                <a href="cart.php" class="nav-link" style="position: relative; margin-right: 20px;">
                    <i class="fa fa-shopping-cart"></i> Cart
                    <span class="cart-count" style="position: absolute; top: -8px; right: -10px; background: #e74c3c; color: white; border-radius: 50%; padding: 2px 6px; font-size: 12px; font-weight: bold;">0</span>
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

    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="all_product.php">Products</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['product_title']); ?></li>
            </ol>
        </nav>

        <!-- Product Detail -->
        <div class="product-detail-section">
            <div class="row g-5">
                <div class="col-lg-6">
                    <?php if (!empty($product['product_image'])): ?>
                        <img src="../<?php echo htmlspecialchars($product['product_image']); ?>"
                             alt="<?php echo htmlspecialchars($product['product_title']); ?>"
                             class="product-image-main">
                    <?php else: ?>
                        <div class="product-image-placeholder">
                            <i class="fa fa-image fa-5x"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-lg-6">
                    <div class="product-meta">
                        <i class="fa fa-tag"></i> <?php echo htmlspecialchars($product['cat_name']); ?>
                    </div>

                    <h1 class="product-title-main"><?php echo htmlspecialchars($product['product_title']); ?></h1>

                    <div class="product-brand-badge">
                        <i class="fa fa-bookmark"></i>
                        <span><?php echo htmlspecialchars($product['brand_name']); ?></span>
                    </div>

                    <div class="product-price-main">
                        GHS <?php echo number_format($product['product_price'], 2); ?>
                    </div>

                    <?php if (!empty($product['product_desc'])): ?>
                        <div class="product-description">
                            <?php echo nl2br(htmlspecialchars($product['product_desc'])); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Info Grid -->
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Product ID</div>
                            <div class="info-value">#<?php echo $product['product_id']; ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Category</div>
                            <div class="info-value"><?php echo htmlspecialchars($product['cat_name']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Brand</div>
                            <div class="info-value"><?php echo htmlspecialchars($product['brand_name']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Price</div>
                            <div class="info-value">GHS <?php echo number_format($product['product_price'], 2); ?></div>
                        </div>
                    </div>

                    <?php if (!empty($product['product_keywords'])): ?>
                        <div class="product-keywords">
                            <?php
                            $keywords = explode(',', $product['product_keywords']);
                            foreach ($keywords as $keyword):
                                $keyword = trim($keyword);
                                if (!empty($keyword)):
                            ?>
                                <span class="keyword-tag">
                                    <i class="fa fa-hashtag"></i> <?php echo htmlspecialchars($keyword); ?>
                                </span>
                            <?php
                                endif;
                            endforeach;
                            ?>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex gap-3">
                        <button class="btn-add-cart-large add-to-cart-btn" data-product-id="<?php echo $product['product_id']; ?>">
                            <i class="fa fa-shopping-cart"></i> Add to Cart
                        </button>
                        <a href="all_product.php" class="btn-back">
                            <i class="fa fa-arrow-left"></i> Back to Products
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if (!empty($related_products)): ?>
            <div class="related-products-section">
                <h2 class="section-title">You May Also Like</h2>
                <div class="row g-4">
                    <?php foreach ($related_products as $related): ?>
                        <div class="col-md-3">
                            <div class="related-product-card">
                                <a href="single_product.php?id=<?php echo $related['product_id']; ?>">
                                    <?php if (!empty($related['product_image'])): ?>
                                        <img src="../<?php echo htmlspecialchars($related['product_image']); ?>"
                                             alt="<?php echo htmlspecialchars($related['product_title']); ?>"
                                             class="related-product-image">
                                    <?php else: ?>
                                        <div style="width: 100%; height: 200px; background: var(--gray-100); display: flex; align-items: center; justify-content: center;">
                                            <i class="fa fa-image fa-3x" style="color: var(--gray-400);"></i>
                                        </div>
                                    <?php endif; ?>
                                </a>
                                <div class="related-product-body">
                                    <h5 style="font-weight: 700; margin-bottom: 8px;">
                                        <a href="single_product.php?id=<?php echo $related['product_id']; ?>" style="text-decoration: none; color: var(--gray-900);">
                                            <?php echo htmlspecialchars($related['product_title']); ?>
                                        </a>
                                    </h5>
                                    <p style="color: var(--primary); font-weight: 700; font-size: 1.2rem; margin: 0;">
                                        GHS <?php echo number_format($related['product_price'], 2); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div style="height: 80px;"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/cart.js"></script>
    <script>
        // Load cart count when page loads
        document.addEventListener('DOMContentLoaded', function() {
            fetch('../actions/get_cart_count_action.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateCartCount(data.cart_count);
                    }
                })
                .catch(error => console.error('Error loading cart count:', error));
        });
    </script>
</body>
</html>
