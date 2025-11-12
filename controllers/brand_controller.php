<?php
require_once __DIR__ . '/../classes/brand_class.php';

// Add brand
function add_brand_ctr($brand_name)
{
    $brand = new Brand();
    $brand_id = $brand->add($brand_name);
    if ($brand_id) {
        return $brand_id;
    } else {
        return false;
    }
}

// Get all brands
function get_all_brands_ctr()
{
    $brand = new Brand();
    $result = $brand->get();
    if ($result) {
        return $result;
    } else {
        return false;
    }
}

// Get brands grouped by category (based on which products use them)
function get_brands_grouped_by_category_ctr()
{
    $brand = new Brand();
    $result = $brand->getBrandsGroupedByCategory();
    if ($result) {
        return $result;
    } else {
        return false;
    }
}

// Get brand by ID
function get_brand_by_id_ctr($brand_id)
{
    $brand = new Brand();
    $result = $brand->getBrandById($brand_id);
    if ($result) {
        return $result;
    } else {
        return false;
    }
}

// Update brand
function update_brand_ctr($brand_id, $brand_name)
{
    $brand = new Brand();
    $updated = $brand->edit($brand_id, $brand_name);
    if ($updated) {
        return true;
    } else {
        return false;
    }
}

// Delete brand
function delete_brand_ctr($brand_id)
{
    $brand = new Brand();
    $deleted = $brand->delete($brand_id);
    if ($deleted) {
        return true;
    } else {
        return false;
    }
}

// Get brand by name
function get_brand_by_name_ctr($brand_name)
{
    $brand = new Brand();
    $result = $brand->getBrandByName($brand_name);
    if ($result) {
        return $result;
    } else {
        return false;
    }
}
?>
