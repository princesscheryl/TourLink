<?php
// Ultra-simple callback test - outputs immediately before doing anything else
echo "CALLBACK PAGE LOADED<br>";
flush();

echo "Reference: " . ($_GET['reference'] ?? 'NONE') . "<br>";
flush();

echo "<hr><h3>🍪 Session Cookie Debug:</h3>";
echo "Session Cookie Name: " . session_name() . "<br>";
echo "Session Cookie in Request: " . (isset($_COOKIE[session_name()]) ? $_COOKIE[session_name()] : 'NOT FOUND') . "<br>";
echo "All Cookies: <pre>" . print_r($_COOKIE, true) . "</pre>";
flush();

echo "<hr>Starting session...<br>";
flush();

session_start();
echo "Session started<br>";
flush();

echo "Session ID: " . session_id() . "<br>";
flush();

echo "User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "<br>";
echo "Session Save Path: " . session_save_path() . "<br>";
flush();

echo "<hr><h3>⚙️ Session Configuration:</h3>";
$cookie_params = session_get_cookie_params();
echo "Cookie Path: " . $cookie_params['path'] . "<br>";
echo "Cookie Domain: " . ($cookie_params['domain'] ?: 'not set') . "<br>";
echo "Cookie Secure: " . ($cookie_params['secure'] ? 'YES' : 'NO') . "<br>";
echo "Cookie HttpOnly: " . ($cookie_params['httponly'] ? 'YES' : 'NO') . "<br>";
echo "Cookie SameSite: " . ($cookie_params['samesite'] ?: 'not set') . "<br>";
flush();

echo "<hr>If you see this, PHP is working. Now testing file includes...<br>";
flush();

echo "Session BEFORE core.php - ID: " . session_id() . ", User: " . ($_SESSION['user_id'] ?? 'NOT SET') . "<br>";
flush();

try {
    require_once '../settings/core.php';
    echo "✅ core.php loaded<br>";
} catch (Throwable $e) {
    echo "❌ core.php ERROR: " . $e->getMessage() . "<br>";
}
flush();

echo "Session AFTER core.php - ID: " . session_id() . ", User: " . ($_SESSION['user_id'] ?? 'NOT SET') . "<br>";
flush();

echo "<hr><h3>Final Session Data:</h3>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

echo "<hr>Done. If session is still empty after core.php, the issue is in session_timeout.php";
?>
