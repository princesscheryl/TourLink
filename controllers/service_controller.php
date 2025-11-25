<?php
require_once __DIR__ . '/../classes/service_class.php';

/**
 * Service Controller for TourLink Platform
 * Contains wrapper functions for service operations
 */

// Add new service
function add_service_ctr($provider_id, $category_id, $title, $description, $base_price, $pricing_unit, $service_location, $available_regions = null, $max_capacity = null, $service_images = null, $festival_id = null)
{
    $service = new Service();
    $service_id = $service->add_service($provider_id, $category_id, $title, $description, $base_price, $pricing_unit, $service_location, $available_regions, $max_capacity, $service_images, $festival_id);

    if ($service_id) {
        return $service_id;
    } else {
        return false;
    }
}

// Get all active services
function get_all_services_ctr()
{
    $service = new Service();
    $result = $service->get_all_services();

    if ($result) {
        return $result;
    } else {
        return false;
    }
}

// Alias for consistency with old code
function view_all_services_ctr()
{
    return get_all_services_ctr();
}

// Get service by ID
function get_service_by_id_ctr($service_id)
{
    $service = new Service();
    $result = $service->get_service_by_id($service_id);

    if ($result) {
        return $result;
    } else {
        return false;
    }
}

// Get services by category
function get_services_by_category_ctr($category_id)
{
    $service = new Service();
    $result = $service->get_services_by_category($category_id);

    if ($result) {
        return $result;
    } else {
        return false;
    }
}

// Get services by provider
function get_services_by_provider_ctr($provider_id)
{
    $service = new Service();
    $result = $service->get_services_by_provider($provider_id);

    if ($result) {
        return $result;
    } else {
        return false;
    }
}

// Search services
function search_services_ctr($keyword)
{
    $service = new Service();
    $result = $service->search_services($keyword);

    if ($result) {
        return $result;
    } else {
        return false;
    }
}

// Update service
function update_service_ctr($service_id, $data)
{
    $service = new Service();
    return $service->update_service($service_id, $data);
}

// Delete service
function delete_service_ctr($service_id)
{
    $service = new Service();
    return $service->delete_service($service_id);
}

// Increment service views
function increment_service_views_ctr($service_id)
{
    $service = new Service();
    return $service->increment_views($service_id);
}

// Get premium services
function get_premium_services_ctr($limit = 6)
{
    $service = new Service();
    $result = $service->get_premium_services($limit);

    if ($result) {
        return $result;
    } else {
        return false;
    }
}

// Get services by festival
function get_services_by_festival_ctr($festival_id)
{
    $service = new Service();
    $result = $service->get_services_by_festival($festival_id);

    if ($result) {
        return $result;
    } else {
        return false;
    }
}
?>
