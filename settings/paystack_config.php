<?php
/**
 * Paystack Configuration for TourLink
 * Secure payment gateway settings for booking payments
 */

// Paystack API Keys (Test Mode)
define('PAYSTACK_SECRET_KEY', 'sk_test_5576530c1a4d158ab83a1e1c92da5e8b2f211724');
define('PAYSTACK_PUBLIC_KEY', 'pk_test_d54f882b0403bdc01fa6c02504cbe81b35d6fc8f');

// Paystack API URLs
define('PAYSTACK_API_URL', 'https://api.paystack.co');
define('PAYSTACK_INIT_ENDPOINT', PAYSTACK_API_URL . '/transaction/initialize');
define('PAYSTACK_VERIFY_ENDPOINT', PAYSTACK_API_URL . '/transaction/verify/');

// App Configuration
define('APP_ENVIRONMENT', 'test'); // Change to 'production' when going live

// TEMPORARY: Using simple callback for debugging
// Change back to paystack_callback.php once fixed
define('PAYSTACK_CALLBACK_URL', 'http://169.239.251.102:442/~princess.donkor/tourlink/view/paystack_callback_simple.php');
// define('PAYSTACK_CALLBACK_URL', 'http://169.239.251.102:442/~princess.donkor/tourlink/view/paystack_callback.php');

/**
 * Initialize a Paystack transaction
 *
 * @param float $amount Amount in GHS (will be converted to pesewas)
 * @param string $email Customer email
 * @param string $reference Transaction reference
 * @param array $metadata Additional metadata
 * @return array Response with 'status' and 'data' containing authorization_url
 */
function paystack_initialize_transaction($amount, $email, $reference, $metadata = [])
{
    // Convert GHS to pesewas (1 GHS = 100 pesewas)
    $amount_in_pesewas = round($amount * 100);

    $default_metadata = [
        'currency' => 'GHS',
        'app' => 'TourLink',
        'environment' => APP_ENVIRONMENT
    ];

    $metadata = array_merge($default_metadata, $metadata);

    $data = [
        'amount' => $amount_in_pesewas,
        'email' => $email,
        'reference' => $reference,
        'callback_url' => PAYSTACK_CALLBACK_URL,
        'metadata' => $metadata,
        'channels' => ['card', 'mobile_money'] // Accept both card and mobile money
    ];

    $response = paystack_api_request('POST', PAYSTACK_INIT_ENDPOINT, $data);

    return $response;
}

/**
 * Verify a Paystack transaction
 *
 * @param string $reference Transaction reference
 * @return array Response with transaction details
 */
function paystack_verify_transaction($reference)
{
    $response = paystack_api_request('GET', PAYSTACK_VERIFY_ENDPOINT . $reference);

    return $response;
}

/**
 * Make a request to Paystack API
 *
 * @param string $method HTTP method (GET, POST, etc)
 * @param string $url Full API endpoint URL
 * @param array $data Optional data to send
 * @return array API response decoded as array
 */
function paystack_api_request($method, $url, $data = null)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    // Set headers
    $headers = [
        'Authorization: Bearer ' . PAYSTACK_SECRET_KEY,
        'Content-Type: application/json',
        'Cache-Control: no-cache'
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // Send data for POST/PUT requests
    if ($method !== 'GET' && $data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    // Execute request
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);

    curl_close($ch);

    // Handle curl errors
    if ($curl_error) {
        error_log("Paystack API CURL Error: $curl_error");
        return [
            'status' => false,
            'message' => 'Connection error: ' . $curl_error
        ];
    }

    // Decode response
    $result = json_decode($response, true);

    // Log for debugging (only in test mode)
    if (APP_ENVIRONMENT === 'test') {
        error_log("Paystack API Response (HTTP $http_code): " . json_encode($result));
    }

    return $result;
}

/**
 * Get currency symbol for display
 */
function get_currency_symbol($currency = 'GHS')
{
    $symbols = [
        'GHS' => 'GH₵',
        'USD' => '$',
        'EUR' => '€',
        'NGN' => '₦',
        'GBP' => '£'
    ];

    return $symbols[$currency] ?? $currency;
}

/**
 * Generate unique payment reference for TourLink
 *
 * @param int $user_id Tourist user ID
 * @param int $booking_id Optional booking ID
 * @return string Unique reference
 */
function generate_payment_reference($user_id, $booking_id = null)
{
    $prefix = 'TL'; // TourLink
    $timestamp = time();

    if ($booking_id) {
        return "{$prefix}-{$booking_id}-{$user_id}-{$timestamp}";
    }

    return "{$prefix}-{$user_id}-{$timestamp}";
}
?>
