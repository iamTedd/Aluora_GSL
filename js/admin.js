/**
 * Aluora GSL - Admin JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    initModals();
    initForms();
});

// Modals
function initModals() {
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-close') || e.target.closest('.modal-close')) {
            const modal = e.target.closest('.modal');
            closeModal(modal.id);
        }
        
        if (e.target.classList.contains('modal')) {
            closeModal(e.target.id);
        }
    });
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal.active').forEach(modal => {
                closeModal(modal.id);
            });
        }
    });
}

function openModal(modalId) {
    document.getElementById(modalId).classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Reset Product Form for new product
function resetProductForm() {
    const form = document.getElementById('productForm');
    if (form) {
        form.reset();
        // Reset modal title
        const modal = document.getElementById('productModal');
        if (modal) {
            modal.querySelector('h2').textContent = 'Add New Product';
        }
        // Remove hidden product_id if exists
        const hiddenId = form.querySelector('input[name="product_id"]');
        if (hiddenId) {
            hiddenId.remove();
        }
        // Reset form submission handler
        form.setAttribute('onsubmit', 'saveProduct(event)');
        form.querySelector('button[type="submit"]').textContent = 'Save Product';
    }
}

// Show notification
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existing = document.querySelector('.admin-notification');
    if (existing) existing.remove();
    
    const notification = document.createElement('div');
    notification.className = `admin-notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        <span>${message}</span>
    `;
    
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#27ae60' : type === 'error' ? '#e74c3c' : '#3498db'};
        color: white;
        padding: 15px 25px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 10px;
        z-index: 10000;
        animation: slideIn 0.3s ease;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Forms
function initForms() {
    // Forms are handled inline
}

// Save Product
function saveProduct(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    formData.append('action', 'add_product');
    
    fetch('index.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            closeModal('productModal');
            location.reload();
        } else {
            showNotification(data.message || 'Failed to save product', 'error');
        }
    })
    .catch(err => {
        console.error('Error saving product:', err);
        showNotification('Failed to save product. Please try again.', 'error');
    });
}

// Edit Product - Open modal with product data
function editProduct(id) {
    // Fetch product data
    const formData = new FormData();
    formData.append('action', 'get_product');
    formData.append('id', id);
    
    fetch('index.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.product) {
            const product = data.product;
            document.getElementById('productModal').querySelector('h2').textContent = 'Edit Product';
            document.querySelector('#productForm input[name="name"]').value = product.name || '';
            document.querySelector('#productForm select[name="category_id"]').value = product.category_id || '';
            document.querySelector('#productForm input[name="price"]').value = product.price || '';
            document.querySelector('#productForm input[name="cost_price"]').value = product.cost_price || '';
            document.querySelector('#productForm input[name="stock_quantity"]').value = product.stock_quantity || '';
            document.querySelector('#productForm input[name="sku"]').value = product.sku || '';
            document.querySelector('#productForm select[name="status"]').value = product.status || 'active';
            document.querySelector('#productForm input[name="short_description"]').value = product.short_description || '';
            document.querySelector('#productForm textarea[name="description"]').value = product.description || '';
            
            // Add hidden input for product ID
            let hiddenId = document.querySelector('#productForm input[name="product_id"]');
            if (!hiddenId) {
                hiddenId = document.createElement('input');
                hiddenId.type = 'hidden';
                hiddenId.name = 'product_id';
                document.querySelector('#productForm').appendChild(hiddenId);
            }
            hiddenId.value = id;
            
            // Change form action to update
            document.querySelector('#productForm').setAttribute('onsubmit', 'updateProduct(event)');
            document.querySelector('#productForm button[type="submit"]').textContent = 'Update Product';
            
            openModal('productModal');
        } else {
            showNotification('Product not found', 'error');
        }
    })
    .catch(() => {
        // Demo mode - show form with placeholder
        showNotification('Edit Mode (Demo)', 'info');
        document.getElementById('productModal').querySelector('h2').textContent = 'Edit Product';
        openModal('productModal');
    });
}

// Update Product
function updateProduct(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    formData.append('action', 'update_product');
    formData.append('id', formData.get('product_id'));
    
    fetch('index.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            closeModal('productModal');
            location.reload();
        } else {
            showNotification(data.message || 'Failed to update product', 'error');
        }
    })
    .catch(err => {
        console.error('Error updating product:', err);
        showNotification('Failed to update product. Please try again.', 'error');
    });
}

// Delete Product
function deleteProduct(id) {
    if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
        const formData = new FormData();
        formData.append('action', 'delete_product');
        formData.append('id', id);
        
        fetch('index.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                location.reload();
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(err => {
            console.error('Error deleting product:', err);
            showNotification('Failed to delete product. Please try again.', 'error');
        });
    }
}

// Save Category
function saveCategory(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    formData.append('action', 'add_category');
    
    fetch('index.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            closeModal('categoryModal');
            location.reload();
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(err => {
        console.error('Error saving category:', err);
        showNotification('Failed to save category. Please try again.', 'error');
    });
}

// Edit Category
function editCategory(id) {
    const name = prompt('Enter new category name:');
    if (name) {
        const formData = new FormData();
        formData.append('action', 'update_category');
        formData.append('id', id);
        formData.append('name', name);
        formData.append('description', '');
        formData.append('icon', 'fa-folder');
        formData.append('status', 'active');
        
        fetch('index.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showNotification('Category updated!', 'success');
                location.reload();
            }
        })
        .catch(err => {
            console.error('Error updating category:', err);
            showNotification('Failed to update category. Please try again.', 'error');
        });
    }
}

// Delete Category
function deleteCategory(id) {
    if (confirm('Are you sure you want to delete this category? Products in this category will be uncategorized.')) {
        const formData = new FormData();
        formData.append('action', 'delete_category');
        formData.append('id', id);
        
        fetch('index.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                location.reload();
            }
        })
        .catch(err => {
            console.error('Error deleting category:', err);
            showNotification('Failed to delete category. Please try again.', 'error');
        });
    }
}

// View Order Details
function viewOrder(id) {
    const formData = new FormData();
    formData.append('action', 'get_order');
    formData.append('id', id);
    
    fetch('index.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.order) {
            const order = data.order;
            const details = `
                <div class="order-details">
                    <p><strong>Order #:</strong> ${order.order_number}</p>
                    <p><strong>Customer:</strong> ${order.first_name} ${order.last_name}</p>
                    <p><strong>Email:</strong> ${order.email}</p>
                    <p><strong>Total:</strong> KES ${parseFloat(order.total).toLocaleString()}</p>
                    <p><strong>Status:</strong> <span class="status-badge ${order.status}">${order.status}</span></p>
                    <p><strong>Payment:</strong> ${order.payment_status}</p>
                    <p><strong>Date:</strong> ${new Date(order.created_at).toLocaleDateString()}</p>
                    <p><strong>Shipping:</strong> ${order.shipping_address || 'N/A'}</p>
                </div>
            `;
            document.getElementById('orderDetailsContent').innerHTML = details;
            openModal('orderDetailsModal');
        } else {
            showNotification('Order not found', 'error');
            document.getElementById('orderDetailsContent').innerHTML = '<p>Order not found.</p>';
            openModal('orderDetailsModal');
        }
    })
    .catch(err => {
        console.error('Error loading order:', err);
        showNotification('Failed to load order details. Please try again.', 'error');
    });
}

// Update Order Status
function updateOrderStatus(id) {
    const statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
    let html = '<div class="status-select-modal"><p>Select new status:</p><div class="status-options">';
    statuses.forEach(status => {
        html += `<button class="btn btn-outline" onclick="confirmOrderStatus(${id}, '${status}')">${status.charAt(0).toUpperCase() + status.slice(1)}</button>`;
    });
    html += '</div></div>';
    
    document.getElementById('orderDetailsContent').innerHTML = html;
    openModal('orderDetailsModal');
}

function confirmOrderStatus(id, status) {
    const formData = new FormData();
    formData.append('action', 'update_order_status');
    formData.append('id', id);
    formData.append('status', status);
    
    fetch('index.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showNotification('Order status updated!', 'success');
            location.reload();
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(err => {
        console.error('Error updating order status:', err);
        showNotification('Failed to update order status. Please try again.', 'error');
    });
}

// Delete Order
function deleteOrder(id) {
    if (confirm('Are you sure you want to delete this order?')) {
        const formData = new FormData();
        formData.append('action', 'delete_order');
        formData.append('id', id);
        
        fetch('index.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showNotification('Order deleted!', 'success');
                location.reload();
            } else {
                showNotification(data.message || 'Failed to delete order', 'error');
            }
        })
        .catch(err => {
            console.error('Error deleting order:', err);
            showNotification('Failed to delete order. Please try again.', 'error');
        });
    }
}

// View Tender Details
function viewTender(id) {
    const formData = new FormData();
    formData.append('action', 'get_tender');
    formData.append('id', id);
    
    fetch('index.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.tender) {
            const tender = data.tender;
            const details = `
                <div class="tender-details">
                    <p><strong>Tender #:</strong> ${tender.tender_number}</p>
                    <p><strong>Title:</strong> ${tender.title}</p>
                    <p><strong>Customer:</strong> ${tender.first_name} ${tender.last_name}</p>
                    <p><strong>Email:</strong> ${tender.email}</p>
                    <p><strong>Category:</strong> ${tender.category || 'N/A'}</p>
                    <p><strong>Quantity:</strong> ${tender.quantity || 'N/A'}</p>
                    <p><strong>Budget:</strong> ${tender.budget || 'N/A'}</p>
                    <p><strong>Status:</strong> <span class="status-badge ${tender.status}">${tender.status}</span></p>
                    <p><strong>Description:</strong> ${tender.description}</p>
                    <p><strong>Admin Notes:</strong> ${tender.admin_notes || 'N/A'}</p>
                    <p><strong>Quoted Price:</strong> KES ${tender.quoted_price ? parseFloat(tender.quoted_price).toLocaleString() : 'N/A'}</p>
                    <p><strong>Date:</strong> ${new Date(tender.created_at).toLocaleDateString()}</p>
                </div>
            `;
            document.getElementById('tenderDetailsContent').innerHTML = details;
            openModal('tenderDetailsModal');
        } else {
            showNotification('Tender not found', 'error');
            document.getElementById('tenderDetailsContent').innerHTML = '<p>Tender not found.</p>';
            openModal('tenderDetailsModal');
        }
    })
    .catch(err => {
        console.error('Error loading tender:', err);
        showNotification('Failed to load tender details. Please try again.', 'error');
    });
}

// Update Tender Status
function updateTenderStatus(id) {
    const statuses = ['pending', 'reviewing', 'quoted', 'accepted', 'rejected'];
    let html = '<div class="status-select-modal"><p>Select new status:</p><div class="status-options">';
    statuses.forEach(status => {
        html += `<button class="btn btn-outline" onclick="confirmTenderStatus(${id}, '${status}')">${status.charAt(0).toUpperCase() + status.slice(1)}</button>`;
    });
    html += '</div></div>';
    
    // Also ask for quoted price
    html += `<div style="margin-top:15px"><label>Quoted Price (KES):</label><input type="number" id="quotedPrice" class="form-control" placeholder="Enter quoted price"></div>`;
    html += `<div style="margin-top:15px"><label>Admin Notes:</label><textarea id="adminNotes" class="form-control" rows="3" placeholder="Enter notes"></textarea></div>`;
    html += `<button class="btn btn-primary" style="margin-top:15px" onclick="submitTenderUpdate(${id})">Update Tender</button>`;
    
    document.getElementById('tenderDetailsContent').innerHTML = html;
    openModal('tenderDetailsModal');
}

function confirmTenderStatus(id, status) {
    submitTenderUpdate(id, status);
}

function submitTenderUpdate(id, status = null) {
    const statusEl = document.querySelector('.status-options .btn-primary') ? 
        document.querySelector('.status-options .btn-primary').textContent.toLowerCase() : 'pending';
    const quotedPrice = document.getElementById('quotedPrice')?.value || 0;
    const adminNotes = document.getElementById('adminNotes')?.value || '';
    const finalStatus = status || statusEl;
    
    const formData = new FormData();
    formData.append('action', 'update_tender');
    formData.append('id', id);
    formData.append('status', finalStatus);
    formData.append('quoted_price', quotedPrice);
    formData.append('admin_notes', adminNotes);
    
    fetch('index.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showNotification('Tender updated!', 'success');
            location.reload();
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(err => {
        console.error('Error updating tender:', err);
        showNotification('Failed to update tender. Please try again.', 'error');
    });
}

// Delete Tender
function deleteTender(id) {
    if (confirm('Are you sure you want to delete this tender?')) {
        const formData = new FormData();
        formData.append('action', 'delete_tender');
        formData.append('id', id);
        
        fetch('index.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showNotification('Tender deleted!', 'success');
                location.reload();
            } else {
                showNotification(data.message || 'Failed to delete tender', 'error');
            }
        })
        .catch(err => {
            console.error('Error deleting tender:', err);
            showNotification('Failed to delete tender. Please try again.', 'error');
        });
    }
}

// View User Details
function viewUser(id) {
    const formData = new FormData();
    formData.append('action', 'get_user');
    formData.append('id', id);
    
    fetch('index.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.user) {
            const user = data.user;
            const details = `
                <div class="user-details">
                    <p><strong>Name:</strong> ${user.first_name} ${user.last_name}</p>
                    <p><strong>Email:</strong> ${user.email}</p>
                    <p><strong>Phone:</strong> ${user.phone || 'N/A'}</p>
                    <p><strong>Company:</strong> ${user.company || 'N/A'}</p>
                    <p><strong>Role:</strong> <span class="status-badge ${user.role}">${user.role}</span></p>
                    <p><strong>Status:</strong> <span class="status-badge ${user.status}">${user.status}</span></p>
                    <p><strong>Joined:</strong> ${new Date(user.created_at).toLocaleDateString()}</p>
                    <p><strong>Last Login:</strong> ${user.last_login ? new Date(user.last_login).toLocaleDateString() : 'Never'}</p>
                </div>
            `;
            document.getElementById('userDetailsContent').innerHTML = details;
            openModal('userDetailsModal');
        } else {
            showNotification('User not found', 'error');
            document.getElementById('userDetailsContent').innerHTML = '<p>User not found.</p>';
            openModal('userDetailsModal');
        }
    })
    .catch(err => {
        console.error('Error loading user:', err);
        showNotification('Failed to load user details. Please try again.', 'error');
    });
}

// Update User
function updateUser(id, field, value) {
    const formData = new FormData();
    formData.append('action', 'update_user');
    formData.append('id', id);
    formData.append(field, value);
    
    fetch('index.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showNotification('User updated!', 'success');
            location.reload();
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(err => {
        console.error('Error updating user:', err);
        showNotification('Failed to update user. Please try again.', 'error');
    });
}

// Toggle User Status
function toggleUserStatus(id, currentStatus) {
    const newStatus = currentStatus === 'active' ? 'suspended' : 'active';
    updateUser(id, 'status', newStatus);
}

// Change User Role
function changeUserRole(id) {
    const roles = ['customer', 'staff', 'admin', 'manager', 'vendor', 'accountant', 'delivery_person'];
    const roleLabels = {
        'customer': 'Customer',
        'staff': 'Staff',
        'admin': 'Admin',
        'manager': 'Manager',
        'vendor': 'Vendor',
        'accountant': 'Accountant',
        'delivery_person': 'Delivery Person'
    };
    let html = '<div class="status-select-modal"><p>Select new role:</p><div class="status-options">';
    roles.forEach(role => {
        html += `<button class="btn btn-outline" onclick="updateUserRole(${id}, '${role}')">${roleLabels[role]}</button>`;
    });
    html += '</div></div>';
    
    document.getElementById('userDetailsContent').innerHTML = html;
    openModal('userDetailsModal');
}

function updateUserRole(id, role) {
    updateUser(id, 'role', role);
}

// Delete User
function deleteUser(id) {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        const formData = new FormData();
        formData.append('action', 'delete_user');
        formData.append('id', id);
        
        fetch('index.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showNotification('User deleted!', 'success');
                location.reload();
            } else {
                showNotification(data.message || 'Failed to delete user', 'error');
            }
        })
        .catch(err => {
            console.error('Error deleting user:', err);
            showNotification('Failed to delete user. Please try again.', 'error');
        });
    }
}

// Export functions to window
window.openModal = openModal;
window.closeModal = closeModal;
window.showNotification = showNotification;
window.resetProductForm = resetProductForm;
window.saveProduct = saveProduct;
window.updateProduct = updateProduct;
window.editProduct = editProduct;
window.deleteProduct = deleteProduct;
window.saveCategory = saveCategory;
window.editCategory = editCategory;
window.deleteCategory = deleteCategory;
window.viewOrder = viewOrder;
window.updateOrderStatus = updateOrderStatus;
window.confirmOrderStatus = confirmOrderStatus;
window.deleteOrder = deleteOrder;
window.viewTender = viewTender;
window.updateTenderStatus = updateTenderStatus;
window.confirmTenderStatus = confirmTenderStatus;
window.submitTenderUpdate = submitTenderUpdate;
window.deleteTender = deleteTender;
window.viewUser = viewUser;
window.updateUser = updateUser;
window.toggleUserStatus = toggleUserStatus;
window.changeUserRole = changeUserRole;
window.updateUserRole = updateUserRole;
window.deleteUser = deleteUser;
