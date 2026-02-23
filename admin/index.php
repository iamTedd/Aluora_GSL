<?php
/**
 * Aluora GSL - Admin Panel
 * Comprehensive Management System
 */

require_once '../config.php';

// Check if admin is logged in
if (!isAdmin()) {
    header('Location: ../index.php');
    exit;
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        $pdo = getDBConnection();
        
        switch ($_POST['action']) {
            // Products
            case 'add_product':
                $name = sanitize($_POST['name']);
                $category_id = (int)$_POST['category_id'];
                $price = (float)$_POST['price'];
                $cost_price = (float)$_POST['cost_price'];
                $stock_quantity = (int)$_POST['stock_quantity'];
                $description = sanitize($_POST['description']);
                $short_description = sanitize($_POST['short_description']);
                $sku = sanitize($_POST['sku']);
                $featured = isset($_POST['featured']) ? 1 : 0;
                $status = sanitize($_POST['status']);
                $image_url = sanitize($_POST['image_url'] ?? '');
                
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
                
                $stmt = $pdo->prepare("INSERT INTO products (name, slug, category_id, price, cost_price, stock_quantity, description, short_description, sku, featured, status, images) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $slug, $category_id, $price, $cost_price, $stock_quantity, $description, $short_description, $sku, $featured, $status, $image_url]);
                
                logActivity($_SESSION['user_id'], 'add_product', "Added product: $name");
                echo jsonResponse(['success' => true, 'message' => 'Product added successfully!']);
                break;
                
            case 'update_product':
                $id = (int)$_POST['id'];
                $name = sanitize($_POST['name']);
                $category_id = (int)$_POST['category_id'];
                $price = (float)$_POST['price'];
                $cost_price = (float)$_POST['cost_price'];
                $stock_quantity = (int)$_POST['stock_quantity'];
                $description = sanitize($_POST['description']);
                $short_description = sanitize($_POST['short_description']);
                $featured = isset($_POST['featured']) ? 1 : 0;
                $status = sanitize($_POST['status']);
                $image_url = sanitize($_POST['image_url'] ?? '');
                
                $stmt = $pdo->prepare("UPDATE products SET name = ?, category_id = ?, price = ?, cost_price = ?, stock_quantity = ?, description = ?, short_description = ?, featured = ?, status = ?, images = ? WHERE id = ?");
                $stmt->execute([$name, $category_id, $price, $cost_price, $stock_quantity, $description, $short_description, $featured, $status, $image_url, $id]);
                
                logActivity($_SESSION['user_id'], 'update_product', "Updated product ID: $id");
                echo jsonResponse(['success' => true, 'message' => 'Product updated successfully!']);
                break;
                
            case 'delete_product':
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
                $stmt->execute([$id]);
                
                logActivity($_SESSION['user_id'], 'delete_product', "Deleted product ID: $id");
                echo jsonResponse(['success' => true, 'message' => 'Product deleted successfully!']);
                break;
                
            // Categories
            case 'add_category':
                $name = sanitize($_POST['name']);
                $description = sanitize($_POST['description']);
                $icon = sanitize($_POST['icon']);
                
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
                
                $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description, icon) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $slug, $description, $icon]);
                
                echo jsonResponse(['success' => true, 'message' => 'Category added successfully!']);
                break;
                
            case 'update_category':
                $id = (int)$_POST['id'];
                $name = sanitize($_POST['name']);
                $description = sanitize($_POST['description']);
                $icon = sanitize($_POST['icon']);
                $status = sanitize($_POST['status']);
                
                $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ?, icon = ?, status = ? WHERE id = ?");
                $stmt->execute([$name, $description, $icon, $status, $id]);
                
                echo jsonResponse(['success' => true, 'message' => 'Category updated successfully!']);
                break;
                
            case 'delete_category':
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
                $stmt->execute([$id]);
                
                echo jsonResponse(['success' => true, 'message' => 'Category deleted successfully!']);
                break;
                
            // Orders
            case 'update_order_status':
                $id = (int)$_POST['id'];
                $status = sanitize($_POST['status']);
                
                $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
                $stmt->execute([$status, $id]);
                
                // Get user info for notification
                $stmt = $pdo->prepare("SELECT user_id, order_number FROM orders WHERE id = ?");
                $stmt->execute([$id]);
                $order = $stmt->fetch();
                
                if ($order) {
                    createNotification($order['user_id'], 'order_status', 'Order Status Updated', "Your order #{$order['order_number']} status has been updated to: $status");
                }
                
                echo jsonResponse(['success' => true, 'message' => 'Order status updated!']);
                break;
                
            // Tenders
            case 'update_tender':
                $id = (int)$_POST['id'];
                $status = sanitize($_POST['status']);
                $admin_notes = sanitize($_POST['admin_notes']);
                $quoted_price = (float)$_POST['quoted_price'];
                
                $stmt = $pdo->prepare("UPDATE tenders SET status = ?, admin_notes = ?, quoted_price = ? WHERE id = ?");
                $stmt->execute([$status, $admin_notes, $quoted_price, $id]);
                
                // Notify user
                $stmt = $pdo->prepare("SELECT user_id, tender_number FROM tenders WHERE id = ?");
                $stmt->execute([$id]);
                $tender = $stmt->fetch();
                
                if ($tender) {
                    createNotification($tender['user_id'], 'tender_update', 'Tender Status Updated', "Your tender #{$tender['tender_number']} has been $status");
                }
                
                echo jsonResponse(['success' => true, 'message' => 'Tender updated successfully!']);
                break;
                
            // Users
            case 'update_user':
                $id = (int)$_POST['id'];
                $status = sanitize($_POST['status']);
                $role = sanitize($_POST['role']);
                
                $stmt = $pdo->prepare("UPDATE users SET status = ?, role = ? WHERE id = ?");
                $stmt->execute([$status, $role, $id]);
                
                echo jsonResponse(['success' => true, 'message' => 'User updated successfully!']);
                break;
                
            // Dashboard stats
            case 'get_stats':
                $stats = [
                    'total_orders' => $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
                    'pending_orders' => $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn(),
                    'total_products' => $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn(),
                    'low_stock' => $pdo->query("SELECT COUNT(*) FROM products WHERE stock_quantity < low_stock_threshold")->fetchColumn(),
                    'total_users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
                    'total_customers' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn(),
                    'total_staff' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'staff'")->fetchColumn(),
                    'total_managers' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'manager'")->fetchColumn(),
                    'total_vendors' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'vendor'")->fetchColumn(),
                    'total_accountants' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'accountant'")->fetchColumn(),
                    'total_delivery' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'delivery_person'")->fetchColumn(),
                    'total_tenders' => $pdo->query("SELECT COUNT(*) FROM tenders")->fetchColumn(),
                    'pending_tenders' => $pdo->query("SELECT COUNT(*) FROM tenders WHERE status = 'pending'")->fetchColumn(),
                    'total_revenue' => $pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE payment_status = 'paid'")->fetchColumn()
                ];
                
                echo jsonResponse(['success' => true, 'stats' => $stats]);
                break;
                
            // Get single order
            case 'get_order':
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("SELECT o.*, u.first_name, u.last_name, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = ?");
                $stmt->execute([$id]);
                $order = $stmt->fetch();
                echo jsonResponse(['success' => true, 'order' => $order]);
                break;
                
            // Get single tender
            case 'get_tender':
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("SELECT t.*, u.first_name, u.last_name, u.email FROM tenders t LEFT JOIN users u ON t.user_id = u.id WHERE t.id = ?");
                $stmt->execute([$id]);
                $tender = $stmt->fetch();
                echo jsonResponse(['success' => true, 'tender' => $tender]);
                break;
                
            // Get single user
            case 'get_user':
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$id]);
                $user = $stmt->fetch();
                echo jsonResponse(['success' => true, 'user' => $user]);
                break;
                
            // Delete order
            case 'delete_order':
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
                $stmt->execute([$id]);
                echo jsonResponse(['success' => true, 'message' => 'Order deleted successfully!']);
                break;
                
            // Delete tender
            case 'delete_tender':
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("DELETE FROM tenders WHERE id = ?");
                $stmt->execute([$id]);
                echo jsonResponse(['success' => true, 'message' => 'Tender deleted successfully!']);
                break;
                
            // Delete user
            case 'delete_user':
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$id]);
                echo jsonResponse(['success' => true, 'message' => 'User deleted successfully!']);
                break;
                
            default:
                echo jsonResponse(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        error_log("Admin AJAX Error: " . $e->getMessage());
        echo jsonResponse(['success' => false, 'message' => 'An error occurred']);
    }
    exit;
}

// Get current section
$section = $_GET['section'] ?? 'dashboard';

// Get data for dashboard
try {
    $pdo = getDBConnection();
    
    // Stats
    $stats = [
        'total_orders' => $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
        'pending_orders' => $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn(),
        'total_products' => $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn(),
        'low_stock' => $pdo->query("SELECT COUNT(*) FROM products WHERE stock_quantity < low_stock_threshold")->fetchColumn(),
        'total_users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        'total_customers' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn(),
        'total_staff' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'staff'")->fetchColumn(),
        'total_managers' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'manager'")->fetchColumn(),
        'total_vendors' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'vendor'")->fetchColumn(),
        'total_accountants' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'accountant'")->fetchColumn(),
        'total_delivery' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'delivery_person'")->fetchColumn(),
        'total_tenders' => $pdo->query("SELECT COUNT(*) FROM tenders")->fetchColumn(),
        'pending_tenders' => $pdo->query("SELECT COUNT(*) FROM tenders WHERE status = 'pending'")->fetchColumn()
    ];
    
    // Recent orders
    $recent_orders = $pdo->query("SELECT o.*, u.first_name, u.last_name, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 10")->fetchAll();
    
    // Recent tenders
    $recent_tenders = $pdo->query("SELECT t.*, u.first_name, u.last_name FROM tenders t LEFT JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC LIMIT 10")->fetchAll();
    
    // Products
    $products = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC LIMIT 50")->fetchAll();
    
    // Categories
    $categories = $pdo->query("SELECT * FROM categories ORDER BY sort_order")->fetchAll();
    
    // Users
    $users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 20")->fetchAll();
    
    // Role-specific users
    $managers = $pdo->query("SELECT * FROM users WHERE role = 'manager' ORDER BY created_at DESC")->fetchAll();
    $vendors = $pdo->query("SELECT * FROM users WHERE role = 'vendor' ORDER BY created_at DESC")->fetchAll();
    $accountants = $pdo->query("SELECT * FROM users WHERE role = 'accountant' ORDER BY created_at DESC")->fetchAll();
    $delivery = $pdo->query("SELECT * FROM users WHERE role = 'delivery_person' ORDER BY created_at DESC")->fetchAll();
    $customers = $pdo->query("SELECT * FROM users WHERE role = 'customer' ORDER BY created_at DESC")->fetchAll();
    $staff = $pdo->query("SELECT * FROM users WHERE role = 'staff' ORDER BY created_at DESC")->fetchAll();
    
    // Orders
    $orders = $pdo->query("SELECT o.*, u.first_name, u.last_name, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 50")->fetchAll();
    
    // Tenders
    $tenders = $pdo->query("SELECT t.*, u.first_name, u.last_name, u.email FROM tenders t LEFT JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC LIMIT 50")->fetchAll();
    
} catch (Exception $e) {
    error_log("Admin Data Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Aluora GSL</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <!-- Sidebar -->
    <aside class="dashboard-sidebar">
        <div class="sidebar-header">
            <a href="../index.php" class="logo">
                <i class="fas fa-box-open"></i>
                <span>Aluora<span>GSL</span></span>
            </a>
            <span class="admin-badge">ADMIN</span>
        </div>
        
        <nav class="sidebar-nav">
            <a href="?section=dashboard" class="<?php echo $section === 'dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-dashboard"></i>
                <span>Dashboard</span>
            </a>
            <a href="?section=orders" class="<?php echo $section === 'orders' ? 'active' : ''; ?>">
                <i class="fas fa-shopping-bag"></i>
                <span>Orders</span>
                <?php if ($stats['pending_orders'] > 0): ?>
                <span class="badge"><?php echo $stats['pending_orders']; ?></span>
                <?php endif; ?>
            </a>
            <a href="?section=products" class="<?php echo $section === 'products' ? 'active' : ''; ?>">
                <i class="fas fa-box"></i>
                <span>Products</span>
                <?php if ($stats['low_stock'] > 0): ?>
                <span class="badge warning"><?php echo $stats['low_stock']; ?></span>
                <?php endif; ?>
            </a>
            <a href="?section=categories" class="<?php echo $section === 'categories' ? 'active' : ''; ?>">
                <i class="fas fa-tags"></i>
                <span>Categories</span>
            </a>
            <a href="?section=tenders" class="<?php echo $section === 'tenders' ? 'active' : ''; ?>">
                <i class="fas fa-file-contract"></i>
                <span>Tenders</span>
                <?php if ($stats['pending_tenders'] > 0): ?>
                <span class="badge"><?php echo $stats['pending_tenders']; ?></span>
                <?php endif; ?>
            </a>
            <a href="?section=users" class="<?php echo $section === 'users' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span>All Users</span>
            </a>
            <a href="?section=managers" class="<?php echo $section === 'managers' ? 'active' : ''; ?>">
                <i class="fas fa-user-tie"></i>
                <span>Managers</span>
                <?php if ($stats['total_managers'] > 0): ?>
                <span class="badge"><?php echo $stats['total_managers']; ?></span>
                <?php endif; ?>
            </a>
            <a href="?section=vendors" class="<?php echo $section === 'vendors' ? 'active' : ''; ?>">
                <i class="fas fa-store"></i>
                <span>Vendors</span>
                <?php if ($stats['total_vendors'] > 0): ?>
                <span class="badge"><?php echo $stats['total_vendors']; ?></span>
                <?php endif; ?>
            </a>
            <a href="?section=accountants" class="<?php echo $section === 'accountants' ? 'active' : ''; ?>">
                <i class="fas fa-calculator"></i>
                <span>Accountants</span>
                <?php if ($stats['total_accountants'] > 0): ?>
                <span class="badge"><?php echo $stats['total_accountants']; ?></span>
                <?php endif; ?>
            </a>
            <a href="?section=delivery" class="<?php echo $section === 'delivery' ? 'active' : ''; ?>">
                <i class="fas fa-truck"></i>
                <span>Delivery</span>
                <?php if ($stats['total_delivery'] > 0): ?>
                <span class="badge"><?php echo $stats['total_delivery']; ?></span>
                <?php endif; ?>
            </a>
            <a href="?section=customers" class="<?php echo $section === 'customers' ? 'active' : ''; ?>">
                <i class="fas fa-user"></i>
                <span>Customers</span>
                <?php if ($stats['total_customers'] > 0): ?>
                <span class="badge"><?php echo $stats['total_customers']; ?></span>
                <?php endif; ?>
            </a>
            <a href="?section=staff" class="<?php echo $section === 'staff' ? 'active' : ''; ?>">
                <i class="fas fa-user-gear"></i>
                <span>Staff</span>
                <?php if ($stats['total_staff'] > 0): ?>
                <span class="badge"><?php echo $stats['total_staff']; ?></span>
                <?php endif; ?>
            </a>
            <a href="?section=support" class="<?php echo $section === 'support' ? 'active' : ''; ?>">
                <i class="fas fa-headset"></i>
                <span>Support</span>
            </a>
            <a href="?section=settings" class="<?php echo $section === 'settings' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </nav>
        
        <div class="sidebar-footer">
            <a href="../logout.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="dashboard-main">
        <!-- Header -->
        <header class="dashboard-header">
            <h1><?php echo ucfirst($section); ?></h1>
            <div class="header-actions">
                <div class="user-menu">
                    <button class="user-btn">
                        <div class="user-avatar"><?php echo substr($_SESSION['first_name'], 0, 1); ?></div>
                        <span><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . ($_SESSION['last_name'] ?? '')); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
            </div>
        </header>

        <!-- Content -->
        <div class="dashboard-content">
            <?php if ($section === 'dashboard'): ?>
            <!-- Dashboard -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon orders"><i class="fas fa-shopping-bag"></i></div>
                    <div class="stat-info">
                        <span class="stat-value"><?php echo $stats['total_orders']; ?></span>
                        <span class="stat-label">Total Orders</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon pending"><i class="fas fa-clock"></i></div>
                    <div class="stat-info">
                        <span class="stat-value"><?php echo $stats['pending_orders']; ?></span>
                        <span class="stat-label">Pending Orders</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon products"><i class="fas fa-box"></i></div>
                    <div class="stat-info">
                        <span class="stat-value"><?php echo $stats['total_products']; ?></span>
                        <span class="stat-label">Products</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon stock"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="stat-info">
                        <span class="stat-value"><?php echo $stats['low_stock']; ?></span>
                        <span class="stat-label">Low Stock</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon users"><i class="fas fa-users"></i></div>
                    <div class="stat-info">
                        <span class="stat-value"><?php echo $stats['total_users']; ?></span>
                        <span class="stat-label">Customers</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon tenders"><i class="fas fa-file-contract"></i></div>
                    <div class="stat-info">
                        <span class="stat-value"><?php echo $stats['total_tenders']; ?></span>
                        <span class="stat-label">Tenders</span>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Recent Orders</h3>
                        <a href="?section=orders" class="view-all">View All</a>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($recent_orders, 0, 5) as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                                <td><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
                                <td>KES <?php echo number_format($order['total'], 2); ?></td>
                                <td><span class="status-badge <?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Recent Tenders</h3>
                        <a href="?section=tenders" class="view-all">View All</a>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Tender #</th>
                                <th>Customer</th>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($recent_tenders, 0, 5) as $tender): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($tender['tender_number']); ?></td>
                                <td><?php echo htmlspecialchars($tender['first_name'] . ' ' . $tender['last_name']); ?></td>
                                <td><?php echo htmlspecialchars(substr($tender['title'], 0, 30)) . '...'; ?></td>
                                <td><span class="status-badge <?php echo $tender['status']; ?>"><?php echo ucfirst($tender['status']); ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($tender['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <?php elseif ($section === 'products'): ?>
            <!-- Products -->
            <div class="section-header">
                <button class="btn btn-primary" onclick="resetProductForm(); openModal('productModal')">
                    <i class="fas fa-plus"></i> Add Product
                </button>
            </div>
            
            <div class="table-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td>
                                <div class="product-cell">
                                    <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                    <small><?php echo htmlspecialchars($product['sku']); ?></small>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></td>
                            <td>KES <?php echo number_format($product['price'], 2); ?></td>
                            <td>
                                <?php if ($product['stock_quantity'] == 0): ?>
                                <span class="stock-badge out">Out of Stock</span>
                                <?php elseif ($product['stock_quantity'] < $product['low_stock_threshold']): ?>
                                <span class="stock-badge low">Low (<?php echo $product['stock_quantity']; ?>)</span>
                                <?php else: ?>
                                <span class="stock-badge in"><?php echo $product['stock_quantity']; ?> in stock</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="status-badge <?php echo $product['status']; ?>"><?php echo ucfirst($product['status']); ?></span></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon" onclick="editProduct(<?php echo $product['id']; ?>)" title="Edit Product"><i class="fas fa-edit"></i></button>
                                    <button class="btn-icon danger" onclick="deleteProduct(<?php echo $product['id']; ?>)" title="Delete Product"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php elseif ($section === 'orders'): ?>
            <!-- Orders -->
            <div class="table-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                            <td>
                                <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?>
                                <small><?php echo htmlspecialchars($order['email']); ?></small>
                            </td>
                            <td><?php 
                                $items = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
                                $items->execute([$order['id']]);
                                echo $items->rowCount() . ' items';
                            ?></td>
                            <td>KES <?php echo number_format($order['total'], 2); ?></td>
                            <td>
                                <select class="status-select" onchange="updateOrderStatus(<?php echo $order['id']; ?>, this.value)">
                                    <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="confirmed" <?php echo $order['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                    <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                    <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                    <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </td>
                            <td><span class="status-badge <?php echo $order['payment_status']; ?>"><?php echo ucfirst($order['payment_status']); ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon" onclick="viewOrder(<?php echo $order['id']; ?>)" title="View Order Details"><i class="fas fa-eye"></i></button>
                                    <button class="btn-icon" onclick="updateOrderStatus(<?php echo $order['id']; ?>)" title="Update Status"><i class="fas fa-sync-alt"></i></button>
                                    <button class="btn-icon danger" onclick="deleteOrder(<?php echo $order['id']; ?>)" title="Delete Order"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php elseif ($section === 'tenders'): ?>
            <!-- Tenders -->
            <div class="table-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tender #</th>
                            <th>Customer</th>
                            <th>Title</th>
                            <th>Budget</th>
                            <th>Deadline</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tenders as $tender): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($tender['tender_number']); ?></strong></td>
                            <td>
                                <?php echo htmlspecialchars($tender['first_name'] . ' ' . $tender['last_name']); ?>
                                <small><?php echo htmlspecialchars($tender['email']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($tender['title']); ?></td>
                            <td><?php echo htmlspecialchars($tender['budget_range'] ?? 'Not specified'); ?></td>
                            <td><?php echo date('M d, Y', strtotime($tender['deadline'])); ?></td>
                            <td><span class="status-badge <?php echo $tender['status']; ?>"><?php echo ucfirst($tender['status']); ?></span></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon" onclick="viewTender(<?php echo $tender['id']; ?>)" title="View Tender Details"><i class="fas fa-eye"></i></button>
                                    <button class="btn-icon" onclick="updateTenderStatus(<?php echo $tender['id']; ?>)" title="Update Status & Quote"><i class="fas fa-edit"></i></button>
                                    <button class="btn-icon danger" onclick="deleteTender(<?php echo $tender['id']; ?>)" title="Delete Tender"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php elseif ($section === 'users'): ?>
            <!-- Users -->
            <div class="table-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Company</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                            <td><?php echo htmlspecialchars($user['company'] ?? '-'); ?></td>
                            <td>
                                <select class="status-select" onchange="updateUser(<?php echo $user['id']; ?>, 'role', this.value)">
                                    <option value="customer" <?php echo $user['role'] === 'customer' ? 'selected' : ''; ?>>Customer</option>
                                    <option value="staff" <?php echo $user['role'] === 'staff' ? 'selected' : ''; ?>>Staff</option>
                                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    <option value="manager" <?php echo $user['role'] === 'manager' ? 'selected' : ''; ?>>Manager</option>
                                    <option value="vendor" <?php echo $user['role'] === 'vendor' ? 'selected' : ''; ?>>Vendor</option>
                                    <option value="accountant" <?php echo $user['role'] === 'accountant' ? 'selected' : ''; ?>>Accountant</option>
                                    <option value="delivery_person" <?php echo $user['role'] === 'delivery_person' ? 'selected' : ''; ?>>Delivery Person</option>
                                </select>
                            </td>
                            <td><span class="status-badge <?php echo $user['status']; ?>"><?php echo ucfirst($user['status']); ?></span></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon" onclick="viewUser(<?php echo $user['id']; ?>)" title="View User Details"><i class="fas fa-eye"></i></button>
                                    <button class="btn-icon" onclick="changeUserRole(<?php echo $user['id']; ?>)" title="Change Role"><i class="fas fa-user-cog"></i></button>
                                    <button class="btn-icon" onclick="toggleUserStatus(<?php echo $user['id']; ?>, '<?php echo $user['status']; ?>')" title="<?php echo $user['status'] === 'active' ? 'Suspend User' : 'Activate User'; ?>">
                                        <i class="fas fa-<?php echo $user['status'] === 'active' ? 'ban' : 'check-circle'; ?>"></i>
                                    </button>
                                    <button class="btn-icon danger" onclick="deleteUser(<?php echo $user['id']; ?>)" title="Delete User"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php elseif ($section === 'managers'): ?>
            <!-- Managers -->
            <div class="section-header">
                <h2><i class="fas fa-user-tie"></i> Managers</h2>
                <span class="badge"><?php echo count($managers); ?> total</span>
            </div>
            <div class="table-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Company</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($managers as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                            <td><?php echo htmlspecialchars($user['company'] ?? '-'); ?></td>
                            <td><span class="status-badge <?php echo $user['status']; ?>"><?php echo ucfirst($user['status']); ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon" onclick="viewUser(<?php echo $user['id']; ?>)" title="View Details"><i class="fas fa-eye"></i></button>
                                    <button class="btn-icon" onclick="changeUserRole(<?php echo $user['id']; ?>)" title="Change Role"><i class="fas fa-user-cog"></i></button>
                                    <button class="btn-icon" onclick="toggleUserStatus(<?php echo $user['id']; ?>, '<?php echo $user['status']; ?>')" title="<?php echo $user['status'] === 'active' ? 'Suspend' : 'Activate'; ?>"><i class="fas fa-<?php echo $user['status'] === 'active' ? 'ban' : 'check-circle'; ?>"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($managers)): ?>
                        <tr><td colspan="8" style="text-align:center;padding:30px;">No managers found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php elseif ($section === 'vendors'): ?>
            <!-- Vendors -->
            <div class="section-header">
                <h2><i class="fas fa-store"></i> Vendors</h2>
                <span class="badge"><?php echo count($vendors); ?> total</span>
            </div>
            <div class="table-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Company</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vendors as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                            <td><?php echo htmlspecialchars($user['company'] ?? '-'); ?></td>
                            <td><span class="status-badge <?php echo $user['status']; ?>"><?php echo ucfirst($user['status']); ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon" onclick="viewUser(<?php echo $user['id']; ?>)" title="View Details"><i class="fas fa-eye"></i></button>
                                    <button class="btn-icon" onclick="changeUserRole(<?php echo $user['id']; ?>)" title="Change Role"><i class="fas fa-user-cog"></i></button>
                                    <button class="btn-icon" onclick="toggleUserStatus(<?php echo $user['id']; ?>, '<?php echo $user['status']; ?>')" title="<?php echo $user['status'] === 'active' ? 'Suspend' : 'Activate'; ?>"><i class="fas fa-<?php echo $user['status'] === 'active' ? 'ban' : 'check-circle'; ?>"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($vendors)): ?>
                        <tr><td colspan="8" style="text-align:center;padding:30px;">No vendors found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php elseif ($section === 'accountants'): ?>
            <!-- Accountants -->
            <div class="section-header">
                <h2><i class="fas fa-calculator"></i> Accountants</h2>
                <span class="badge"><?php echo count($accountants); ?> total</span>
            </div>
            <div class="table-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Company</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($accountants as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                            <td><?php echo htmlspecialchars($user['company'] ?? '-'); ?></td>
                            <td><span class="status-badge <?php echo $user['status']; ?>"><?php echo ucfirst($user['status']); ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon" onclick="viewUser(<?php echo $user['id']; ?>)" title="View Details"><i class="fas fa-eye"></i></button>
                                    <button class="btn-icon" onclick="changeUserRole(<?php echo $user['id']; ?>)" title="Change Role"><i class="fas fa-user-cog"></i></button>
                                    <button class="btn-icon" onclick="toggleUserStatus(<?php echo $user['id']; ?>, '<?php echo $user['status']; ?>')" title="<?php echo $user['status'] === 'active' ? 'Suspend' : 'Activate'; ?>"><i class="fas fa-<?php echo $user['status'] === 'active' ? 'ban' : 'check-circle'; ?>"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($accountants)): ?>
                        <tr><td colspan="8" style="text-align:center;padding:30px;">No accountants found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php elseif ($section === 'delivery'): ?>
            <!-- Delivery Personnel -->
            <div class="section-header">
                <h2><i class="fas fa-truck"></i> Delivery Personnel</h2>
                <span class="badge"><?php echo count($delivery); ?> total</span>
            </div>
            <div class="table-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Company</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($delivery as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                            <td><?php echo htmlspecialchars($user['company'] ?? '-'); ?></td>
                            <td><span class="status-badge <?php echo $user['status']; ?>"><?php echo ucfirst($user['status']); ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon" onclick="viewUser(<?php echo $user['id']; ?>)" title="View Details"><i class="fas fa-eye"></i></button>
                                    <button class="btn-icon" onclick="changeUserRole(<?php echo $user['id']; ?>)" title="Change Role"><i class="fas fa-user-cog"></i></button>
                                    <button class="btn-icon" onclick="toggleUserStatus(<?php echo $user['id']; ?>, '<?php echo $user['status']; ?>')" title="<?php echo $user['status'] === 'active' ? 'Suspend' : 'Activate'; ?>"><i class="fas fa-<?php echo $user['status'] === 'active' ? 'ban' : 'check-circle'; ?>"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($delivery)): ?>
                        <tr><td colspan="8" style="text-align:center;padding:30px;">No delivery personnel found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php elseif ($section === 'customers'): ?>
            <!-- Customers -->
            <div class="section-header">
                <h2><i class="fas fa-user"></i> Customers</h2>
                <span class="badge"><?php echo count($customers); ?> total</span>
            </div>
            <div class="table-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Company</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                            <td><?php echo htmlspecialchars($user['company'] ?? '-'); ?></td>
                            <td><span class="status-badge <?php echo $user['status']; ?>"><?php echo ucfirst($user['status']); ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon" onclick="viewUser(<?php echo $user['id']; ?>)" title="View Details"><i class="fas fa-eye"></i></button>
                                    <button class="btn-icon" onclick="changeUserRole(<?php echo $user['id']; ?>)" title="Change Role"><i class="fas fa-user-cog"></i></button>
                                    <button class="btn-icon" onclick="toggleUserStatus(<?php echo $user['id']; ?>, '<?php echo $user['status']; ?>')" title="<?php echo $user['status'] === 'active' ? 'Suspend' : 'Activate'; ?>"><i class="fas fa-<?php echo $user['status'] === 'active' ? 'ban' : 'check-circle'; ?>"></i></button>
                                    <button class="btn-icon danger" onclick="deleteUser(<?php echo $user['id']; ?>)" title="Delete User"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($customers)): ?>
                        <tr><td colspan="8" style="text-align:center;padding:30px;">No customers found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php elseif ($section === 'staff'): ?>
            <!-- Staff -->
            <div class="section-header">
                <h2><i class="fas fa-user-gear"></i> Staff</h2>
                <span class="badge"><?php echo count($staff); ?> total</span>
            </div>
            <div class="table-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Company</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($staff as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                            <td><?php echo htmlspecialchars($user['company'] ?? '-'); ?></td>
                            <td><span class="status-badge <?php echo $user['status']; ?>"><?php echo ucfirst($user['status']); ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon" onclick="viewUser(<?php echo $user['id']; ?>)" title="View Details"><i class="fas fa-eye"></i></button>
                                    <button class="btn-icon" onclick="changeUserRole(<?php echo $user['id']; ?>)" title="Change Role"><i class="fas fa-user-cog"></i></button>
                                    <button class="btn-icon" onclick="toggleUserStatus(<?php echo $user['id']; ?>, '<?php echo $user['status']; ?>')" title="<?php echo $user['status'] === 'active' ? 'Suspend' : 'Activate'; ?>"><i class="fas fa-<?php echo $user['status'] === 'active' ? 'ban' : 'check-circle'; ?>"></i></button>
                                    <button class="btn-icon danger" onclick="deleteUser(<?php echo $user['id']; ?>)" title="Delete User"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($staff)): ?>
                        <tr><td colspan="8" style="text-align:center;padding:30px;">No staff found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php elseif ($section === 'categories'): ?>
            <!-- Categories -->
            <div class="section-header">
                <button class="btn btn-primary" onclick="openModal('categoryModal')">
                    <i class="fas fa-plus"></i> Add Category
                </button>
            </div>
            
            <div class="categories-admin-grid">
                <?php foreach ($categories as $cat): ?>
                <div class="category-admin-card">
                    <div class="cat-icon"><i class="fas <?php echo $cat['icon'] ?: 'fa-tag'; ?>"></i></div>
                    <div class="cat-info">
                        <h4><?php echo htmlspecialchars($cat['name']); ?></h4>
                        <p><?php echo htmlspecialchars($cat['description'] ?? ''); ?></p>
                        <span class="status-badge <?php echo $cat['status']; ?>"><?php echo ucfirst($cat['status']); ?></span>
                    </div>
                    <div class="cat-actions">
                        <button class="btn-icon" onclick="editCategory(<?php echo $cat['id']; ?>)" title="Edit Category"><i class="fas fa-edit"></i></button>
                        <button class="btn-icon danger" onclick="deleteCategory(<?php echo $cat['id']; ?>)" title="Delete Category"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php else: ?>
            <div class="coming-soon">
                <i class="fas fa-cog"></i>
                <h2>Coming Soon</h2>
                <p>This section is under development.</p>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Product Modal -->
    <div class="modal" id="productModal">
        <div class="modal-content modal-large">
            <button class="modal-close" onclick="closeModal('productModal')"><i class="fas fa-times"></i></button>
            <div class="modal-header">
                <h2>Add New Product</h2>
            </div>
            <form id="productForm" onsubmit="saveProduct(event)">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Product Name *</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Category *</label>
                        <select name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Price (KES) *</label>
                        <input type="number" name="price" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Cost Price (KES)</label>
                        <input type="number" name="cost_price" step="0.01">
                    </div>
                    <div class="form-group">
                        <label>Stock Quantity *</label>
                        <input type="number" name="stock_quantity" required>
                    </div>
                    <div class="form-group">
                        <label>SKU</label>
                        <input type="text" name="sku">
                    </div>
                    <div class="form-group">
                        <label>Product Image URL</label>
                        <input type="url" name="image_url" placeholder="https://example.com/image.jpg">
                        <small>Enter image URL or leave empty for default</small>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="out_of_stock">Out of Stock</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="featured">
                            <span>Featured Product</span>
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label>Short Description</label>
                    <input type="text" name="short_description">
                </div>
                <div class="form-group">
                    <label>Full Description</label>
                    <textarea name="description" rows="4"></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-outline" onclick="closeModal('productModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Product</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Category Modal -->
    <div class="modal" id="categoryModal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal('categoryModal')"><i class="fas fa-times"></i></button>
            <div class="modal-header">
                <h2>Add New Category</h2>
            </div>
            <form onsubmit="saveCategory(event)">
                <div class="form-group">
                    <label>Category Name *</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Icon (Font Awesome class)</label>
                    <input type="text" name="icon" placeholder="fa-briefcase">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3"></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-outline" onclick="closeModal('categoryModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Category</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div class="modal" id="orderDetailsModal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal('orderDetailsModal')"><i class="fas fa-times"></i></button>
            <div class="modal-header">
                <h2>Order Details</h2>
            </div>
            <div id="orderDetailsContent" class="modal-body">
                <!-- Content loaded via JS -->
            </div>
        </div>
    </div>

    <!-- Tender Details Modal -->
    <div class="modal" id="tenderDetailsModal">
        <div class="modal-content modal-large">
            <button class="modal-close" onclick="closeModal('tenderDetailsModal')"><i class="fas fa-times"></i></button>
            <div class="modal-header">
                <h2>Tender Details</h2>
            </div>
            <div id="tenderDetailsContent" class="modal-body">
                <!-- Content loaded via JS -->
            </div>
        </div>
    </div>

    <!-- User Details Modal -->
    <div class="modal" id="userDetailsModal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal('userDetailsModal')"><i class="fas fa-times"></i></button>
            <div class="modal-header">
                <h2>User Details</h2>
            </div>
            <div id="userDetailsContent" class="modal-body">
                <!-- Content loaded via JS -->
            </div>
        </div>
    </div>

    <script src="../js/admin.js"></script>
</body>
</html>
