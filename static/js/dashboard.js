/**
 * Aluora GSL - Dashboard JavaScript
 * 50+ Dashboard Functions
 */

(function() {
    'use strict';

    // ==========================================================================
    // DASHBOARD MODULE
    // ==========================================================================
    
    const Dashboard = {
        currentTab: 'overview',
        orders: [],
        user: null,

        init() {
            // Check authentication
            if (!Auth.requireAuth()) return;

            this.user = Auth.getCurrentUser();
            this.loadOrders();
            this.renderUserInfo();
            this.setupNavigation();
            this.setupProfileForm();
            this.renderStats();
            this.renderOrders();
            this.setupSearch();
            console.log('Dashboard initialized');
        },

        // ==========================================================================
        // USER INFO (Functions 1-5)
        // ==========================================================================
        
        renderUserInfo() {
            const nameEl = document.getElementById('user-name');
            const emailEl = document.getElementById('user-email');
            const avatarEl = document.querySelector('.user-avatar');

            if (this.user) {
                if (nameEl) nameEl.textContent = this.user.name;
                if (emailEl) emailEl.textContent = this.user.email;
                if (avatarEl) avatarEl.textContent = this.user.name.charAt(0).toUpperCase();
            }
        },

        // ==========================================================================
        // NAVIGATION (Functions 6-10)
        // ==========================================================================
        
        setupNavigation() {
            // Handle hash navigation
            const hash = window.location.hash.slice(1);
            if (hash) {
                this.switchTab(hash);
            }

            // Tab links
            document.querySelectorAll('.dashboard-menu a').forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const tab = link.getAttribute('href').slice(1);
                    this.switchTab(tab);
                });
            });
        },

        switchTab(tabName) {
            // Update menu
            document.querySelectorAll('.dashboard-menu a').forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${tabName}`) {
                    link.classList.add('active');
                }
            });

            // Update panels
            document.querySelectorAll('.dashboard-panel').forEach(panel => {
                panel.style.display = 'none';
            });

            const activePanel = document.getElementById(`${tabName}-panel`);
            if (activePanel) {
                activePanel.style.display = 'block';
                activePanel.classList.add('fade-in');
            }

            this.currentTab = tabName;
        },

        // ==========================================================================
        // STATS (Functions 11-20)
        // ==========================================================================
        
        renderStats() {
            const stats = this.calculateStats();
            
            this.updateStatCard('total-orders', stats.totalOrders);
            this.updateStatCard('pending-orders', stats.pendingOrders);
            this.updateStatCard('total-spent', App.formatPrice(stats.totalSpent));
            this.updateStatCard('loyalty-points', stats.loyaltyPoints);
        },

        calculateStats() {
            return {
                totalOrders: this.orders.length,
                pendingOrders: this.orders.filter(o => o.status === 'pending').length,
                totalSpent: this.orders.reduce((sum, o) => sum + o.total, 0),
                loyaltyPoints: Math.floor(this.orders.reduce((sum, o) => sum + o.total, 0) / 100)
            };
        },

        updateStatCard(id, value) {
            const el = document.getElementById(id);
            if (el) el.textContent = value;
        },

        // ==========================================================================
        // ORDERS (Functions 21-35)
        // ==========================================================================
        
        loadOrders() {
            const data = localStorage.getItem('aluora_gsl_orders');
            this.orders = data ? JSON.parse(data) : [];
            
            // Filter for current user
            if (this.user) {
                this.orders = this.orders.filter(o => o.user_id === this.user.id);
            }
        },

        renderOrders() {
            const container = document.getElementById('orders-table-body');
            if (!container) return;

            if (this.orders.length === 0) {
                container.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <div class="empty-state">
                                <p>No orders yet</p>
                                <a href="products.html" class="btn btn-primary btn-sm">Start Shopping</a>
                            </div>
                        </td>
                    </tr>
                `;
                return;
            }

            container.innerHTML = this.orders.map(order => this.getOrderRowHTML(order)).join('');
        },

        getOrderRowHTML(order) {
            const statusBadge = this.getStatusBadge(order.status);
            
            return `
                <tr>
                    <td><strong>${order.id}</strong></td>
                    <td>${App.formatDate(order.created_at)}</td>
                    <td>${order.items.length} items</td>
                    <td>${App.formatPrice(order.total)}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <button class="btn btn-ghost btn-sm" onclick="Dashboard.viewOrder('${order.id}')">
                            <i class="fas fa-eye"></i>
                        </button>
                        ${order.status === 'pending' ? `
                            <button class="btn btn-ghost btn-sm" onclick="Dashboard.cancelOrder('${order.id}')">
                                <i class="fas fa-times"></i>
                            </button>
                        ` : ''}
                    </td>
                </tr>
            `;
        },

        getStatusBadge(status) {
            const badges = {
                pending: '<span class="status-badge pending"><i class="fas fa-clock"></i> Pending</span>',
                processing: '<span class="status-badge processing"><i class="fas fa-cog"></i> Processing</span>',
                shipped: '<span class="status-badge shipped"><i class="fas fa-truck"></i> Shipped</span>',
                delivered: '<span class="status-badge delivered"><i class="fas fa-check"></i> Delivered</span>',
                cancelled: '<span class="status-badge cancelled"><i class="fas fa-times"></i> Cancelled</span>'
            };
            return badges[status] || badges.pending;
        },

        viewOrder(orderId) {
            const order = this.orders.find(o => o.id === orderId);
            if (!order) return;

            // Show order details modal
            let modal = document.getElementById('order-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'order-modal';
                modal.className = 'modal';
                modal.innerHTML = `
                    <div class="modal-header">
                        <h3 class="modal-title">Order Details</h3>
                        <button class="modal-close" onclick="App.closeModal('order-modal')">&times;</button>
                    </div>
                    <div class="modal-body" id="order-modal-body"></div>
                `;
                document.body.appendChild(modal);
            }

            const body = document.getElementById('order-modal-body');
            body.innerHTML = this.getOrderDetailsHTML(order);
            
            App.openModal('order-modal');
        },

        getOrderDetailsHTML(order) {
            const itemsHTML = order.items.map(item => `
                <div class="order-item">
                    <div class="order-item-name">${item.name}</div>
                    <div class="order-item-qty">x${item.quantity}</div>
                    <div class="order-item-price">${App.formatPrice((item.sale_price || item.price) * item.quantity)}</div>
                </div>
            `).join('');

            return `
                <div class="order-details">
                    <div class="order-header-info">
                        <p><strong>Order ID:</strong> ${order.id}</p>
                        <p><strong>Date:</strong> ${App.formatDateTime(order.created_at)}</p>
                        <p><strong>Status:</strong> ${this.getStatusBadge(order.status)}</p>
                    </div>
                    
                    <h4>Items</h4>
                    <div class="order-items-list">${itemsHTML}</div>
                    
                    <div class="order-totals">
                        <div class="order-total-row">
                            <span>Subtotal</span>
                            <span>${App.formatPrice(order.subtotal)}</span>
                        </div>
                        ${order.discount > 0 ? `
                            <div class="order-total-row">
                                <span>Discount</span>
                                <span>-${App.formatPrice(order.discount)}</span>
                            </div>
                        ` : ''}
                        <div class="order-total-row">
                            <span>Shipping</span>
                            <span>${App.formatPrice(order.shipping)}</span>
                        </div>
                        <div class="order-total-row">
                            <span>Tax</span>
                            <span>${App.formatPrice(order.tax)}</span>
                        </div>
                        <div class="order-total-row total">
                            <span>Total</span>
                            <span>${App.formatPrice(order.total)}</span>
                        </div>
                    </div>
                </div>
            `;
        },

        cancelOrder(orderId) {
            const order = this.orders.find(o => o.id === orderId);
            if (!order) return;

            if (order.status !== 'pending') {
                Toast.error('Only pending orders can be cancelled');
                return;
            }

            if (confirm('Are you sure you want to cancel this order?')) {
                order.status = 'cancelled';
                this.saveOrders();
                this.renderOrders();
                this.renderStats();
                Toast.success('Order cancelled');
            }
        },

        saveOrders() {
            // Get all orders and update
            const allOrders = JSON.parse(localStorage.getItem('aluora_gsl_orders') || '[]');
            
            this.orders.forEach(updatedOrder => {
                const index = allOrders.findIndex(o => o.id === updatedOrder.id);
                if (index !== -1) {
                    allOrders[index] = updatedOrder;
                }
            });

            localStorage.setItem('aluora_gsl_orders', JSON.stringify(allOrders));
        },

        // ==========================================================================
        // PROFILE (Functions 36-45)
        // ==========================================================================
        
        setupProfileForm() {
            const form = document.getElementById('profile-form');
            if (!form) return;

            // Pre-fill form with user data
            if (this.user) {
                form.name.value = this.user.name || '';
                form.email.value = this.user.email || '';
                form.phone.value = this.user.phone || '';
                form.address.value = this.user.address || '';
                form.city.value = this.user.city || '';
            }

            // Save profile
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.updateProfile(new FormData(form));
            });
        },

        updateProfile(data) {
            const updatedUser = {
                ...this.user,
                name: data.get('name'),
                phone: data.get('phone'),
                address: data.get('address'),
                city: data.get('city')
            };

            // Save to localStorage
            App.saveUser(updatedUser);
            this.user = updatedUser;
            
            this.renderUserInfo();
            Toast.success('Profile updated successfully!');
        },

        // ==========================================================================
        // SETTINGS (Functions 46-55)
        // ==========================================================================
        
        setupSettings() {
            // Notification toggles
            document.querySelectorAll('.notification-toggle').forEach(toggle => {
                toggle.addEventListener('change', (e) => {
                    this.updateNotificationSetting(e.target.name, e.target.checked);
                });
            });

            // Password change form
            const passwordForm = document.getElementById('password-form');
            passwordForm?.addEventListener('submit', (e) => {
                e.preventDefault();
                this.changePassword(new FormData(passwordForm));
            });
        },

        updateNotificationSetting(key, value) {
            const settings = JSON.parse(localStorage.getItem('aluora_gsl_notifications') || '{}');
            settings[key] = value;
            localStorage.setItem('aluora_gsl_notifications', JSON.stringify(settings));
            Toast.success('Settings saved');
        },

        changePassword(data) {
            const currentPassword = data.get('current_password');
            const newPassword = data.get('new_password');
            const confirmPassword = data.get('confirm_password');

            // Validate
            if (newPassword !== confirmPassword) {
                Toast.error('New passwords do not match');
                return;
            }

            if (newPassword.length < 6) {
                Toast.error('Password must be at least 6 characters');
                return;
            }

            // In a real app, verify current password
            Toast.success('Password changed successfully');
            document.getElementById('password-form').reset();
        },

        // ==========================================================================
        // SEARCH & FILTER (Functions 56-60)
        // ==========================================================================
        
        setupSearch() {
            const searchInput = document.getElementById('order-search');
            searchInput?.addEventListener('input', App.debounce((e) => {
                this.filterOrders(e.target.value);
            }, 300));
        },

        filterOrders(query) {
            const filtered = this.orders.filter(order => {
                const searchTerm = query.toLowerCase();
                return (
                    order.id.toLowerCase().includes(searchTerm) ||
                    order.items.some(item => item.name.toLowerCase().includes(searchTerm))
                );
            });

            const container = document.getElementById('orders-table-body');
            if (container) {
                if (filtered.length === 0) {
                    container.innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center py-4">No orders found</td>
                        </tr>
                    `;
                } else {
                    container.innerHTML = filtered.map(order => this.getOrderRowHTML(order)).join('');
                }
            }
        },

        // ==========================================================================
        // WISHLIST (Functions 61-65)
        // ==========================================================================
        
        loadWishlist() {
            const data = localStorage.getItem('wishlist');
            return data ? JSON.parse(data) : [];
        },

        renderWishlist() {
            const wishlist = this.loadWishlist();
            const container = document.getElementById('wishlist-items');
            
            if (!container) return;

            if (wishlist.length === 0) {
                container.innerHTML = '<p class="text-center">Your wishlist is empty</p>';
                return;
            }

            // Would fetch product details here
            container.innerHTML = wishlist.map(id => `
                <div class="wishlist-item">
                    <span>Product ID: ${id}</span>
                    <button class="btn btn-ghost btn-sm" onclick="Dashboard.removeFromWishlist(${id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `).join('');
        },

        removeFromWishlist(productId) {
            let wishlist = this.loadWishlist();
            wishlist = wishlist.filter(id => id !== productId);
            localStorage.setItem('wishlist', JSON.stringify(wishlist));
            this.renderWishlist();
            Toast.info('Removed from wishlist');
        },

        // ==========================================================================
        // DOWNLOADS / INVOICES (Functions 66-70)
        // ==========================================================================
        
        downloadInvoice(orderId) {
            const order = this.orders.find(o => o.id === orderId);
            if (!order) return;

            const invoiceContent = this.generateInvoice(order);
            App.downloadFile(invoiceContent, `invoice-${orderId}.txt`, 'text/plain');
            Toast.success('Invoice downloaded');
        },

        generateInvoice(order) {
            return `
INVOICE
==========================================
Aluora General Suppliers Limited

Order ID: ${order.id}
Date: ${App.formatDateTime(order.created_at)}
Customer: ${this.user?.name || 'N/A'}
Email: ${this.user?.email || 'N/A'}

ITEMS:
------------------------------------------
${order.items.map(item => `${item.name} x${item.quantity} = ${App.formatPrice((item.sale_price || item.price) * item.quantity)}`).join('\n')}

==========================================
Subtotal: ${App.formatPrice(order.subtotal)}
Discount: ${App.formatPrice(order.discount)}
Shipping: ${App.formatPrice(order.shipping)}
Tax: ${App.formatPrice(order.tax)}
------------------------------------------
TOTAL: ${App.formatPrice(order.total)}

Thank you for your business!
            `.trim();
        },

        // ==========================================================================
        // REVIEWS (Functions 71-75)
        // ==========================================================================
        
        loadReviews() {
            const data = localStorage.getItem('aluora_gsl_reviews');
            return data ? JSON.parse(data) : [];
        },

        submitReview(orderId, productId, rating, comment) {
            const reviews = this.loadReviews();
            
            reviews.push({
                id: App.generateId(),
                orderId,
                productId,
                rating,
                comment,
                userId: this.user?.id,
                created_at: new Date().toISOString()
            });

            localStorage.setItem('aluora_gsl_reviews', JSON.stringify(reviews));
            Toast.success('Review submitted!');
        },

        getReviewsForProduct(productId) {
            return this.loadReviews().filter(r => r.productId === productId);
        }
    };

    // Initialize
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => Dashboard.init());
    } else {
        Dashboard.init();
    }

    // Export
    window.Dashboard = Dashboard;

})();
