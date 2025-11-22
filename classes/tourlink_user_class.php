<?php
require_once __DIR__ . '/../settings/db_class.php';

/**
 * TourLink User Class
 * Handles user authentication and management for tl_users table
 */
class TourLinkUser extends db_connection
{
    public function __construct()
    {
        $this->db_connect();
    }

    /**
     * Register new user (tourist or provider)
     * @param string $email
     * @param string $password
     * @param string $user_type (tourist, provider, admin)
     * @param string $first_name
     * @param string $last_name
     * @param string $phone
     * @return int|bool User ID on success, false on failure
     */
    public function register_user($email, $password, $user_type, $first_name, $last_name, $phone = null)
    {
        // Check if email already exists
        if ($this->email_exists($email)) {
            return false;
        }

        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $account_status = ($user_type === 'provider') ? 'pending_verification' : 'active';

        $stmt = $this->db->prepare(
            "INSERT INTO tl_users (email, password_hash, user_type, first_name, last_name, phone, account_status)
            VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssssss", $email, $password_hash, $user_type, $first_name, $last_name, $phone, $account_status);

        if ($stmt->execute()) {
            return $this->last_insert_id();
        }
        return false;
    }

    /**
     * Login user
     * @param string $email
     * @param string $password
     * @return array|bool User data on success, false on failure
     */
    public function login_user($email, $password)
    {
        $stmt = $this->db->prepare("SELECT * FROM tl_users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Update last login
            $this->update_last_login($user['user_id']);

            // Remove password from returned data
            unset($user['password_hash']);
            return $user;
        }
        return false;
    }

    /**
     * Check if email exists
     * @param string $email
     * @return bool
     */
    public function email_exists($email)
    {
        $stmt = $this->db->prepare("SELECT user_id FROM tl_users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }

    /**
     * Get user by ID
     * @param int $user_id
     * @return array|bool User data or false
     */
    public function get_user_by_id($user_id)
    {
        $stmt = $this->db->prepare("SELECT * FROM tl_users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result) {
            unset($result['password_hash']);
        }
        return $result;
    }

    /**
     * Get user by email
     * @param string $email
     * @return array|bool User data or false
     */
    public function get_user_by_email($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM tl_users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result) {
            unset($result['password_hash']);
        }
        return $result;
    }

    /**
     * Update last login timestamp
     * @param int $user_id
     * @return bool
     */
    private function update_last_login($user_id)
    {
        $stmt = $this->db->prepare("UPDATE tl_users SET last_login = NOW() WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        return $stmt->execute();
    }

    /**
     * Update user profile
     * @param int $user_id
     * @param array $data Associative array of fields to update
     * @return bool
     */
    public function update_user($user_id, $data)
    {
        $allowed_fields = ['first_name', 'last_name', 'phone', 'profile_image'];
        $updates = [];
        $params = [];
        $types = '';

        foreach ($data as $field => $value) {
            if (in_array($field, $allowed_fields)) {
                $updates[] = "$field = ?";
                $params[] = $value;
                $types .= 's';
            }
        }

        if (empty($updates)) {
            return false;
        }

        $params[] = $user_id;
        $types .= 'i';

        $sql = "UPDATE tl_users SET " . implode(', ', $updates) . " WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);

        return $stmt->execute();
    }

    /**
     * Update user password
     * @param int $user_id
     * @param string $new_password
     * @return bool
     */
    public function update_password($user_id, $new_password)
    {
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE tl_users SET password_hash = ? WHERE user_id = ?");
        $stmt->bind_param("si", $password_hash, $user_id);

        return $stmt->execute();
    }

    /**
     * Verify user email
     * @param int $user_id
     * @return bool
     */
    public function verify_email($user_id)
    {
        $stmt = $this->db->prepare("UPDATE tl_users SET email_verified = TRUE WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);

        return $stmt->execute();
    }

    /**
     * Update account status
     * @param int $user_id
     * @param string $status (active, suspended, pending_verification)
     * @return bool
     */
    public function update_account_status($user_id, $status)
    {
        $valid_statuses = ['active', 'suspended', 'pending_verification'];

        if (!in_array($status, $valid_statuses)) {
            return false;
        }

        $stmt = $this->db->prepare("UPDATE tl_users SET account_status = ? WHERE user_id = ?");
        $stmt->bind_param("si", $status, $user_id);

        return $stmt->execute();
    }

    /**
     * Update user email
     * @param int $user_id
     * @param string $new_email
     * @return bool
     */
    public function update_email($user_id, $new_email)
    {
        $stmt = $this->db->prepare("UPDATE tl_users SET email = ? WHERE user_id = ?");
        $stmt->bind_param("si", $new_email, $user_id);

        return $stmt->execute();
    }

    /**
     * Update user preferences (language, country)
     * @param int $user_id
     * @param string $language
     * @param string $country
     * @return bool
     */
    public function update_preferences($user_id, $language, $country)
    {
        $stmt = $this->db->prepare("UPDATE tl_users SET preferred_language = ?, country = ? WHERE user_id = ?");
        $stmt->bind_param("ssi", $language, $country, $user_id);

        return $stmt->execute();
    }

    /**
     * Delete user account
     * @param int $user_id
     * @return bool
     */
    public function delete_user($user_id)
    {
        $stmt = $this->db->prepare("DELETE FROM tl_users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);

        return $stmt->execute();
    }
}
?>
