<?php
require_once 'includes_platform/auth_check.php';
require_privilege('view_dashboard');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impact Metrics - TourLink Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: #f8fafc;
            margin: 0;
        }
        .container {
            text-align: center;
            padding: 40px;
        }
        h1 {
            color: #1e293b;
            margin-bottom: 16px;
        }
        p {
            color: #64748b;
            margin-bottom: 24px;
        }
        a {
            display: inline-block;
            padding: 12px 24px;
            background: #1b4332;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Impact Metrics</h1>
        <p>This page is under construction</p>
        <a href="platform_dashboard.php">Back to Dashboard</a>
    </div>
</body>
</html>
