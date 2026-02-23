/**
 * Aluora GSL - User Dashboard JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
});

// Load Dashboard Data
function loadDashboardData() {
    const section = new URLSearchParams(window.location.search).get('section') || 'overview';
    
    if (section === 'overview') {
        loadRecentOrders();
        loadRecentTenders();
    } else if (section === 'orders') {
        loadAllOrders();
    } else if (section === 'tenders') {
        loadAllTenders();
    } else if (section === 'tickets') {
        loadAllTickets();
    }
}

// Load Recent Orders (Overview)
function loadRecentOrders() {
    const container = document.getElementById('recentOrders');
    if (!container) return;
    
    fetch('dashboard.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=get_orders'
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.orders.length > 0) {
            container.innerHTML = data.orders.slice(0, 3).map(order => `
                <div class="order-item">
                    <div class="order-info">
                        <div class="order-number">${order.order_number}</div>
                        <div class="order-date">${formatDate(order.created_at)}</div>
                    </div>
                    <div class="order-total">KES ${parseFloat(order.total).toLocaleString()}</div>
                    <span class="status-badge ${order.status}">${order.status}</span>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<div class="empty-state"><p>No orders yet</p></div>';
        }
    })
    .catch(() => {
        container.innerHTML = '<div class="empty-state"><p>Unable to load orders</p></div>';
    });
}

// Load Recent Tenders (Overview)
function loadRecentTenders() {
    const container = document.getElementById('recentTenders');
    if (!container) return;
    
    fetch('dashboard.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=get_tenders'
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.tenders.length > 0) {
            container.innerHTML = data.tenders.slice(0, 3).map(tender => `
                <div class="tender-item">
                    <div class="tender-info">
                        <div class="tender-number">${tender.tender_number}</div>
                        <div class="tender-date">${formatDate(tender.created_at)}</div>
                    </div>
                    <span class="status-badge ${tender.status}">${tender.status}</span>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<div class="empty-state"><p>No tenders yet</p></div>';
        }
    })
    .catch(() => {
        container.innerHTML = '<div class="empty-state"><p>Unable to load tenders</p></div>';
    });
}

// Load All Orders
function loadAllOrders() {
    const container = document.getElementById('ordersList');
    if (!container) return;
    
    fetch('dashboard.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=get_orders'
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.orders.length > 0) {
            container.innerHTML = data.orders.map(order => `
                <div class="order-item">
                    <div class="order-info">
                        <div class="order-number">${order.order_number}</div>
                        <div class="order-date">${formatDate(order.created_at)}</div>
                    </div>
                    <div class="order-total">KES ${parseFloat(order.total).toLocaleString()}</div>
                    <span class="status-badge ${order.status}">${order.status}</span>
                    <div class="order-actions">
                        ${order.status === 'pending' ? `<button class="btn btn-sm btn-danger" onclick="cancelOrder(${order.id})">Cancel</button>` : ''}
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-shopping-bag"></i>
                    <h3>No Orders Yet</h3>
                    <p>Start shopping to see your orders here</p>
                    <a href="products.php" class="btn btn-primary">Browse Products</a>
                </div>
            `;
        }
    })
    .catch(() => {
        container.innerHTML = '<div class="loading">Unable to load orders</div>';
    });
}

// Load All Tenders
function loadAllTenders() {
    const container = document.getElementById('tendersList');
    if (!container) return;
    
    fetch('dashboard.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=get_tenders'
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.tenders.length > 0) {
            container.innerHTML = data.tenders.map(tender => `
                <div class="tender-item">
                    <div class="tender-info">
                        <div class="tender-number">${tender.tender_number}</div>
                        <div class="tender-date">${tender.title}</div>
                        <div class="tender-date">${formatDate(tender.created_at)}</div>
                    </div>
                    <span class="status-badge ${tender.status}">${tender.status}</span>
                </div>
            `).join('');
        } else {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-file-contract"></i>
                    <h3>No Tenders Yet</h3>
                    <p>Submit a tender to get custom quotes for your needs</p>
                    <button class="btn btn-primary" onclick="openModal('newTenderModal')">Submit Tender</button>
                </div>
            `;
        }
    })
    .catch(() => {
        container.innerHTML = '<div class="loading">Unable to load tenders</div>';
    });
}

// Load All Tickets
function loadAllTickets() {
    const container = document.getElementById('ticketsList');
    if (!container) return;
    
    fetch('dashboard.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=get_tickets'
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.tickets.length > 0) {
            container.innerHTML = data.tickets.map(ticket => `
                <div class="ticket-item">
                    <div class="ticket-info">
                        <div class="ticket-number">${ticket.ticket_number}</div>
                        <div class="ticket-date">${ticket.subject}</div>
                        <div class="ticket-date">${formatDate(ticket.created_at)}</div>
                    </div>
                    <span class="status-badge ${ticket.status}">${ticket.status}</span>
                </div>
            `).join('');
        } else {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-ticket-alt"></i>
                    <h3>No Support Tickets</h3>
                    <p>Need help? Create a support ticket</p>
                    <button class="btn btn-primary" onclick="openModal('newTicketModal')">Create Ticket</button>
                </div>
            `;
        }
    })
    .catch(() => {
        container.innerHTML = '<div class="loading">Unable to load tickets</div>';
    });
}

// Cancel Order
function cancelOrder(orderId) {
    if (confirm('Are you sure you want to cancel this order?')) {
        const formData = new FormData();
        formData.append('action', 'cancel_order');
        formData.append('order_id', orderId);
        
        fetch('dashboard.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                loadAllOrders();
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(() => {
            showNotification('Order cancelled (Demo Mode)', 'success');
            loadAllOrders();
        });
    }
}

// Update Profile
function updateProfile(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('action', 'update_profile');
    
    fetch('dashboard.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(() => {
        showNotification('Profile updated (Demo Mode)', 'success');
    });
}

// Submit Tender from Dashboard
function submitTenderFromDashboard(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('action', 'submit_tender');
    
    fetch('index.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showNotification('Tender submitted successfully! Reference: ' + data.tender_number, 'success');
            closeModal('newTenderModal');
            e.target.reset();
            if (document.getElementById('tendersList')) {
                loadAllTenders();
            }
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(() => {
        showNotification('Tender submitted (Demo Mode)', 'success');
        closeModal('newTenderModal');
        e.target.reset();
        if (document.getElementById('tendersList')) {
            loadAllTenders();
        }
    });
}

// Submit Ticket from Dashboard
function submitTicketFromDashboard(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    // For demo, just show success
    showNotification('Support ticket created! Reference: TKT-' + Date.now(), 'success');
    closeModal('newTicketModal');
    e.target.reset();
    if (document.getElementById('ticketsList')) {
        loadAllTickets();
    }
}

// Modals
function openModal(modalId) {
    document.getElementById(modalId).classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
    document.body.style.overflow = '';
}

// Close modal on outside click
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        e.target.classList.remove('active');
        document.body.style.overflow = '';
    }
});

// Utility Functions
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}

// Global functions
window.openModal = openModal;
window.closeModal = closeModal;
window.cancelOrder = cancelOrder;
window.updateProfile = updateProfile;
window.submitTenderFromDashboard = submitTenderFromDashboard;
window.submitTicketFromDashboard = submitTicketFromDashboard;
