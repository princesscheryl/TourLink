<?php
session_start();
require_once '../settings/core.php';
require_once '../controllers/product_controller.php';
require_once '../controllers/category_controller.php';
require_once '../controllers/brand_controller.php';

// Get search query and filters
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$active_category = isset($_GET['category']) ? intval($_GET['category']) : 0;
$active_brand = isset($_GET['brand']) ? intval($_GET['brand']) : 0;
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : null;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : null;

// Get all categories and brands for filters
$categories = get_all_categories_ctr();
$brands = get_all_brands_ctr();

// Perform search with composite filters (EXTRA CREDIT)
if (!empty($search_query) || $active_category > 0 || $active_brand > 0 || $min_price !== null || $max_price !== null) {
    $search_results = composite_search_ctr($search_query, $active_category, $active_brand, $min_price, $max_price);
} else {
    $search_results = [];
}

// Pagination
$items_per_page = 12;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$total_results = count($search_results);
$total_pages = ceil($total_results / $items_per_page);
$offset = ($current_page - 1) * $items_per_page;
$paginated_results = array_slice($search_results, $offset, $items_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results<?php echo !empty($search_query) ? ' - ' . htmlspecialchars($search_query) : ''; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../css/index.css" rel="stylesheet">
    <style>
        body {
            background: var(--gray-50);
        }

        .search-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
        }

        .search-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 16px;
        }

        .search-query-display {
            font-size: 1.2rem;
            opacity: 0.95;
        }

        .search-bar-section {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: var(--shadow-md);
            margin-bottom: 32px;
        }

        .search-input-main {
            flex: 1;
            padding: 16px 24px;
            border: 2px solid var(--gray-300);
            border-radius: 8px;
            font-size: 1rem;
            outline: none;
        }

        .search-input-main:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(103, 146, 103, 0.1);
        }

        .search-btn-main {
            background: var(--primary);
            color: white;
            border: none;
            padding: 16px 32px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .search-btn-main:hover {
            background: var(--primary-dark);
        }

        .filter-section {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 32px;
        }

        .filter-title {
            font-weight: 700;
            margin-bottom: 16px;
            color: var(--gray-900);
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

        .active-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 24px;
        }

        .filter-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: var(--primary-light);
            color: white;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .filter-badge i {
            cursor: pointer;
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

    <!-- Search Header -->
    <div class="search-header">
        <div class="container">
            <h1><i class="fa fa-search"></i> Search Results</h1>
            <?php if (!empty($search_query)): ?>
                <div class="search-query-display">
                    Showing results for: <strong>"<?php echo htmlspecialchars($search_query); ?>"</strong>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="container">
        <!-- Search Bar -->
        <div class="search-bar-section">
            <form method="GET" action="product_search_result.php">
                <div class="d-flex gap-2">
                    <input type="text" name="q" class="search-input-main"
                           placeholder="Search for products..."
                           value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit" class="search-btn-main">
                        <i class="fa fa-search"></i> Search
                    </button>
                </div>
            </form>
        </div>

        <!-- Active Filters Display -->
        <?php if ($active_category > 0 || $active_brand > 0 || $min_price !== null || $max_price !== null): ?>
            <div class="active-filters">
                <strong style="align-self: center; margin-right: 8px;">Active Filters:</strong>
                <?php if ($active_category > 0):
                    $cat_name = '';
                    foreach ($categories as $cat) {
                        if ($cat['cat_id'] == $active_category) {
                            $cat_name = $cat['cat_name'];
                            break;
                        }
                    }
                ?>
                    <span class="filter-badge">
                        Category: <?php echo htmlspecialchars($cat_name); ?>
                        <i class="fa fa-times" onclick="removeFilter('category')"></i>
                    </span>
                <?php endif; ?>

                <?php if ($active_brand > 0):
                    $brand_name = '';
                    foreach ($brands as $brand) {
                        if ($brand['brand_id'] == $active_brand) {
                            $brand_name = $brand['brand_name'];
                            break;
                        }
                    }
                ?>
                    <span class="filter-badge">
                        Brand: <?php echo htmlspecialchars($brand_name); ?>
                        <i class="fa fa-times" onclick="removeFilter('brand')"></i>
                    </span>
                <?php endif; ?>

                <?php if ($min_price !== null || $max_price !== null): ?>
                    <span class="filter-badge">
                        Price: $<?php echo $min_price ?? '0'; ?> - $<?php echo $max_price ?? '∞'; ?>
                        <i class="fa fa-times" onclick="removeFilter('price')"></i>
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Filter Section -->
        <div class="filter-section">
            <h3 class="filter-title"><i class="fa fa-filter"></i> Refine Your Search</h3>
            <form method="GET" action="product_search_result.php" id="filterForm">
                <input type="hidden" name="q" value="<?php echo htmlspecialchars($search_query); ?>">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Category</label>
                        <select class="form-select" name="category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['cat_id']; ?>" <?php echo $active_category == $cat['cat_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['cat_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Brand</label>
                        <select class="form-select" name="brand">
                            <option value="">All Brands</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?php echo $brand['brand_id']; ?>" <?php echo $active_brand == $brand['brand_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($brand['brand_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Min Price</label>
                        <input type="number" class="form-control" name="min_price"
                               placeholder="$0" step="0.01" min="0"
                               value="<?php echo $min_price !== null ? $min_price : ''; ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Max Price</label>
                        <input type="number" class="form-control" name="max_price"
                               placeholder="No limit" step="0.01" min="0"
                               value="<?php echo $max_price !== null ? $max_price : ''; ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fa fa-filter"></i> Apply
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Results Count -->
        <div class="mb-3">
            <p class="text-muted">
                <?php if ($total_results > 0): ?>
                    Found <?php echo $total_results; ?> result<?php echo $total_results != 1 ? 's' : ''; ?>
                <?php else: ?>
                    No results found
                <?php endif; ?>
            </p>
        </div>

        <!-- Product Grid -->
        <?php if (empty($paginated_results)): ?>
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i>
                <?php if (empty($search_query) && $active_category == 0 && $active_brand == 0): ?>
                    Enter a search term or select filters to find products.
                <?php else: ?>
                    No products found matching your search criteria. Try adjusting your filters.
                <?php endif; ?>
            </div>
            <div class="text-center mb-4">
                <a href="all_product.php" class="btn btn-primary">
                    <i class="fa fa-shopping-bag"></i> Browse All Products
                </a>
            </div>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($paginated_results as $product): ?>
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
                                $<?php echo number_format($product['product_price'], 2); ?>
                            </div>
                            <button class="btn-add-cart" onclick="alert('Add to cart functionality coming soon!')">
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
                                <a class="page-link" href="?page=<?php echo $current_page - 1; ?>&q=<?php echo urlencode($search_query); ?><?php echo $active_category ? '&category='.$active_category : ''; ?><?php echo $active_brand ? '&brand='.$active_brand : ''; ?><?php echo $min_price !== null ? '&min_price='.$min_price : ''; ?><?php echo $max_price !== null ? '&max_price='.$max_price : ''; ?>">Previous</a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&q=<?php echo urlencode($search_query); ?><?php echo $active_category ? '&category='.$active_category : ''; ?><?php echo $active_brand ? '&brand='.$active_brand : ''; ?><?php echo $min_price !== null ? '&min_price='.$min_price : ''; ?><?php echo $max_price !== null ? '&max_price='.$max_price : ''; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($current_page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $current_page + 1; ?>&q=<?php echo urlencode($search_query); ?><?php echo $active_category ? '&category='.$active_category : ''; ?><?php echo $active_brand ? '&brand='.$active_brand : ''; ?><?php echo $min_price !== null ? '&min_price='.$min_price : ''; ?><?php echo $max_price !== null ? '&max_price='.$max_price : ''; ?>">Next</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div style="height: 80px;"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function removeFilter(filterType) {
            const urlParams = new URLSearchParams(window.location.search);

            if (filterType === 'category') {
                urlParams.delete('category');
            } else if (filterType === 'brand') {
                urlParams.delete('brand');
            } else if (filterType === 'price') {
                urlParams.delete('min_price');
                urlParams.delete('max_price');
            }

            window.location.href = 'product_search_result.php?' + urlParams.toString();
        }
    </script>
</body>
</html>
