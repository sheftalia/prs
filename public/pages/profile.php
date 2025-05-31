<?php
// Get user data
$user = $_SESSION['user'];
?>

<div class="page-header">
    <h1>My Profile</h1>
    <p class="page-description">Manage your personal information and settings</p>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Personal Information</h2>
            </div>
            <div class="card-body">
                <div class="profile-info">
                    <p><strong>PRS ID:</strong> <span id="profile-prs-id"><?php echo $user['prs_id']; ?></span></p>
                    <p><strong>Full Name:</strong> <span id="profile-name"><?php echo $user['full_name']; ?></span></p>
                    <p><strong>Email:</strong> <span id="profile-email"><?php echo $user['email']; ?></span></p>
                    <p><strong>Phone:</strong> <span id="profile-phone">Loading...</span></p>
                    <p><strong>Date of Birth:</strong> <span id="profile-dob">Loading...</span></p>
                    <p><strong>Account Status:</strong> <span id="profile-status" class="badge badge-success">Active</span></p>
                </div>
                
                <button class="btn btn-primary mt-3" data-modal-target="edit-profile-modal">Edit Profile</button>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h2 class="card-title">Account Security</h2>
            </div>
            <div class="card-body">
                <p>Change your password to maintain account security.</p>
                <button class="btn btn-secondary" data-modal-target="change-password-modal">Change Password</button>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <?php if ($user['role_id'] === 4): // Only for Public Users ?>
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Family Members</h2>
                <button class="btn btn-primary btn-sm" data-modal-target="add-family-modal">Add Family Member</button>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>PRS ID</th>
                                <th>Date of Birth</th>
                                <th>Relationship</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="family-members-list">
                            <tr>
                                <td colspan="5" class="text-center">Loading family members...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="card mt-4">
            <div class="card-header">
                <h2 class="card-title">Recent Activity</h2>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Activity</th>
                                <th>Date</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody id="activity-log">
                            <tr>
                                <td colspan="3" class="text-center">Loading activity log...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div id="edit-profile-modal" class="modal-backdrop hidden">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Edit Profile</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="edit-profile-form" data-api-submit="/prs/api/users/<?php echo $user['user_id']; ?>" data-api-method="PUT">
                <div class="form-group">
                    <label for="edit-full-name">Full Name</label>
                    <input type="text" id="edit-full-name" name="full_name" value="<?php echo $user['full_name']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="edit-phone">Phone Number</label>
                    <input type="tel" id="edit-phone" name="phone" placeholder="Enter your phone number">
                </div>
                
                <div class="alert-container"></div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary modal-close">Cancel</button>
            <button class="btn btn-primary" form="edit-profile-form">Save Changes</button>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div id="change-password-modal" class="modal-backdrop hidden">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Change Password</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="change-password-form" data-api-submit="/prs/api/users/change-password" data-api-method="POST">
                <div class="form-group">
                    <label for="current-password">Current Password</label>
                    <input type="password" id="current-password" name="current_password" required>
                </div>
                
                <div class="form-group">
                    <label for="new-password">New Password</label>
                    <input type="password" id="new-password" name="new_password" required minlength="8">
                </div>
                
                <div class="form-group">
                    <label for="confirm-password">Confirm New Password</label>
                    <input type="password" id="confirm-password" name="confirm_password" required minlength="8">
                </div>
                
                <div class="alert-container"></div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary modal-close">Cancel</button>
            <button class="btn btn-primary" form="change-password-form">Change Password</button>
        </div>
    </div>
</div>

<?php if ($user['role_id'] === 4): // Only for Public Users ?>
<!-- Add Family Member Modal -->
<div id="add-family-modal" class="modal-backdrop hidden">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Add Family Member</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="add-family-form" data-api-submit="/prs/api/users/add-family" data-api-method="POST">
                <div class="form-group">
                    <label for="family-full-name">Full Name</label>
                    <input type="text" id="family-full-name" name="full_name" required>
                </div>
                
                <div class="form-group">
                    <label for="family-dob">Date of Birth</label>
                    <input type="date" id="family-dob" name="dob" required>
                </div>
                
                <div class="form-group">
                    <label for="family-relation">Relationship</label>
                    <select id="family-relation" name="relation_type" required>
                        <option value="">Select relationship</option>
                        <option value="spouse">Spouse</option>
                        <option value="child">Child</option>
                        <option value="parent">Parent</option>
                        <option value="sibling">Sibling</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div class="alert-container"></div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary modal-close">Cancel</button>
            <button class="btn btn-primary" form="add-family-form">Add Family Member</button>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load profile data
    loadProfileData();
    
    <?php if ($user['role_id'] === 4): ?>
    // Load family members
    loadFamilyMembers();
    <?php endif; ?>
    
    // Load activity log
    loadActivityLog();
    
    // Form validation for password change
    document.getElementById('change-password-form').addEventListener('submit', function(e) {
        const newPassword = document.getElementById('new-password').value;
        const confirmPassword = document.getElementById('confirm-password').value;
        
        if (newPassword !== confirmPassword) {
            e.preventDefault();
            
            const alertContainer = this.querySelector('.alert-container');
            alertContainer.innerHTML = '<div class="alert alert-error">Passwords do not match</div>';
            
            return false;
        }
    });
});

// Load profile data
function loadProfileData() {
    fetch('/prs/api/users/profile')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const profile = data.data;
                
                // Update profile information
                document.getElementById('profile-phone').textContent = profile.phone || 'Not provided';
                document.getElementById('profile-dob').textContent = new Date(profile.dob).toLocaleDateString();
                
                // Update edit form fields
                document.getElementById('edit-phone').value = profile.phone || '';
            }
        })
        .catch(error => {
            console.error('Error loading profile data:', error);
        });
}

<?php if ($user['role_id'] === 4): ?>
// Load family members
function loadFamilyMembers() {
    fetch('/prs/api/users/profile')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const familyMembers = data.data.family_members || [];
                const tbody = document.getElementById('family-members-list');
                
                if (familyMembers.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center">No family members added</td></tr>';
                    return;
                }
                
                tbody.innerHTML = '';
                
                familyMembers.forEach(member => {
                    const row = document.createElement('tr');
                    
                    row.innerHTML = `
                        <td>${member.full_name}</td>
                        <td>${member.prs_id}</td>
                        <td>${new Date(member.dob).toLocaleDateString()}</td>
                        <td>${member.relation_type}</td>
                        <td>
                            <a href="index.php?page=vaccinations&user_id=${member.user_id}" class="btn btn-secondary btn-sm">Vaccinations</a>
                            <button class="btn btn-error btn-sm remove-family-btn" data-family-id="${member.user_id}">Remove</button>
                        </td>
                    `;
                    
                    tbody.appendChild(row);
                });
                
                // Add event listeners to remove buttons
                const removeButtons = document.querySelectorAll('.remove-family-btn');
                removeButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const familyId = this.getAttribute('data-family-id');
                        removeFamilyMember(familyId, this);
                    });
                });
            }
        })
        .catch(error => {
            console.error('Error loading family members:', error);
            document.getElementById('family-members-list').innerHTML = 
                '<tr><td colspan="5" class="text-center">Failed to load family members</td></tr>';
        });
}

// Remove family member
function removeFamilyMember(familyId, button) {
    if (confirm('Are you sure you want to remove this family member?')) {
        // Disable button while processing
        button.disabled = true;
        button.textContent = 'Removing...';
        
        fetch(`/prs/api/users/${familyId}/family`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Remove row from table
                button.closest('tr').remove();
                
                // Check if there are any remaining rows
                const tbody = document.getElementById('family-members-list');
                if (tbody.children.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center">No family members added</td></tr>';
                }
            } else {
                // Reset button
                button.disabled = false;
                button.textContent = 'Remove';
                alert('Failed to remove family member: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error removing family member:', error);
            // Reset button
            button.disabled = false;
            button.textContent = 'Remove';
            alert('Failed to remove family member due to network error');
        });
    }
}
<?php endif; ?>

// Load activity log
function loadActivityLog() {
    fetch('/prs/api/users/activity')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const activities = data.data.activities || [];
                const tbody = document.getElementById('activity-log');
                
                if (activities.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="3" class="text-center">No recent activity</td></tr>';
                    return;
                }
                
                tbody.innerHTML = '';
                
                activities.forEach(activity => {
                    const row = document.createElement('tr');
                    
                    row.innerHTML = `
                        <td>${activity.action} ${activity.entity_affected || ''}</td>
                        <td>${new Date(activity.timestamp).toLocaleString()}</td>
                        <td>${activity.ip_address}</td>
                    `;
                    
                    tbody.appendChild(row);
                });
            }
        })
        .catch(error => {
            console.error('Error loading activity log:', error);
            document.getElementById('activity-log').innerHTML = 
                '<tr><td colspan="3" class="text-center">Failed to load activity log</td></tr>';
        });
}
</script>