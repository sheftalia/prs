<div class="page-header">
    <h1>Purchase Transactions</h1>
    <p class="page-description">Manage critical item purchases</p>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Purchase History</h2>
        <button class="btn btn-primary" id="record-purchase-btn">Record Purchase</button>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>User</th>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Location</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="purchases-list">
                    <tr>
                        <td colspan="6" class="text-center">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="pagination" id="purchases-pagination"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load purchase data
    fetch('/prs/api/purchases?action=history')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const purchases = data.data.purchases;
                const tbody = document.getElementById('purchases-list');
                
                if (purchases.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center">No purchase history found</td></tr>';
                    return;
                }
                
                tbody.innerHTML = '';
                
                purchases.forEach(purchase => {
                    const row = document.createElement('tr');
                    
                    row.innerHTML = `
                        <td>${new Date(purchase.purchase_date).toLocaleDateString('en-GB')}</td>
                        <td>${purchase.user_name || 'N/A'}</td>
                        <td>${purchase.item_name}</td>
                        <td>${purchase.quantity}</td>
                        <td>${purchase.location_name}</td>
                        <td>
                            <button class="btn btn-primary btn-sm">View</button>
                        </td>
                    `;
                    
                    tbody.appendChild(row);
                });
            } else {
                document.getElementById('purchases-list').innerHTML = 
                    '<tr><td colspan="6" class="text-center">Failed to load purchase history</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error loading purchases:', error);
            document.getElementById('purchases-list').innerHTML = 
                '<tr><td colspan="6" class="text-center">Failed to load purchase history</td></tr>';
        });
});
</script>