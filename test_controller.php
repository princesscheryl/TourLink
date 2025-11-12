<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'controllers/user_controller.php';

// 1. Test email_exists_ctr
$email="test@example.com";
if(email_exists_ctr($email)){
    echo "Controller: Email '$email' exists<br>";
}else{
    echo "Controller: Email '$email' not found<br>";
}

// 2. Test register_user_ctr
$newId=register_user_ctr("Controller User","controller@example.com","password123","Ghana","Accra","0241234567",2);
if($newId){
    echo "Controller: User registered successfully with ID $newId<br>";
}else{
    echo "Controller: Failed to register user<br>";
}

// 3. Test get_user_by_email_ctr
$result=get_user_by_email_ctr("controller@example.com");
if($result){
    echo "Controller: Retrieved user -> ";
    print_r($result);
}else{
    echo "Controller: No user found for email";
}