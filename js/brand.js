// Validate brand information and check type
function validateBrandName(name) {
    if (!name || name.trim() === '') {
        return { valid: false, message: 'Brand name is required' };
    }
    if (name.trim().length > 100) {
        return { valid: false, message: 'Brand name must be less than 100 characters' };
    }
    if (!/^[a-zA-Z0-9\s\-\_]+$/.test(name.trim())) {
        return { valid: false, message: 'Brand name contains invalid characters. Use only letters, numbers, spaces, hyphens, and underscores.' };
    }
    return { valid: true, message: '' };
}

// Handle add brand form submission
document.getElementById('addBrandForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    const brandName = formData.get('brand_name').trim();

    // Validate brand information
    const nameValidation = validateBrandName(brandName);
    if (!nameValidation.valid) {
        showAlert(nameValidation.message, 'danger');
        return;
    }

    // Asynchronously invoke add brand action script
    fetch('../actions/add_brand_action.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            // Inform user of success/failure using pop-up
            if (data.success) {
                showAlert(data.message, 'success');
                this.reset();
                refreshPage();
            } else {
                showAlert(data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred while adding the brand', 'danger');
        });
});

// Edit brand function to populate modal
function editBrand(id, name) {
    document.getElementById('editBrandId').value = id;
    document.getElementById('displayBrandId').value = id;
    document.getElementById('editBrandName').value = name;
    const editModal = new bootstrap.Modal(document.getElementById('editBrandModal'));
    editModal.show();
}

// Handle edit brand form submission
document.getElementById('editBrandForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    const brandName = formData.get('brand_name').trim();

    // Validate updated brand information
    const nameValidation = validateBrandName(brandName);
    if (!nameValidation.valid) {
        showAlert(nameValidation.message, 'danger');
        return;
    }

    // Asynchronously invoke update brand action script
    fetch('../actions/update_brand_action.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            // Inform user of success/failure using modal
            if (data.success) {
                showAlert(data.message, 'success');
                const editModal = bootstrap.Modal.getInstance(document.getElementById('editBrandModal'));
                editModal.hide();
                refreshPage();
            } else {
                showAlert(data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred while updating the brand', 'danger');
        });
});

// Delete brand function with confirmation
function deleteBrand(id, name) {
    if (!confirm(`Are you sure you want to delete the brand "${name}"?`)) {
        return;
    }

    const formData = new FormData();
    formData.append('brand_id', id);

    // Asynchronously invoke delete brand action script
    fetch('../actions/delete_brand_action.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            // Inform user of success/failure using pop-up
            if (data.success) {
                showAlert(data.message, 'success');
                refreshPage();
            } else {
                showAlert(data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred while deleting the brand', 'danger');
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
        location.reload();
    }, 1500);
}
