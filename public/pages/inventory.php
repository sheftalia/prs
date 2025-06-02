<div class="page-header">
    <h1>Inventory Management</h1>
    <p class="page-description">Manage critical item stock levels</p>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Inventory Overview</h2>
        <button class="btn btn-primary" id="update-inventory-btn">Update Stock Levels</button>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Location</th>
                        <th>Quantity</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="inventory-list">
                    <tr>
                        <td colspan="6" class="text-center">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="pagination" id="inventory-pagination"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load inventory data
    fetch('/prs/api/inventory')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const items = data.data.items;
                const tbody = document.getElementById('inventory-list');
                
                if (items.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center">No inventory items found</td></tr>';
                    return;
                }
                
                tbody.innerHTML = '';
                
                items.forEach(item => {
                    const row = document.createElement('tr');
                    
                    row.innerHTML = `
                        <td>${item.item_name}</td>
                        <td>${item.item_category}</td>
                        <td>${item.location_name}</td>
                        <td>${item.quantity_available}</td>
                        <td>${new Date(item.last_updated).toLocaleDateString('en-GB')}</td>
                        <td>
                            <button class="btn btn-primary btn-sm">Update</button>
                            <button class="btn btn-secondary btn-sm">History</button>
                        </td>
                    `;
                    
                    tbody.appendChild(row);
                });
            } else {
                document.getElementById('inventory-list').innerHTML = 
                    '<tr><td colspan="6" class="text-center">Failed to load inventory data</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error loading inventory:', error);
            document.getElementById('inventory-list').innerHTML = 
                '<tr><td colspan="6" class="text-center">Failed to load inventory data</td></tr>';
        });
});
</script>