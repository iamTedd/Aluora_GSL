/**
 * Aluora GSL - Cart JavaScript
 * Enhanced with Payment Modal & Transaction References
 */

(function() {
    'use strict';

    const CartPage = {
        cart: [],
        promoCode: null,
        discount: 0,

        init() {
            this.loadCart();
            this.renderCart();
            this.setupEventListeners();
            this.calculateTotals();
            console.log('Cart initialized');
        },

        loadCart() {
            try {
                const data = localStorage.getItem('aluora_gsl_cart');
                this.cart = data ? JSON.parse(data) : [];
            } catch (error) {
                console.error('Load cart error:', error);
                this.cart = [];
                Toast.error('Failed to load cart');
            }
        },

        saveCart() {
            try {
                localStorage.setItem('aluora_gsl_cart', JSON.stringify(this.cart));
                App.updateCartCount();
                this.calculateTotals();
            } catch (error) {
                console.error('Save cart error:', error);
            }
        },

        renderCart() {
            const container = document.getElementById('cart-items');
            if (!container) return;

            if (this.cart.length === 0) {
                container.innerHTML = this.getEmptyCartHTML();
                this.hideSummary();
                return;
            }

            this.showSummary();
            container.innerHTML = this.cart.map((item, index) => this.getCartItemHTML(item, index)).join('');
        },

        getEmptyCartHTML() {
            return `
                <div class="empty-cart">
                    <div class="empty-cart-icon">🛒</div>
                    <h3>Your cart is empty</h3>
                    <p>Looks like you haven't added anything to your cart yet.</p>
                    <a href="products.html" class="btn btn-primary">Start Shopping</a>
                </div>
            `;
        },

        showSummary() {
            const summary = document.querySelector('.cart-summary');
            if (summary) summary.style.display = 'block';
        },

        hideSummary() {
            const summary = document.querySelector('.cart-summary');
            if (summary) summary.style.display = 'none';
        },

        getCartItemHTML(item, index) {
            const price = item.sale_price || item.price;
            const total = price * item.quantity;
            const image = item.image || 'https://via.placeholder.com/100x100?text=Product';
            
            return `
                <div class="cart-item" data-id="${item.id}">
                    <div class="cart-item-image">
                        <img src="${image}" alt="${item.name}" onerror="this.src='https://via.placeholder.com/100x100?text=Product'">
                    </div>
                    <div class="cart-item-details">
                        <h4>${item.name}</h4>
                        <span class="sku">SKU: ${item.sku || 'N/A'}</span>
                        <span class="price-each">${App.formatPrice(price)} each</span>
                    </div>
                    <div class="quantity-control">
                        <button onclick="CartPage.updateQuantity(${item.id}, ${item.quantity - 1})" ${item.quantity <= 1 ? 'disabled' : ''}>
                            <i class="fas fa-minus"></i>
                        </button>
                        <span>${item.quantity}</span>
                        <button onclick="CartPage.updateQuantity(${item.id}, ${item.quantity + 1})">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <div class="cart-item-total">${App.formatPrice(total)}</div>
                    <button class="remove-item" onclick="CartPage.removeItem(${item.id})" title="Remove item">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
        },

        updateQuantity(productId, newQuantity) {
            if (newQuantity < 1) {
                return this.removeItem(productId);
            }
            
            const item = this.cart.find(item => item.id === productId);
            if (item) {
                item.quantity = Math.min(newQuantity, 99);
                this.saveCart();
                this.renderCart();
            }
        },

        removeItem(productId) {
            const item = this.cart.find(i => i.id === productId);
            const itemName = item?.name || 'Item';
            
            this.cart = this.cart.filter(item => item.id !== productId);
            this.saveCart();
            this.renderCart();
            Toast.info(`${itemName} removed from cart`);
        },

        clearCart() {
            this.cart = [];
            this.promoCode = null;
            this.discount = 0;
            this.saveCart();
            this.renderCart();
            Toast.info('Cart cleared');
        },

        // Totals
        calculateTotals() {
            const subtotal = this.getSubtotal();
            const discount = this.getDiscount();
            const shipping = this.getShipping();
            const tax = this.getTax(subtotal - discount);
            const total = subtotal - discount + shipping + tax;

            this.updateTotalsDisplay(subtotal, discount, shipping, tax, total);
        },

        getSubtotal() {
            return this.cart.reduce((sum, item) => {
                const price = item.sale_price || item.price;
                return sum + (price * item.quantity);
            }, 0);
        },

        getDiscount() {
            if (this.promoCode) return this.discount;
            return 0;
        },

        getShipping() {
            const subtotal = this.getSubtotal();
            if (subtotal > 10000) return 0;
            if (subtotal > 0) return 500;
            return 0;
        },

        getTax(subtotal) {
            return Math.round(subtotal * 0.16);
        },

        updateTotalsDisplay(subtotal, discount, shipping, tax, total) {
            const elements = {
                'cart-subtotal': subtotal,
                'cart-discount': discount,
                'cart-shipping': shipping,
                'cart-tax': tax,
                'cart-total': total
            };

            Object.entries(elements).forEach(([id, value]) => {
                const el = document.getElementById(id);
                if (el) el.textContent = App.formatPrice(value);
            });

            const discountRow = document.querySelector('.summary-discount');
            if (discountRow) discountRow.style.display = discount > 0 ? 'flex' : 'none';
        },

        // Promo Code
        setupEventListeners() {
            const promoForm = document.querySelector('.promo-form');
            promoForm?.addEventListener('submit', (e) => {
                e.preventDefault();
                const input = promoForm.querySelector('input');
                this.applyPromoCode(input.value);
            });
        },

        applyPromoCode(code) {
            const validCodes = {
                'WELCOME10': { type: 'percent', value: 10 },
                'SAVE20': { type: 'fixed', value: 2000 },
                'FREESHIP': { type: 'shipping', value: 0 }
            };

            const promo = validCodes[code.toUpperCase()];
            
            if (!promo) {
                Toast.error('Invalid promo code');
                return;
            }

            if (promo.type === 'percent') {
                this.discount = this.getSubtotal() * (promo.value / 100);
            } else if (promo.type === 'fixed') {
                this.discount = promo.value;
            } else if (promo.type === 'shipping') {
                this.discount = this.getShipping();
            }

            this.promoCode = code.toUpperCase();
            this.savePromoCode();
            this.showPromoApplied(code);
            this.calculateTotals();
            Toast.success('Promo code applied!');
        },

        showPromoApplied(code) {
            const promoForm = document.querySelector('.promo-form');
            if (!promoForm) return;

            promoForm.style.display = 'none';
            
            const promoApplied = document.createElement('div');
            promoApplied.className = 'promo-applied';
            promoApplied.innerHTML = `
                <div>
                    <span class="badge badge-success">Applied</span>
                    <strong>${code}</strong>
                </div>
                <button onclick="CartPage.removePromoCode()" style="background:none;border:none;cursor:pointer;color:#666;">
                    <i class="fas fa-times"></i>
                </button>
            `;

            promoForm.parentNode.insertBefore(promoApplied, promoForm.nextSibling);
        },

        removePromoCode() {
            this.promoCode = null;
            this.discount = 0;
            localStorage.removeItem('aluora_gsl_promo');
            
            const promoApplied = document.querySelector('.promo-applied');
            const promoForm = document.querySelector('.promo-form');
            
            if (promoApplied) promoApplied.remove();
            if (promoForm) promoForm.style.display = 'flex';
            
            this.calculateTotals();
            Toast.info('Promo code removed');
        },

        // Payment - Main Functionality
        async proceedToCheckout() {
            if (this.cart.length === 0) {
                Toast.warning('Your cart is empty');
                return;
            }

            // Check authentication
            if (!Auth.isLoggedIn()) {
                Toast.warning('Please login to checkout');
                setTimeout(() => {
                    window.location.href = 'login.html?redirect=cart.html';
                }, 1500);
                return;
            }

            // Show payment modal
            this.showPaymentModal();
        },

        showPaymentModal() {
            const total = this.getSubtotal() - this.getDiscount() + this.getShipping() + this.getTax(this.getSubtotal() - this.getDiscount());
            const user = Auth.getCurrentUser();
            
            // Remove existing modal
            const existing = document.getElementById('payment-modal');
            if (existing) existing.remove();

            const modal = document.createElement('div');
            modal.id = 'payment-modal';
            modal.className = 'modal-overlay';
            modal.innerHTML = `
                <div class="modal-content payment-modal" style="background: white; border-radius: 16px; max-width: 500px; width: 95%; max-height: 90vh; overflow-y: auto; animation: zoomIn 0.3s ease;">
                    <div class="payment-header" style="padding: 1.5rem; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                        <h2 style="margin: 0; font-size: 1.5rem;">Complete Payment</h2>
                        <button onclick="CartPage.closePaymentModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #666;">&times;</button>
                    </div>
                    
                    <div class="payment-body" style="padding: 1.5rem;">
                        <!-- Order Summary -->
                        <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                            <h4 style="margin: 0 0 0.5rem 0;">Order Summary</h4>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span>Items (${this.cart.reduce((c, i) => c + i.quantity, 0)})</span>
                                <span>${App.formatPrice(this.getSubtotal())}</span>
                            </div>
                            ${this.getDiscount() > 0 ? `
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; color: #22c55e;">
                                <span>Discount</span>
                                <span>-${App.formatPrice(this.getDiscount())}</span>
                            </div>
                            ` : ''}
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span>Shipping</span>
                                <span>${App.formatPrice(this.getShipping())}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span>Tax (16%)</span>
                                <span>${App.formatPrice(this.getTax(this.getSubtotal() - this.getDiscount()))}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1.25rem; padding-top: 0.5rem; border-top: 1px solid #ddd;">
                                <span>Total</span>
                                <span style="color: #1a5f4a;">${App.formatPrice(total)}</span>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Select Payment Method</label>
                            <div class="payment-methods" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem;">
                                <label class="payment-method" style="border: 2px solid #e2e8f0; border-radius: 8px; padding: 1rem; cursor: pointer; text-align: center; transition: all 0.2s;">
                                    <input type="radio" name="payment_method" value="mpesa" checked style="display: none;">
                                    <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">📱</div>
                                    <div style="font-weight: 500;">M-Pesa</div>
                                    <div style="font-size: 0.75rem; color: #666;">Instant</div>
                                </label>
                                <label class="payment-method" style="border: 2px solid #e2e8f0; border-radius: 8px; padding: 1rem; cursor: pointer; text-align: center; transition: all 0.2s;">
                                    <input type="radio" name="payment_method" value="bank" style="display: none;">
                                    <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">🏦</div>
                                    <div style="font-weight: 500;">Bank</div>
                                    <div style="font-size: 0.75rem; color: #666;">Transfer</div>
                                </label>
                                <label class="payment-method" style="border: 2px solid #e2e8f0; border-radius: 8px; padding: 1rem; cursor: pointer; text-align: center; transition: all 0.2s;">
                                    <input type="radio" name="payment_method" value="card" style="display: none;">
                                    <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">💳</div>
                                    <div style="font-weight: 500;">Card</div>
                                    <div style="font-size: 0.75rem; color: #666;">Visa/Master</div>
                                </label>
                                <label class="payment-method" style="border: 2px solid #e2e8f0; border-radius: 8px; padding: 1rem; cursor: pointer; text-align: center; transition: all 0.2s;">
                                    <input type="radio" name="payment_method" value="cash" style="display: none;">
                                    <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">💵</div>
                                    <div style="font-weight: 500;">Cash</div>
                                    <div style="font-size: 0.75rem; color: #666;">On Delivery</div>
                                </label>
                            </div>
                        </div>

                        <!-- Phone for M-Pesa -->
                        <div id="mpesa-fields" style="margin-bottom: 1.5rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">M-Pesa Phone Number</label>
                            <input type="tel" id="payment-phone" placeholder="e.g., 254712345678" 
                                   style="width: 100%; padding: 0.875rem; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 1rem;">
                            <small style="color: #666; display: block; margin-top: 0.5rem;">
                                You will receive an STK push prompt on this number
                            </small>
                        </div>

                        <!-- Error message area -->
                        <div id="payment-error" style="display: none; padding: 1rem; background: #fee2e2; border-radius: 8px; margin-bottom: 1rem;">
                            <p style="margin: 0; color: #dc2626;"></p>
                        </div>

                        <!-- Loading state -->
                        <div id="payment-loading" style="display: none; text-align: center; padding: 1rem;">
                            <div class="spinner"></div>
                            <p style="margin-top: 0.5rem;">Processing payment...</p>
                        </div>
                    </div>

                    <div class="payment-footer" style="padding: 1.5rem; border-top: 1px solid #eee; display: flex; gap: 1rem;">
                        <button onclick="CartPage.closePaymentModal()" class="btn btn-outline" style="flex: 1;">
                            Cancel
                        </button>
                        <button onclick="CartPage.processPayment()" class="btn btn-primary" style="flex: 2;" id="pay-button">
                            <i class="fas fa-lock"></i> Pay ${App.formatPrice(total)}
                        </button>
                    </div>
                </div>
            `;

            modal.addEventListener('click', (e) => {
                if (e.target === modal) this.closePaymentModal();
            });

            document.body.appendChild(modal);

            // Payment method selection styling
            const methodInputs = document.querySelectorAll('input[name="payment_method"]');
            const mpesaFields = document.getElementById('mpesa-fields');
            
            methodInputs.forEach(input => {
                input.addEventListener('change', (e) => {
                    document.querySelectorAll('.payment-method').forEach(m => {
                        m.style.borderColor = '#e2e8f0';
                        m.style.background = 'white';
                    });
                    e.target.closest('.payment-method').style.borderColor = '#1a5f4a';
                    e.target.closest('.payment-method').style.background = '#f0fdf4';
                    
                    if (mpesaFields) {
                        mpesaFields.style.display = e.target.value === 'mpesa' ? 'block' : 'none';
                    }
                });
            });
        },

        closePaymentModal() {
            const modal = document.getElementById('payment-modal');
            if (modal) modal.remove();
        },

        async processPayment() {
            const errorDiv = document.getElementById('payment-error');
            const loadingDiv = document.getElementById('payment-loading');
            const payButton = document.getElementById('pay-button');
            
            // Get payment details
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value;
            const phone = document.getElementById('payment-phone')?.value;
            
            // Validation
            if (!paymentMethod) {
                this.showPaymentError('Please select a payment method');
                return;
            }

            if (paymentMethod === 'mpesa' && !phone) {
                this.showPaymentError('Please enter your M-Pesa phone number');
                return;
            }

            if (paymentMethod === 'mpesa' && phone) {
                // Basic phone validation for Kenya
                const phoneClean = phone.replace(/\D/g, '');
                if (phoneClean.length < 12) {
                    this.showPaymentError('Please enter a valid phone number (254XXXXXXXXX)');
                    return;
                }
            }

            // Show loading
            this.hidePaymentError();
            loadingDiv.style.display = 'block';
            payButton.disabled = true;

            try {
                // Simulate payment processing
                await new Promise(resolve => setTimeout(resolve, 2000));

                // Generate transaction reference
                const transactionRef = this.generateTransactionRef();
                
                // Create order
                const order = this.createOrder(transactionRef, paymentMethod);
                
                // Save order
                this.saveOrder(order);
                
                // Clear cart
                this.clearCart();
                
                // Close modal
                this.closePaymentModal();
                
                // Show success
                this.showPaymentSuccess(order, transactionRef);
                
            } catch (error) {
                console.error('Payment error:', error);
                this.showPaymentError('Payment failed. Please try again.');
            } finally {
                loadingDiv.style.display = 'none';
                payButton.disabled = false;
            }
        },

        showPaymentError(message) {
            const errorDiv = document.getElementById('payment-error');
            if (errorDiv) {
                errorDiv.querySelector('p').textContent = message;
                errorDiv.style.display = 'block';
            }
        },

        hidePaymentError() {
            const errorDiv = document.getElementById('payment-error');
            if (errorDiv) errorDiv.style.display = 'none';
        },

        generateTransactionRef() {
            const prefix = 'ALG';
            const timestamp = Date.now().toString(36).toUpperCase();
            const random = Math.random().toString(36).substring(2, 6).toUpperCase();
            return `${prefix}-${timestamp}-${random}`;
        },

        createOrder(transactionRef, paymentMethod) {
            const subtotal = this.getSubtotal();
            const discount = this.getDiscount();
            const shipping = this.getShipping();
            const tax = this.getTax(subtotal - discount);
            const total = subtotal - discount + shipping + tax;
            const user = Auth.getCurrentUser();

            return {
                id: 'ORD-' + Date.now().toString(36).toUpperCase(),
                transaction_ref: transactionRef,
                user_id: user?.id,
                user_name: user?.name,
                user_email: user?.email,
                items: this.cart,
                subtotal: subtotal,
                discount: discount,
                shipping: shipping,
                tax: tax,
                total: total,
                payment_method: paymentMethod,
                payment_status: 'completed',
                status: 'pending',
                created_at: new Date().toISOString(),
                updated_at: new Date().toISOString()
            };
        },

        saveOrder(order) {
            try {
                const orders = JSON.parse(localStorage.getItem('aluora_gsl_orders') || '[]');
                orders.unshift(order);
                localStorage.setItem('aluora_gsl_orders', JSON.stringify(orders));
                
                // Also save transaction details separately
                const transactions = JSON.parse(localStorage.getItem('aluora_gsl_transactions') || '[]');
                transactions.unshift({
                    transaction_ref: order.transaction_ref,
                    order_id: order.id,
                    amount: order.total,
                    method: order.payment_method,
                    status: 'completed',
                    created_at: order.created_at
                });
                localStorage.setItem('aluora_gsl_transactions', JSON.stringify(transactions));
                
            } catch (error) {
                console.error('Save order error:', error);
            }
        },

        showPaymentSuccess(order, transactionRef) {
            // Remove existing
            const existing = document.getElementById('success-modal');
            if (existing) existing.remove();

            const modal = document.createElement('div');
            modal.id = 'success-modal';
            modal.className = 'modal-overlay';
            modal.innerHTML = `
                <div class="modal-content" style="background: white; padding: 2rem; border-radius: 16px; max-width: 450px; width: 95%; text-align: center; animation: zoomIn 0.3s ease;">
                    <div style="width: 80px; height: 80px; background: #d4edda; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-size: 2.5rem; color: #28a745;">✓</div>
                    <h2 style="margin-bottom: 0.5rem;">Payment Successful!</h2>
                    <p style="color: #666; margin-bottom: 1.5rem;">Thank you for your order</p>
                    
                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: left;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span style="color: #666;">Order ID</span>
                            <strong>${order.id}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span style="color: #666;">Transaction Ref</span>
                            <strong style="color: #1a5f4a;">${transactionRef}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #666;">Amount Paid</span>
                            <strong>${App.formatPrice(order.total)}</strong>
                        </div>
                    </div>

                    <p style="font-size: 0.875rem; color: #666; margin-bottom: 1.5rem;">
                        A confirmation email has been sent to ${order.user_email || 'your email'}
                    </p>

                    <div style="display: flex; gap: 0.75rem;">
                        <a href="dashboard.html#orders" class="btn btn-outline" style="flex: 1; text-decoration: none;">
                            View Orders
                        </a>
                        <a href="products.html" class="btn btn-primary" style="flex: 1; text-decoration: none;">
                            Continue Shopping
                        </a>
                    </div>
                </div>
            `;

            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.remove();
                    window.location.href = 'dashboard.html#orders';
                }
            });

            document.body.appendChild(modal);
        }
    };

    // Initialize
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => CartPage.init());
    } else {
        CartPage.init();
    }

    window.CartPage = CartPage;
})();
