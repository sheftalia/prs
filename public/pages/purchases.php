<?php
// Only accessible to public users
if ($_SESSION['user']['role_id'] !== 4) {
    echo '<div class="alert alert-error">Access denied. This page is only available to public users.</div>';
    exit;
}
?>

<div class="page-header">
    <h1>My Purchase History</h1>
    <p class="page-description">View your critical item purchase transactions</p>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Purchase History</h2>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Location</th>
                        <th>Business</th>
                    </tr>
                </thead>
                <tbody id="purchases-list">
                    <tr>
                        <td colspan="5" class="text-center">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="pagination" id="purchases-pagination"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load purchase data for the current user
    loadPurchaseHistory();
    
    // Setup pagination
    setupPagination();
});

// Load purchase history
function loadPurchaseHistory(page = 1) {
    fetch(`/prs/api/purchases?action=history&limit=1000`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const purchases = data.data.purchases;
                const tbody = document.getElementById('purchases-list');
                
                if (purchases.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center">No purchase history found</td></tr>';
                    updatePagination(1, 1);
                    return;
                }
                
                tbody.innerHTML = '';
                
                purchases.forEach(purchase => {
                    const row = document.createElement('tr');
                    
                    row.innerHTML = `
                        <td>${new Date(purchase.purchase_date).toLocaleDateString('en-GB')}</td>
                        <td>${purchase.item_name}</td>
                        <td>${purchase.quantity}</td>
                        <td>${purchase.location_name}</td>
                        <td>${purchase.business_name}</td>
                    `;
                    
                    tbody.appendChild(row);
                });
                
                // Update pagination if provided
                if (data.data.pagination) {
                    updatePagination(page, data.data.pagination.pages);
                }
            } else {
                document.getElementById('purchases-list').innerHTML = 
                    '<tr><td colspan="5" class="text-center">Failed to load purchase history</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error loading purchases:', error);
            document.getElementById('purchases-list').innerHTML = 
                '<tr><td colspan="5" class="text-center">Failed to load purchase history</td></tr>';
        });
}

// Setup pagination
function setupPagination() {
    const pagination = document.getElementById('purchases-pagination');
    
    pagination.addEventListener('click', function(e) {
        if (e.target.tagName === 'A') {
            e.preventDefault();
            const page = parseInt(e.target.getAttribute('data-page'));
            loadPurchaseHistory(page);
        }
    });
}

// Update pagination links
function updatePagination(currentPage, totalPages) {
    const pagination = document.getElementById('purchases-pagination');
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
</script>