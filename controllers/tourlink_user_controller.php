<?php
require_once __DIR__ . '/../classes/tourlink_user_class.php';

/**
 * Register new TourLink user
 */
function register_tourlink_user_ctr($email, $password, $user_type, $first_name, $last_name, $phone = null)
{
    $user = new TourLinkUser();
    return $user->register_user($email, $password, $user_type, $first_name, $last_name, $phone);
}

/**
 * Login TourLink user
 */
function login_tourlink_user_ctr($email, $password)
{
    $user = new TourLinkUser();
    return $user->login_user($email, $password);
}

/**
 * Check if email exists
 */
function tourlink_email_exists_ctr($email)
{
    $user = new TourLinkUser();
    return $user->email_exists($email);
}

/**
 * Get user by ID
 */
function get_tourlink_user_by_id_ctr($user_id)
{
    $user = new TourLinkUser();
    return $user->get_user_by_id($user_id);
}

/**
 * Get user by email
 */
function get_tourlink_user_by_email_ctr($email)
{
    $user = new TourLinkUser();
    return $user->get_user_by_email($email);
}

/**
 * Update user profile
 */
function update_tourlink_user_ctr($user_id, $data)
{
    $user = new TourLinkUser();
    return $user->update_user($user_id, $data);
}

/**
 * Update user password
 */
function update_tourlink_password_ctr($user_id, $new_password)
{
    $user = new TourLinkUser();
    return $user->update_password($user_id, $new_password);
}

/**
 * Verify user email
 */
function verify_tourlink_email_ctr($user_id)
{
    $user = new TourLinkUser();
    return $user->verify_email($user_id);
}

/**
 * Update account status
 */
function update_tourlink_account_status_ctr($user_id, $status)
{
    $user = new TourLinkUser();
    return $user->update_account_status($user_id, $status);
}
?>
