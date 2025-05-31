<?php
// Only accessible to public users
if ($_SESSION['user']['role_id'] !== 4) {
    echo '<div class="alert alert-error">Access denied. This page is only available to public users.</div>';
    exit;
}
?>

<div class="page-header">
    <h1>Find Critical Items</h1>
    <p class="page-description">Locate essential supplies near you</p>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Search Criteria</h2>
    </div>
    <div class="card-body">
        <form id="search-form" class="search-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="item-select">Item</label>
                    <select id="item-select" name="item_id" required>
                        <option value="">Select an item</option>
                        <!-- Items will be loaded here -->
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="search-radius">Search Radius (km)</label>
                    <input type="number" id="search-radius" name="radius" min="1" max="50" value="10">
                </div>
                
                <div class="form-group">
                    <label for="use-location">Use My Location</label>
                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="use-location" name="use_location" checked>
                        <span class="checkbox-label">Allow location access</span>
                    </div>
                </div>
                
                <div class="form-group location-inputs" style="display: none;">
                    <label for="postal-code">Postal Code</label>
                    <input type="text" id="postal-code" name="postal_code" placeholder="e.g., SW1A 1AA">
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Find Locations</button>
        </form>
    </div>
</div>

<div id="results-container" style="display: none;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Item Information</h2>
        </div>
        <div class="card-body">
            <div id="item-details">
                <div class="loading-container">
                    <div class="loading-spinner"></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Available Locations</h2>
        </div>
        <div class="card-body">
            <div id="locations-container">
                <div class="loading-container">
                    <div class="loading-spinner"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load critical items for select dropdown
    loadCriticalItems();
    
    // Toggle location inputs based on checkbox
    document.getElementById('use-location').addEventListener('change', function() {
        const locationInputs = document.querySelector('.location-inputs');
        locationInputs.style.display = this.checked ? 'none' : 'block';
    });
    
    // Handle form submission
    document.getElementById('search-form').addEventListener('submit', function(e) {
        e.preventDefault();
        findLocations();
    });
    
    // Check if item_id is in URL
    const urlParams = new URLSearchParams(window.location.search);
    const itemId = urlParams.get('item_id');
    
    if (itemId) {
        // Pre-select the item
        setTimeout(() => {
            document.getElementById('item-select').value = itemId;
            // Submit the form automatically
            findLocations();
        }, 1000); // Small delay to ensure items are loaded
    }
});

// Load critical items
function loadCriticalItems() {
    fetch('/prs/api/items?action=active')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const items = data.data.items;
                const select = document.getElementById('item-select');
                
                if (items.length === 0) {
                    select.innerHTML = '<option value="">No items available</option>';
                    return;
                }
                
                items.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.item_id;
                    option.textContent = item.item_name;
                    select.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error loading critical items:', error);
            document.getElementById('item-select').innerHTML = '<option value="">Failed to load items</option>';
        });
}

// Find locations with available stock
function findLocations() {
    // Show results container
    document.getElementById('results-container').style.display = 'block';
    
    // Show loading state
    document.getElementById('item-details').innerHTML = '<div class="loading-container"><div class="loading-spinner"></div></div>';
    document.getElementById('locations-container').innerHTML = '<div class="loading-container"><div class="loading-spinner"></div></div>';
    
    // Get form data
    const itemId = document.getElementById('item-select').value;
    const radius = document.getElementById('search-radius').value;
    const useLocation = document.getElementById('use-location').checked;
    
    // Validate form
    if (!itemId) {
        document.getElementById('item-details').innerHTML = '<div class="alert alert-error">Please select an item</div>';
        document.getElementById('locations-container').innerHTML = '';
        return;
    }
    
    // Prepare API endpoint
    let apiUrl = `/prs/api/inventory?action=find&item_id=${itemId}&radius=${radius}`;
    
    // Get user location if allowed
    if (useLocation) {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                // Success callback
                function(position) {
                    const latitude = position.coords.latitude;
                    const longitude = position.coords.longitude;
                    
                    // Add coordinates to API URL
                    apiUrl += `&lat=${latitude}&lng=${longitude}`;
                    
                    // Fetch data
                    fetchItemLocations(apiUrl);
                },
                // Error callback
                function(error) {
                    console.error('Geolocation error:', error);
                    document.getElementById('locations-container').innerHTML = `
                        <div class="alert alert-error">
                            Failed to get your location. Please try again or enter your postal code manually.
                        </div>
                    `;
                    
                    // Show postal code input
                    document.getElementById('use-location').checked = false;
                    document.querySelector('.location-inputs').style.display = 'block';
                }
            );
        } else {
            // Geolocation not supported
            document.getElementById('locations-container').innerHTML = `
                <div class="alert alert-error">
                    Geolocation is not supported by your browser. Please enter your postal code manually.
                </div>
            `;
            
            // Show postal code input
            document.getElementById('use-location').checked = false;
            document.querySelector('.location-inputs').style.display = 'block';
        }
    } else {
        // Use postal code
        const postalCode = document.getElementById('postal-code').value;
        
        if (!postalCode) {
            document.getElementById('locations-container').innerHTML = `
                <div class="alert alert-error">
                    Please enter a postal code or allow location access.
                </div>
            `;
            return;
        }
        
        // Add postal code to API URL
        apiUrl += `&postal_code=${encodeURIComponent(postalCode)}`;
        
        // Fetch data
        fetchItemLocations(apiUrl);
    }
}

// Fetch item and location data
function fetchItemLocations(apiUrl) {
    fetch(apiUrl)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Display item details
                displayItemDetails(data.data.item, data.data.can_purchase_today, data.data.has_reached_limit);
                
                // Display locations
                displayLocations(data.data.locations);
            } else {
                document.getElementById('item-details').innerHTML = '<div class="alert alert-error">Failed to load item information</div>';
                document.getElementById('locations-container').innerHTML = '<div class="alert alert-error">Failed to find locations</div>';
            }
        })
        .catch(error => {
            console.error('Error fetching data:', error);
            document.getElementById('item-details').innerHTML = '<div class="alert alert-error">Network error. Please try again.</div>';
            document.getElementById('locations-container').innerHTML = '<div class="alert alert-error">Network error. Please try again.</div>';
        });
}

// Display item details
function displayItemDetails(item, canPurchaseToday, hasReachedLimit) {
    const container = document.getElementById('item-details');
    
    // Format purchase restrictions
    let purchaseStatus = '';
    let purchaseStatusClass = '';
    
    if (!canPurchaseToday) {
        purchaseStatus = 'You cannot purchase this item today based on your date of birth';
        purchaseStatusClass = 'text-error';
    } else if (hasReachedLimit) {
        purchaseStatus = `You have reached the purchase limit of ${item.purchase_limit} per ${item.purchase_frequency}`;
        purchaseStatusClass = 'text-error';
    } else {
        purchaseStatus = 'You are eligible to purchase this item today';
        purchaseStatusClass = 'text-success';
    }
    
    container.innerHTML = `
        <div class="item-info">
            <h3>${item.item_name}</h3>
            <p>${item.item_description || 'No description available'}</p>
            
            <div class="item-details">
                <p><strong>Category:</strong> ${item.item_category}</p>
                <p><strong>Purchase Limit:</strong> ${item.purchase_limit} per ${item.purchase_frequency}</p>
                <p class="${purchaseStatusClass}"><strong>Purchase Status:</strong> ${purchaseStatus}</p>
            </div>
        </div>
    `;
}

// Display locations
function displayLocations(locations) {
    const container = document.getElementById('locations-container');
    
    if (locations.length === 0) {
        container.innerHTML = `
            <div class="alert alert-info">
                No locations found with available stock within the specified radius.
                Try increasing the search radius or selecting a different item.
            </div>
        `;
        return;
    }
    
    // Create locations grid
    let locationsHtml = '<div class="locations-grid">';
    
    locations.forEach(location => {
        locationsHtml += `
            <div class="location-card">
                <div class="location-header">
                    <h3>${location.business_name}</h3>
                    <span class="location-name">${location.location_name}</span>
                </div>
                
                <div class="location-body">
                    <p><strong>Address:</strong> ${location.address}, ${location.city}, ${location.postal_code}</p>
                    <p><strong>Distance:</strong> ${location.distance} km</p>
                    <p><strong>Available:</strong> ${location.quantity_available} in stock</p>
                </div>
                
                <div class="location-footer">
                    <a href="https://www.google.com/maps/search/?api=1&query=${location.latitude},${location.longitude}" 
                       class="btn btn-secondary btn-sm" target="_blank">
                        Directions
                    </a>
                </div>
            </div>
        `;
    });
    
    locationsHtml += '</div>';
    container.innerHTML = locationsHtml;
}
</script>