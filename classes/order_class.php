<?php
// Include the database connection class
require_once(__DIR__ . '/../settings/db_class.php');

/**
 * Order class for managing orders, order details and payments
 * Handles the complete order processing workflow
 */
class Order extends db_connection {

    /**
     * Create a new order in the orders table
     *
     * @param int $customer_id The ID of the customer placing the order
     * @param int $invoice_no The invoice number for this order
     * @param string $order_date The date of the order
     * @param string $order_status The status of the order (e.g., 'Pending', 'Completed')
     * @return int|bool The order ID if successful, false on failure
     */
    public function createOrder($customer_id, $invoice_no, $order_date, $order_status) {
        // Connect to database
        if (!$this->db_connect()) {
            return false;
        }

        $sql = "INSERT INTO orders (customer_id, invoice_no, order_date, order_status)
                VALUES (?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("iiss", $customer_id, $invoice_no, $order_date, $order_status);
        $result = $stmt->execute();

        if ($result) {
            $order_id = $stmt->insert_id;
            $stmt->close();
            return $order_id;
        }

        $stmt->close();
        return false;
    }

    /**
     * Add order details to the orderdetails table
     * This links products to an order with quantities
     *
     * @param int $order_id The ID of the order
     * @param int $product_id The ID of the product
     * @param int $quantity The quantity ordered
     * @return bool True on success, false on failure
     */
    public function addOrderDetails($order_id, $product_id, $quantity) {
        // Connect to database
        if (!$this->db_connect()) {
            return false;
        }

        $sql = "INSERT INTO orderdetails (order_id, product_id, qty)
                VALUES (?, ?, ?)";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("iii", $order_id, $product_id, $quantity);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    /**
     * Record a payment in the payment table
     *
     * @param float $amount The payment amount
     * @param int $customer_id The ID of the customer
     * @param int $order_id The ID of the order
     * @param string $currency The currency code (e.g., 'USD', 'GHS')
     * @param string $payment_date The date of payment
     * @return int|bool The payment ID if successful, false on failure
     */
    public function recordPayment($amount, $customer_id, $order_id, $currency, $payment_date) {
        // Connect to database
        if (!$this->db_connect()) {
            return false;
        }

        $sql = "INSERT INTO payment (amt, customer_id, order_id, currency, payment_date)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("diiss", $amount, $customer_id, $order_id, $currency, $payment_date);
        $result = $stmt->execute();

        if ($result) {
            $payment_id = $stmt->insert_id;
            $stmt->close();
            return $payment_id;
        }

        $stmt->close();
        return false;
    }

    /**
     * Get all orders for a specific customer
     *
     * @param int $customer_id The customer ID
     * @return array Array of orders with details
     */
    public function getUserOrders($customer_id) {
        // Connect to database
        if (!$this->db_connect()) {
            return array();
        }

        $sql = "SELECT o.order_id, o.invoice_no, o.order_date, o.order_status,
                p.amt as payment_amount, p.currency, p.payment_date
                FROM orders o
                LEFT JOIN payment p ON o.order_id = p.order_id
                WHERE o.customer_id = ?
                ORDER BY o.order_date DESC";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return array();
        }

        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $orders = array();
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }

        $stmt->close();
        return $orders;
    }

    /**
     * Get details of a specific order including all items
     *
     * @param int $order_id The order ID
     * @return array Order details with items
     */
    public function getOrderDetails($order_id) {
        // Connect to database
        if (!$this->db_connect()) {
            return array();
        }

        $sql = "SELECT od.product_id, od.qty, p.product_title, p.product_price, p.product_image,
                (od.qty * p.product_price) as subtotal
                FROM orderdetails od
                JOIN products p ON od.product_id = p.product_id
                WHERE od.order_id = ?";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return array();
        }

        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $items = array();
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }

        $stmt->close();
        return $items;
    }

    /**
     * Get a single order by order ID
     *
     * @param int $order_id The order ID
     * @return array|null Order information or null if not found
     */
    public function getOrderById($order_id) {
        // Connect to database
        if (!$this->db_connect()) {
            return null;
        }

        $sql = "SELECT o.*, c.customer_name, c.customer_email, c.customer_contact
                FROM orders o
                JOIN customer c ON o.customer_id = c.customer_id
                WHERE o.order_id = ?";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        $stmt->close();

        return $order;
    }

    /**
     * Update order status
     *
     * @param int $order_id The order ID
     * @param string $status The new status
     * @return bool True on success, false on failure
     */
    public function updateOrderStatus($order_id, $status) {
        // Connect to database
        if (!$this->db_connect()) {
            return false;
        }

        $sql = "UPDATE orders SET order_status = ? WHERE order_id = ?";
        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("si", $status, $order_id);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    /**
     * Generate a unique invoice number
     *
     * @return int Unique invoice number
     */
    public function generateInvoiceNumber() {
        // Get the maximum invoice number and add 1
        $sql = "SELECT MAX(invoice_no) as max_invoice FROM orders";
        $result = $this->db_query($sql);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['max_invoice'] ? $row['max_invoice'] + 1 : 1000;
        }

        return 1000; // Start from 1000 if no orders exist
    }
}
?>
