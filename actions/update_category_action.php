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

$cat_id=isset($_POST['category_id'])?(int)$_POST['category_id']:0;
$cat_name=isset($_POST['category_name'])?trim($_POST['category_name']):'';

//validate input
if($cat_id<=0){
    $response['success']=false;
    $response['message']='Invalid category ID';
}else if($cat_name===''){
    $response['success']=false;
    $response['message']='Category name is required';
}else if(strlen($cat_name)>100){
    $response['success']=false;
    $response['message']='Category name must be less than 100 characters';
}else{
    //update category using controller
    $updated=edit_category_ctr($cat_id,$cat_name);
    if($updated){
        $response['success']=true;
        $response['message']='Category updated successfully';
    }else{
        $response['success']=false;
        $response['message']='Failed to update category. Category name may already exist';
    }
}

echo json_encode($response);
?>