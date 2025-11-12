// Validate product information and check type
function validateProductTitle(title) {
    if (!title || title.trim() === '') {
        return { valid: false, message: 'Product title is required' };
    }
    if (title.trim().length > 200) {
        return { valid: false, message: 'Product title must be less than 200 characters' };
    }
    return { valid: true, message: '' };
}

function validateProductPrice(price) {
    if (!price || price <= 0) {
        return { valid: false, message: 'Product price must be greater than 0' };
    }
    if (isNaN(price)) {
        return { valid: false, message: 'Product price must be a valid number' };
    }
    return { valid: true, message: '' };
}

function validateCategorySelection(catId) {
    if (!catId || catId === '' || catId === '0') {
        return { valid: false, message: 'Please select a category' };
    }
    return { valid: true, message: '' };
}

function validateBrandSelection(brandId) {
    if (!brandId || brandId === '' || brandId === '0') {
        return { valid: false, message: 'Please select a brand' };
    }
    return { valid: true, message: '' };
}

// Image preview functionality
document.getElementById('productImage').addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (file) {
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            showAlert('Invalid file type. Please select a JPG, PNG, or GIF image.', 'danger');
            e.target.value = '';
            return;
        }

        // Validate file size (max 5MB)
        const maxSize = 5 * 1024 * 1024; // 5MB
        if (file.size > maxSize) {
            showAlert('File size exceeds 5MB limit. Please select a smaller image.', 'danger');
            e.target.value = '';
            return;
        }

        // Show preview
        const reader = new FileReader();
        reader.onload = function (e) {
            const previewContainer = document.getElementById('imagePreview');
            previewContainer.innerHTML = `<img src="${e.target.result}" class="image-preview" alt="Image preview">`;
        };
        reader.readAsDataURL(file);
    }
});

// Handle product form submission
document.getElementById('productForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const productTitle = formData.get('product_title').trim();
    const productPrice = parseFloat(formData.get('product_price'));
    const catId = formData.get('cat_id');
    const brandId = formData.get('brand_id');
    const productId = formData.get('product_id');
    const imageFile = document.getElementById('productImage').files[0];

    // Validate product information
    const titleValidation = validateProductTitle(productTitle);
    if (!titleValidation.valid) {
        showAlert(titleValidation.message, 'danger');
        return;
    }

    const priceValidation = validateProductPrice(productPrice);
    if (!priceValidation.valid) {
        showAlert(priceValidation.message, 'danger');
        return;
    }

    const catValidation = validateCategorySelection(catId);
    if (!catValidation.valid) {
        showAlert(catValidation.message, 'danger');
        return;
    }

    const brandValidation = validateBrandSelection(brandId);
    if (!brandValidation.valid) {
        showAlert(brandValidation.message, 'danger');
        return;
    }

    try {
        // If there's an image file, upload it first
        if (imageFile) {
            showAlert('Uploading image...', 'info');

            const imageFormData = new FormData();
            imageFormData.append('product_image', imageFile);
            // For new products, use 0 as temporary product_id
            // We'll update the image after product creation
            imageFormData.append('product_id', productId || '0');

            const imageResponse = await fetch('../actions/upload_product_image_action.php', {
                method: 'POST',
                body: imageFormData
            });

            const imageData = await imageResponse.json();

            if (imageData.success) {
                // Update the hidden image path field
                document.getElementById('productImagePath').value = imageData.file_path;
                formData.set('product_image', imageData.file_path);
            } else {
                showAlert('Image upload failed: ' + imageData.message, 'danger');
                return;
            }
        }

        // Determine if we're adding or updating
        const isEditing = productId && productId !== '';
        const actionUrl = isEditing ?
            '../actions/update_product_action.php' :
            '../actions/add_product_action.php';

        // Submit product data
        const response = await fetch(actionUrl, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            showAlert(data.message, 'success');

            // If we just added a new product and there was an image with temp ID 0,
            // we should re-upload with the actual product ID
            if (!isEditing && imageFile && data.product_id) {
                const correctImageFormData = new FormData();
                correctImageFormData.append('product_image', imageFile);
                correctImageFormData.append('product_id', data.product_id);

                await fetch('../actions/upload_product_image_action.php', {
                    method: 'POST',
                    body: correctImageFormData
                });
            }

            refreshPage();
        } else {
            showAlert(data.message, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('An error occurred while processing the product', 'danger');
    }
});

// Delete product function with confirmation
function deleteProduct(id, title) {
    if (!confirm(`Are you sure you want to delete the product "${title}"?`)) {
        return;
    }

    const formData = new FormData();
    formData.append('product_id', id);

    // Note: You'll need to create a delete_product_action.php file
    fetch('../actions/delete_product_action.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                refreshPage();
            } else {
                showAlert(data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred while deleting the product', 'danger');
        });
}

// Utility function to show alert messages
function showAlert(message, type) {
    const alertContainer = document.getElementById('alertContainer');
    if (!alertContainer) {
        const container = document.createElement('div');
        container.id = 'alertContainer';
        container.style.position = 'fixed';
        container.style.top = '20px';
        container.style.right = '20px';
        container.style.zIndex = '1060';
        document.body.appendChild(container);
    }

    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
    document.getElementById('alertContainer').appendChild(alertDiv);

    // Auto-remove alert after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Refresh page after successful operations
function refreshPage() {
    setTimeout(() => {
        window.location.href = 'product.php';
    }, 1500);
}
