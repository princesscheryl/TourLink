<?php
require_once __DIR__.'/../classes/category_class.php';

//create new category and return category id if successful
function add_category_ctr($cat_name)
{
    $category=new Category();
    $cat_id=$category->add($cat_name);
    if($cat_id){
        return $cat_id;
    }else{
        return false;
    }
}

//retrieve all categories from database for display
function get_all_categories_ctr()
{
    $category=new Category();
    $result=$category->get();
    if($result){
        return $result;
    }else{
        return false;
    }
}

//get single category details using its id
function get_category_by_id_ctr($cat_id)
{
    $category=new Category();
    $result=$category->getCategoryById($cat_id);
    if($result){
        return $result;
    }else{
        return false;
    }
}

//update existing category with new name
function edit_category_ctr($cat_id,$cat_name)
{
    $category=new Category();
    $updated=$category->edit($cat_id,$cat_name);
    if($updated){
        return true;
    }else{
        return false;
    }
}

//remove category from database permanently
function delete_category_ctr($cat_id)
{
    $category=new Category();
    $deleted=$category->delete($cat_id);
    if($deleted){
        return true;
    }else{
        return false;
    }
}

//search for category using its name
function get_category_by_name_ctr($cat_name)
{
    $category=new Category();
    $result=$category->getCategoryByName($cat_name);
    if($result){
        return $result;
    }else{
        return false;
    }
}
?>