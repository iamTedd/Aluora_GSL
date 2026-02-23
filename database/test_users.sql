-- Test Users for Aluora GSL
-- Password for all users: password123

-- Admin User
INSERT INTO users (email, password, first_name, last_name, phone, role, status) 
VALUES ('admin@aluora.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', '+254700000001', 'admin', 'active')
ON DUPLICATE KEY UPDATE role = 'admin';

-- Staff User
INSERT INTO users (email, password, first_name, last_name, phone, role, status) 
VALUES ('staff@aluora.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Staff', 'Member', '+254700000002', 'staff', 'active')
ON DUPLICATE KEY UPDATE role = 'staff';

-- Manager User
INSERT INTO users (email, password, first_name, last_name, phone, role, status) 
VALUES ('manager@aluora.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Manager', 'User', '+254700000003', 'manager', 'active')
ON DUPLICATE KEY UPDATE role = 'manager';

-- Vendor User
INSERT INTO users (email, password, first_name, last_name, phone, role, status) 
VALUES ('vendor@aluora.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Vendor', 'User', '+254700000004', 'vendor', 'active')
ON DUPLICATE KEY UPDATE role = 'vendor';

-- Accountant User
INSERT INTO users (email, password, first_name, last_name, phone, role, status) 
VALUES ('accountant@aluora.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Accountant', 'User', '+254700000005', 'accountant', 'active')
ON DUPLICATE KEY UPDATE role = 'accountant';

-- Delivery Person User
INSERT INTO users (email, password, first_name, last_name, phone, role, status) 
VALUES ('delivery@aluora.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Delivery', 'Person', '+254700000006', 'delivery_person', 'active')
ON DUPLICATE KEY UPDATE role = 'delivery_person';

-- Customer User
INSERT INTO users (email, password, first_name, last_name, phone, role, status) 
VALUES ('customer@aluora.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Customer', 'User', '+254700000007', 'customer', 'active')
ON DUPLICATE KEY UPDATE role = 'customer';
