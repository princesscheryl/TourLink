<?php
// Ultra-simple callback test - outputs immediately before doing anything else
echo "CALLBACK PAGE LOADED<br>";
flush();

echo "Reference: " . ($_GET['reference'] ?? 'NONE') . "<br>";
flush();

echo "Starting session...<br>";
flush();

session_start();
echo "Session started<br>";
flush();

echo "Session ID: " . session_id() . "<br>";
flush();

echo "User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "<br>";
flush();

echo "<hr>If you see this, PHP is working. Now testing file includes...<br>";
flush();

try {
    require_once '../settings/core.php';
    echo "✅ core.php loaded<br>";
} catch (Throwable $e) {
    echo "❌ core.php ERROR: " . $e->getMessage() . "<br>";
}
flush();

echo "<hr>Done. Check if all lines above are visible.";
?>
