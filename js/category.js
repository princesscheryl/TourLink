//validate category information and check type
function validateCategoryName(name){
    if(!name || name.trim()===''){ 
        return {valid:false,message:'Category name is required'};
    }
    if(name.trim().length>100){
        return {valid:false,message:'Category name must be less than 100 characters'};
    }
    if(!/^[a-zA-Z0-9\s\-\_\&]+$/.test(name.trim())){
        return {valid:false,message:'Category name contains invalid characters'};
    }
    return {valid:true,message:''};
}

//handle add category form submission
document.getElementById('addCategoryForm').addEventListener('submit',function(e){
    e.preventDefault();
    const formData=new FormData(this);
    const categoryName=formData.get('category_name').trim();
    
    //validate category information
    const validation=validateCategoryName(categoryName);
    if(!validation.valid){
        showAlert(validation.message,'danger');
        return;
    }
    
    //asynchronously invoke add category action script
    fetch('../actions/add_category_action.php',{
        method:'POST',
        body:formData
    })
    .then(response=>response.json())
    .then(data=>{
        //inform user of success/failure using pop-up
        if(data.success){
            showAlert(data.message,'success');
            this.reset();
            refreshPage();
        }else{
            showAlert(data.message,'danger');
        }
    })
    .catch(error=>{
        console.error('Error:',error);
        showAlert('An error occurred while adding the category','danger');
    });
});

//edit category function to populate modal
function editCategory(id,name){
    document.getElementById('editCategoryId').value=id;
    document.getElementById('displayCategoryId').value=id;
    document.getElementById('editCategoryName').value=name;
    const editModal=new bootstrap.Modal(document.getElementById('editCategoryModal'));
    editModal.show();
}

//handle edit category form submission
document.getElementById('editCategoryForm').addEventListener('submit',function(e){
    e.preventDefault();
    const formData=new FormData(this);
    const categoryName=formData.get('category_name').trim();
    
    //validate updated category information
    const validation=validateCategoryName(categoryName);
    if(!validation.valid){
        showAlert(validation.message,'danger');
        return;
    }
    
    //asynchronously invoke update category action script
    fetch('../actions/update_category_action.php',{
        method:'POST',
        body:formData
    })
    .then(response=>response.json())
    .then(data=>{
        //inform user of success/failure using modal
        if(data.success){
            showAlert(data.message,'success');
            const editModal=bootstrap.Modal.getInstance(document.getElementById('editCategoryModal'));
            editModal.hide();
            refreshPage();
        }else{
            showAlert(data.message,'danger');
        }
    })
    .catch(error=>{
        console.error('Error:',error);
        showAlert('An error occurred while updating the category','danger');
    });
});

//delete category function with confirmation
function deleteCategory(id,name){
    if(!confirm(`Are you sure you want to delete the category "${name}"?`)){
        return;
    }
    
    const formData=new FormData();
    formData.append('category_id',id);
    
    //asynchronously invoke delete category action script
    fetch('../actions/delete_category_action.php',{
        method:'POST',
        body:formData
    })
    .then(response=>response.json())
    .then(data=>{
        //inform user of success/failure using pop-up
        if(data.success){
            showAlert(data.message,'success');
            refreshPage();
        }else{
            showAlert(data.message,'danger');
        }
    })
    .catch(error=>{
        console.error('Error:',error);
        showAlert('An error occurred while deleting the category','danger');
    });
}

//utility function to show alert messages
function showAlert(message,type){
    const alertContainer=document.getElementById('alertContainer');
    if(!alertContainer){
        const container=document.createElement('div');
        container.id='alertContainer';
        container.style.position='fixed';
        container.style.top='20px';
        container.style.right='20px';
        container.style.zIndex='1050';
        document.body.appendChild(container);
    }
    
    const alertDiv=document.createElement('div');
    alertDiv.className=`alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML=`${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
    document.getElementById('alertContainer').appendChild(alertDiv);
    
    //auto-remove alert after 5 seconds
    setTimeout(()=>{
        if(alertDiv.parentNode){
            alertDiv.remove();
        }
    },5000);
}

//refresh page after successful operations
function refreshPage(){
    setTimeout(()=>{
        location.reload();
    },1500);
}