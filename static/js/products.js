/**
 * Products Page JavaScript
 */

let allProducts = [];

document.addEventListener('DOMContentLoaded', function() {
    loadProducts();
    initFilters();
});

/**
 * Load all products
 */
async function loadProducts() {
    const grid = document.getElementById('products-grid');
    if (!grid) return;
    
    grid.innerHTML = `
        <div style="grid-column: 1/-1; text-align: center; padding: 60px;">
            <div class="spinner" style="margin: 0 auto;"></div>
            <p style="margin-top: 20px; color: var(--gray);">Loading products...</p>
        </div>
    `;
    
    allProducts = await Products.getAll();
    renderProducts(allProducts);
}

/**
 * Render products to grid
 */
function renderProducts(products) {
    const grid = document.getElementById('products-grid');
    if (!grid) return;
    
    if (products.length === 0) {
        grid.innerHTML = `
            <div class="empty-state" style="grid-column: 1/-1;">
                <div class="empty-state-icon">🔍</div>
                <h3>No products found</h3>
                <p>Try adjusting your search or filters.</p>
            </div>
        `;
        return;
    }
    
    grid.innerHTML = products.map(product => `
        <div class="product-card">
            <div class="product-image">
                <div class="placeholder-icon"><i class="fa-solid fa-box"></i></div>
                ${product.sale_price ? '<span class="product-badge sale">SALE</span>' : ''}
                ${product.stock_quantity === 0 ? '<span class="product-badge out-of-stock">Out of Stock</span>' : ''}
            </div>
            <div class="product-content">
                <span class="product-category">${product.category_slug}</span>
                <h3 class="product-title">${product.name}</h3>
                <p class="product-description">${product.short_description}</p>
                <div class="product-price">
                    <span class="price-current">${Products.formatPrice(product.sale_price || product.price)}</span>
                    ${product.sale_price ? `<span class="price-original">${Products.formatPrice(product.price)}</span>` : ''}
                </div>
                <div class="product-actions">
                    <button onclick="ProductsPage.addToCart(${product.id})" class="btn btn-primary btn-sm" ${product.stock_quantity === 0 ? 'disabled' : ''}>
                        ${product.stock_quantity === 0 ? 'Out of Stock' : 'Add to Cart'}
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

/**
 * Add product to cart
 */
function addToCart(productId) {
    const product = allProducts.find(p => p.id === productId);
    if (product) {
        const result = Cart.addItem(product, 1);
        UI.showToast(result.message, result.success ? 'success' : 'error');
    }
}

/**
 * Initialize filters
 */
function initFilters() {
    const searchInput = document.getElementById('search-input');
    const categoryFilter = document.getElementById('category-filter');
    const sortFilter = document.getElementById('sort-filter');
    
    if (searchInput) {
        searchInput.addEventListener('input', filterProducts);
    }
    
    if (categoryFilter) {
        categoryFilter.addEventListener('change', filterProducts);
    }
    
    if (sortFilter) {
        sortFilter.addEventListener('change', filterProducts);
    }
    
    // Check URL for category filter
    const urlParams = new URLSearchParams(window.location.search);
    const categoryParam = urlParams.get('category');
    if (categoryParam && categoryFilter) {
        categoryFilter.value = categoryParam;
        filterProducts();
    }
}

/**
 * Filter products based on search and category
 */
function filterProducts() {
    const searchInput = document.getElementById('search-input');
    const categoryFilter = document.getElementById('category-filter');
    const sortFilter = document.getElementById('sort-filter');
    
    const search = searchInput ? searchInput.value.toLowerCase() : '';
    const category = categoryFilter ? categoryFilter.value : '';
    const sort = sortFilter ? sortFilter.value : 'name';
    
    let filtered = allProducts.filter(product => {
        const matchesSearch = !search || 
            product.name.toLowerCase().includes(search) || 
            product.description.toLowerCase().includes(search);
        const matchesCategory = !category || product.category_slug === category;
        return matchesSearch && matchesCategory;
    });
    
    // Sort
    if (sort === 'price-low') {
        filtered.sort((a, b) => (a.sale_price || a.price) - (b.sale_price || b.price));
    } else if (sort === 'price-high') {
        filtered.sort((a, b) => (b.sale_price || b.price) - (a.sale_price || a.price));
    } else {
        filtered.sort((a, b) => a.name.localeCompare(b.name));
    }
    
    renderProducts(filtered);
}

// Make function globally available
window.ProductsPage = {
    addToCart: addToCart
};
