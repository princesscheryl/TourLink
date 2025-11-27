<?php
/**
 * Admin Privileges Configuration
 * Defines what each role can and cannot do
 */

// Define all available privileges
$ADMIN_PRIVILEGES = [
    // Dashboard & Statistics
    'view_dashboard' => ['super_admin', 'admin', 'moderator'],
    'view_full_statistics' => ['super_admin', 'admin'],
    'view_financial_reports' => ['super_admin'],

    // Provider Management
    'view_providers' => ['super_admin', 'admin', 'moderator'],
    'approve_providers' => ['super_admin', 'admin'],
    'suspend_providers' => ['super_admin', 'admin'],
    'delete_providers' => ['super_admin'],

    // Service Management
    'view_services' => ['super_admin', 'admin', 'moderator'],
    'edit_services' => ['super_admin', 'admin'],
    'delete_services' => ['super_admin'],
    'feature_services' => ['super_admin', 'admin'],

    // Booking Management
    'view_bookings' => ['super_admin', 'admin', 'moderator'],
    'manage_disputes' => ['super_admin', 'admin'],
    'refund_bookings' => ['super_admin'],

    // User Management
    'view_users' => ['super_admin', 'admin', 'moderator'],
    'edit_users' => ['super_admin', 'admin'],
    'suspend_users' => ['super_admin', 'admin'],
    'delete_users' => ['super_admin'],

    // Festival Management
    'view_festivals' => ['super_admin', 'admin', 'moderator'],
    'add_festivals' => ['super_admin', 'admin'],
    'edit_festivals' => ['super_admin', 'admin'],
    'delete_festivals' => ['super_admin'],

    // Region & Content Management
    'manage_regions' => ['super_admin', 'admin'],
    'manage_categories' => ['super_admin', 'admin'],
    'manage_stories' => ['super_admin', 'admin'],

    // Admin Management
    'view_admins' => ['super_admin'],
    'add_admins' => ['super_admin'],
    'edit_admins' => ['super_admin'],
    'delete_admins' => ['super_admin'],

    // System Settings
    'view_settings' => ['super_admin'],
    'edit_settings' => ['super_admin'],
    'view_logs' => ['super_admin'],
];

/**
 * Check if current admin has a specific privilege
 */
function has_privilege($privilege) {
    global $ADMIN_PRIVILEGES;

    if (!isset($_SESSION['admin_role'])) {
        return false;
    }

    $role = $_SESSION['admin_role'];

    if (!isset($ADMIN_PRIVILEGES[$privilege])) {
        return false;
    }

    return in_array($role, $ADMIN_PRIVILEGES[$privilege]);
}

/**
 * Require a specific privilege or redirect
 */
function require_privilege($privilege, $redirect = 'platform_dashboard.php') {
    if (!has_privilege($privilege)) {
        $_SESSION['admin_error'] = 'You do not have permission to access this feature.';
        header("Location: $redirect");
        exit();
    }
}

/**
 * Get role display name
 */
function get_role_display_name($role) {
    $names = [
        'super_admin' => 'Super Administrator',
        'admin' => 'Administrator',
        'moderator' => 'Moderator'
    ];
    return $names[$role] ?? ucfirst($role);
}

/**
 * Get role badge color
 */
function get_role_badge_class($role) {
    $classes = [
        'super_admin' => 'badge-danger',
        'admin' => 'badge-primary',
        'moderator' => 'badge-secondary'
    ];
    return $classes[$role] ?? 'badge-secondary';
}

/**
 * Actions admins should NEVER be able to do (security constraints)
 */
$RESTRICTED_ACTIONS = [
    'Access user passwords (always hashed)',
    'Process payments directly (automated system only)',
    'Modify completed booking amounts',
    'Delete audit logs',
    'Access other admin accounts',
    'Export raw database',
];

/**
 * Audit log function for admin actions
 */
function log_admin_action($action, $details = []) {
    // In a real system, this would log to database
    $log_entry = [
        'admin_id' => $_SESSION['admin_id'] ?? null,
        'admin_email' => $_SESSION['admin_email'] ?? null,
        'action' => $action,
        'details' => json_encode($details),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        'timestamp' => date('Y-m-d H:i:s')
    ];

    // For now, we'll just return true
    // In production, insert into tl_admin_logs table
    return true;
}
