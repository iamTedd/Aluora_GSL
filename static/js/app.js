/**
 * Aluora GSL - Core Application JavaScript
 * Enhanced Error Handling & Clean Layout
 */

(function() {
    'use strict';

    // ==========================================================================
    // GLOBAL ERROR HANDLING
    // ==========================================================================
    
    // Global error handlers
    window.addEventListener('error', function(event) {
        console.error('Global error:', event.error);
        if (Toast) {
            Toast.error('An unexpected error occurred. Please try again.');
        }
    });

    // Unhandled promise rejection
    window.addEventListener('unhandledrejection', function(event) {
        console.error('Unhandled rejection:', event.reason);
        if (Toast) {
            Toast.error('Something went wrong. Please refresh the page.');
        }
    });

    // ==========================================================================
    // CORE APPLICATION
    // ==========================================================================
    
    const App = {
        config: {
            storageKey: 'aluora_gsl',
            toastDuration: 5000,
            animationDuration: 300,
            debounceDelay: 250,
            maxCartItems: 99,
            defaultCurrency: 'KES'
        },

        init() {
            try {
                this.setupPreloader();
                this.setupHeader();
                this.setupMobileMenu();
                this.setupBackToTop();
                this.setupChatWidget();
                this.setupSmoothScroll();
                this.setupAnimations();
                this.setupFormValidation();
                this.setupKeyboardShortcuts();
                this.loadUserSession();
                this.updateCartCount();
            } catch (error) {
                console.error('Initialization error:', error);
                this.hidePreloader();
            }
        },

        // Hide preloader on error
        hidePreloader() {
            const preloader = document.getElementById('preloader');
            if (preloader) {
                preloader.classList.add('hidden');
                setTimeout(() => preloader.remove(), 500);
            }
        },

        // ==========================================================================
        // PRELOADER
        // ==========================================================================
        
        setupPreloader() {
            const preloader = document.getElementById('preloader');
            if (!preloader) return;

            window.addEventListener('load', () => {
                setTimeout(() => {
                    preloader.classList.add('hidden');
                    setTimeout(() => {
                        preloader.style.display = 'none';
                    }, 500);
                }, 800);
            });

            // Fallback: hide after 10 seconds even if load doesn't fire
            setTimeout(() => {
                preloader.classList.add('hidden');
                setTimeout(() => preloader.remove(), 500);
            }, 10000);
        },

        // ==========================================================================
        // HEADER
        // ==========================================================================
        
        setupHeader() {
            const header = document.querySelector('.header');
            if (!header) return;

            let lastScroll = 0;

            window.addEventListener('scroll', () => {
                try {
                    const currentScroll = window.pageYOffset;

                    if (currentScroll > 100) {
                        header.classList.add('scrolled');
                    } else {
                        header.classList.remove('scrolled');
                    }

                    if (currentScroll > lastScroll && currentScroll > 200) {
                        header.style.transform = 'translateY(-100%)';
                    } else {
                        header.style.transform = 'translateY(0)';
                    }

                    lastScroll = currentScroll;
                } catch (error) {
                    console.error('Scroll handler error:', error);
                }
            });
        },

        // ==========================================================================
        // MOBILE MENU
        // ==========================================================================
        
        setupMobileMenu() {
            const menuBtn = document.querySelector('.mobile-menu-btn');
            const menu = document.querySelector('.nav-menu');
            const overlay = document.querySelector('.menu-overlay');

            if (!menuBtn || !menu) return;

            const toggleMenu = () => {
                menu.classList.toggle('active');
                overlay?.classList.toggle('active');
                document.body.style.overflow = menu.classList.contains('active') ? 'hidden' : '';
            };

            menuBtn.addEventListener('click', toggleMenu);
            overlay?.addEventListener('click', toggleMenu);

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && menu.classList.contains('active')) {
                    toggleMenu();
                }
            });

            menu.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => {
                    if (window.innerWidth <= 992) {
                        toggleMenu();
                    }
                });
            });
        },

        // ==========================================================================
        // BACK TO TOP
        // ==========================================================================
        
        setupBackToTop() {
            const backToTop = document.querySelector('.back-to-top');
            if (!backToTop) return;

            window.addEventListener('scroll', () => {
                if (window.pageYOffset > 300) {
                    backToTop.classList.add('visible');
                } else {
                    backToTop.classList.remove('visible');
                }
            });

            backToTop.addEventListener('click', () => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        },

        // ==========================================================================
        // CHAT WIDGET
        // ==========================================================================
        
        setupChatWidget() {
            const chatToggle = document.getElementById('chat-toggle');
            const chatBox = document.getElementById('chat-box');
            const chatClose = document.getElementById('chat-close');
            const chatSend = document.getElementById('chat-send');
            const chatInput = document.getElementById('chat-input');

            if (!chatToggle) return;

            const toggleChat = () => {
                chatBox?.classList.toggle('active');
            };

            chatToggle?.addEventListener('click', toggleChat);
            chatClose?.addEventListener('click', toggleChat);

            const sendMessage = () => {
                try {
                    const message = chatInput?.value.trim();
                    if (!message) return;

                    App.addChatMessage(message, 'user');
                    chatInput.value = '';

                    setTimeout(() => {
                        const responses = [
                            "Thank you for your message! Our team will get back to you shortly.",
                            "We appreciate your inquiry. Let me connect you with a specialist.",
                            "Great question! Here's what I can tell you..."
                        ];
                        const randomResponse = responses[Math.floor(Math.random() * responses.length)];
                        App.addChatMessage(randomResponse, 'support');
                    }, 1000);
                } catch (error) {
                    console.error('Chat error:', error);
                    Toast.error('Failed to send message');
                }
            };

            chatSend?.addEventListener('click', sendMessage);
            chatInput?.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') sendMessage();
            });
        },

        addChatMessage(message, type) {
            const chatMessages = document.getElementById('chat-messages');
            if (!chatMessages) return;

            try {
                const messageEl = document.createElement('div');
                messageEl.className = `chat-message ${type}`;
                messageEl.textContent = message;
                chatMessages.appendChild(messageEl);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            } catch (error) {
                console.error('Add message error:', error);
            }
        },

        // ==========================================================================
        // SMOOTH SCROLL
        // ==========================================================================
        
        setupSmoothScroll() {
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', (e) => {
                    const href = anchor.getAttribute('href');
                    if (href === '#') return;

                    const target = document.querySelector(href);
                    if (target) {
                        e.preventDefault();
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            });
        },

        // ==========================================================================
        // ANIMATIONS
        // ==========================================================================
        
        setupAnimations() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-in');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });

            document.querySelectorAll('[data-animate]').forEach(el => {
                observer.observe(el);
            });
        },

        // ==========================================================================
        // FORM VALIDATION - Enhanced
        // ==========================================================================
        
        setupFormValidation() {
            document.querySelectorAll('.form-input').forEach(input => {
                input.addEventListener('blur', () => App.validateField(input));
                input.addEventListener('input', () => {
                    if (input.classList.contains('error')) {
                        App.validateField(input);
                    }
                });
            });

            document.querySelectorAll('form[data-validate]').forEach(form => {
                form.addEventListener('submit', (e) => {
                    if (!App.validateForm(form)) {
                        e.preventDefault();
                    }
                });
            });
        },

        validateField(input) {
            const value = input.value.trim();
            const type = input.type;
            let isValid = true;
            let message = '';

            // Required
            if (input.required && !value) {
                isValid = false;
                message = 'This field is required';
            }

            // Email
            if (type === 'email' && value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    isValid = false;
                    message = 'Please enter a valid email';
                }
            }

            // Phone
            if (type === 'tel' && value) {
                const phoneRegex = /^[\d\s\-\+\(\)]+$/;
                if (!phoneRegex.test(value) || value.replace(/\D/g, '').length < 10) {
                    isValid = false;
                    message = 'Please enter a valid phone number';
                }
            }

            // Password
            if (type === 'password' && value) {
                if (value.length < 6) {
                    isValid = false;
                    message = 'Password must be at least 6 characters';
                }
            }

            // Number
            if (type === 'number' && value) {
                const min = parseFloat(input.min);
                const max = parseFloat(input.max);
                const numValue = parseFloat(value);

                if (!isNaN(min) && numValue < min) {
                    isValid = false;
                    message = `Minimum value is ${min}`;
                }
                if (!isNaN(max) && numValue > max) {
                    isValid = false;
                    message = `Maximum value is ${max}`;
                }
            }

            // Update UI
            input.classList.remove('error', 'success');
            if (value) {
                input.classList.add(isValid ? 'success' : 'error');
            }

            // Error message
            let errorEl = input.parentElement?.querySelector('.form-error');
            if (!isValid && !errorEl) {
                errorEl = document.createElement('div');
                errorEl.className = 'form-error';
                input.parentElement?.appendChild(errorEl);
            }
            if (errorEl) {
                errorEl.innerHTML = isValid ? '' : `<i class="fas fa-exclamation-circle"></i> ${message}`;
                if (isValid) errorEl.remove();
            }

            return isValid;
        },

        validateForm(form) {
            const inputs = form.querySelectorAll('.form-input[required]');
            let isValid = true;

            inputs.forEach(input => {
                if (!App.validateField(input)) {
                    isValid = false;
                }
            });

            return isValid;
        },

        // ==========================================================================
        // KEYBOARD SHORTCUTS
        // ==========================================================================
        
        setupKeyboardShortcuts() {
            document.addEventListener('keydown', (e) => {
                // Ctrl/Cmd + K
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    const searchInput = document.querySelector('#search-input, .search-input, input[type="search"]');
                    searchInput?.focus();
                }

                // Escape
                if (e.key === 'Escape') {
                    document.querySelectorAll('.modal.active').forEach(modal => {
                        App.closeModal(modal.id);
                    });
                }
            });
        },

        // ==========================================================================
        // USER SESSION
        // ==========================================================================
        
        loadUserSession() {
            try {
                const user = this.getUser();
                if (user) {
                    this.updateAuthUI(user);
                }
            } catch (error) {
                console.error('Session load error:', error);
            }
        },

        getUser() {
            try {
                const data = localStorage.getItem(`${this.config.storageKey}_user`);
                return data ? JSON.parse(data) : null;
            } catch (error) {
                console.error('Get user error:', error);
                return null;
            }
        },

        saveUser(user) {
            try {
                localStorage.setItem(`${this.config.storageKey}_user`, JSON.stringify(user));
            } catch (error) {
                console.error('Save user error:', error);
            }
        },

        logout() {
            try {
                localStorage.removeItem(`${this.config.storageKey}_user`);
                localStorage.removeItem(`${this.config.storageKey}_cart`);
                Toast.success('Logged out successfully');
                setTimeout(() => {
                    window.location.href = 'index.html';
                }, 1000);
            } catch (error) {
                console.error('Logout error:', error);
            }
        },

        updateAuthUI(user) {
            const authButtons = document.getElementById('auth-buttons');
            if (!authButtons) return;

            if (user) {
                authButtons.innerHTML = `
                    <div class="dropdown" id="user-dropdown">
                        <div class="dropdown-toggle" onclick="this.parentElement.classList.toggle('active')">
                            <div class="user-avatar-sm">${user.name?.charAt(0).toUpperCase() || 'U'}</div>
                            <span>${user.name?.split(' ')[0] || 'User'}</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="dropdown-menu">
                            <a href="dashboard.html" class="dropdown-item"><i class="fas fa-user"></i> Dashboard</a>
                            <a href="dashboard.html#orders" class="dropdown-item"><i class="fas fa-box"></i> My Orders</a>
                            <a href="dashboard.html#settings" class="dropdown-item"><i class="fas fa-cog"></i> Settings</a>
                            <div class="dropdown-divider"></div>
                            <button onclick="App.logout()" class="dropdown-item" style="width:100%;text-align:left;background:none;border:none;cursor:pointer;"><i class="fas fa-sign-out-alt"></i> Logout</button>
                        </div>
                    </div>
                `;
            } else {
                authButtons.innerHTML = `
                    <a href="login.html" class="btn btn-outline">Login</a>
                    <a href="register.html" class="btn btn-primary">Register</a>
                `;
            }
        },

        // ==========================================================================
        // CART
        // ==========================================================================
        
        getCart() {
            try {
                const data = localStorage.getItem(`${this.config.storageKey}_cart`);
                return data ? JSON.parse(data) : [];
            } catch (error) {
                console.error('Get cart error:', error);
                return [];
            }
        },

        saveCart(cart) {
            try {
                localStorage.setItem(`${this.config.storageKey}_cart`, JSON.stringify(cart));
                this.updateCartCount();
            } catch (error) {
                console.error('Save cart error:', error);
            }
        },

        addToCart(product, quantity = 1) {
            try {
                let cart = this.getCart();
                const existingItem = cart.find(item => item.id === product.id);

                if (existingItem) {
                    existingItem.quantity += quantity;
                } else {
                    cart.push({ ...product, quantity });
                }

                this.saveCart(cart);
                Toast.success(`${product.name} added to cart!`);
            } catch (error) {
                console.error('Add to cart error:', error);
                Toast.error('Failed to add item to cart');
            }
        },

        removeFromCart(productId) {
            try {
                let cart = this.getCart();
                cart = cart.filter(item => item.id !== productId);
                this.saveCart(cart);
                Toast.info('Item removed from cart');
            } catch (error) {
                console.error('Remove from cart error:', error);
            }
        },

        updateCartQuantity(productId, quantity) {
            try {
                let cart = this.getCart();
                const item = cart.find(item => item.id === productId);

                if (item) {
                    if (quantity <= 0) {
                        return this.removeFromCart(productId);
                    }
                    item.quantity = Math.min(quantity, this.config.maxCartItems);
                    this.saveCart(cart);
                }
            } catch (error) {
                console.error('Update quantity error:', error);
            }
        },

        clearCart() {
            this.saveCart([]);
            Toast.info('Cart cleared');
        },

        getCartTotal() {
            try {
                const cart = this.getCart();
                return cart.reduce((total, item) => total + (item.sale_price || item.price) * item.quantity, 0);
            } catch (error) {
                console.error('Get cart total error:', error);
                return 0;
            }
        },

        getCartItemCount() {
            try {
                const cart = this.getCart();
                return cart.reduce((count, item) => count + item.quantity, 0);
            } catch (error) {
                console.error('Get cart count error:', error);
                return 0;
            }
        },

        updateCartCount() {
            const countEl = document.getElementById('cart-count');
            if (countEl) {
                try {
                    const count = this.getCartItemCount();
                    countEl.textContent = count;
                    countEl.style.display = count > 0 ? 'flex' : 'none';
                } catch (error) {
                    console.error('Update cart count error:', error);
                }
            }
        },

        // ==========================================================================
        // MODALS
        // ==========================================================================
        
        openModal(modalId) {
            try {
                const modal = document.getElementById(modalId);
                const backdrop = document.querySelector('.modal-backdrop');
                
                if (modal) {
                    modal.classList.add('active');
                    backdrop?.classList.add('active');
                    document.body.style.overflow = 'hidden';
                }
            } catch (error) {
                console.error('Open modal error:', error);
            }
        },

        closeModal(modalId) {
            try {
                const modal = document.getElementById(modalId);
                const backdrop = document.querySelector('.modal-backdrop');
                
                if (modal) {
                    modal.classList.remove('active');
                    backdrop?.classList.remove('active');
                    document.body.style.overflow = '';
                }
            } catch (error) {
                console.error('Close modal error:', error);
            }
        },

        // ==========================================================================
        // DATA
        // ==========================================================================
        
        async fetchData(url) {
            try {
                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return await response.json();
            } catch (error) {
                console.error('Fetch error:', error);
                Toast.error('Failed to load data. Please refresh the page.');
                return null;
            }
        },

        getProducts() {
            return this.fetchData('data/products.json');
        },

        getCategories() {
            return this.fetchData('data/categories.json');
        },

        getUsers() {
            return this.fetchData('data/users.json');
        },

        searchProducts(products, query) {
            if (!products) return [];
            const searchTerm = query.toLowerCase();
            return products.filter(product => 
                product.name?.toLowerCase().includes(searchTerm) ||
                product.description?.toLowerCase().includes(searchTerm) ||
                product.category?.toLowerCase().includes(searchTerm)
            );
        },

        filterProducts(products, category) {
            if (!products) return [];
            if (!category || category === 'all') return products;
            return products.filter(product => product.category_slug === category);
        },

        sortProducts(products, sortBy) {
            if (!products) return [];
            const sorted = [...products];
            switch (sortBy) {
                case 'price-low':
                    return sorted.sort((a, b) => (a.sale_price || a.price) - (b.sale_price || b.price));
                case 'price-high':
                    return sorted.sort((a, b) => (b.sale_price || b.price) - (a.sale_price || a.price));
                case 'name':
                default:
                    return sorted.sort((a, b) => (a.name || '').localeCompare(b.name || ''));
            }
        },

        // ==========================================================================
        // UTILITIES
        // ==========================================================================
        
        formatPrice(price) {
            try {
                return new Intl.NumberFormat('en-KE', {
                    style: 'currency',
                    currency: 'KES'
                }).format(price || 0);
            } catch (error) {
                return `KES ${(price || 0).toFixed(2)}`;
            }
        },

        formatDate(date) {
            try {
                return new Intl.DateTimeFormat('en-KE', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                }).format(new Date(date));
            } catch (error) {
                return date;
            }
        },

        formatDateTime(date) {
            try {
                return new Intl.DateTimeFormat('en-KE', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                }).format(new Date(date));
            } catch (error) {
                return date;
            }
        },

        debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

        throttle(func, limit) {
            let inThrottle;
            return function(...args) {
                if (!inThrottle) {
                    func.apply(this, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        },

        copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                Toast.success('Copied to clipboard!');
            }).catch(() => {
                Toast.error('Failed to copy');
            });
        },

        isValidEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        },

        isValidPhone(phone) {
            return /^[\d\s\-\+\(\)]{10,}$/.test(phone);
        },

        generateId() {
            return Date.now().toString(36) + Math.random().toString(36).substr(2);
        },

        getURLParams() {
            try {
                const params = new URLSearchParams(window.location.search);
                const obj = {};
                for (const [key, value] of params) {
                    obj[key] = value;
                }
                return obj;
            } catch (error) {
                return {};
            }
        },

        // DOM utilities
        $(selector) {
            return document.querySelector(selector);
        },

        $$(selector) {
            return document.querySelectorAll(selector);
        },

        createElement(tag, className, innerHTML) {
            const el = document.createElement(tag);
            if (className) el.className = className;
            if (innerHTML) el.innerHTML = innerHTML;
            return el;
        }
    };

    // ==========================================================================
    // TOAST - Error Handling Enhanced
    // ==========================================================================
    
    const Toast = {
        container: null,

        init() {
            this.container = document.getElementById('toast-container');
            if (!this.container) {
                this.container = App.createElement('div', 'toast-container');
                this.container.id = 'toast-container';
                document.body.appendChild(this.container);
            }
        },

        show(message, type = 'info', options = {}) {
            try {
                if (!this.container) this.init();

                const {
                    title = '',
                    duration = App.config.toastDuration,
                    dismissible = true
                } = options;

                const toast = App.createElement('div', 'toast');
                toast.innerHTML = `
                    <div class="toast-icon ${type}">
                        <i class="fas fa-${this.getIcon(type)}"></i>
                    </div>
                    <div class="toast-content">
                        ${title ? `<div class="toast-title">${title}</div>` : ''}
                        <div class="toast-message">${message}</div>
                    </div>
                    ${dismissible ? '<button class="toast-close">&times;</button>' : ''}
                    <div class="toast-progress ${type}" style="animation-duration: ${duration}ms"></div>
                `;

                const closeBtn = toast.querySelector('.toast-close');
                closeBtn?.addEventListener('click', () => this.remove(toast));

                this.container.appendChild(toast);
                setTimeout(() => this.remove(toast), duration);

                return toast;
            } catch (error) {
                console.error('Toast error:', error);
            }
        },

        remove(toast) {
            if (!toast) return;
            try {
                toast.classList.add('removing');
                setTimeout(() => toast.remove(), 300);
            } catch (error) {
                console.error('Remove toast error:', error);
            }
        },

        getIcon(type) {
            const icons = {
                success: 'check-circle',
                error: 'exclamation-circle',
                warning: 'exclamation-triangle',
                info: 'info-circle'
            };
            return icons[type] || 'info-circle';
        },

        success(message, options = {}) {
            return this.show(message, 'success', options);
        },

        error(message, options = {}) {
            return this.show(message, 'error', options);
        },

        warning(message, options = {}) {
            return this.show(message, 'warning', options);
        },

        info(message, options = {}) {
            return this.show(message, 'info', options);
        }
    };

    // ==========================================================================
    // PRODUCTS MODULE
    // ==========================================================================
    
    const Products = {
        all: [],
        filtered: [],
        currentCategory: 'all',
        currentSort: 'name',
        searchQuery: '',

        async init() {
            try {
                this.all = await App.getProducts();
                if (!this.all) {
                    this.all = [];
                    Toast.error('Failed to load products');
                    return;
                }
                this.filtered = [...this.all];
                this.render();
                this.setupFilters();
            } catch (error) {
                console.error('Products init error:', error);
                Toast.error('Failed to initialize products');
            }
        },

        render() {
            const grid = document.getElementById('products-grid');
            if (!grid) return;

            if (this.filtered.length === 0) {
                grid.innerHTML = this.getEmptyState();
                return;
            }

            try {
                grid.innerHTML = this.filtered.map(product => this.getProductCard(product)).join('');
            } catch (error) {
                console.error('Render error:', error);
                grid.innerHTML = '<p class="text-center">Error loading products</p>';
            }
        },

        getProductCard(product) {
            const price = product.sale_price || product.price;
            const originalPrice = product.sale_price ? product.price : null;
            
            return `
                <div class="product-card">
                    <div class="product-image">
                        <div class="placeholder-icon"><i class="fas fa-box"></i></div>
                        ${product.sale_price ? '<span class="product-badge sale">SALE</span>' : ''}
                        ${product.stock_quantity === 0 ? '<span class="product-badge out-of-stock">Out of Stock</span>' : ''}
                        <button class="wishlist-btn" onclick="Products.toggleWishlist(${product.id})">
                            <i class="fas fa-heart"></i>
                        </button>
                    </div>
                    <div class="product-content">
                        <span class="product-category">${product.category_slug || 'General'}</span>
                        <h3 class="product-title">${product.name || 'Unnamed Product'}</h3>
                        <p class="product-description">${product.short_description || ''}</p>
                        <div class="product-price">
                            <span class="price-current">${App.formatPrice(price)}</span>
                            ${originalPrice ? `<span class="price-original">${App.formatPrice(originalPrice)}</span>` : ''}
                        </div>
                        <div class="product-actions">
                            <button onclick="Products.addToCart(${product.id})" class="btn btn-primary btn-sm" ${product.stock_quantity === 0 ? 'disabled' : ''}>
                                ${product.stock_quantity === 0 ? 'Out of Stock' : 'Add to Cart'}
                            </button>
                        </div>
                    </div>
                </div>
            `;
        },

        getEmptyState() {
            return `
                <div class="empty-state" style="grid-column: 1/-1; text-align: center; padding: 3rem;">
                    <div class="empty-state-icon" style="font-size: 3rem; margin-bottom: 1rem;">🔍</div>
                    <h3>No products found</h3>
                    <p>Try adjusting your search or filters.</p>
                    <button onclick="Products.resetFilters()" class="btn btn-primary">Reset Filters</button>
                </div>
            `;
        },

        setupFilters() {
            const searchInput = document.getElementById('search-input');
            const categoryFilter = document.getElementById('category-filter');
            const sortFilter = document.getElementById('sort-filter');

            searchInput?.addEventListener('input', App.debounce((e) => {
                this.searchQuery = e.target.value.toLowerCase();
                this.applyFilters();
            }, 300));

            categoryFilter?.addEventListener('change', (e) => {
                this.currentCategory = e.target.value;
                this.applyFilters();
            });

            sortFilter?.addEventListener('change', (e) => {
                this.currentSort = e.target.value;
                this.applyFilters();
            });
        },

        applyFilters() {
            let products = [...this.all];

            if (this.searchQuery) {
                products = App.searchProducts(products, this.searchQuery);
            }

            if (this.currentCategory) {
                products = App.filterProducts(products, this.currentCategory);
            }

            products = App.sortProducts(products, this.currentSort);

            this.filtered = products;
            this.render();
            this.updateCount();
        },

        updateCount() {
            const countEl = document.getElementById('results-count');
            if (countEl) {
                countEl.textContent = `${this.filtered.length} products found`;
            }
        },

        resetFilters() {
            this.searchQuery = '';
            this.currentCategory = '';
            this.currentSort = 'name';

            const searchInput = document.getElementById('search-input');
            const categoryFilter = document.getElementById('category-filter');
            const sortFilter = document.getElementById('sort-filter');

            if (searchInput) searchInput.value = '';
            if (categoryFilter) categoryFilter.value = '';
            if (sortFilter) sortFilter.value = 'name';

            this.applyFilters();
        },

        addToCart(productId) {
            try {
                const product = this.all.find(p => p.id === productId);
                if (product) {
                    App.addToCart(product);
                }
            } catch (error) {
                console.error('Add to cart error:', error);
                Toast.error('Failed to add item');
            }
        },

        toggleWishlist(productId) {
            Toast.info('Added to wishlist!');
        }
    };

    // ==========================================================================
    // AUTH MODULE
    // ==========================================================================
    
    const Auth = {
        async login(email, password) {
            try {
                const users = await App.getUsers();
                if (!users) {
                    Toast.error('Unable to connect. Please try again.');
                    return false;
                }

                const user = users.find(u => u.email === email && u.password === password);

                if (user) {
                    const { password: _, ...safeUser } = user;
                    App.saveUser(safeUser);
                    App.updateAuthUI(safeUser);
                    Toast.success('Login successful!');
                    return true;
                }

                Toast.error('Invalid email or password');
                return false;
            } catch (error) {
                console.error('Login error:', error);
                Toast.error('Login failed. Please try again.');
                return false;
            }
        },

        async register(userData) {
            try {
                const users = await App.getUsers();
                
                if (users?.some(u => u.email === userData.email)) {
                    Toast.error('Email already registered');
                    return false;
                }

                Toast.success('Registration successful! Please login.');
                return true;
            } catch (error) {
                console.error('Register error:', error);
                Toast.error('Registration failed. Please try again.');
                return false;
            }
        },

        getCurrentUser() {
            return App.getUser();
        },

        isLoggedIn() {
            return !!App.getUser();
        },

        requireAuth() {
            if (!this.isLoggedIn()) {
                Toast.warning('Please login to continue');
                setTimeout(() => {
                    window.location.href = 'login.html';
                }, 1500);
                return false;
            }
            return true;
        }
    };

    // Export
    window.App = App;
    window.Toast = Toast;
    window.Products = Products;
    window.Auth = Auth;

    // Initialize
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => App.init());
    } else {
        App.init();
    }

})();
