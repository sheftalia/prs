// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize any charts if the page contains chart elements
    initializeCharts();
    
    // Setup event listeners
    setupEventListeners();
    
    // Initialize API handlers
    initializeApiHandlers();
});

// Initialize charts if the page contains chart elements
function initializeCharts() {
    // Check if Chart.js is loaded and if there are chart elements
    if (typeof Chart !== 'undefined') {
        // Dashboard charts
        const dashboardCharts = {
            'vaccination-trend-chart': setupVaccinationTrendChart,
            'vaccine-distribution-chart': setupVaccineDistributionChart,
            'purchase-trend-chart': setupPurchaseTrendChart,
            'item-distribution-chart': setupItemDistributionChart
        };
        
        // Initialize each chart if the element exists
        for (const [elementId, setupFunction] of Object.entries(dashboardCharts)) {
            const chartElement = document.getElementById(elementId);
            if (chartElement) {
                setupFunction(chartElement);
            }
        }
    }
}

// Setup vaccination trend chart
function setupVaccinationTrendChart(element) {
    // Fetch data from the API
    fetch('/prs/api/stats/vaccinations')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.data.vaccination_trend) {
                const trend = data.data.vaccination_trend;
                
                // Prepare data for the chart
                const labels = trend.map(item => item.month);
                const values = trend.map(item => item.count);
                
                // Create the chart
                new Chart(element, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Vaccinations',
                            data: values,
                            borderColor: '#005eb8',
                            backgroundColor: 'rgba(0, 94, 184, 0.1)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            title: {
                                display: true,
                                text: 'Vaccination Trend'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error fetching vaccination trend data:', error);
            element.parentElement.innerHTML = '<div class="alert alert-error">Failed to load chart data</div>';
        });
}

// Setup vaccine distribution chart
function setupVaccineDistributionChart(element) {
    // Fetch data from the API
    fetch('/prs/api/stats/vaccinations')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.data.vaccine_distribution) {
                const distribution = data.data.vaccine_distribution;
                
                // Prepare data for the chart
                const labels = distribution.map(item => item.vaccine_name);
                const values = distribution.map(item => item.count);
                const backgroundColors = [
                    '#005eb8', // NHS Blue
                    '#41b6e6', // Light Blue
                    '#330072', // Purple
                    '#ae2573', // Pink
                    '#00a499', // Teal
                    '#78be20', // Green
                    '#fae100', // Yellow
                    '#ed8b00', // Orange
                    '#d5281b'  // Red
                ];
                
                // Create the chart
                new Chart(element, {
                    type: 'pie',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: values,
                            backgroundColor: backgroundColors.slice(0, labels.length),
                            borderWidth: 1,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right'
                            },
                            title: {
                                display: true,
                                text: 'Vaccine Distribution'
                            }
                        }
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error fetching vaccine distribution data:', error);
            element.parentElement.innerHTML = '<div class="alert alert-error">Failed to load chart data</div>';
        });
}

// Setup purchase trend chart
function setupPurchaseTrendChart(element) {
    // Fetch data from the API
    fetch('/prs/api/stats/purchases')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.data.purchase_trend) {
                const trend = data.data.purchase_trend;
                
                // Prepare data for the chart
                const labels = trend.map(item => item.date);
                const transactionCounts = trend.map(item => item.transaction_count);
                const itemCounts = trend.map(item => item.item_count);
                
                // Create the chart
                new Chart(element, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Transactions',
                                data: transactionCounts,
                                backgroundColor: '#005eb8', // NHS Blue
                                borderWidth: 0
                            },
                            {
                                label: 'Items',
                                data: itemCounts,
                                backgroundColor: '#41b6e6', // Light Blue
                                borderWidth: 0
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Purchase Trend'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error fetching purchase trend data:', error);
            element.parentElement.innerHTML = '<div class="alert alert-error">Failed to load chart data</div>';
        });
}

// Setup item distribution chart
function setupItemDistributionChart(element) {
    // Fetch data from the API
    fetch('/prs/api/stats/inventory')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.data.category_distribution) {
                const distribution = data.data.category_distribution;
                
                // Prepare data for the chart
                const labels = distribution.map(item => item.item_category);
                const values = distribution.map(item => item.total_stock);
                const backgroundColors = [
                    '#005eb8', // NHS Blue
                    '#330072', // Purple
                    '#00a499', // Teal
                    '#78be20', // Green
                    '#ed8b00', // Orange
                    '#d5281b'  // Red
                ];
                
                // Create the chart
                new Chart(element, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: values,
                            backgroundColor: backgroundColors.slice(0, labels.length),
                            borderWidth: 1,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right'
                            },
                            title: {
                                display: true,
                                text: 'Inventory by Category'
                            }
                        }
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error fetching inventory distribution data:', error);
            element.parentElement.innerHTML = '<div class="alert alert-error">Failed to load chart data</div>';
        });
}

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