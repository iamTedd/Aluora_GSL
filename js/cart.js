/**
 * Aluora GSL - Cart JavaScript
 * 40+ Cart Functions
 */

(function() {
    'use strict';

    // ==========================================================================
    // CART PAGE
    // ==========================================================================
    
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

        // ==========================================================================
        // CART LOADING (Functions 1-5)
        // ==========================================================================
        
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
            localStorage.setItem('aluora_gsl_cart', JSON.stringify(this.cart));
            App.updateCartCount();
            this.calculateTotals();
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

        // ==========================================================================
        // CART ITEM HTML (Functions 6-10)
        // ==========================================================================
        
        getCartItemHTML(item, index) {
            const price = item.sale_price || item.price;
            const total = price * item.quantity;
            
            return `
                <div class="cart-item" data-id="${item.id}" style="animation: fadeInUp 0.3s ease ${index * 0.05}s forwards">
                    <div class="cart-item-image">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="cart-item-details">
                        <h4>${item.name}</h4>
                        <span class="sku">SKU: ${item.sku || 'N/A'}</span>
                    </div>
                    <div class="cart-item-price">${App.formatPrice(price)}</div>
                    <div class="quantity-control">
                        <button onclick="CartPage.updateQuantity(${item.id}, ${item.quantity - 1})">
                            <i class="fas fa-minus"></i>
                        </button>
                        <span>${item.quantity}</span>
                        <button onclick="CartPage.updateQuantity(${item.id}, ${item.quantity + 1})">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <div class="cart-item-total">${App.formatPrice(total)}</div>
                    <button class="remove-item" onclick="CartPage.removeItem(${item.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
        },

        // ==========================================================================
        // CART OPERATIONS (Functions 11-20)
        // ==========================================================================
        
        addItem(product, quantity = 1) {
            const existingItem = this.cart.find(item => item.id === product.id);
            
            if (existingItem) {
                existingItem.quantity += quantity;
            } else {
                this.cart.push({ ...product, quantity });
            }
            
            this.saveCart();
            this.renderCart();
            Toast.success(`${product.name} added to cart!`);
        },

        removeItem(productId) {
            this.cart = this.cart.filter(item => item.id !== productId);
            this.saveCart();
            this.renderCart();
            Toast.info('Item removed from cart');
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

        clearCart() {
            this.cart = [];
            this.promoCode = null;
            this.discount = 0;
            this.saveCart();
            this.renderCart();
            Toast.info('Cart cleared');
        },

        // ==========================================================================
        // TOTALS CALCULATION (Functions 21-30)
        // ==========================================================================
        
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
            if (this.promoCode) {
                return this.discount;
            }
            return 0;
        },

        getShipping() {
            const subtotal = this.getSubtotal();
            if (subtotal > 10000) return 0; // Free shipping over KES 10,000
            if (subtotal > 0) return 500;   // Standard shipping
            return 0;
        },

        getTax(subtotal) {
            return subtotal * 0.16; // 16% VAT
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
                if (el) {
                    el.textContent = App.formatPrice(value);
                }
            });

            // Show/hide discount row
            const discountRow = document.querySelector('.summary-discount');
            if (discountRow) {
                discountRow.style.display = discount > 0 ? 'flex' : 'none';
            }
        },

        // ==========================================================================
        // PROMO CODE (Functions 31-40)
        // ==========================================================================
        
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

            // Validate
            if (promo.type === 'percent') {
                this.discount = this.getSubtotal() * (promo.value / 100);
            } else if (promo.type === 'fixed') {
                this.discount = promo.value;
            } else if (promo.type === 'shipping') {
                this.discount = this.getShipping(); // Free shipping
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
                    <span class="badge">Applied</span>
                    <strong>${code}</strong>
                </div>
                <button onclick="CartPage.removePromoCode()" style="background:none;border:none;cursor:pointer;color:var(--gray)">
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

        savePromoCode() {
            localStorage.setItem('aluora_gsl_promo', JSON.stringify({
                code: this.promoCode,
                discount: this.discount
            }));
        },

        loadPromoCode() {
            const saved = localStorage.getItem('aluora_gsl_promo');
            if (saved) {
                const { code, discount } = JSON.parse(saved);
                this.promoCode = code;
                this.discount = discount;
                
                // Show applied promo
                const promoForm = document.querySelector('.promo-form');
                if (promoForm) {
                    const input = promoForm.querySelector('input');
                    if (input) input.value = code;
                    this.showPromoApplied(code);
                }
            }
        },

        // ==========================================================================
        // CHECKOUT (Functions 41-50)
        // ==========================================================================
        
        proceedToCheckout() {
            if (this.cart.length === 0) {
                Toast.warning('Your cart is empty');
                return;
            }

            // Check if user is logged in
            if (!Auth.isLoggedIn()) {
                Toast.warning('Please login to checkout');
                setTimeout(() => {
                    window.location.href = 'login.html?redirect=cart.html';
                }, 1500);
                return;
            }

            // Save cart for checkout
            localStorage.setItem('aluora_gsl_checkout_cart', JSON.stringify(this.cart));
            
            // Proceed to checkout (simulated)
            Toast.success('Processing order...');
            setTimeout(() => {
                this.createOrder();
            }, 1500);
        },

        createOrder() {
            const user = Auth.getCurrentUser();
            if (!user) return;

            const order = {
                id: 'ORD-' + App.generateId(),
                user_id: user.id,
                items: this.cart,
                subtotal: this.getSubtotal(),
                discount: this.getDiscount(),
                shipping: this.getShipping(),
                tax: this.getTax(this.getSubtotal() - this.getDiscount()),
                total: this.getSubtotal() - this.getDiscount() + this.getShipping() + this.getTax(this.getSubtotal() - this.getDiscount()),
                status: 'pending',
                created_at: new Date().toISOString()
            };

            // Save order
            const orders = JSON.parse(localStorage.getItem('aluora_gsl_orders') || '[]');
            orders.unshift(order);
            localStorage.setItem('aluora_gsl_orders', JSON.stringify(orders));

            // Clear cart
            this.clearCart();

            // Redirect to dashboard
            Toast.success('Order placed successfully!');
            setTimeout(() => {
                window.location.href = 'dashboard.html#orders';
            }, 1500);
        },

        // ==========================================================================
        // MINI CART (Functions 51-55)
        // ==========================================================================
        
        getMiniCartHTML() {
            if (this.cart.length === 0) {
                return '<div class="text-center p-4">Your cart is empty</div>';
            }

            const items = this.cart.slice(0, 3).map(item => `
                <div class="mini-cart-item">
                    <div class="mini-cart-item-info">
                        <h4>${item.name}</h4>
                        <span>${item.quantity} x ${App.formatPrice(item.sale_price || item.price)}</span>
                    </div>
                </div>
            `).join('');

            const more = this.cart.length > 3 ? `<div class="mini-cart-more">+${this.cart.length - 3} more items</div>` : '';

            return items + more;
        },

        updateMiniCart() {
            const miniCart = document.getElementById('mini-cart');
            if (miniCart) {
                miniCart.innerHTML = this.getMiniCartHTML();
            }
        }
    };

    // Initialize
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => CartPage.init());
    } else {
        CartPage.init();
    }

    // Export
    window.CartPage = CartPage;

})();
