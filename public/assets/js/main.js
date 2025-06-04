// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Setup event listeners
    setupEventListeners();
    
    // Initialize API handlers
    initializeApiHandlers();
});

// Setup event listeners for interactive elements
function setupEventListeners() {
    // Handle form submissions
    const forms = document.querySelectorAll('form[data-api-submit]');
    forms.forEach(form => {
        form.addEventListener('submit', handleApiFormSubmit);
    });
    
    // Handle modal triggers
    const modalTriggers = document.querySelectorAll('[data-modal-target]');
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', toggleModal);
    });
    
    // Handle dynamic content loading
    const dynamicLoaders = document.querySelectorAll('[data-load-content]');
    dynamicLoaders.forEach(loader => {
        loader.addEventListener('click', loadDynamicContent);
    });
}

// Handle API form submissions
function handleApiFormSubmit(event) {
    event.preventDefault();
    
    const form = event.target;
    const apiEndpoint = form.getAttribute('data-api-submit');
    const method = form.getAttribute('data-api-method') || 'POST';
    const redirectUrl = form.getAttribute('data-redirect-url');
    
    // Show loading state
    const submitButton = form.querySelector('button[type="submit"]');
    const originalButtonText = submitButton.innerHTML;
    submitButton.disabled = true;
    submitButton.innerHTML = '<span class="loading-spinner-sm"></span> Processing...';
    
    // Gather form data
    const formData = new FormData(form);
    const data = {};
    
    formData.forEach((value, key) => {
        data[key] = value;
    });
    
    // Make API request
    fetch(apiEndpoint, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('jwt_token')}`
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        // Reset button state
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonText;
        
        if (data.status === 'success') {
            // Show success message
            const successAlert = document.createElement('div');
            successAlert.className = 'alert alert-success';
            successAlert.textContent = data.message;
            
            const alertContainer = document.querySelector('.alert-container') || form.parentElement;
            alertContainer.prepend(successAlert);
            
            // Clear form
            form.reset();
            
            // Redirect if specified
            if (redirectUrl) {
                window.location.href = redirectUrl;
            }
            
            // Remove success message after 3 seconds
            setTimeout(() => {
                successAlert.remove();
            }, 3000);
        } else {
            // Show error message
            const errorAlert = document.createElement('div');
            errorAlert.className = 'alert alert-error';
            errorAlert.textContent = data.message || 'An error occurred. Please try again.';
            
            const alertContainer = document.querySelector('.alert-container') || form.parentElement;
            alertContainer.prepend(errorAlert);
            
            // Remove error message after 5 seconds
            setTimeout(() => {
                errorAlert.remove();
            }, 5000);
        }
    })
    .catch(error => {
        // Reset button state
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonText;
        
        // Show error message
        const errorAlert = document.createElement('div');
        errorAlert.className = 'alert alert-error';
        errorAlert.textContent = 'Network error. Please try again.';
        
        const alertContainer = document.querySelector('.alert-container') || form.parentElement;
        alertContainer.prepend(errorAlert);
        
        console.error('API request failed:', error);
        
        // Remove error message after 5 seconds
        setTimeout(() => {
            errorAlert.remove();
        }, 5000);
    });
}

// Toggle modal visibility
function toggleModal(modalElement) {
    // If passed an event object, get the modal from data attribute
    if (modalElement && modalElement.currentTarget) {
        const modalId = modalElement.currentTarget.getAttribute('data-modal-target');
        modalElement = document.getElementById(modalId);
    }
    // If passed a string ID, get the element
    else if (typeof modalElement === 'string') {
        modalElement = document.getElementById(modalElement);
    }
    
    // Toggle the modal visibility
    if (modalElement) {
        modalElement.classList.toggle('hidden');
        
        // Setup close button events
        if (!modalElement.classList.contains('hidden')) {
            const closeButtons = modalElement.querySelectorAll('.modal-close');
            closeButtons.forEach(button => {
                button.onclick = function() {
                    modalElement.classList.add('hidden');
                };
            });
            
            // Handle click outside
            modalElement.onclick = function(e) {
                if (e.target === modalElement) {
                    modalElement.classList.add('hidden');
                }
            };
        }
    }
}

// Load dynamic content via AJAX
function loadDynamicContent(event) {
    event.preventDefault();
    
    const trigger = event.currentTarget;
    const url = trigger.getAttribute('data-load-content');
    const targetId = trigger.getAttribute('data-target');
    const target = document.getElementById(targetId);
    
    if (target) {
        // Show loading state
        target.innerHTML = '<div class="loading-container"><div class="loading-spinner"></div></div>';
        
        // Fetch content
        fetch(url, {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('jwt_token')}`
            }
        })
        .then(response => response.text())
        .then(html => {
            target.innerHTML = html;
            
            // Initialize any scripts in the loaded content
            const scripts = target.querySelectorAll('script');
            scripts.forEach(script => {
                const newScript = document.createElement('script');
                if (script.src) {
                    newScript.src = script.src;
                } else {
                    newScript.textContent = script.textContent;
                }
                document.body.appendChild(newScript);
            });
        })
        .catch(error => {
            target.innerHTML = '<div class="alert alert-error">Failed to load content. Please try again.</div>';
            console.error('Error loading dynamic content:', error);
        });
    }
}

// Initialize API handlers
function initializeApiHandlers() {
    // Check if JWT token exists and is valid
    const token = localStorage.getItem('jwt_token');
    
    if (token) {
        // Set default Authorization header for all fetch requests
        const originalFetch = window.fetch;
        window.fetch = function(url, options = {}) {
            options.headers = options.headers || {};
            
            if (!options.headers.Authorization) {
                options.headers.Authorization = `Bearer ${token}`;
            }
            
            return originalFetch(url, options);
        };
    }
}