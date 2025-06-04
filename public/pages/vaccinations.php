<?php
// Get user role for role-specific content
$userRole = $_SESSION['user']['role_id'];
$userId = $_SESSION['user']['user_id'];
?>

<div class="page-header">
    <h1>Vaccinations</h1>
    <p class="page-description">Manage vaccination records and certificates</p>
</div>

<?php if ($userRole <= 2): // Admin & Government Officials ?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Vaccination Statistics</h2>
    </div>
    <div class="card-body">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-syringe"></i>
                </div>
                <div class="stat-value" id="total-vaccinations">--</div>
                <div class="stat-label">Total Records</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-value" id="verified-vaccinations">--</div>
                <div class="stat-label">Verified Records</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value" id="vaccinated-users">--</div>
                <div class="stat-label">Vaccinated Users</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="stat-value" id="pending-verification">--</div>
                <div class="stat-label">Pending Verification</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Verification Queue</h2>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>PRS ID</th>
                        <th>Vaccine</th>
                        <th>Dose</th>
                        <th>Date Administered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="verification-queue">
                    <tr>
                        <td colspan="6" class="text-center">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Verified Vaccination Records</h2>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>PRS ID</th>
                        <th>Vaccine</th>
                        <th>Dose</th>
                        <th>Date Administered</th>
                        <th>Verified By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="verified-records">
                    <tr>
                        <td colspan="7" class="text-center">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php else: // Merchant & Public Users ?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <?php if (isset($_GET['user_id']) && $_GET['user_id'] != $userId): ?>
                Family Member's Vaccination Records
            <?php else: ?>
                My Vaccination Records
            <?php endif; ?>
        </h2>
        <button class="btn btn-primary" data-modal-target="add-vaccination-modal">Add Vaccination Record</button>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Vaccine Name</th>
                        <th>Dose</th>
                        <th>Date Administered</th>
                        <th>Provider</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="vaccination-records">
                    <tr>
                        <td colspan="6" class="text-center">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Vaccination Record Modal -->
<div id="add-vaccination-modal" class="modal-backdrop hidden">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Add Vaccination Record</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="add-vaccination-form" data-api-submit="/prs/api/vaccinations?action=upload" data-api-method="POST">
                <?php if ($userRole === 4): // Public User ?>
                <div class="form-group">
                    <label for="user-select">Person</label>
                    <select id="user-select" name="user_id" required>
                        <option value="<?php echo $userId; ?>">Myself</option>
                        <!-- Family members will be loaded here -->
                    </select>
                </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="vaccine-name">Vaccine Name</label>
                    <input type="text" id="vaccine-name" name="vaccine_name" required placeholder="e.g., COVID-19 Vaccine AstraZeneca">
                </div>
                
                <div class="form-group">
                    <label for="dose-number">Dose Number</label>
                    <input type="number" id="dose-number" name="dose_number" required min="1" value="1">
                </div>
                
                <div class="form-group">
                    <label for="date-administered">Date Administered</label>
                    <input type="date" id="date-administered" name="date_administered" required>
                </div>
                
                <div class="form-group">
                    <label for="provider">Healthcare Provider</label>
                    <input type="text" id="provider" name="provider" required placeholder="e.g., NHS London Vaccination Centre">
                </div>
                
                <div class="form-group">
                    <label for="lot-number">Lot Number (Optional)</label>
                    <input type="text" id="lot-number" name="lot_number" placeholder="e.g., AZ-12345">
                </div>
                
                <div class="form-group">
                    <label for="expiration-date">Expiration Date (Optional)</label>
                    <input type="date" id="expiration-date" name="expiration_date">
                </div>
                
                <div class="alert-container"></div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary modal-close">Cancel</button>
            <button class="btn btn-primary" form="add-vaccination-form">Save Record</button>
        </div>
    </div>
</div>

<!-- Upload Document Modal -->
<div id="upload-document-modal" class="modal-backdrop hidden">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Upload Vaccination Document</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="upload-document-form" enctype="multipart/form-data">
                <input type="hidden" id="record-id" name="record_id">
                
                <div class="form-group">
                    <label for="document-file">Select Document (PDF, JPG, PNG)</label>
                    <input type="file" id="document-file" name="document" accept=".pdf,.jpg,.jpeg,.png" required>
                </div>
                
                <div class="alert-container"></div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary modal-close">Cancel</button>
            <button class="btn btn-primary" id="upload-document-btn">Upload Document</button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- View Vaccination Record Modal -->
<div id="view-vaccination-modal" class="modal-backdrop hidden">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Vaccination Record Details</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div id="vaccination-details">
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load data based on user role
    const userRole = <?php echo $userRole; ?>;
    
    if (userRole <= 2) { // Admin & Government Officials
        loadVaccinationStats();
        loadVerificationQueue();
        loadVerifiedRecords();
    } else {
        loadVaccinationRecords();
        
        <?php if ($userRole === 4): // Public User ?>
        loadFamilyMembers();
        <?php endif; ?>
    }
    
    // Handle document upload
    const uploadDocumentBtn = document.getElementById('upload-document-btn');
    if (uploadDocumentBtn) {
        uploadDocumentBtn.addEventListener('click', uploadDocument);
    }
});

<?php if ($userRole <= 2): // Admin & Government Officials ?>
// Load vaccination statistics
function loadVaccinationStats() {
    fetch('/prs/api/stats/vaccinations')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Use vaccination_stats instead of stats
                const stats = data.data.vaccination_stats;
                
                if (stats) {
                    // Update statistics
                    document.getElementById('total-vaccinations').textContent = stats.total_records || 0;
                    document.getElementById('verified-vaccinations').textContent = stats.verified_records || 0;
                    document.getElementById('vaccinated-users').textContent = stats.vaccinated_users || 0;
                    document.getElementById('pending-verification').textContent = 
                        (stats.total_records || 0) - (stats.verified_records || 0);
                    
                    // Initialize charts
                    if (data.data.vaccination_trend) {
                        setupVaccinationTrendChart(data.data.vaccination_trend);
                    }
                    if (data.data.vaccine_distribution) {
                        setupVaccineDistributionChart(data.data.vaccine_distribution);
                    }
                } else {
                    console.error('vaccination_stats object is missing in the API response');
                }
            } else {
                console.error('API returned error:', data);
            }
        })
        .catch(error => {
            console.error('Error loading vaccination statistics:', error);
        });
}

// Load verification queue
function loadVerificationQueue(page = 1) {
    fetch(`/prs/api/vaccinations?action=unverified&page=${page}&limit=10`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const records = data.data.records;
                const tbody = document.getElementById('verification-queue');
                
                if (records.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center">No pending verification records</td></tr>';
                    return;
                }
                
                tbody.innerHTML = '';
                
                records.forEach(record => {
                    const row = document.createElement('tr');
                    
                    row.innerHTML = `
                        <td>${record.user_name}</td>
                        <td>${record.prs_id}</td>
                        <td>${record.vaccine_name}</td>
                        <td>${record.dose_number}</td>
                        <td>${new Date(record.date_administered).toLocaleDateString()}</td>
                        <td>
                            <button class="btn btn-primary btn-sm view-btn" data-record-id="${record.record_id}">View</button>
                            <button class="btn btn-success btn-sm verify-btn" data-record-id="${record.record_id}">Verify</button>
                        </td>
                    `;
                    
                    tbody.appendChild(row);
                });
                
                // Add event listeners to buttons
                const viewButtons = document.querySelectorAll('.view-btn');
                viewButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const recordId = this.getAttribute('data-record-id');
                        viewVaccinationRecord(recordId);
                    });
                });
                
                const verifyButtons = document.querySelectorAll('.verify-btn');
                verifyButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const recordId = this.getAttribute('data-record-id');
                        verifyVaccinationRecord(recordId, this);
                    });
                });
            }
        })
        .catch(error => {
            console.error('Error loading verification queue:', error);
            document.getElementById('verification-queue').innerHTML = 
                '<tr><td colspan="6" class="text-center">Failed to load data</td></tr>';
        });
}

// Load verified vaccination records
function loadVerifiedRecords(page = 1) {
    fetch(`/prs/api/vaccinations?action=verified&page=${page}&limit=20`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const records = data.data.records || [];
                const tbody = document.getElementById('verified-records');
                
                if (records.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center">No verified vaccination records</td></tr>';
                    return;
                }
                
                tbody.innerHTML = '';
                
                records.forEach(record => {
                    const row = document.createElement('tr');
                    
                    row.innerHTML = `
                        <td>${record.user_name}</td>
                        <td>${record.prs_id}</td>
                        <td>${record.vaccine_name}</td>
                        <td>${record.dose_number}</td>
                        <td>${new Date(record.date_administered).toLocaleDateString()}</td>
                        <td>${record.verifier_name}</td>
                        <td>
                            <button class="btn btn-primary btn-sm view-verified-btn" data-record-id="${record.record_id}">View</button>
                        </td>
                    `;
                    
                    tbody.appendChild(row);
                });
                
                // Add event listeners to view buttons for verified records
                const viewVerifiedButtons = document.querySelectorAll('.view-verified-btn');
                viewVerifiedButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const recordId = this.getAttribute('data-record-id');
                        viewVaccinationRecord(recordId);
                    });
                });
            }
        })
        .catch(error => {
            console.error('Error loading verified records:', error);
            document.getElementById('verified-records').innerHTML = 
                '<tr><td colspan="7" class="text-center">Failed to load data</td></tr>';
        });
}

// Verify vaccination record
function verifyVaccinationRecord(recordId, button) {
    // Disable button while processing
    button.disabled = true;
    button.innerHTML = '<span class="loading-spinner-sm"></span> Verifying...';
    
    // Send verification request
    fetch('/prs/api/vaccinations?action=verify', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            record_id: recordId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Update button to show success
            button.innerHTML = 'Verified ✓';
            button.classList.remove('btn-success');
            button.classList.add('btn-secondary');
            
            // Remove row after a short delay
            setTimeout(() => {
                button.closest('tr').remove();
                
                // Check if there are any remaining rows
                const tbody = document.getElementById('verification-queue');
                if (tbody.children.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center">No pending verification records</td></tr>';
                }
                
                // Update statistics
                loadVaccinationStats();
            }, 1000);
        } else {
            // Show error
            button.innerHTML = 'Failed';
            button.classList.remove('btn-success');
            button.classList.add('btn-error');
            
            // Reset after a short delay
            setTimeout(() => {
                button.innerHTML = 'Verify';
                button.classList.remove('btn-error');
                button.classList.add('btn-success');
                button.disabled = false;
            }, 2000);
        }
    })
    .catch(error => {
        console.error('Error verifying vaccination record:', error);
        
        // Show error
        button.innerHTML = 'Failed';
        button.classList.remove('btn-success');
        button.classList.add('btn-error');
        
        // Reset after a short delay
        setTimeout(() => {
            button.innerHTML = 'Verify';
            button.classList.remove('btn-error');
            button.classList.add('btn-success');
            button.disabled = false;
        }, 2000);
    });
}
<?php else: // Merchant & Public Users ?>
// Load vaccination records
function loadVaccinationRecords(page = 1) {
    // Check if viewing family member's records
    const urlParams = new URLSearchParams(window.location.search);
    const userId = urlParams.get('user_id') || <?php echo $userId; ?>;
    
    fetch(`/prs/api/vaccinations?action=user&user_id=${userId}&page=${page}&limit=10`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const records = data.data.records;
                const tbody = document.getElementById('vaccination-records');
                
                if (records.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center">No vaccination records found</td></tr>';
                    return;
                }
                
                tbody.innerHTML = '';
                
                records.forEach(record => {
                    const row = document.createElement('tr');
                    
                    // Determine status badge
                    let statusBadge = '';
                    if (record.verified) {
                        statusBadge = '<span class="badge badge-success">Verified</span>';
                    } else {
                        statusBadge = '<span class="badge badge-warning">Pending</span>';
                    }
                    
                    row.innerHTML = `
                        <td>${record.vaccine_name}</td>
                        <td>${record.dose_number}</td>
                        <td>${new Date(record.date_administered).toLocaleDateString()}</td>
                        <td>${record.provider}</td>
                        <td>${statusBadge}</td>
                        <td>
                            <button class="btn btn-primary btn-sm view-btn" data-record-id="${record.record_id}">View</button>
                            <button class="btn btn-secondary btn-sm upload-btn" data-record-id="${record.record_id}">Upload Document</button>
                        </td>
                    `;
                    
                    tbody.appendChild(row);
                });
                
                // Add event listeners to buttons
                const viewButtons = document.querySelectorAll('.view-btn');
                viewButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const recordId = this.getAttribute('data-record-id');
                        viewVaccinationRecord(recordId);
                    });
                });
                
                const uploadButtons = document.querySelectorAll('.upload-btn');
                uploadButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const recordId = this.getAttribute('data-record-id');
                        document.getElementById('record-id').value = recordId;
                        toggleModal(document.getElementById('upload-document-modal'));
                    });
                });
            }
        })
        .catch(error => {
            console.error('Error loading vaccination records:', error);
            document.getElementById('vaccination-records').innerHTML = 
                '<tr><td colspan="6" class="text-center">Failed to load data</td></tr>';
        });
}

<?php if ($userRole === 4): // Public User ?>
// Load family members for select dropdown
function loadFamilyMembers() {
    fetch('/prs/api/users/profile')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const familyMembers = data.data.family_members || [];
                const select = document.getElementById('user-select');
                
                familyMembers.forEach(member => {
                    const option = document.createElement('option');
                    option.value = member.user_id;
                    option.textContent = `${member.full_name} (${member.relation_type})`;
                    select.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error loading family members:', error);
        });
}
<?php endif; ?>

// Upload document for vaccination record
function uploadDocument() {
    const form = document.getElementById('upload-document-form');
    const formData = new FormData(form);
    const recordId = formData.get('record_id');
    
    // Check if file is selected
    if (!formData.get('document').size) {
        const alertContainer = form.querySelector('.alert-container');
        alertContainer.innerHTML = '<div class="alert alert-error">Please select a file to upload</div>';
        return;
    }
    
    // Disable button while uploading
    const button = document.getElementById('upload-document-btn');
    button.disabled = true;
    button.innerHTML = '<span class="loading-spinner-sm"></span> Uploading...';
    
    // Send upload request
    fetch(`/prs/api/vaccinations/${recordId}/upload-document`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Show success message
            const alertContainer = form.querySelector('.alert-container');
            alertContainer.innerHTML = '<div class="alert alert-success">Document uploaded successfully</div>';
            
            // Reset form
            form.reset();
            
            // Close modal after a delay
            setTimeout(() => {
                toggleModal(document.getElementById('upload-document-modal'));
                // Reset button
                button.innerHTML = 'Upload Document';
                button.disabled = false;
            }, 2000);
        } else {
            // Show error message
            const alertContainer = form.querySelector('.alert-container');
            alertContainer.innerHTML = `<div class="alert alert-error">${data.message || 'Failed to upload document'}</div>`;
            
            // Reset button
            button.innerHTML = 'Upload Document';
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error uploading document:', error);
        
        // Show error message
        const alertContainer = form.querySelector('.alert-container');
        alertContainer.innerHTML = '<div class="alert alert-error">Network error. Please try again.</div>';
        
        // Reset button
        button.innerHTML = 'Upload Document';
        button.disabled = false;
    });
}
<?php endif; ?>

// View vaccination record details
function viewVaccinationRecord(recordId) {
    // Show modal
    toggleModal(document.getElementById('view-vaccination-modal'));
    
    // Load record details
    const detailsContainer = document.getElementById('vaccination-details');
    detailsContainer.innerHTML = '<div class="loading-container"><div class="loading-spinner"></div></div>';
    
    fetch(`/prs/api/vaccinations/${recordId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const record = data.data;
                
                // Format status badge
                let statusBadge = '';
                if (record.verified) {
                    statusBadge = '<span class="badge badge-success">Verified</span>';
                } else {
                    statusBadge = '<span class="badge badge-warning">Pending Verification</span>';
                }
                
                // Format verification details
                let verificationDetails = '';
                if (record.verified) {
                    verificationDetails = `
                        <p><strong>Verified By:</strong> ${record.verifier_name}</p>
                        <p><strong>Verification Date:</strong> ${new Date(record.verified_date).toLocaleString()}</p>
                    `;
                }
                
                // Format documents list
                let documentsSection = '';
                if (record.documents && record.documents.length > 0) {
                    let documentsList = '';
                    record.documents.forEach(doc => {
                        documentsList += `
                            <li>
                                <a href="/prs/api/documents/${doc.document_id}" target="_blank">${doc.file_name}</a>
                                <span class="text-muted">(${new Date(doc.upload_date).toLocaleDateString()})</span>
                            </li>
                        `;
                    });
                    
                    documentsSection = `
                        <div class="section">
                            <h4>Supporting Documents</h4>
                            <ul>${documentsList}</ul>
                        </div>
                    `;
                } else {
                    documentsSection = `
                        <div class="section">
                            <h4>Supporting Documents</h4>
                            <p>No documents uploaded yet.</p>
                        </div>
                    `;
                }
                
                // Build HTML content
                detailsContainer.innerHTML = `
                    <div class="vaccination-detail-card">
                        <div class="section">
                            <h4>Patient Information</h4>
                            <p><strong>Name:</strong> ${record.user_name}</p>
                            <p><strong>PRS ID:</strong> ${record.prs_id || 'N/A'}</p>
                        </div>
                        
                        <div class="section">
                            <h4>Vaccination Details</h4>
                            <p><strong>Vaccine:</strong> ${record.vaccine_name}</p>
                            <p><strong>Dose Number:</strong> ${record.dose_number}</p>
                            <p><strong>Date Administered:</strong> ${new Date(record.date_administered).toLocaleDateString()}</p>
                            <p><strong>Healthcare Provider:</strong> ${record.provider}</p>
                            <p><strong>Lot Number:</strong> ${record.lot_number || 'N/A'}</p>
                            <p><strong>Expiration Date:</strong> ${record.expiration_date ? new Date(record.expiration_date).toLocaleDateString() : 'N/A'}</p>
                            <p><strong>Status:</strong> ${statusBadge}</p>
                            ${verificationDetails}
                        </div>
                        
                        ${documentsSection}
                    </div>
                `;
            } else {
                detailsContainer.innerHTML = '<div class="alert alert-error">Failed to load record details</div>';
            }
        })
        .catch(error => {
            console.error('Error loading vaccination record:', error);
            detailsContainer.innerHTML = '<div class="alert alert-error">Network error. Please try again.</div>';
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