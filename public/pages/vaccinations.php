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
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19.82 2H4.18A2.18 2.18 0 0 0 2 4.18v15.64A2.18 2.18 0 0 0 4.18 22h15.64A2.18 2.18 0 0 0 22 19.82V4.18A2.18 2.18 0 0 0 19.82 2Z"></path>
                        <path d="M7 2v20"></path>
                        <path d="M17 2v20"></path>
                        <path d="M2 12h20"></path>
                        <path d="M2 7h5"></path>
                        <path d="M2 17h5"></path>
                        <path d="M17 17h5"></path>
                        <path d="M17 7h5"></path>
                    </svg>
                </div>
                <div class="stat-value" id="total-vaccinations">--</div>
                <div class="stat-label">Total Records</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <div class="stat-value" id="verified-vaccinations">--</div>
                <div class="stat-label">Verified Records</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <div class="stat-value" id="vaccinated-users">--</div>
                <div class="stat-label">Vaccinated Users</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M12 8v4"></path>
                        <path d="M12 16h.01"></path>
                    </svg>
                </div>
                <div class="stat-value" id="pending-verification">--</div>
                <div class="stat-label">Pending Verification</div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="chart-container">
                    <canvas id="vaccination-trend-chart"></canvas>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="chart-container">
                    <canvas id="vaccine-distribution-chart"></canvas>
                </div>
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
        
        <div class="pagination" id="verification-pagination"></div>
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
        
        <div class="pagination" id="records-pagination"></div>
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
        setupPagination();
    } else {
        loadVaccinationRecords();
        setupRecordsPagination();
        
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
                // Update statistics
                document.getElementById('total-vaccinations').textContent = data.data.stats.total_records;
                document.getElementById('verified-vaccinations').textContent = data.data.stats.verified_records;
                document.getElementById('vaccinated-users').textContent = data.data.stats.vaccinated_users;
                document.getElementById('pending-verification').textContent = 
                    data.data.stats.total_records - data.data.stats.verified_records;
                
                // Initialize charts
                setupVaccinationTrendChart(data.data.vaccination_trend);
                setupVaccineDistributionChart(data.data.vaccine_distribution);
            }
        })
        .catch(error => {
            console.error('Error loading vaccination statistics:', error);
        });
}

// Setup vaccination trend chart
function setupVaccinationTrendChart(trendData) {
    const ctx = document.getElementById('vaccination-trend-chart').getContext('2d');
    
    // Prepare data
    const labels = trendData.map(item => item.month);
    const data = trendData.map(item => item.count);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Vaccinations',
                data: data,
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
                title: {
                    display: true,
                    text: 'Vaccination Trend by Month'
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

// Setup vaccine distribution chart
function setupVaccineDistributionChart(distributionData) {
    const ctx = document.getElementById('vaccine-distribution-chart').getContext('2d');
    
    // Prepare data
    const labels = distributionData.map(item => item.vaccine_name);
    const data = distributionData.map(item => item.count);
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
    
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: backgroundColors.slice(0, labels.length),
                borderWidth: 1,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Vaccine Distribution'
                }
            }
        }
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
                
                // Update pagination
                updatePagination(page, data.data.total_pages);
            }
        })
        .catch(error => {
            console.error('Error loading verification queue:', error);
            document.getElementById('verification-queue').innerHTML = 
                '<tr><td colspan="6" class="text-center">Failed to load data</td></tr>';
        });
}

// Setup pagination
function setupPagination() {
    const pagination = document.getElementById('verification-pagination');
    
    pagination.addEventListener('click', function(e) {
        if (e.target.tagName === 'A') {
            e.preventDefault();
            const page = parseInt(e.target.getAttribute('data-page'));
            loadVerificationQueue(page);
        }
    });
}

// Update pagination links
function updatePagination(currentPage, totalPages) {
    const pagination = document.getElementById('verification-pagination');
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
                
                // Update pagination
                updateRecordsPagination(page, data.data.total_pages);
            }
        })
        .catch(error => {
            console.error('Error loading vaccination records:', error);
            document.getElementById('vaccination-records').innerHTML = 
                '<tr><td colspan="6" class="text-center">Failed to load data</td></tr>';
        });
}

// Setup records pagination
function setupRecordsPagination() {
    const pagination = document.getElementById('records-pagination');
    
    pagination.addEventListener('click', function(e) {
        if (e.target.tagName === 'A') {
            e.preventDefault();
            const page = parseInt(e.target.getAttribute('data-page'));
            loadVaccinationRecords(page);
        }
    });
}

// Update records pagination links
function updateRecordsPagination(currentPage, totalPages) {
    const pagination = document.getElementById('records-pagination');
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