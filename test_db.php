<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'settings/db_class.php';

$conn=new db_connection();
if($conn->db_connect()){
    echo "✅ Connected to database '".DATABASE."' successfully!";
}else{
    echo "❌ Failed to connect to database '".DATABASE."'";
}