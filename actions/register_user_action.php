<?php
header('Content-Type: application/json');
session_start();

$response=array();

// TODO: Check if the user is already logged in and redirect to the dashboard
if(isset($_SESSION['user_id'])){
    $response['status']='error';
    $response['message']='You are already logged in';
    echo json_encode($response);
    exit();
}

require_once '../controllers/user_controller.php';

$name=isset($_POST['full_name'])?trim($_POST['full_name']):'';
$email=isset($_POST['email'])?trim($_POST['email']):'';
$password=isset($_POST['password'])?trim($_POST['password']):'';
$country=isset($_POST['country'])?trim($_POST['country']):'';
$city=isset($_POST['city'])?trim($_POST['city']):'';
$phone_number=isset($_POST['contact'])?trim($_POST['contact']):'';
$role=isset($_POST['role'])?(int)$_POST['role']:2;

$errors=array();

if($name===''||strlen($name)>100){
    $errors[]='Full name is required and must be less than 100 characters';
}
if(!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($email)>50){
    $errors[]='Please provide a valid email address';
}
if(strlen($password)<8){
    $errors[]='Password must be at least 8 characters long';
}
if($country===''||strlen($country)>30){
    $errors[]='Country is required and must be less than 30 characters';
}
if($city===''||strlen($city)>30){
    $errors[]='City is required and must be less than 30 characters';
}
if($phone_number===''||strlen($phone_number)>15){
    $errors[]='Contact number is required and must be less than 15 characters';
}

if(!empty($errors)){
    $response['status']='error';
    $response['message']=$errors[0];
}else if(email_exists_ctr($email)){
    $response['status']='error';
    $response['message']='Email already exists';
}else{
    $user_id=register_user_ctr($name,$email,$password,$country,$city,$phone_number,$role);
    if($user_id){
        $response['status']='success';
        $response['message']='Registered successfully';
        $response['user_id']=$user_id;
        $response['redirect']='../login/login.php';
    }else{
        $response['status']='error';
        $response['message']='Failed to register';
    }
}

echo json_encode($response);