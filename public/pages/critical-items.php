<?php
// Get user role for role-specific content
$userRole = $_SESSION['user']['role_id'];
?>

<div class="page-header">
    <h1>Critical Items</h1>
    <p class="page-description">Manage essential items during pandemic</p>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Critical Items List</h2>
        <?php if ($userRole <= 2): // Admin & Government Officials ?>
        <button class="btn btn-primary" data-modal-target="add-item-modal">Add Item</button>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Purchase Limit</th>
                        <th>Frequency</th>
                        <?php if ($userRole <= 2): // Admin & Government Officials ?>
                        <th>Status</th>
                        <th>Actions</th>
                        <?php else: ?>
                        <th>Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody id="items-list">
                    <tr>
                        <td colspan="<?php echo $userRole <= 2 ? '6' : '5'; ?>" class="text-center">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="pagination" id="items-pagination"></div>
    </div>
</div>

<?php if ($userRole <= 2): // Admin & Government Officials ?>
<!-- Add Item Modal -->
<div id="add-item-modal" class="modal-backdrop hidden">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Add Critical Item</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="add-item-form" data-api-submit="/prs/api/items" data-api-method="POST">
                <div class="form-group">
                    <label for="item-name">Item Name</label>
                    <input type="text" id="item-name" name="item_name" required placeholder="e.g., Face Masks (Pack of 10)">
                </div>
                
                <div class="form-group">
                    <label for="item-description">Description</label>
                    <textarea id="item-description" name="item_description" rows="3" placeholder="Provide a brief description"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="item-category">Category</label>
                    <select id="item-category" name="item_category" required>
                        <option value="">Select category</option>
                        <option value="Medical Supplies">Medical Supplies</option>
                        <option value="Hygiene Products">Hygiene Products</option>
                        <option value="Medication">Medication</option>
                        <option value="Essential Food">Essential Food</option>
                        <option value="PPE">PPE</option>
                        <option value="Testing Kits">Testing Kits</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="purchase-limit">Purchase Limit</label>
                    <input type="number" id="purchase-limit" name="purchase_limit" required min="1" value="1">
                </div>
                
                <div class="form-group">
                    <label for="purchase-frequency">Purchase Frequency</label>
                    <select id="purchase-frequency" name="purchase_frequency" required>
                        <option value="">Select frequency</option>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="dob-restriction">DOB Restriction (Optional)</label>
                    <input type="text" id="dob-restriction" name="dob_restriction" placeholder="e.g., 0,2:Monday;1,3:Tuesday;4,6:Wednesday;7,9:Thursday">
                    <small class="form-text">Format: lastDigitOfYear,lastDigitOfYear:Day;lastDigitOfYear,lastDigitOfYear:Day</small>
                </div>
                
                <div class="alert-container"></div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary modal-close">Cancel</button>
            <button class="btn btn-primary" form="add-item-form">Save Item</button>
        </div>
    </div>
</div>

<!-- Edit Item Modal -->
<div id="edit-item-modal" class="modal-backdrop hidden">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Edit Critical Item</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="edit-item-form" data-api-method="PUT">
                <input type="hidden" id="edit-item-id" name="item_id">
                
                <div class="form-group">
                    <label for="edit-item-name">Item Name</label>
                    <input type="text" id="edit-item-name" name="item_name" required>
                </div>
                
                <div class="form-group">
                    <label for="edit-item-description">Description</label>
                    <textarea id="edit-item-description" name="item_description" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="edit-item-category">Category</label>
                    <select id="edit-item-category" name="item_category" required>
                        <option value="">Select category</option>
                        <option value="Medical Supplies">Medical Supplies</option>
                        <option value="Hygiene Products">Hygiene Products</option>
                        <option value="Medication">Medication</option>
                        <option value="Essential Food">Essential Food</option>
                        <option value="PPE">PPE</option>
                        <option value="Testing Kits">Testing Kits</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edit-purchase-limit">Purchase Limit</label>
                    <input type="number" id="edit-purchase-limit" name="purchase_limit" required min="1">
                </div>
                
                <div class="form-group">
                    <label for="edit-purchase-frequency">Purchase Frequency</label>
                    <select id="edit-purchase-frequency" name="purchase_frequency" required>
                        <option value="">Select frequency</option>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edit-dob-restriction">DOB Restriction (Optional)</label>
                    <input type="text" id="edit-dob-restriction" name="dob_restriction">
                    <small class="form-text">Format: lastDigitOfYear,lastDigitOfYear:Day;lastDigitOfYear,lastDigitOfYear:Day</small>
                </div>
                
                <div class="form-group">
                    <label for="edit-is-active">Status</label>
                    <select id="edit-is-active" name="is_active" required>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                
                <div class="alert-container"></div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary modal-close">Cancel</button>
            <button class="btn btn-primary" form="edit-item-form">Update Item</button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- View Item Modal -->
<div id="view-item-modal" class="modal-backdrop hidden">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Item Details</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div id="item-details">
                <div class="loading-container">
                    <div class="loading-spinner"></div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary modal-close">Close</button>
            <?php if ($userRole === 4): // Public User ?>
            <a href="#" id="find-locations-link" class="btn btn-primary">Find Locations</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load items list
    loadItems();
    
    // Setup pagination
    setupPagination();
    
    // Setup form submissions
    <?php if ($userRole <= 2): // Admin & Government Officials ?>
    document.getElementById('edit-item-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const itemId = document.getElementById('edit-item-id').value;
        const formData = new FormData(this);
        const data = {};
        
        formData.forEach((value, key) => {
            data[key] = value;
        });
        
        updateItem(itemId, data, this);
    });
    <?php endif; ?>
});

// Load items list
function loadItems(page = 1) {
    const apiEndpoint = <?php echo $userRole <= 2 ? "'/prs/api/items'" : "'/prs/api/items?action=active'"; ?>;
    
    fetch(`${apiEndpoint}?page=${page}&limit=10`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const items = data.data.items;
                const tbody = document.getElementById('items-list');
                
                if (items.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="<?php echo $userRole <= 2 ? '6' : '5'; ?>" class="text-center">No items found</td></tr>';
                    return;
                }
                
                tbody.innerHTML = '';
                
                items.forEach(item => {
                    const row = document.createElement('tr');
                    
                    <?php if ($userRole <= 2): // Admin & Government Officials ?>
                    // Determine status badge
                    let statusBadge = '';
                    if (item.is_active == 1) {
                        statusBadge = '<span class="badge badge-success">Active</span>';
                    } else {
                        statusBadge = '<span class="badge badge-secondary">Inactive</span>';
                    }
                    
                    row.innerHTML = `
                        <td>${item.item_name}</td>
                        <td>${item.item_category}</td>
                        <td>${item.purchase_limit}</td>
                        <td>${item.purchase_frequency}</td>
                        <td>${statusBadge}</td>
                        <td>
                            <button class="btn btn-primary btn-sm view-btn" data-item-id="${item.item_id}">View</button>
                            <button class="btn btn-secondary btn-sm edit-btn" data-item-id="${item.item_id}">Edit</button>
                        </td>
                    `;
                    <?php else: // Merchant & Public Users ?>
                    row.innerHTML = `
                        <td>${item.item_name}</td>
                        <td>${item.item_category}</td>
                        <td>${item.purchase_limit}</td>
                        <td>${item.purchase_frequency}</td>
                        <td>
                            <button class="btn btn-primary btn-sm view-btn" data-item-id="${item.item_id}">View</button>
                            <?php if ($userRole === 4): // Public User ?>
                            <a href="index.php?page=find-items&item_id=${item.item_id}" class="btn btn-secondary btn-sm">Find Locations</a>
                            <?php endif; ?>
                        </td>
                    `;
                    <?php endif; ?>
                    
                    tbody.appendChild(row);
                });
                
                // Add event listeners to buttons
                const viewButtons = document.querySelectorAll('.view-btn');
                viewButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const itemId = this.getAttribute('data-item-id');
                        viewItem(itemId);
                    });
                });
                
                <?php if ($userRole <= 2): // Admin & Government Officials ?>
                const editButtons = document.querySelectorAll('.edit-btn');
                editButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const itemId = this.getAttribute('data-item-id');
                        editItem(itemId);
                    });
                });
                <?php endif; ?>
                
                // Update pagination
                if (data.data.pagination) {
                    updatePagination(page, data.data.pagination.pages);
                }
            }
        })
        .catch(error => {
            console.error('Error loading items:', error);
            document.getElementById('items-list').innerHTML = 
                '<tr><td colspan="<?php echo $userRole <= 2 ? '6' : '5'; ?>" class="text-center">Failed to load data</td></tr>';
        });
}

// Setup pagination
function setupPagination() {
    const pagination = document.getElementById('items-pagination');
    
    pagination.addEventListener('click', function(e) {
        if (e.target.tagName === 'A') {
            e.preventDefault();
            const page = parseInt(e.target.getAttribute('data-page'));
            loadItems(page);
        }
    });
}

// Update pagination links
function updatePagination(currentPage, totalPages) {
    const pagination = document.getElementById('items-pagination');
    pagination.innerHTML = '';
    
    // Don't show pagination if there's only one page
    if (totalPages <= 1) {
        return;
    }
    
    // Previous page
    const prevItem = document.createElement('li');
    prevItem.className = 'pagination-item';
    const prevLink = document.createElement('a');
    prevLink.href = '#';
    prevLink.className = 'pagination-link';
    prevLink.setAttribute('data-page', Math.max(1, currentPage - 1));
    prevLink.textContent = 'Previous';
    prevItem.appendChild(prevLink);
    pagination.appendChild(prevItem);
    
    // Page numbers
    const maxVisiblePages = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
    
    if (endPage - startPage + 1 < maxVisiblePages) {
        startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }
    
    for (let i = startPage; i <= endPage; i++) {
        const pageItem = document.createElement('li');
        pageItem.className = 'pagination-item';
        const pageLink = document.createElement('a');
        pageLink.href = '#';
        pageLink.className = 'pagination-link';
        if (i === currentPage) {
            pageLink.className += ' active';
        }
        pageLink.setAttribute('data-page', i);
        pageLink.textContent = i;
        pageItem.appendChild(pageLink);
        pagination.appendChild(pageItem);
    }
    
    // Next page
    const nextItem = document.createElement('li');
    nextItem.className = 'pagination-item';
    const nextLink = document.createElement('a');
    nextLink.href = '#';
    nextLink.className = 'pagination-link';
    nextLink.setAttribute('data-page', Math.min(totalPages, currentPage + 1));
    nextLink.textContent = 'Next';
    nextItem.appendChild(nextLink);
    pagination.appendChild(nextItem);
}

// View item details
function viewItem(itemId) {
    // Show modal
    toggleModal(document.getElementById('view-item-modal'));
    
    // Load item details
    const detailsContainer = document.getElementById('item-details');
    detailsContainer.innerHTML = '<div class="loading-container"><div class="loading-spinner"></div></div>';
    
    fetch(`/prs/api/items/${itemId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const item = data.data;
                
                // Format DOB restriction if exists
                let dobRestrictionHtml = '';
                if (item.dob_restriction) {
                    let restrictions = item.dob_restriction.split(';');
                    let restrictionList = '';
                    
                    restrictions.forEach(restriction => {
                        const [digits, day] = restriction.split(':');
                        restrictionList += `<li>Birth years ending with ${digits}: ${day}</li>`;
                    });
                    
                    dobRestrictionHtml = `
                        <div class="section">
                            <h4>Purchase Day Restrictions</h4>
                            <p>Based on the last digit of your birth year:</p>
                            <ul>${restrictionList}</ul>
                        </div>
                    `;
                }
                
                // Build HTML content
                detailsContainer.innerHTML = `
                    <div class="item-detail-card">
                        <div class="section">
                            <h4>${item.item_name}</h4>
                            <p>${item.item_description || 'No description available'}</p>
                        </div>
                        
                        <div class="section">
                            <h4>Purchase Information</h4>
                            <p><strong>Category:</strong> ${item.item_category}</p>
                            <p><strong>Purchase Limit:</strong> ${item.purchase_limit} per ${item.purchase_frequency}</p>
                            <p><strong>Status:</strong> ${item.is_active == 1 ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-secondary">Inactive</span>'}</p>
                        </div>
                        
                        ${dobRestrictionHtml}
                    </div>
                `;
                
                // Update find locations link
                const findLocationsLink = document.getElementById('find-locations-link');
                if (findLocationsLink) {
                    findLocationsLink.href = `index.php?page=find-items&item_id=${item.item_id}`;
                }
            } else {
                detailsContainer.innerHTML = '<div class="alert alert-error">Failed to load item details</div>';
            }
        })
        .catch(error => {
            console.error('Error loading item details:', error);
            detailsContainer.innerHTML = '<div class="alert alert-error">Network error. Please try again.</div>';
        });
}

<?php if ($userRole <= 2): // Admin & Government Officials ?>
// Edit item
function editItem(itemId) {
    // Load item details
    fetch(`/prs/api/items/${itemId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const item = data.data;
                
                // Populate form fields
                document.getElementById('edit-item-id').value = item.item_id;
                document.getElementById('edit-item-name').value = item.item_name;
                document.getElementById('edit-item-description').value = item.item_description || '';
                document.getElementById('edit-item-category').value = item.item_category;
                document.getElementById('edit-purchase-limit').value = item.purchase_limit;
                document.getElementById('edit-purchase-frequency').value = item.purchase_frequency;
                document.getElementById('edit-dob-restriction').value = item.dob_restriction || '';
                document.getElementById('edit-is-active').value = item.is_active;
                
                // Set form action
                document.getElementById('edit-item-form').setAttribute('data-api-submit', `/prs/api/items/${item.item_id}`);
                
                // Show modal
                toggleModal(document.getElementById('edit-item-modal'));
            } else {
                alert('Failed to load item details');
            }
        })
        .catch(error => {
            console.error('Error loading item details:', error);
            alert('Network error. Please try again.');
        });
}

// Update item
function updateItem(itemId, data, form) {
    // Show loading state
    const submitButton = form.querySelector('button[type="submit"]');
    const originalButtonText = submitButton.innerHTML;
    submitButton.disabled = true;
    submitButton.innerHTML = '<span class="loading-spinner-sm"></span> Updating...';
    
    // Send update request
    fetch(`/prs/api/items/${itemId}`, {
        method: 'PUT',
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
            alertContainer.innerHTML = '<div class="alert alert-success">Item updated successfully</div>';
            
            // Reload items list
            loadItems();
            
            // Close modal after a delay
            setTimeout(() => {
                toggleModal(document.getElementById('edit-item-modal'));
                // Reset button
                submitButton.innerHTML = originalButtonText;
                submitButton.disabled = false;
                // Clear alert
                alertContainer.innerHTML = '';
            }, 2000);
        } else {
            // Show error message
            const alertContainer = form.querySelector('.alert-container');
            alertContainer.innerHTML = `<div class="alert alert-error">${result.message || 'Failed to update item'}</div>`;
            
            // Reset button
            submitButton.innerHTML = originalButtonText;
            submitButton.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error updating item:', error);
        
        // Show error message
        const alertContainer = form.querySelector('.alert-container');
        alertContainer.innerHTML = '<div class="alert alert-error">Network error. Please try again.</div>';
        
        // Reset button
        submitButton.innerHTML = originalButtonText;
        submitButton.disabled = false;
    });
}
<?php endif; ?>

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