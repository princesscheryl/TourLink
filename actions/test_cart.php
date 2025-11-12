<?php
// Simple test to check if files exist on server
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing cart setup...\n\n";

// Check if controller exists
$controller_path = __DIR__ . '/../controllers/cart_controller.php';
echo "Controller path: $controller_path\n";
echo "Controller exists: " . (file_exists($controller_path) ? "YES" : "NO") . "\n\n";

// Check if cart class exists
$class_path = __DIR__ . '/../classes/cart_class.php';
echo "Cart class path: $class_path\n";
echo "Cart class exists: " . (file_exists($class_path) ? "YES" : "NO") . "\n\n";

// Try to include controller
if (file_exists($controller_path)) {
    require_once($controller_path);
    echo "Controller loaded successfully!\n";

    // Check if function exists
    if (function_exists('add_to_cart_ctr')) {
        echo "Function add_to_cart_ctr exists!\n";
    } else {
        echo "Function add_to_cart_ctr NOT FOUND!\n";
    }
} else {
    echo "ERROR: Controller file not found!\n";
}
?>
