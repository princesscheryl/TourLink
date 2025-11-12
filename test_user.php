<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'classes/user_class.php';

$user=new User();

// test emailExists
if($user->emailExists("test@example.com")){
    echo "Email already exists<br>";
}else{
    echo "Email not found<br>";
}

// test createUser
$newId=$user->createUser("Test User","test@example.com","password123","Ghana","Accra","0551234567",2);
if($newId){
    echo "User created successfully with ID: ".$newId;
}else{
    echo "Failed to create user";
}