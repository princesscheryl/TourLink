<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
//session_start();

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

$cat_name=isset($_POST['category_name'])?trim($_POST['category_name']):'';

//validate category name
if($cat_name===''){
    $response['success']=false;
    $response['message']='Category name is required';
}else if(strlen($cat_name)>100){
    $response['success']=false;
    $response['message']='Category name must be less than 100 characters';
}else{
    //add category using controller
    $cat_id=add_category_ctr($cat_name);
    if($cat_id){
        $response['success']=true;
        $response['message']='Category added successfully';
        $response['category_id']=$cat_id;
    }else{
        $response['success']=false;
        $response['message']='Failed to add category. Category name may already exist';
    }
}

echo json_encode($response);
?>