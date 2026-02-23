/**
 * Aluora GSL - Products Page JavaScript
 * Fixed with Online Images & Cart Functionality
 */

(function() {
    'use strict';

    const ProductsPage = {
        products: [],
        filteredProducts: [],
        currentCategory: 'all',
        searchQuery: '',
        sortBy: 'name',
        loading: false,

        async init() {
            try {
                this.showLoading();
                await this.loadProducts();
                this.renderCategories();
                this.renderProducts();
                this.setupEventListeners();
                this.updateResultsCount();
            } catch (error) {
                console.error('Products init error:', error);
                this.showError('Failed to load products. Please refresh the page.');
            } finally {
                this.hideLoading();
            }
        },

        showLoading() {
            const grid = document.getElementById('products-grid');
            if (grid) {
                grid.innerHTML = `
                    <div class="loading-state" style="grid-column: 1/-1; text-align: center; padding: 3rem;">
                        <div class="spinner" style="margin: 0 auto;"></div>
                        <p style="margin-top: 1rem;">Loading products...</p>
                    </div>
                `;
            }
        },

        hideLoading() {
            this.loading = false;
        },

        showError(message) {
            const grid = document.getElementById('products-grid');
            if (grid) {
                grid.innerHTML = `
                    <div class="error-state" style="grid-column: 1/-1; text-align: center; padding: 3rem;">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">⚠️</div>
                        <h3>Oops!</h3>
                        <p>${message}</p>
                        <button onclick="ProductsPage.init()" class="btn btn-primary" style="margin-top: 1rem;">
                            Try Again
                        </button>
                    </div>
                `;
            }
        },

        async loadProducts() {
            try {
                const response = await fetch('data/products.json');
                if (!response.ok) {
                    throw new Error('Failed to fetch products');
                }
                this.products = await response.json();
                this.filteredProducts = [...this.products];
            } catch (error) {
                console.error('Load products error:', error);
                throw error;
            }
        },

        renderCategories() {
            const container = document.getElementById('category-filter');
            if (!container) return;

            const categories = [...new Set(this.products.map(p => ({ 
                slug: p.category_slug, 
                name: p.category 
            })))];

            let options = '<option value="">All Categories</option>';
            categories.forEach(cat => {
                options += `<option value="${cat.slug}">${cat.name}</option>`;
            });
            container.innerHTML = options;
        },

        renderProducts() {
            const grid = document.getElementById('products-grid');
            if (!grid) return;

            if (this.filteredProducts.length === 0) {
                grid.innerHTML = `
                    <div class="empty-state" style="grid-column: 1/-1; text-align: center; padding: 3rem;">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">🔍</div>
                        <h3>No products found</h3>
                        <p>Try adjusting your search or filters</p>
                        <button onclick="ProductsPage.resetFilters()" class="btn btn-primary" style="margin-top: 1rem;">
                            Clear Filters
                        </button>
                    </div>
                `;
                return;
            }

            grid.innerHTML = this.filteredProducts.map(product => this.getProductCard(product)).join('');
        },

        getProductCard(product) {
            const price = product.sale_price || product.price;
            const originalPrice = product.sale_price ? product.price : null;
            const discount = originalPrice ? Math.round((1 - product.sale_price / product.price) * 100) : 0;
            
            return `
                <div class="product-card">
                    <div class="product-image">
                        <img src="${product.image || 'https://via.placeholder.com/400x400?text=Product'}" 
                             alt="${product.name}" 
                             loading="lazy"
                             onerror="this.src='https://via.placeholder.com/400x400?text=Product'">
                        ${discount > 0 ? `<span class="product-badge sale">-${discount}%</span>` : ''}
                        ${product.stock_quantity === 0 ? '<span class="product-badge out-of-stock">Out of Stock</span>' : ''}
                        <button class="wishlist-btn" onclick="ProductsPage.toggleWishlist(${product.id})" title="Add to Wishlist">
                            <i class="fas fa-heart"></i>
                        </button>
                    </div>
                    <div class="product-content">
                        <span class="product-category">${product.category}</span>
                        <h3 class="product-title">${product.name}</h3>
                        <p class="product-description">${product.short_description}</p>
                        <div class="product-price">
                            <span class="price-current">${App.formatPrice(price)}</span>
                            ${originalPrice ? `<span class="price-original">${App.formatPrice(originalPrice)}</span>` : ''}
                        </div>
                        <div class="product-stock ${product.stock_quantity > 10 ? 'in-stock' : product.stock_quantity > 0 ? 'low-stock' : 'out-of-stock'}">
                            ${product.stock_quantity > 10 ? '✓ In Stock' : product.stock_quantity > 0 ? `⚠ Only ${product.stock_quantity} left` : '✗ Out of Stock'}
                        </div>
                        <div class="product-actions">
                            <button onclick="ProductsPage.addToCart(${product.id})" 
                                    class="btn btn-primary" 
                                    ${product.stock_quantity === 0 ? 'disabled' : ''}>
                                <i class="fas fa-cart-plus"></i>
                                ${product.stock_quantity === 0 ? 'Out of Stock' : 'Add to Cart'}
                            </button>
                        </div>
                    </div>
                </div>
            `;
        },

        setupEventListeners() {
            // Search
            const searchInput = document.getElementById('search-input');
            if (searchInput) {
                searchInput.addEventListener('input', App.debounce((e) => {
                    this.searchQuery = e.target.value.toLowerCase();
                    this.applyFilters();
                }, 300));
            }

            // Category filter
            const categoryFilter = document.getElementById('category-filter');
            if (categoryFilter) {
                categoryFilter.addEventListener('change', (e) => {
                    this.currentCategory = e.target.value;
                    this.applyFilters();
                });
            }

            // Sort filter
            const sortFilter = document.getElementById('sort-filter');
            if (sortFilter) {
                sortFilter.addEventListener('change', (e) => {
                    this.sortBy = e.target.value;
                    this.applyFilters();
                });
            }

            // View toggle
            const gridViewBtn = document.getElementById('grid-view-btn');
            const listViewBtn = document.getElementById('list-view-btn');
            
            if (gridViewBtn) {
                gridViewBtn.addEventListener('click', () => this.setViewMode('grid'));
            }
            if (listViewBtn) {
                listViewBtn.addEventListener('click', () => this.setViewMode('list'));
            }
        },

        applyFilters() {
            let products = [...this.products];

            // Search filter
            if (this.searchQuery) {
                products = products.filter(p => 
                    p.name?.toLowerCase().includes(this.searchQuery) ||
                    p.description?.toLowerCase().includes(this.searchQuery) ||
                    p.category?.toLowerCase().includes(this.searchQuery)
                );
            }

            // Category filter
            if (this.currentCategory && this.currentCategory !== 'all') {
                products = products.filter(p => p.category_slug === this.currentCategory);
            }

            // Sort
            products.sort((a, b) => {
                switch (this.sortBy) {
                    case 'price-low':
                        return (a.sale_price || a.price) - (b.sale_price || b.price);
                    case 'price-high':
                        return (b.sale_price || b.price) - (a.sale_price || a.price);
                    case 'name':
                    default:
                        return (a.name || '').localeCompare(b.name || '');
                }
            });

            this.filteredProducts = products;
            this.renderProducts();
            this.updateResultsCount();
        },

        updateResultsCount() {
            const countEl = document.getElementById('results-count');
            if (countEl) {
                countEl.textContent = `${this.filteredProducts.length} products found`;
            }
        },

        resetFilters() {
            this.searchQuery = '';
            this.currentCategory = 'all';
            this.sortBy = 'name';

            const searchInput = document.getElementById('search-input');
            const categoryFilter = document.getElementById('category-filter');
            const sortFilter = document.getElementById('sort-filter');

            if (searchInput) searchInput.value = '';
            if (categoryFilter) categoryFilter.value = '';
            if (sortFilter) sortFilter.value = 'name';

            this.applyFilters();
        },

        setViewMode(mode) {
            const grid = document.getElementById('products-grid');
            if (!grid) return;

            if (mode === 'list') {
                grid.style.gridTemplateColumns = '1fr';
                grid.classList.add('view-list');
            } else {
                grid.style.gridTemplateColumns = 'repeat(auto-fill, minmax(280px, 1fr))';
                grid.classList.remove('view-list');
            }

            const gridBtn = document.getElementById('grid-view-btn');
            const listBtn = document.getElementById('list-view-btn');
            
            if (gridBtn) gridBtn.classList.toggle('active', mode === 'grid');
            if (listBtn) listBtn.classList.toggle('active', mode === 'list');
        },

        addToCart(productId) {
            try {
                const product = this.products.find(p => p.id === productId);
                if (!product) {
                    Toast.error('Product not found');
                    return;
                }

                if (product.stock_quantity === 0) {
                    Toast.error('This product is out of stock');
                    return;
                }

                // Add to cart
                let cart = this.getCart();
                const existingItem = cart.find(item => item.id === product.id);

                if (existingItem) {
                    if (existingItem.quantity >= product.stock_quantity) {
                        Toast.warning('Maximum stock reached');
                        return;
                    }
                    existingItem.quantity += 1;
                } else {
                    cart.push({ ...product, quantity: 1 });
                }

                this.saveCart(cart);
                App.updateCartCount();
                
                // Show success modal
                this.showAddedToCartModal(product);
                
            } catch (error) {
                console.error('Add to cart error:', error);
                Toast.error('Failed to add to cart');
            }
        },

        getCart() {
            try {
                const data = localStorage.getItem('aluora_gsl_cart');
                return data ? JSON.parse(data) : [];
            } catch (error) {
                return [];
            }
        },

        saveCart(cart) {
            localStorage.setItem('aluora_gsl_cart', JSON.stringify(cart));
        },

        showAddedToCartModal(product) {
            // Remove existing modal
            const existing = document.getElementById('added-to-cart-modal');
            if (existing) existing.remove();

            const modal = document.createElement('div');
            modal.id = 'added-to-cart-modal';
            modal.className = 'modal-overlay';
            modal.innerHTML = `
                <div class="modal-content" style="background: white; padding: 2rem; border-radius: 16px; max-width: 400px; width: 90%; text-align: center; animation: zoomIn 0.3s ease;">
                    <div style="width: 80px; height: 80px; background: #d4edda; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 2rem;">✓</div>
                    <h3 style="margin-bottom: 0.5rem;">Added to Cart!</h3>
                    <p style="color: #666; margin-bottom: 1rem;">${product.name}</p>
                    <div style="display: flex; gap: 0.5rem; justify-content: center;">
                        <button onclick="document.getElementById('added-to-cart-modal').remove()" class="btn btn-outline" style="flex: 1;">
                            Continue Shopping
                        </button>
                        <a href="cart.html" class="btn btn-primary" style="flex: 1; text-decoration: none;">
                            View Cart
                        </a>
                    </div>
                </div>
            `;

            modal.addEventListener('click', (e) => {
                if (e.target === modal) modal.remove();
            });

            document.body.appendChild(modal);
            
            // Auto close after 5 seconds
            setTimeout(() => modal.remove(), 5000);
        },

        toggleWishlist(productId) {
            try {
                let wishlist = JSON.parse(localStorage.getItem('aluora_gsl_wishlist') || '[]');
                
                if (wishlist.includes(productId)) {
                    wishlist = wishlist.filter(id => id !== productId);
                    Toast.info('Removed from wishlist');
                } else {
                    wishlist.push(productId);
                    Toast.success('Added to wishlist!');
                }
                
                localStorage.setItem('aluora_gsl_wishlist', JSON.stringify(wishlist));
            } catch (error) {
                console.error('Wishlist error:', error);
            }
        }
    };

    // Initialize
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => ProductsPage.init());
    } else {
        ProductsPage.init();
    }

    window.ProductsPage = ProductsPage;
})();
