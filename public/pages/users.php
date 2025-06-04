<div class="page-header">
    <h1>Users Management</h1>
    <p class="page-description">Manage system users</p>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Users List</h2>
        <button class="btn btn-primary" id="add-user-btn">Add User</button>
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
        <div class="pagination" id="users-pagination"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load users list
    loadUsers();
    
    // Setup add user button (placeholder for now)
    document.getElementById('add-user-btn').addEventListener('click', function() {
        alert('Add user functionality coming soon');
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
                    } else {
                        statusBadge = '<span class="badge badge-secondary">Inactive</span>';
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
    // For now, just show user details in an alert
    // In a real implementation, this would open a modal or redirect to a details page
    fetch(`/prs/api/users/${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const user = data.data;
                alert(`User Details:\nName: ${user.full_name}\nEmail: ${user.email}\nPRS ID: ${user.prs_id}\nStatus: ${user.account_status}`);
            } else {
                alert('Failed to load user details');
            }
        })
        .catch(error => {
            console.error('Error loading user details:', error);
            alert('Failed to load user details');
        });
}

// Edit user
function editUser(userId) {
    // For now, just show a placeholder alert
    // In a real implementation, this would open an edit modal
    alert('Edit user functionality coming soon');
}
</script>