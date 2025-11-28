<?php
// Simple test file to verify callback URL is accessible
echo "<!DOCTYPE html><html><head><title>Callback Test</title>";
echo "<style>body{font-family:monospace;padding:40px;background:#1a1a1a;color:#0f0;}</style></head><body>";
echo "<h1>✅ Callback URL is Accessible</h1>";
echo "<p><strong>Server Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>REQUEST_URI:</strong> " . htmlspecialchars($_SERVER['REQUEST_URI']) . "</p>";
echo "<p><strong>GET Parameters:</strong></p>";
echo "<pre>" . print_r($_GET, true) . "</pre>";
echo "<p><strong>Session Status:</strong> " . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Not Active') . "</p>";

// Try to start session
session_start();
echo "<p><strong>Session Started:</strong> YES</p>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Session Data:</strong></p>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

echo "<hr><p>If you see this, the callback URL works. The issue is in paystack_callback.php code.</p>";
echo "</body></html>";
?>
