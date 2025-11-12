<?php
require_once __DIR__.'/../settings/db_class.php';

class Brand extends db_connection
{
    public function __construct()
    {
        $this->db_connect();
    }

    // Add new brand to database
    public function add($brand_name)
    {
        // Check if brand name already exists
        $stmt = $this->db->prepare("SELECT brand_id FROM brands WHERE brand_name = ?");
        $stmt->bind_param("s", $brand_name);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            return false; // Brand already exists
        }

        // Insert new brand
        $stmt = $this->db->prepare("INSERT INTO brands (brand_name) VALUES (?)");
        $stmt->bind_param("s", $brand_name);
        if ($stmt->execute()) {
            return $this->last_insert_id();
        }
        return false;
    }

    // Retrieve all brands
    public function get()
    {
        $sql = "SELECT brand_id, brand_name FROM brands ORDER BY brand_name ASC";
        return $this->db_fetch_all($sql);
    }

    // Get brands organized by categories (based on products)
    public function getBrandsGroupedByCategory()
    {
        $sql = "SELECT DISTINCT b.brand_id, b.brand_name, c.cat_id, c.cat_name
                FROM brands b
                LEFT JOIN products p ON b.brand_id = p.product_brand
                LEFT JOIN categories c ON p.product_cat = c.cat_id
                ORDER BY c.cat_name, b.brand_name ASC";
        return $this->db_fetch_all($sql);
    }

    // Get single brand by ID
    public function getBrandById($brand_id)
    {
        $stmt = $this->db->prepare("SELECT brand_id, brand_name FROM brands WHERE brand_id = ?");
        $stmt->bind_param("i", $brand_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Update existing brand
    public function edit($brand_id, $brand_name)
    {
        // Check if new brand name already exists in other brands
        $stmt = $this->db->prepare("SELECT brand_id FROM brands WHERE brand_name = ? AND brand_id != ?");
        $stmt->bind_param("si", $brand_name, $brand_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            return false; // Brand name already exists
        }

        // Update brand
        $stmt = $this->db->prepare("UPDATE brands SET brand_name = ? WHERE brand_id = ?");
        $stmt->bind_param("si", $brand_name, $brand_id);
        return $stmt->execute();
    }

    // Delete brand from database
    public function delete($brand_id)
    {
        $stmt = $this->db->prepare("DELETE FROM brands WHERE brand_id = ?");
        $stmt->bind_param("i", $brand_id);
        return $stmt->execute();
    }

    // Get brand by name
    public function getBrandByName($brand_name)
    {
        $stmt = $this->db->prepare("SELECT * FROM brands WHERE brand_name = ?");
        $stmt->bind_param("s", $brand_name);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
?>
