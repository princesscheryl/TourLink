<?php
session_start();
require_once '../settings/core.php';
require_once '../controllers/product_controller.php';
require_once '../controllers/category_controller.php';
require_once '../controllers/brand_controller.php';

// Get all products, categories, and brands
$products = view_all_products_ctr();
$categories = get_all_categories_ctr();
$brands = get_all_brands_ctr();

// Apply filters if set
$filtered_products = $products;
$active_category = isset($_GET['category']) ? intval($_GET['category']) : 0;
$active_brand = isset($_GET['brand']) ? intval($_GET['brand']) : 0;

// Filter by both category and brand if both are selected
if ($active_category > 0 && $active_brand > 0) {
    $filtered_products = filter_products_by_category_ctr($active_category);
    // Further filter by brand
    $filtered_products = array_filter($filtered_products, function($product) use ($active_brand) {
        return $product['product_brand'] == $active_brand;
    });
} elseif ($active_category > 0) {
    $filtered_products = filter_products_by_category_ctr($active_category);
} elseif ($active_brand > 0) {
    $filtered_products = filter_products_by_brand_ctr($active_brand);
}

// Pagination
$items_per_page = 12;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$total_products = count($filtered_products);
$total_pages = ceil($total_products / $items_per_page);
$offset = ($current_page - 1) * $items_per_page;
$paginated_products = array_slice($filtered_products, $offset, $items_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Products - Shop Now</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../css/index.css" rel="stylesheet">
    <style>
        body {
            background: var(--gray-50);
        }

        .products-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
        }

        .products-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .filter-bar {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 32px;
        }

        .filter-btn {
            background: white;
            border: 2px solid var(--gray-300);
            color: var(--gray-700);
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            cursor: pointer;
        }

        .filter-btn:hover, .filter-btn.active {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--gray-50);
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        .product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
        }

        .product-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-4px);
        }

        .product-image {
            width: 100%;
            height: 280px;
            object-fit: cover;
            background: var(--gray-100);
        }

        .product-image-placeholder {
            width: 100%;
            height: 280px;
            background: linear-gradient(135deg, var(--gray-100) 0%, var(--gray-200) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-400);
        }

        .product-card-body {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .product-category {
            font-size: 0.85rem;
            color: var(--gray-600);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .product-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .product-brand {
            font-size: 0.9rem;
            color: var(--gray-600);
            margin-bottom: 12px;
        }

        .product-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 16px;
            margin-top: auto;
        }

        .btn-add-cart {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            width: 100%;
        }

        .btn-add-cart:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(103, 146, 103, 0.3);
        }

        .pagination {
            justify-content: center;
            margin-top: 40px;
        }

        .page-link {
            color: var(--primary);
            border-color: var(--gray-300);
        }

        .page-link:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .page-item.active .page-link {
            background: var(--primary);
            border-color: var(--primary);
        }

        .back-link {
            color: var(--gray-700);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
        }

        .back-link:hover {
            color: var(--primary);
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

    <!-- Products Header -->
    <div class="products-header">
        <div class="container">
            <h1><i class="fa fa-shopping-bag"></i> All Products</h1>
            <p>Discover amazing products from top brands</p>
        </div>
    </div>

    <div class="container">
        <a href="../index.php" class="back-link">
            <i class="fa fa-arrow-left"></i> Back to Home
        </a>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Filter by Category</label>
                    <select class="form-select" id="categoryFilter" onchange="applyFilter()">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['cat_id']; ?>" <?php echo $active_category == $cat['cat_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['cat_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Filter by Brand</label>
                    <select class="form-select" id="brandFilter" onchange="applyFilter()">
                        <option value="">All Brands</option>
                        <?php foreach ($brands as $brand): ?>
                            <option value="<?php echo $brand['brand_id']; ?>" <?php echo $active_brand == $brand['brand_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($brand['brand_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Products Count -->
        <div class="mb-3">
            <p class="text-muted">Showing <?php echo count($paginated_products); ?> of <?php echo $total_products; ?> products</p>
        </div>

        <!-- Product Grid -->
        <?php if (empty($paginated_products)): ?>
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i> No products found. Try adjusting your filters.
            </div>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($paginated_products as $product): ?>
                    <div class="product-card">
                        <a href="single_product.php?id=<?php echo $product['product_id']; ?>" style="text-decoration: none; color: inherit;">
                            <?php if (!empty($product['product_image'])): ?>
                                <img src="../<?php echo htmlspecialchars($product['product_image']); ?>"
                                     alt="<?php echo htmlspecialchars($product['product_title']); ?>"
                                     class="product-image">
                            <?php else: ?>
                                <div class="product-image-placeholder">
                                    <i class="fa fa-image fa-4x"></i>
                                </div>
                            <?php endif; ?>
                        </a>

                        <div class="product-card-body">
                            <div class="product-category">
                                <i class="fa fa-tag"></i> <?php echo htmlspecialchars($product['cat_name']); ?>
                            </div>
                            <a href="single_product.php?id=<?php echo $product['product_id']; ?>" style="text-decoration: none; color: inherit;">
                                <h3 class="product-title"><?php echo htmlspecialchars($product['product_title']); ?></h3>
                            </a>
                            <div class="product-brand">
                                <i class="fa fa-bookmark"></i> <?php echo htmlspecialchars($product['brand_name']); ?>
                            </div>
                            <div class="product-price">
                                GHS <?php echo number_format($product['product_price'], 2); ?>
                            </div>
                            <button class="btn-add-cart add-to-cart-btn" data-product-id="<?php echo $product['product_id']; ?>">
                                <i class="fa fa-shopping-cart"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav>
                    <ul class="pagination">
                        <?php if ($current_page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $current_page - 1; ?><?php echo $active_category ? '&category='.$active_category : ''; ?><?php echo $active_brand ? '&brand='.$active_brand : ''; ?>">Previous</a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo $active_category ? '&category='.$active_category : ''; ?><?php echo $active_brand ? '&brand='.$active_brand : ''; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($current_page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $current_page + 1; ?><?php echo $active_category ? '&category='.$active_category : ''; ?><?php echo $active_brand ? '&brand='.$active_brand : ''; ?>">Next</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/cart.js"></script>
    <script>
        function applyFilter() {
            const category = document.getElementById('categoryFilter').value;
            const brand = document.getElementById('brandFilter').value;

            let url = 'all_product.php?';
            if (category) url += 'category=' + category;
            if (brand) {
                if (category) url += '&';
                url += 'brand=' + brand;
            }

            window.location.href = url || 'all_product.php';
        }

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
