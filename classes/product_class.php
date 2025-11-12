<?php
require_once __DIR__.'/../settings/db_class.php';

class Product extends db_connection
{
    public function __construct()
    {
        $this->db_connect();
    }

    // Add new product to database
    public function add($cat_id, $brand_id, $title, $price, $desc, $image, $keywords)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO products (product_cat, product_brand, product_title, product_price, product_desc, product_image, product_keywords)
            VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("iisdsss", $cat_id, $brand_id, $title, $price, $desc, $image, $keywords);

        if ($stmt->execute()) {
            return $this->last_insert_id();
        }
        return false;
    }

    // Retrieve all products - wrapper for view_all_products
    public function get()
    {
        return $this->view_all_products();
    }

    // Fetch all products with category and brand details
    public function view_all_products()
    {
        $sql = "SELECT p.*, c.cat_name, b.brand_name
                FROM products p
                INNER JOIN categories c ON p.product_cat = c.cat_id
                INNER JOIN brands b ON p.product_brand = b.brand_id
                ORDER BY p.product_id DESC";
        return $this->db_fetch_all($sql);
    }

    // Get single product by ID - wrapper for view_single_product
    public function getProductById($product_id)
    {
        return $this->view_single_product($product_id);
    }

    // Retrieve detailed information for a specific product
    public function view_single_product($id)
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, c.cat_name, b.brand_name
            FROM products p
            INNER JOIN categories c ON p.product_cat = c.cat_id
            INNER JOIN brands b ON p.product_brand = b.brand_id
            WHERE p.product_id = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Update existing product
    public function edit($product_id, $cat_id, $brand_id, $title, $price, $desc, $image, $keywords)
    {
        // If image is empty, don't update it
        if (empty($image)) {
            $stmt = $this->db->prepare(
                "UPDATE products
                SET product_cat = ?, product_brand = ?, product_title = ?, product_price = ?,
                    product_desc = ?, product_keywords = ?
                WHERE product_id = ?"
            );
            $stmt->bind_param("iisdssi", $cat_id, $brand_id, $title, $price, $desc, $keywords, $product_id);
        } else {
            $stmt = $this->db->prepare(
                "UPDATE products
                SET product_cat = ?, product_brand = ?, product_title = ?, product_price = ?,
                    product_desc = ?, product_image = ?, product_keywords = ?
                WHERE product_id = ?"
            );
            $stmt->bind_param("iisdsssi", $cat_id, $brand_id, $title, $price, $desc, $image, $keywords, $product_id);
        }

        return $stmt->execute();
    }

    // Delete product from database
    public function delete($product_id)
    {
        $stmt = $this->db->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        return $stmt->execute();
    }

    // Get products by category - wrapper for filter function
    public function getProductsByCategory($cat_id)
    {
        return $this->filter_products_by_category($cat_id);
    }

    // Retrieve products filtered by specific category
    public function filter_products_by_category($cat_id)
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, c.cat_name, b.brand_name
            FROM products p
            INNER JOIN categories c ON p.product_cat = c.cat_id
            INNER JOIN brands b ON p.product_brand = b.brand_id
            WHERE p.product_cat = ?
            ORDER BY p.product_id DESC"
        );
        $stmt->bind_param("i", $cat_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Get products by brand - wrapper for filter function
    public function getProductsByBrand($brand_id)
    {
        return $this->filter_products_by_brand($brand_id);
    }

    // Retrieve products filtered by specific brand
    public function filter_products_by_brand($brand_id)
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, c.cat_name, b.brand_name
            FROM products p
            INNER JOIN categories c ON p.product_cat = c.cat_id
            INNER JOIN brands b ON p.product_brand = b.brand_id
            WHERE p.product_brand = ?
            ORDER BY p.product_id DESC"
        );
        $stmt->bind_param("i", $brand_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Search products by keyword - wrapper for search function
    public function searchProducts($keyword)
    {
        return $this->search_products($keyword);
    }

    // Search products by title, description, or keywords
    public function search_products($query)
    {
        $searchTerm = "%{$query}%";
        $stmt = $this->db->prepare(
            "SELECT p.*, c.cat_name, b.brand_name
            FROM products p
            INNER JOIN categories c ON p.product_cat = c.cat_id
            INNER JOIN brands b ON p.product_brand = b.brand_id
            WHERE p.product_title LIKE ? OR p.product_desc LIKE ? OR p.product_keywords LIKE ?
            ORDER BY p.product_id DESC"
        );
        $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Advanced search with multiple filters - supports query, category, brand, and price range
    public function composite_search($query = '', $cat_id = null, $brand_id = null, $min_price = null, $max_price = null)
    {
        $sql = "SELECT p.*, c.cat_name, b.brand_name
                FROM products p
                INNER JOIN categories c ON p.product_cat = c.cat_id
                INNER JOIN brands b ON p.product_brand = b.brand_id
                WHERE 1=1";

        $params = [];
        $types = '';

        if (!empty($query)) {
            $sql .= " AND (p.product_title LIKE ? OR p.product_desc LIKE ? OR p.product_keywords LIKE ?)";
            $searchTerm = "%{$query}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= 'sss';
        }

        if ($cat_id !== null && $cat_id > 0) {
            $sql .= " AND p.product_cat = ?";
            $params[] = $cat_id;
            $types .= 'i';
        }

        if ($brand_id !== null && $brand_id > 0) {
            $sql .= " AND p.product_brand = ?";
            $params[] = $brand_id;
            $types .= 'i';
        }

        if ($min_price !== null && $min_price >= 0) {
            $sql .= " AND p.product_price >= ?";
            $params[] = $min_price;
            $types .= 'd';
        }

        if ($max_price !== null && $max_price > 0) {
            $sql .= " AND p.product_price <= ?";
            $params[] = $max_price;
            $types .= 'd';
        }

        $sql .= " ORDER BY p.product_id DESC";

        if (empty($params)) {
            return $this->db_fetch_all($sql);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>
