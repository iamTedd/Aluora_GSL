/**
 * Aluora GSL - Enhanced JavaScript
 * Advanced Interactive Features
 */

// Preloader
window.addEventListener('load', function() {
    setTimeout(function() {
        document.querySelector('.preloader').classList.add('hidden');
    }, 2000);
});

// Global state
let chatOpen = false;
let productsCache = null;

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    initPreloader();
    initScrollEffects();
    initSearch();
    initProducts();
    initChatWidget();
    initCounters();
    initModals();
    initFormHandlers();
    initNewsletter();
    
    // Check login state sync
    syncLoginState();
});

// Sync localStorage with PHP session
function syncLoginState() {
    const isLoggedInLocal = localStorage.getItem('aluora_logged_in') === 'true';
    const hasBodyClass = document.body.classList.contains('user-logged-in');
    
    // If localStorage says logged in but PHP session says logged out, clear localStorage
    if (isLoggedInLocal && !hasBodyClass) {
        localStorage.removeItem('aluora_logged_in');
        localStorage.removeItem('aluora_user');
    }
    
    // If PHP session says logged in but localStorage doesn't, set localStorage
    if (!isLoggedInLocal && hasBodyClass) {
        localStorage.setItem('aluora_logged_in', 'true');
    }
}

// Preloader
function initPreloader() {
    const progress = document.querySelector('.loader-progress');
    if (progress) {
        setTimeout(() => {
            document.querySelector('.preloader').classList.add('hidden');
        }, 2200);
    }
}

// Scroll Effects
function initScrollEffects() {
    const header = document.getElementById('mainHeader');
    const backToTop = document.getElementById('backToTop');
    
    window.addEventListener('scroll', function() {
        // Header scroll effect
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
        
        // Back to top button
        if (window.scrollY > 300) {
            backToTop.classList.add('visible');
        } else {
            backToTop.classList.remove('visible');
        }
    });
}

// Search Functionality
function initSearch() {
    const searchInput = document.getElementById('globalSearch');
    const searchResults = document.getElementById('searchResults');
    
    if (!searchInput) return;
    
    searchInput.addEventListener('input', debounce(function(e) {
        const query = e.target.value.trim();
        if (query.length < 2) {
            searchResults.classList.remove('active');
            return;
        }
        
        // Mock search results (replace with API call)
        const mockResults = [
            { name: 'Office Supplies', icon: 'fa-briefcase', type: 'category' },
            { name: 'Cleaning Products', icon: 'fa-sparkles', type: 'category' },
            { name: 'Safety Equipment', icon: 'fa-shield-halved', type: 'category' },
            { name: 'Industrial Tools', icon: 'fa-tools', type: 'category' },
            { name: 'A4 Paper Ream', icon: 'fa-file', type: 'product' },
            { name: 'Safety Helmet', icon: 'fa-hard-hat', type: 'product' }
        ].filter(item => item.name.toLowerCase().includes(query.toLowerCase()));
        
        if (mockResults.length > 0) {
            searchResults.innerHTML = mockResults.map(item => `
                <div class="search-result-item">
                    <i class="fas ${item.icon}"></i>
                    <span>${item.name}</span>
                    <small>${item.type}</small>
                </div>
            `).join('');
            searchResults.classList.add('active');
        } else {
            searchResults.classList.remove('active');
        }
    }, 300));
    
    // Close on click outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.classList.remove('active');
        }
    });
}

// Products Loading
function initProducts() {
    loadProducts();
}

async function loadProducts() {
    const grid = document.getElementById('productsGrid');
    if (!grid) return;
    
    try {
        const response = await fetch('api/products.php?featured=1');
        const products = await response.json();
        
        if (products.length > 0) {
            renderProducts(products);
        } else {
            // Fallback demo products
            renderDemoProducts();
        }
    } catch (error) {
        console.log('Using demo products');
        renderDemoProducts();
    }
}

function renderProducts(products) {
    const grid = document.getElementById('productsGrid');
    if (!grid) return;
    
    grid.innerHTML = products.map(product => `
        <div class="product-card" data-id="${product.id}">
            <div class="product-image">
                <span class="product-badge">${product.badge || 'Featured'}</span>
                <img src="${product.image || 'https://via.placeholder.com/300x200'}" alt="${product.name}">
                <div class="product-actions">
                    <button onclick="quickView(${product.id})" title="Quick View"><i class="fas fa-eye"></i></button>
                    <button onclick="addToCart(${product.id})" title="Add to Cart"><i class="fas fa-shopping-cart"></i></button>
                </div>
            </div>
            <div class="product-info">
                <span class="product-category">${product.category || 'General'}</span>
                <h3>${product.name}</h3>
                <p>${product.short_description || ''}</p>
                <div class="product-meta">
                    <span class="product-price">KES ${parseFloat(product.price).toLocaleString()}</span>
                    <button class="btn btn-sm btn-primary" onclick="quickOrder(${product.id})">Order Now</button>
                </div>
            </div>
        </div>
    `).join('');
}

function renderDemoProducts() {
    const grid = document.getElementById('productsGrid');
    if (!grid) return;
    
    const demoProducts = [
        { id: 1, name: 'Premium Office Chair', category: 'Office', price: 15000, image: 'https://images.unsplash.com/photo-1580480055273-228ff5388ef8?w=300', badge: 'Best Seller' },
        { id: 2, name: 'Industrial Cleaning Kit', category: 'Cleaning', price: 8500, image: 'https://images.unsplash.com/photo-1563453392212-326f5e854473?w=300', badge: 'Popular' },
        { id: 3, name: 'Safety Helmet Set', category: 'Safety', price: 3500, image: 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=300', badge: 'New' },
        { id: 4, name: 'Power Drill Kit', category: 'Industrial', price: 25000, image: 'https://images.unsplash.com/photo-1504148455328-c376907d081c?w=300', badge: 'Featured' },
        { id: 5, name: 'Wireless Keyboard', category: 'Electronics', price: 5500, image: 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=300', badge: 'Hot' },
        { id: 6, name: 'Hotel Amenities Set', category: 'Hospitality', price: 12000, image: 'https://images.unsplash.com/photo-1582719508461-905c673771fd?w=300', badge: 'Premium' },
        { id: 7, name: 'Retail Display Stand', category: 'Retail', price: 18000, image: 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=300', badge: 'Sale' },
        { id: 8, name: 'Complete First Aid Kit', category: 'Safety', price: 4500, image: 'https://images.unsplash.com/photo-1603398938378-e54eab446dde?w=300', badge: 'Essential' }
    ];
    
    grid.innerHTML = demoProducts.map(product => `
        <div class="product-card" data-id="${product.id}">
            <div class="product-image">
                <span class="product-badge">${product.badge}</span>
                <img src="${product.image}" alt="${product.name}">
                <div class="product-actions">
                    <button onclick="quickView(${product.id})" title="Quick View"><i class="fas fa-eye"></i></button>
                    <button onclick="addToCart(${product.id})" title="Add to Cart"><i class="fas fa-shopping-cart"></i></button>
                </div>
            </div>
            <div class="product-info">
                <span class="product-category">${product.category}</span>
                <h3>${product.name}</h3>
                <div class="product-meta">
                    <span class="product-price">KES ${product.price.toLocaleString()}</span>
                    <button class="btn btn-sm btn-primary" onclick="quickOrder(${product.id})">Order Now</button>
                </div>
            </div>
        </div>
    `).join('');
}

// Quick View
function quickView(productId) {
    openModal('quickViewModal');
    // Load product details
    const content = document.getElementById('quickViewContent');
    content.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    
    setTimeout(() => {
        content.innerHTML = `
            <button class="modal-close" onclick="closeModal('quickViewModal')"><i class="fas fa-times"></i></button>
            <div style="padding: 40px; text-align: center;">
                <i class="fas fa-box-open" style="font-size: 4rem; color: var(--primary); margin-bottom: 20px;"></i>
                <h2>Product Details</h2>
                <p>Product ID: ${productId}</p>
                <p class="text-muted">Full product details would be loaded from the database.</p>
                <button class="btn btn-primary" onclick="closeModal('quickViewModal')">Close</button>
            </div>
        `;
    }, 500);
}

// Quick Order
function quickOrder(productId) {
    // Check if logged in via localStorage
    const isLoggedIn = localStorage.getItem('aluora_logged_in') === 'true';
    
    if (isLoggedIn) {
        if (confirm('Place a quick order for this product?')) {
            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=quick_order&product_id=' + productId + '&quantity=1'
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showNotification('Order placed! Order number: ' + data.order_number, 'success');
                } else {
                    showNotification(data.message, 'error');
                }
            });
        }
    } else {
        openModal('loginModal');
    }
}

// Add to Cart
function addToCart(productId) {
    const isLoggedIn = localStorage.getItem('aluora_logged_in') === 'true';
    
    if (isLoggedIn) {
        // Add to cart logic
        showNotification('Added to cart!', 'success');
    } else {
        openModal('loginModal');
    }
}

// Chat Widget
function initChatWidget() {
    // Load chat history if exists
}

function toggleChat() {
    const widget = document.getElementById('chatWidget');
    const container = document.getElementById('chatContainer');
    
    chatOpen = !chatOpen;
    container.classList.toggle('active', chatOpen);
    
    if (chatOpen) {
        document.getElementById('chatInput').focus();
    }
}

function handleChatKeyPress(e) {
    if (e.key === 'Enter') {
        sendChatMessage();
    }
}

function sendChatMessage() {
    const input = document.getElementById('chatInput');
    const message = input.value.trim();
    
    if (!message) return;
    
    // Add user message
    addChatMessage(message, 'user');
    input.value = '';
    
    // Show typing indicator
    showTypingIndicator();
    
    // Send to server
    fetch('index.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=chat&message=' + encodeURIComponent(message)
    })
    .then(r => r.json())
    .then(data => {
        removeTypingIndicator();
        
        if (data.success) {
            addChatMessage(data.message, 'bot');
            
            if (data.human_transfer) {
                // Show waiting message
                setTimeout(() => {
                    addChatMessage(data.waiting_message, 'bot');
                }, 1500);
                
                // Create ticket notification
                showNotification('Support ticket created: ' + data.ticket_number, 'success');
            }
        } else {
            addChatMessage('Sorry, something went wrong. Please try again.', 'bot');
        }
    })
    .catch(() => {
        removeTypingIndicator();
        addChatMessage('Sorry, I could not process your request. Please try again.', 'bot');
    });
}

function addChatMessage(message, sender) {
    const container = document.getElementById('chatMessages');
    const div = document.createElement('div');
    div.className = 'chat-message ' + sender;
    div.innerHTML = `<div class="message-content"><p>${message}</p></div>`;
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
}

function showTypingIndicator() {
    const container = document.getElementById('chatMessages');
    const div = document.createElement('div');
    div.className = 'chat-message bot typing';
    div.innerHTML = '<div class="message-content"><i class="fas fa-ellipsis-h"></i></div>';
    div.id = 'typingIndicator';
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
}

function removeTypingIndicator() {
    const indicator = document.getElementById('typingIndicator');
    if (indicator) indicator.remove();
}

// Counter Animation
function initCounters() {
    const counters = document.querySelectorAll('.stat-number[data-count]');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                const target = parseInt(counter.dataset.count);
                animateCounter(counter, target);
                observer.unobserve(counter);
            }
        });
    }, { threshold: 0.5 });
    
    counters.forEach(counter => observer.observe(counter));
}

function animateCounter(element, target) {
    let current = 0;
    const increment = target / 50;
    const duration = 2000;
    const stepTime = duration / 50;
    
    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            element.textContent = target + '+';
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current) + '+';
        }
    }, stepTime);
}

// Modals
function initModals() {
    // Close modal on outside click
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });
    
    // Close on escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal.active').forEach(modal => {
                closeModal(modal.id);
            });
        }
    });
}

function openModal(modalId) {
    // Check if modal requires login
    if (modalId === 'tenderModal') {
        const isLoggedIn = localStorage.getItem('aluora_logged_in') === 'true' || document.body.classList.contains('user-logged-in');
        if (!isLoggedIn) {
            openModal('loginModal');
            showNotification('Please login to submit a tender', 'info');
            return;
        }
    }
    
    document.getElementById(modalId).classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
    document.body.style.overflow = '';
}

// Form Handlers
function initFormHandlers() {
    // Login form
    const loginForm = document.querySelector('#loginModal form');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }
    
    // Register form
    const registerForm = document.querySelector('#registerModal form');
    if (registerForm) {
        registerForm.addEventListener('submit', handleRegister);
    }
    
    // Tender form
    const tenderForm = document.querySelector('#tenderModal form');
    if (tenderForm) {
        tenderForm.addEventListener('submit', submitTender);
    }
}

function handleLogin(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    fetch('auth/login.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Set localStorage for persistence across pages
            localStorage.setItem('aluora_logged_in', 'true');
            if (data.user) {
                localStorage.setItem('aluora_user', JSON.stringify(data.user));
            }
            
            // Close modal and show success
            closeModal('loginModal');
            showNotification('Login successful! Welcome back!', 'success');
            
            // Update UI to show logged in state
            document.body.classList.add('user-logged-in');
            
            // Redirect based on user role (admin gets admin panel, others get dashboard)
            const redirectUrl = data.redirect || 'dashboard.php';
            setTimeout(() => {
                window.location.href = redirectUrl;
            }, 1000);
        } else {
            showNotification(data.message || 'Login failed', 'error');
        }
    })
    .catch(() => {
        // Demo login for testing
        showNotification('Demo: Login successful! Redirecting...', 'success');
        location.reload();
    });
}

function handleRegister(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    if (formData.get('password') !== formData.get('confirm_password')) {
        showNotification('Passwords do not match!', 'warning');
        return;
    }
    
    fetch('auth/register.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showNotification('Registration successful! Please login.', 'success');
            openModal('loginModal');
            closeModal('registerModal');
        } else {
            showNotification(data.message || 'Registration failed', 'error');
        }
    })
    .catch(() => {
        // Demo registration
        showNotification('Demo: Registration successful! Please login.', 'success');
        openModal('loginModal');
        closeModal('registerModal');
    });
}

function submitTender(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    fetch('index.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=submit_tender&' + new URLSearchParams(formData).toString()
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showNotification('Tender submitted successfully! Reference: ' + data.tender_number, 'success');
            closeModal('tenderModal');
            e.target.reset();
        } else {
            showNotification(data.message || 'Failed to submit tender', 'error');
        }
    })
    .catch(() => {
        showNotification('Tender submitted successfully (Demo)!', 'success');
        closeModal('tenderModal');
        e.target.reset();
    });
}

// Newsletter
function initNewsletter() {
    // Already has inline handler
}

function subscribeNewsletter(e) {
    e.preventDefault();
    const email = e.target.querySelector('input').value;
    
    fetch('index.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=newsletter&email=' + encodeURIComponent(email)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showNotification('Thank you for subscribing!', 'success');
            e.target.reset();
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(() => {
        showNotification('Thank you for subscribing! (Demo)', 'success');
        e.target.reset();
    });
}

// Utility Functions
function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function scrollToProducts() {
    document.getElementById('products').scrollIntoView({ behavior: 'smooth' });
}

function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    menu.classList.toggle('active');
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Rating handler
function rateChat(chatId, rating) {
    fetch('index.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=rate_chat&chat_id=' + chatId + '&rating=' + rating
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showNotification('Thank you for your feedback!', 'success');
        }
    });
}

// Modern Toast Notification System
function showNotification(message, type = 'info', title = null) {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    
    const defaultTitles = {
        success: 'Success!',
        error: 'Error',
        warning: 'Warning',
        info: 'Information'
    };
    
    const toastTitle = title || defaultTitles[type] || 'Notification';
    
    const icons = {
        success: '<i class="fas fa-check-circle"></i>',
        error: '<i class="fas fa-times-circle"></i>',
        warning: '<i class="fas fa-exclamation-triangle"></i>',
        info: '<i class="fas fa-info-circle"></i>'
    };
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <div class="toast-icon">${icons[type]}</div>
        <div class="toast-content">
            <div class="toast-title">${toastTitle}</div>
            <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close" onclick="dismissToast(this.parentElement)"><i class="fas fa-times"></i></button>
        <div class="toast-progress"></div>
    `;
    
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('show');
        toast.classList.add('animating');
    }, 10);
    
    setTimeout(() => {
        dismissToast(toast);
    }, 4000);
}

function dismissToast(toast) {
    if (toast.classList.contains('removing')) return;
    
    toast.classList.remove('show', 'animating');
    toast.classList.add('removing');
    
    setTimeout(() => {
        if (toast.parentElement) {
            toast.parentElement.removeChild(toast);
        }
    }, 350);
}

// Global functions for onclick handlers
window.openModal = openModal;
window.closeModal = closeModal;
window.toggleChat = toggleChat;
window.sendChatMessage = sendChatMessage;
window.handleChatKeyPress = handleChatKeyPress;
window.quickView = quickView;
window.quickOrder = quickOrder;
window.addToCart = addToCart;
window.scrollToTop = scrollToTop;
window.scrollToProducts = scrollToProducts;
window.toggleMobileMenu = toggleMobileMenu;
window.submitTender = submitTender;
window.subscribeNewsletter = subscribeNewsletter;
window.rateChat = rateChat;
window.showNotification = showNotification;
window.dismissToast = dismissToast;
