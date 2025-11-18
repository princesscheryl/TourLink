/**
 * Favorites Management
 * Client-side functionality for adding/removing services from favorites
 */

/**
 * Toggle favorite status for a service
 * @param {number} serviceId - The ID of the service to favorite/unfavorite
 * @param {HTMLElement} buttonElement - The button element that was clicked
 */
function toggleFavorite(serviceId, buttonElement) {
    // Prevent multiple clicks during processing
    if (buttonElement.disabled) {
        return;
    }

    // Get the heart icon element
    const heartIcon = buttonElement.querySelector('i');
    const originalClasses = heartIcon.className;

    // Disable button and show loading state
    buttonElement.disabled = true;
    heartIcon.className = 'fas fa-spinner fa-spin';

    // Make AJAX request
    $.ajax({
        url: '../actions/toggle_favorite_action.php',
        method: 'POST',
        data: {
            service_id: serviceId
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Update heart icon based on action
                if (response.action === 'added') {
                    heartIcon.className = 'fas fa-heart';
                    buttonElement.classList.add('active');

                    // Show success toast
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: response.message,
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                } else if (response.action === 'removed') {
                    heartIcon.className = 'far fa-heart';
                    buttonElement.classList.remove('active');

                    // Show info toast
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'info',
                        title: response.message,
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                }

                // Update favorite count in navigation if element exists
                updateFavoriteCount(response.count);

                // Trigger custom event for other components that might need to update
                $(document).trigger('favoriteToggled', {
                    serviceId: serviceId,
                    action: response.action,
                    count: response.count
                });

            } else {
                // Handle failure
                heartIcon.className = originalClasses;

                // Check if login is required
                if (response.require_login) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Login Required',
                        text: response.message,
                        confirmButtonText: 'Sign In',
                        showCancelButton: true,
                        confirmButtonColor: '#2d6a4f'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '../login/login.php';
                        }
                    });
                } else {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: response.message || 'Failed to update favorites',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                }
            }
        },
        error: function(xhr, status, error) {
            // Restore original icon
            heartIcon.className = originalClasses;

            console.error('Favorite toggle error:', error);
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: 'Failed to update favorites. Please try again.',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        },
        complete: function() {
            // Re-enable button
            buttonElement.disabled = false;
        }
    });
}

/**
 * Update the favorite count badge in navigation
 * @param {number} count - The new favorite count
 */
function updateFavoriteCount(count) {
    // Update count in navigation if elements exist
    const countElements = document.querySelectorAll('.favorite-count, [data-favorite-count]');

    countElements.forEach(element => {
        if (count > 0) {
            element.textContent = count;
            element.style.display = 'inline';
        } else {
            element.textContent = '0';
            element.style.display = 'none';
        }
    });
}

/**
 * Initialize favorite buttons on page load
 */
function initializeFavoriteButtons() {
    // Add click handlers to all favorite buttons
    const favoriteButtons = document.querySelectorAll('.favorite-btn, [data-favorite-btn]');

    favoriteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation(); // Prevent triggering parent click events

            const serviceId = this.getAttribute('data-service-id');
            if (serviceId) {
                toggleFavorite(parseInt(serviceId), this);
            } else {
                console.error('Service ID not found on favorite button');
            }
        });
    });
}

/**
 * Remove a service card from favorites page
 * @param {number} serviceId - The ID of the service to remove
 */
function removeFromFavoritesPage(serviceId) {
    const serviceCard = document.querySelector(`.service-card[data-service-id="${serviceId}"]`);

    if (serviceCard) {
        // Fade out animation
        serviceCard.style.transition = 'opacity 0.3s, transform 0.3s';
        serviceCard.style.opacity = '0';
        serviceCard.style.transform = 'scale(0.9)';

        // Remove after animation
        setTimeout(() => {
            serviceCard.remove();

            // Check if there are any favorites left
            const remainingCards = document.querySelectorAll('.service-card');
            if (remainingCards.length === 0) {
                showEmptyFavoritesMessage();
            }
        }, 300);
    }
}

/**
 * Show empty favorites message
 */
function showEmptyFavoritesMessage() {
    const container = document.querySelector('.services-grid, .favorites-grid');

    if (container) {
        container.innerHTML = `
            <div class="empty-favorites">
                <i class="far fa-heart" style="font-size: 64px; color: #ccc; margin-bottom: 20px;"></i>
                <h3 data-i18n="favorites.no_favorites">No favorites yet</h3>
                <p data-i18n="favorites.start_adding">Start adding services to your favorites to see them here.</p>
                <a href="all_services.php" class="btn-primary" data-i18n="favorites.browse_services">Browse Services</a>
            </div>
        `;
    }
}

// Initialize when document is ready
$(document).ready(function() {
    initializeFavoriteButtons();

    // Listen for favoriteToggled event on favorites page
    if (window.location.pathname.includes('my_favorites.php')) {
        $(document).on('favoriteToggled', function(event, data) {
            if (data.action === 'removed') {
                removeFromFavoritesPage(data.serviceId);
            }
        });
    }
});
