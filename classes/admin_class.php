<?php
require_once __DIR__ . '/../settings/db_class.php';

/**
 * Admin Class for TourLink Platform
 * Handles admin authentication and management
 */
class Admin extends db_connection
{
    public function __construct()
    {
        $this->db_connect();
    }

    /**
     * Authenticate admin login
     */
    public function admin_login($email, $password)
    {
        $stmt = $this->db->prepare("SELECT * FROM tl_admins WHERE email = ? AND is_active = 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();

        if ($admin && password_verify($password, $admin['password'])) {
            // Update last login
            $update = $this->db->prepare("UPDATE tl_admins SET last_login = NOW() WHERE admin_id = ?");
            $update->bind_param("i", $admin['admin_id']);
            $update->execute();
            return $admin;
        }
        return false;
    }

    /**
     * Get admin by ID
     */
    public function get_admin_by_id($admin_id)
    {
        $stmt = $this->db->prepare("SELECT * FROM tl_admins WHERE admin_id = ?");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Get platform statistics for dashboard
     */
    public function get_platform_stats()
    {
        $stats = [];

        // Total revenue generated for providers
        $result = $this->db->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM tl_bookings WHERE booking_status IN ('confirmed', 'completed')");
        $stats['total_revenue'] = $result->fetch_assoc()['total'];

        // Total bookings
        $result = $this->db->query("SELECT COUNT(*) as total FROM tl_bookings");
        $stats['total_bookings'] = $result->fetch_assoc()['total'];

        // Completed bookings
        $result = $this->db->query("SELECT COUNT(*) as total FROM tl_bookings WHERE booking_status = 'completed'");
        $stats['completed_bookings'] = $result->fetch_assoc()['total'];

        // Total providers
        $result = $this->db->query("SELECT COUNT(*) as total FROM tl_service_providers WHERE verification_status = 'verified'");
        $stats['total_providers'] = $result->fetch_assoc()['total'];

        // Total tourists (users who are not providers)
        $result = $this->db->query("SELECT COUNT(*) as total FROM tl_users WHERE user_type = 'tourist'");
        $stats['total_tourists'] = $result->fetch_assoc()['total'];

        // Total services
        $result = $this->db->query("SELECT COUNT(*) as total FROM tl_services WHERE service_status = 'active'");
        $stats['total_services'] = $result->fetch_assoc()['total'];

        // This month's bookings
        $result = $this->db->query("SELECT COUNT(*) as total FROM tl_bookings WHERE MONTH(booking_date) = MONTH(CURRENT_DATE()) AND YEAR(booking_date) = YEAR(CURRENT_DATE())");
        $stats['monthly_bookings'] = $result->fetch_assoc()['total'];

        // This month's revenue
        $result = $this->db->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM tl_bookings WHERE booking_status IN ('confirmed', 'completed') AND MONTH(booking_date) = MONTH(CURRENT_DATE()) AND YEAR(booking_date) = YEAR(CURRENT_DATE())");
        $stats['monthly_revenue'] = $result->fetch_assoc()['total'];

        // Pending bookings
        $result = $this->db->query("SELECT COUNT(*) as total FROM tl_bookings WHERE booking_status = 'pending'");
        $stats['pending_bookings'] = $result->fetch_assoc()['total'];

        // Average booking value
        $result = $this->db->query("SELECT COALESCE(AVG(total_amount), 0) as avg FROM tl_bookings WHERE booking_status IN ('confirmed', 'completed')");
        $stats['avg_booking_value'] = $result->fetch_assoc()['avg'];

        return $stats;
    }

    /**
     * Get regional breakdown of providers
     */
    public function get_regional_stats()
    {
        $sql = "SELECT region, COUNT(*) as provider_count
                FROM tl_service_providers
                WHERE verification_status = 'verified'
                GROUP BY region
                ORDER BY provider_count DESC";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get booking trends (last 6 months)
     */
    public function get_booking_trends()
    {
        $sql = "SELECT
                    DATE_FORMAT(booking_date, '%Y-%m') as month,
                    COUNT(*) as bookings,
                    COALESCE(SUM(total_amount), 0) as revenue
                FROM tl_bookings
                WHERE booking_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(booking_date, '%Y-%m')
                ORDER BY month ASC";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get recent bookings for dashboard
     */
    public function get_recent_bookings($limit = 10)
    {
        $sql = "SELECT b.*, s.service_title, u.first_name, u.last_name, u.email
                FROM tl_bookings b
                JOIN tl_services s ON b.service_id = s.service_id
                JOIN tl_users u ON b.tourist_id = u.user_id
                ORDER BY b.booking_date DESC
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get all users
     */
    public function get_all_users($type = null)
    {
        if ($type) {
            $stmt = $this->db->prepare("SELECT * FROM tl_users WHERE user_type = ? ORDER BY date_created DESC");
            $stmt->bind_param("s", $type);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
        return $this->db_fetch_all("SELECT * FROM tl_users ORDER BY date_created DESC");
    }

    /**
     * Get all providers with details
     */
    public function get_all_providers()
    {
        $sql = "SELECT sp.*, u.first_name, u.last_name, u.email, u.phone,
                       (SELECT COUNT(*) FROM tl_services WHERE provider_id = sp.provider_id) as service_count,
                       (SELECT COUNT(*) FROM tl_bookings b JOIN tl_services s ON b.service_id = s.service_id WHERE s.provider_id = sp.provider_id) as booking_count
                FROM tl_service_providers sp
                JOIN tl_users u ON sp.user_id = u.user_id
                ORDER BY sp.date_registered DESC";
        return $this->db_fetch_all($sql);
    }

    /**
     * Get all bookings with details
     */
    public function get_all_bookings()
    {
        $sql = "SELECT b.*,
                       s.service_title, s.base_price,
                       u.first_name as tourist_first, u.last_name as tourist_last, u.email as tourist_email,
                       sp.business_name as provider_name
                FROM tl_bookings b
                JOIN tl_services s ON b.service_id = s.service_id
                JOIN tl_users u ON b.tourist_id = u.user_id
                JOIN tl_service_providers sp ON s.provider_id = sp.provider_id
                ORDER BY b.booking_date DESC";
        return $this->db_fetch_all($sql);
    }

    /**
     * Update booking status
     */
    public function update_booking_status($booking_id, $status)
    {
        $stmt = $this->db->prepare("UPDATE tl_bookings SET booking_status = ? WHERE booking_id = ?");
        $stmt->bind_param("si", $status, $booking_id);
        return $stmt->execute();
    }

    /**
     * Get impact metrics for ADF dashboard
     */
    public function get_impact_metrics()
    {
        $metrics = [];

        // Jobs supported (providers + estimated employees)
        $result = $this->db->query("SELECT COUNT(*) as providers FROM tl_service_providers WHERE verification_status = 'verified'");
        $providers = $result->fetch_assoc()['providers'];
        $metrics['jobs_supported'] = $providers * 2; // Estimate 2 jobs per provider

        // Communities reached (unique regions/locations)
        $result = $this->db->query("SELECT COUNT(DISTINCT region) as regions FROM tl_service_providers");
        $metrics['communities_reached'] = $result->fetch_assoc()['regions'];

        // Total tourist arrivals (unique users who booked)
        $result = $this->db->query("SELECT COUNT(DISTINCT tourist_id) as tourists FROM tl_bookings");
        $metrics['tourist_arrivals'] = $result->fetch_assoc()['tourists'];

        // Provider income generated
        $result = $this->db->query("SELECT COALESCE(SUM(total_amount * 0.85), 0) as income FROM tl_bookings WHERE booking_status IN ('confirmed', 'completed')");
        $metrics['provider_income'] = $result->fetch_assoc()['income']; // 85% goes to providers

        // Platform commission
        $result = $this->db->query("SELECT COALESCE(SUM(total_amount * 0.15), 0) as commission FROM tl_bookings WHERE booking_status IN ('confirmed', 'completed')");
        $metrics['platform_commission'] = $result->fetch_assoc()['commission'];

        // Growth rate (compare this month to last month)
        $result = $this->db->query("
            SELECT
                (SELECT COUNT(*) FROM tl_bookings WHERE MONTH(booking_date) = MONTH(CURRENT_DATE()) AND YEAR(booking_date) = YEAR(CURRENT_DATE())) as this_month,
                (SELECT COUNT(*) FROM tl_bookings WHERE MONTH(booking_date) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH)) AND YEAR(booking_date) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))) as last_month
        ");
        $growth = $result->fetch_assoc();
        $metrics['growth_rate'] = $growth['last_month'] > 0 ? round((($growth['this_month'] - $growth['last_month']) / $growth['last_month']) * 100, 1) : 0;

        return $metrics;
    }
}
?>
