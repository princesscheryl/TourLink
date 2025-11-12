<?php
require_once __DIR__.'/../classes/user_class.php';

function login_customer_ctr($email,$password){
    $user=new User();
    $customer=$user->loginCustomer($email,$password);
    if($customer){
        return $customer;
    }else{
        return false;
    }
}

function register_customer_ctr($name,$email,$password,$country,$city,$phone_number,$role=2){
    $user=new User();
    $user_id=$user->createUser($name,$email,$password,$country,$city,$phone_number,$role);
    if($user_id){
        return $user_id;
    }else{
        return false;
    }
}

function customer_email_exists_ctr($email){
    $user=new User();
    $exists=$user->emailExists($email);
    if($exists){
        return true;
    }else{
        return false;
    }
}

function get_customer_by_email_ctr($email){
    $user=new User();
    $result=$user->getUserByEmail($email);
    if($result){
        return $result;
    }else{
        return false;
    }
}

function get_customer_by_id_ctr($id){
    $user=new User();
    $result=$user->getUserById($id);
    if($result){
        return $result;
    }else{
        return false;
    }
}

function update_customer_ctr($id,$name,$phone_number,$role){
    $user=new User();
    $updated=$user->updateUser($id,$name,$phone_number,$role);
    if($updated){
        return true;
    }else{
        return false;
    }
}

function update_customer_password_ctr($id,$password){
    $user=new User();
    $updated=$user->updatePassword($id,$password);
    if($updated){
        return true;
    }else{
        return false;
    }
}

function delete_customer_ctr($id){
    $user=new User();
    $deleted=$user->deleteUser($id);
    if($deleted){
        return true;
    }else{
        return false;
    }
}