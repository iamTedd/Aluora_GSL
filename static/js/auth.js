/**
 * Auth Pages JavaScript - Login & Register
 */

document.addEventListener('DOMContentLoaded', function() {
    initLoginForm();
    initRegisterForm();
});

/**
 * Initialize login form
 */
function initLoginForm() {
    const form = document.getElementById('login-form');
    if (!form) return;
    
    // Check if already logged in
    if (Auth.isLoggedIn()) {
        window.location.href = 'dashboard.html';
        return;
    }
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        
        if (!email || !password) {
            UI.showToast('Please enter email and password', 'error');
            return;
        }
        
        const result = Auth.login(email, password);
        
        if (result.success) {
            UI.showToast(result.message, 'success');
            setTimeout(() => {
                window.location.href = 'dashboard.html';
            }, 1000);
        } else {
            UI.showToast(result.message, 'error');
        }
    });
}

/**
 * Initialize register form
 */
function initRegisterForm() {
    const form = document.getElementById('register-form');
    if (!form) return;
    
    // Check if already logged in
    if (Auth.isLoggedIn()) {
        window.location.href = 'dashboard.html';
        return;
    }
    
    // Custom validation
    form.addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (password !== confirmPassword) {
            e.preventDefault();
            UI.showToast('Passwords do not match!', 'error');
            return;
        }
        
        if (password.length < 6) {
            e.preventDefault();
            UI.showToast('Password must be at least 6 characters!', 'error');
            return;
        }
        
        const terms = document.getElementById('terms');
        if (!terms || !terms.checked) {
            e.preventDefault();
            UI.showToast('You must agree to the Terms & Privacy Policy', 'error');
            return;
        }
        
        e.preventDefault();
        
        const userData = {
            first_name: document.getElementById('first_name').value,
            last_name: document.getElementById('last_name').value,
            email: document.getElementById('email').value,
            phone: document.getElementById('phone').value,
            company: document.getElementById('company').value,
            password: password
        };
        
        const result = Auth.register(userData);
        
        if (result.success) {
            UI.showToast(result.message, 'success');
            setTimeout(() => {
                window.location.href = 'dashboard.html';
            }, 1000);
        } else {
            UI.showToast(result.message, 'error');
        }
    });
}
