<div class="page-header">
    <h1>Inventory Management</h1>
    <p class="page-description">Manage critical item stock levels by location</p>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Select Location</h2>
    </div>
    <div class="card-body">
        <div class="form-group">
            <label for="location-select">Choose a location to manage inventory:</label>
            <select id="location-select" class="form-control">
                <option value="">Select a location...</option>
                <!-- Locations will be loaded here -->
            </select>
        </div>
    </div>
</div>

<div id="inventory-section" style="display: none;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Inventory for <span id="selected-location-name"></span></h2>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Current Stock</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="inventory-list">
                        <tr>
                            <td colspan="5" class="text-center">Select a location to view inventory</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Update Stock Modal -->
<div id="update-stock-modal" class="modal-backdrop hidden">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Update Stock Level</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="update-stock-form">
                <input type="hidden" id="inventory-id" name="inventory_id">
                <input type="hidden" id="location-id" name="location_id">
                <input type="hidden" id="item-id" name="item_id">
                
                <div class="form-group">
                    <label for="item-name-display">Item:</label>
                    <input type="text" id="item-name-display" readonly class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="current-stock-display">Current Stock:</label>
                    <input type="number" id="current-stock-display" readonly class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="new-stock">New Stock Level:</label>
                    <input type="number" id="new-stock" name="quantity" min="0" required class="form-control">
                </div>
                
                <div class="alert-container"></div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary modal-close">Cancel</button>
            <button class="btn btn-primary" id="update-stock-btn">Update Stock</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load locations for the dropdown
    loadLocations();
    
    // Handle location selection
    document.getElementById('location-select').addEventListener('change', function() {
        const locationId = this.value;
        if (locationId) {
            loadLocationInventory(locationId);
        } else {
            document.getElementById('inventory-section').style.display = 'none';
        }
    });
    
    // Handle stock update form submission
    document.getElementById('update-stock-btn').addEventListener('click', function() {
        updateStock();
    });
});

// Load available locations
function loadLocations() {
    // For now, we'll need to get locations from the inventory API
    // This assumes you have merchant locations in your database
    fetch('/prs/api/inventory')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const items = data.data.items;
                const select = document.getElementById('location-select');
                
                // Get unique locations
                const locations = {};
                items.forEach(item => {
                    if (!locations[item.location_id]) {
                        locations[item.location_id] = item.location_name;
                    }
                });
                
                // Populate dropdown
                for (const [locationId, locationName] of Object.entries(locations)) {
                    const option = document.createElement('option');
                    option.value = locationId;
                    option.textContent = locationName;
                    select.appendChild(option);
                }
            }
        })
        .catch(error => {
            console.error('Error loading locations:', error);
        });
}

// Load inventory for selected location
function loadLocationInventory(locationId) {
    fetch(`/prs/api/inventory?action=location&id=${locationId}`)
        .then(response => response.json())
        .then(data => {
            console.log('Location inventory response:', data);
            
            if (data.status === 'success') {
                // The API returns items array, not inventory
                let locationName = 'Selected Location';
                const inventory = data.data.items || []; // Changed from data.inventory to data.items
                
                // Try to get location name from first inventory item
                if (inventory.length > 0 && inventory[0].location_name) {
                    locationName = inventory[0].location_name;
                }
                
                // Update location name
                document.getElementById('selected-location-name').textContent = locationName;
                
                // Show inventory section
                document.getElementById('inventory-section').style.display = 'block';
                
                // Populate inventory table
                const tbody = document.getElementById('inventory-list');
                
                if (inventory.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center">No inventory items found for this location</td></tr>';
                    return;
                }
                
                tbody.innerHTML = '';
                
                inventory.forEach(item => {
                    const row = document.createElement('tr');
                    
                    row.innerHTML = `
                        <td>${item.item_name}</td>
                        <td>${item.item_category}</td>
                        <td>${item.quantity_available}</td>
                        <td>${new Date(item.last_updated).toLocaleDateString('en-GB')}</td>
                        <td>
                            <button class="btn btn-primary btn-sm update-btn" 
                                    data-inventory-id="${item.inventory_id}"
                                    data-location-id="${locationId}"
                                    data-item-id="${item.item_id}"
                                    data-item-name="${item.item_name}"
                                    data-current-stock="${item.quantity_available}">
                                Update Stock
                            </button>
                        </td>
                    `;
                    
                    tbody.appendChild(row);
                });
                
                // Add event listeners to update buttons
                const updateButtons = document.querySelectorAll('.update-btn');
                updateButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        openUpdateModal(this);
                    });
                });
            } else {
                console.error('Failed to load location inventory:', data.message);
                document.getElementById('inventory-section').style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error loading location inventory:', error);
            document.getElementById('inventory-section').style.display = 'none';
        });
}

// Open update stock modal
function openUpdateModal(button) {
    const inventoryId = button.getAttribute('data-inventory-id');
    const locationId = button.getAttribute('data-location-id');
    const itemId = button.getAttribute('data-item-id');
    const itemName = button.getAttribute('data-item-name');
    const currentStock = button.getAttribute('data-current-stock');
    
    // Populate modal fields
    document.getElementById('inventory-id').value = inventoryId;
    document.getElementById('location-id').value = locationId;
    document.getElementById('item-id').value = itemId;
    document.getElementById('item-name-display').value = itemName;
    document.getElementById('current-stock-display').value = currentStock;
    document.getElementById('new-stock').value = currentStock;
    
    // Show modal
    toggleModal(document.getElementById('update-stock-modal'));
}

// Update stock level
function updateStock() {
    const form = document.getElementById('update-stock-form');
    const formData = new FormData(form);
    
    const locationId = formData.get('location_id');
    const itemId = formData.get('item_id');
    const quantity = formData.get('quantity');
    
    // Disable button while processing
    const button = document.getElementById('update-stock-btn');
    button.disabled = true;
    button.innerHTML = '<span class="loading-spinner-sm"></span> Updating...';
    
    // Prepare data for API
    const data = {
        location_id: parseInt(locationId),
        items: [{
            item_id: parseInt(itemId),
            quantity: parseInt(quantity)
        }]
    };
    
    // Send update request
    fetch('/prs/api/inventory?action=update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.status === 'success') {
            // Show success message
            const alertContainer = form.querySelector('.alert-container');
            alertContainer.innerHTML = '<div class="alert alert-success">Stock updated successfully</div>';
            
            // Reload inventory for current location
            const currentLocationId = document.getElementById('location-select').value;
            loadLocationInventory(currentLocationId);
            
            // Close modal after a delay
            setTimeout(() => {
                toggleModal(document.getElementById('update-stock-modal'));
                // Reset button
                button.innerHTML = 'Update Stock';
                button.disabled = false;
                // Clear alert
                alertContainer.innerHTML = '';
            }, 2000);
        } else {
            // Show error message
            const alertContainer = form.querySelector('.alert-container');
            alertContainer.innerHTML = `<div class="alert alert-error">${result.message || 'Failed to update stock'}</div>`;
            
            // Reset button
            button.innerHTML = 'Update Stock';
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error updating stock:', error);
        
        // Show error message
        const alertContainer = form.querySelector('.alert-container');
        alertContainer.innerHTML = '<div class="alert alert-error">Network error. Please try again.</div>';
        
        // Reset button
        button.innerHTML = 'Update Stock';
        button.disabled = false;
    });
}

// Toggle modal visibility
function toggleModal(modal) {
    if (modal) {
        modal.classList.toggle('hidden');
        
        // Handle click outside to close
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });
        
        // Handle close button
        const closeButtons = modal.querySelectorAll('.modal-close');
        closeButtons.forEach(button => {
            button.addEventListener('click', function() {
                modal.classList.add('hidden');
            });
        });
    }
}
</script>