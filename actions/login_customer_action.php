<?php
header('Content-Type: application/json');
session_start();

$response=array();

if(isset($_SESSION['user_id'])){
    $response['status']='error';
    $response['message']='You are already logged in';
    echo json_encode($response);
    exit();
}

require_once '../controllers/customer_controller.php';

$email=isset($_POST['email'])?trim($_POST['email']):'';
$password=isset($_POST['password'])?trim($_POST['password']):'';

$errors=array();

if(!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($email)>50){
    $errors[]='Please provide a valid email address';
}
if(strlen($password)<8){
    $errors[]='Password must be at least 8 characters long';
}

if(!empty($errors)){
    $response['status']='error';
    $response['message']=$errors[0];
}else{
    $customer=login_customer_ctr($email,$password);
    
    if($customer){
        $_SESSION['user_id']=$customer['customer_id'];
        $_SESSION['user_role']=$customer['user_role'];
        $_SESSION['user_name']=$customer['customer_name'];
        $_SESSION['user_email']=$customer['customer_email'];
        $_SESSION['user_country']=$customer['customer_country'];
        $_SESSION['user_city']=$customer['customer_city'];
        $_SESSION['user_contact']=$customer['customer_contact'];
        
        $response['status']='success';
        $response['message']='Login successful! Welcome back, '.$customer['customer_name'];
        $response['redirect']='../index.php';
        $response['user']=array(
            'id'=>$customer['customer_id'],
            'name'=>$customer['customer_name'],
            'role'=>$customer['user_role']
        );
    }else{
        $response['status']='error';
        $response['message']='Invalid email or password';
    }
}

echo json_encode($response);