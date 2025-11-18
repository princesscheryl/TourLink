<?php
session_start();

//for header redirection
ob_start();

// Include session timeout management (15 minute auto-logout)
require_once __DIR__ . '/session_timeout.php';

//funtion to check for login
function isLoggedIn(){
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    else{
        return true;
    }
}

//function to get user ID
function getUserID(){
    if (isLoggedIn()){
        return $_SESSION['user_id'];
    }
    return false;
}

//function to check for role (admin, customer, etc)
function isAdmin(){
    if (isLoggedIn()){
        return $_SESSION['user_role'] == 1;
    }
}


?>