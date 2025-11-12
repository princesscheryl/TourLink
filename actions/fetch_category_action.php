<?php
header('Content-Type: application/json');
session_start();

require_once '../controllers/category_controller.php';
require_once '../settings/core.php';

$response=array();

//check if user is logged in and is admin
if(!isLoggedIn()){
    $response['success']=false;
    $response['message']='Please log in first';
    echo json_encode($response);
    exit();
}

if(!isAdmin()){
    $response['success']=false;
    $response['message']='Access denied. Admin privileges required';
    echo json_encode($response);
    exit();
}

//fetch all categories using controller
$categories=get_all_categories_ctr();
if($categories){
    $response['success']=true;
    $response['data']=$categories;
}else{
    $response['success']=true;
    $response['data']=array();
    $response['message']='No categories found';
}

echo json_encode($response);
?>