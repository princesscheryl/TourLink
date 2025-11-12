<?php
require_once __DIR__ . '/../classes/service_category_class.php';

/**
 * Service Category Controller for TourLink Platform
 * Contains wrapper functions for service category operations
 */

// Add new category
function add_service_category_ctr($category_name, $category_description = null, $icon_url = null, $display_order = 0)
{
    $category = new ServiceCategory();
    $category_id = $category->add_category($category_name, $category_description, $icon_url, $display_order);

    if ($category_id) {
        return $category_id;
    } else {
        return false;
    }
}

// Get all categories
function get_all_service_categories_ctr()
{
    $category = new ServiceCategory();
    $result = $category->get_all_categories();

    if ($result) {
        return $result;
    } else {
        return false;
    }
}

// Alias for consistency
function get_all_categories_ctr()
{
    return get_all_service_categories_ctr();
}

// Get category by ID
function get_service_category_by_id_ctr($category_id)
{
    $category = new ServiceCategory();
    $result = $category->get_category_by_id($category_id);

    if ($result) {
        return $result;
    } else {
        return false;
    }
}

// Update category
function update_service_category_ctr($category_id, $category_name, $category_description = null, $icon_url = null, $display_order = 0)
{
    $category = new ServiceCategory();
    return $category->update_category($category_id, $category_name, $category_description, $icon_url, $display_order);
}

// Delete category
function delete_service_category_ctr($category_id)
{
    $category = new ServiceCategory();
    return $category->delete_category($category_id);
}

// Get categories with service count
function get_categories_with_count_ctr()
{
    $category = new ServiceCategory();
    $result = $category->get_categories_with_count();

    if ($result) {
        return $result;
    } else {
        return false;
    }
}
?>
