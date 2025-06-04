<div class="page-header">
    <h1>Users Management</h1>
    <p class="page-description">Manage system users</p>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Users List</h2>
        <button class="btn btn-primary" data-modal-target="add-user-modal">Add User</button>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>PRS ID</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="users-list">
                    <tr>
                        <td colspan="6" class="text-center">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div id="add-user-modal" class="modal-backdrop hidden">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Add New User</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="add-user-form">
                <div class="form-group">
                    <label for="add-full-name">Full Name</label>
                    <input type="text" id="add-full-name" name="full_name" required>
                </div>
                
                <div class="form-group">
                    <label for="add-email">Email</label>
                    <input type="email" id="add-email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="add-phone">Phone</label>
                    <input type="tel" id="add-phone" name="phone">
                </div>
                
                <div class="form-group">
                    <label for="add-national-id">National ID</label>
                    <input type="text" id="add-national-id" name="national_id">
                </div>
                
                <div class="form-group">
                    <label for="add-dob">Date of Birth</label>
                    <input type="date" id="add-dob" name="dob" required>
                </div>
                
                <div class="form-group">
                    <label for="add-role">Role</label>
                    <select id="add-role" name="role_id" required>
                        <option value="">Select role</option>
                        <option value="1">Administrator</option>
                        <option value="2">Government Official</option>
                        <option value="3">Merchant</option>
                        <option value="4">Public User</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="add-password">Password</label>
                    <input type="password" id="add-password" name="password" required minlength="8">
                </div>
                
                <div class="alert-container"></div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary modal-close">Cancel</button>
            <button class="btn btn-primary" id="add-user-btn">Add User</button>
        </div>
    </div>
</div>

<!-- View User Modal -->
<div id="view-user-modal" class="modal-backdrop hidden">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">User Details</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div id="user-details">
                <div class="loading-container">
                    <div class="loading-spinner"></div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary modal-close">Close</button>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div id="edit-user-modal" class="modal-backdrop hidden">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Edit User</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="edit-user-form">
                <input type="hidden" id="edit-user-id" name="user_id">
                
                <div class="form-group">
                    <label for="edit-full-name">Full Name</label>
                    <input type="text" id="edit-full-name" name="full_name" required>
                </div>
                
                <div class="form-group">
                    <label for="edit-phone">Phone</label>
                    <input type="tel" id="edit-phone" name="phone">
                </div>
                
                <div class="form-group">
                    <label for="edit-account-status">Account Status</label>
                    <select id="edit-account-status" name="account_status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
                
                <div class="alert-container"></div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary modal-close">Cancel</button>
            <button class="btn btn-primary" id="edit-user-btn">Update User</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load users list
    loadUsers();
    
    // Setup add user button
    document.getElementById('add-user-btn').addEventListener('click', function() {
        addUser();
    });
    
    // Setup edit user button
    document.getElementById('edit-user-btn').addEventListener('click', function() {
        updateUser();
    });
});

// Load users list
function loadUsers() {
    fetch('/prs/api/users?limit=1000') // Get all users
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const users = data.data.users;
                const tbody = document.getElementById('users-list');
                
                if (users.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center">No users found</td></tr>';
                    return;
                }
                
                tbody.innerHTML = '';
                
                users.forEach(user => {
                    const row = document.createElement('tr');
                    
                    let statusBadge = '';
                    if (user.account_status === 'active') {
                        statusBadge = '<span class="badge badge-success">Active</span>';
                    } else if (user.account_status === 'inactive') {
                        statusBadge = '<span class="badge badge-secondary">Inactive</span>';
                    } else {
                        statusBadge = '<span class="badge badge-warning">Suspended</span>';
                    }
                    
                    row.innerHTML = `
                        <td>${user.full_name}</td>
                        <td>${user.email}</td>
                        <td>${user.prs_id}</td>
                        <td>${user.role_name}</td>
                        <td>${statusBadge}</td>
                        <td>
                            <button class="btn btn-primary btn-sm view-btn" data-user-id="${user.user_id}">View</button>
                            <button class="btn btn-secondary btn-sm edit-btn" data-user-id="${user.user_id}">Edit</button>
                        </td>
                    `;
                    
                    tbody.appendChild(row);
                });
                
                // Add event listeners to buttons
                const viewButtons = document.querySelectorAll('.view-btn');
                viewButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const userId = this.getAttribute('data-user-id');
                        viewUser(userId);
                    });
                });
                
                const editButtons = document.querySelectorAll('.edit-btn');
                editButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const userId = this.getAttribute('data-user-id');
                        editUser(userId);
                    });
                });
            } else {
                document.getElementById('users-list').innerHTML = 
                    '<tr><td colspan="6" class="text-center">Failed to load users</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error loading users:', error);
            document.getElementById('users-list').innerHTML = 
                '<tr><td colspan="6" class="text-center">Failed to load users</td></tr>';
        });
}

// View user details
function viewUser(userId) {
    // Show modal
    toggleModal(document.getElementById('view-user-modal'));
    
    // Load user details
    const detailsContainer = document.getElementById('user-details');
    detailsContainer.innerHTML = '<div class="loading-container"><div class="loading-spinner"></div></div>';
    
    fetch(`/prs/api/users/${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const user = data.data;
                
                // Build HTML content
                detailsContainer.innerHTML = `
                    <div class="user-detail-card">
                        <div class="section">
                            <h4>Personal Information</h4>
                            <p><strong>Full Name:</strong> ${user.full_name}</p>
                            <p><strong>Email:</strong> ${user.email}</p>
                            <p><strong>Phone:</strong> ${user.phone || 'Not provided'}</p>
                            <p><strong>PRS ID:</strong> ${user.prs_id}</p>
                        </div>
                        
                        <div class="section">
                            <h4>Account Information</h4>
                            <p><strong>Role:</strong> ${getRoleName(user.role_id)}</p>
                            <p><strong>Account Status:</strong> ${getStatusBadge(user.account_status)}</p>
                            <p><strong>Created:</strong> ${new Date(user.created_at).toLocaleDateString('en-GB')}</p>
                        </div>
                    </div>
                `;
            } else {
                detailsContainer.innerHTML = '<div class="alert alert-error">Failed to load user details</div>';
            }
        })
        .catch(error => {
            console.error('Error loading user details:', error);
            detailsContainer.innerHTML = '<div class="alert alert-error">Network error. Please try again.</div>';
        });
}

// Edit user
function editUser(userId) {
    // Load user details
    fetch(`/prs/api/users/${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const user = data.data;
                
                // Populate form fields
                document.getElementById('edit-user-id').value = user.user_id;
                document.getElementById('edit-full-name').value = user.full_name;
                document.getElementById('edit-phone').value = user.phone || '';
                document.getElementById('edit-account-status').value = user.account_status;
                
                // Show modal
                toggleModal(document.getElementById('edit-user-modal'));
            } else {
                alert('Failed to load user details');
            }
        })
        .catch(error => {
            console.error('Error loading user details:', error);
            alert('Network error. Please try again.');
        });
}

// Add user
function addUser() {
    const form = document.getElementById('add-user-form');
    const formData = new FormData(form);
    const data = {};
    
    formData.forEach((value, key) => {
        data[key] = value;
    });
    
    // Show loading state
    const button = document.getElementById('add-user-btn');
    button.disabled = true;
    button.innerHTML = '<span class="loading-spinner-sm"></span> Adding...';
    
    // Send add request
    fetch('/prs/api/auth', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'register',
            ...data
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.status === 'success') {
            // Show success message
            const alertContainer = form.querySelector('.alert-container');
            alertContainer.innerHTML = '<div class="alert alert-success">User added successfully</div>';
            
            // Clear form
            form.reset();
            
            // Reload users list
            loadUsers();
            
            // Close modal after a delay
            setTimeout(() => {
                toggleModal(document.getElementById('add-user-modal'));
                // Reset button
                button.innerHTML = 'Add User';
                button.disabled = false;
                // Clear alert
                alertContainer.innerHTML = '';
            }, 2000);
        } else {
            // Show error message
            const alertContainer = form.querySelector('.alert-container');
            alertContainer.innerHTML = `<div class="alert alert-error">${result.message || 'Failed to add user'}</div>`;
            
            // Reset button
            button.innerHTML = 'Add User';
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error adding user:', error);
        
        // Show error message
        const alertContainer = form.querySelector('.alert-container');
        alertContainer.innerHTML = '<div class="alert alert-error">Network error. Please try again.</div>';
        
        // Reset button
        button.innerHTML = 'Add User';
        button.disabled = false;
    });
}

// Update user
function updateUser() {
    const form = document.getElementById('edit-user-form');
    const formData = new FormData(form);
    const data = {};
    const userId = formData.get('user_id');
    
    formData.forEach((value, key) => {
        if (key !== 'user_id') {
            data[key] = value;
        }
    });
    
    // Show loading state
    const button = document.getElementById('edit-user-btn');
    button.disabled = true;
    button.innerHTML = '<span class="loading-spinner-sm"></span> Updating...';
    
    // Send update request
    fetch(`/prs/api/users/${userId}`, {
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
            alertContainer.innerHTML = '<div class="alert alert-success">User updated successfully</div>';
            
            // Reload users list
            loadUsers();
            
            // Close modal after a delay
            setTimeout(() => {
                toggleModal(document.getElementById('edit-user-modal'));
                // Reset button
                button.innerHTML = 'Update User';
                button.disabled = false;
                // Clear alert
                alertContainer.innerHTML = '';
            }, 2000);
        } else {
            // Show error message
            const alertContainer = form.querySelector('.alert-container');
            alertContainer.innerHTML = `<div class="alert alert-error">${result.message || 'Failed to update user'}</div>`;
            
            // Reset button
            button.innerHTML = 'Update User';
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error updating user:', error);
        
        // Show error message
        const alertContainer = form.querySelector('.alert-container');
        alertContainer.innerHTML = '<div class="alert alert-error">Network error. Please try again.</div>';
        
        // Reset button
        button.innerHTML = 'Update User';
        button.disabled = false;
    });
}

// Helper functions
function getRoleName(roleId) {
    const roles = {
        1: 'Administrator',
        2: 'Government Official',
        3: 'Merchant',
        4: 'Public User'
    };
    return roles[roleId] || 'Unknown';
}

function getStatusBadge(status) {
    if (status === 'active') {
        return '<span class="badge badge-success">Active</span>';
    } else if (status === 'inactive') {
        return '<span class="badge badge-secondary">Inactive</span>';
    } else {
        return '<span class="badge badge-warning">Suspended</span>';
    }
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