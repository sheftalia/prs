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
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load purchase data for the current user
    loadPurchaseHistory();
});

// Load purchase history (removed pagination)
function loadPurchaseHistory() {
    fetch(`/prs/api/purchases?action=history&limit=1000`) // Load all records
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const purchases = data.data.purchases;
                const tbody = document.getElementById('purchases-list');
                
                if (purchases.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center">No purchase history found</td></tr>';
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
</script>