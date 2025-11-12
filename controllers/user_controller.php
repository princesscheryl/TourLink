<?php
require_once __DIR__ . '/../classes/user_class.php';

function register_user_ctr($name,$email,$password,$country,$city,$phone_number,$role=2)
{
    $user=new User();
    $user_id=$user->createUser($name,$email,$password,$country,$city,$phone_number,$role);
    if($user_id){
        return $user_id;
    }else{
        return false;
    }
}
function email_exists_ctr($email)
{
    $user=new User();
    $exists=$user->emailExists($email);
    if($exists){
        return true;
    }else{
        return false;
    }
}
function get_user_by_email_ctr($email)
{
    $user=new User();
    $result=$user->getUserByEmail($email);
    if($result){
        return $result;
    }else{
        return false;
    }
}
function get_user_by_id_ctr($id)
{
    $user=new User();
    $result=$user->getUserById($id);
    if($result){
        return $result;
    }else{
        return false;
    }
}
function update_user_ctr($id,$name,$phone_number,$role)
{
    $user=new User();
    $updated=$user->updateUser($id,$name,$phone_number,$role);
    if($updated){
        return true;
    }else{
        return false;
    }
}
function update_password_ctr($id,$password)
{
    $user=new User();
    $updated=$user->updatePassword($id,$password);
    if($updated){
        return true;
    }else{
        return false;
    }
}
function delete_user_ctr($id)
{
    $user=new User();
    $deleted=$user->deleteUser($id);
    if($deleted){
        return true;
    }else{
        return false;
    }
}