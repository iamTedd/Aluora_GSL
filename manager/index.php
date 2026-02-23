<?php
/**
 * Aluora GSL - Manager Dashboard
 * Manager Portal for managing team, orders, and operations
 */

require_once '../config.php';

// Check if manager is logged in
if (!isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

// Only allow manager, admin, and staff roles
if (!in_array($_SESSION['role'], ['admin', 'staff', 'manager'])) {
    header('Location: ' . getDashboardUrl());
    exit;
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        $pdo = getDBConnection();
        
        switch ($_POST['action']) {
            // Get manager stats
            case 'get_stats':
                $stats = [
                    'total_orders' => $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
                    'pending_orders' => $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn(),
                    'total_products' => $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn(),
                    'low_stock' => $pdo->query("SELECT COUNT(*) FROM products WHERE stock_quantity < low_stock_threshold")->fetchColumn(),
                    'total_users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
                    'total_tenders' => $pdo->query("SELECT COUNT(*) FROM tenders")->fetchColumn(),
                ];
                echo jsonResponse(['success' => true, 'stats' => $stats]);
                break;
                
            default:
                echo jsonResponse(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        error_log("Manager AJAX Error: " . $e->getMessage());
        echo jsonResponse(['success' => false, 'message' => 'An error occurred']);
    }
    exit;
}

// Get current section
$section = $_GET['section'] ?? 'overview';

// Get data
try {
    $pdo = getDBConnection();
    
    // Stats
    $stats = [
        'total_orders' => $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
        'pending_orders' => $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn(),
        'total_products' => $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn(),
        'low_stock' => $pdo->query("SELECT COUNT(*) FROM products WHERE stock_quantity < 10 AND status = 'active'")->fetchColumn(),
    ];
    
    // Recent orders
    $recent_orders = $pdo->query("SELECT o.*, u.first_name, u.last_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 10")->fetchAll();
    
} catch (Exception $e) {
    error_log("Manager Data Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard - Aluora GSL</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <style>
        .manager-badge {
            background: linear-gradient(135deg, #3498db, #2980b9);
            padding: 5px 10px;
            border-radius: 8px;
            font-size: 0.7rem;
            font-weight: 600;
            color: white;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="dashboard-sidebar">
        <div class="sidebar-header">
            <a href="../index.php" class="logo">
                <i class="fas fa-box-open"></i>
                <span>Aluora<span>GSL</span></span>
            </a>
            <span class="manager-badge">MANAGER</span>
        </div>
        
        <nav class="sidebar-nav">
            <a href="index.php" class="<?php echo $section === 'overview' ? 'active' : ''; ?>">
                <i class="fas fa-th-large"></i>
                <span>Overview</span>
            </a>
            <a href="index.php?section=orders" class="<?php echo $section === 'orders' ? 'active' : ''; ?>">
                <i class="fas fa-shopping-bag"></i>
                <span>Orders</span>
            </a>
            <a href="index.php?section=products" class="<?php echo $section === 'products' ? 'active' : ''; ?>">
                <i class="fas fa-box"></i>
                <span>Products</span>
            </a>
            <a href="index.php?section=inventory" class="<?php echo $section === 'inventory' ? 'active' : ''; ?>">
                <i class="fas fa-warehouse"></i>
                <span>Inventory</span>
            </a>
            <a href="index.php?section=reports" class="<?php echo $section === 'reports' ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
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
        <header class="dashboard-header">
            <h1>Manager Dashboard</h1>
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

        <div class="dashboard-content">
            <?php if ($section === 'overview'): ?>
            <!-- Overview -->
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
                        <span class="stat-label">Active Products</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon stock"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="stat-info">
                        <span class="stat-value"><?php echo $stats['low_stock']; ?></span>
                        <span class="stat-label">Low Stock Items</span>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Recent Orders</h3>
                        <a href="index.php?section=orders" class="view-all">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_orders)): ?>
                        <p class="empty-state">No orders yet</p>
                        <?php else: ?>
                        <?php foreach (array_slice($recent_orders, 0, 5) as $order): ?>
                        <div class="order-item">
                            <div class="order-info">
                                <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>
                                <span><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></span>
                            </div>
                            <div class="order-meta">
                                <span class="order-total">KES <?php echo number_format($order['total'], 2); ?></span>
                                <span class="status-badge <?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <a href="index.php?section=orders" class="action-link">
                            <i class="fas fa-list"></i>
                            <span>View All Orders</span>
                        </a>
                        <a href="index.php?section=inventory" class="action-link">
                            <i class="fas fa-warehouse"></i>
                            <span>Check Inventory</span>
                        </a>
                        <a href="index.php?section=reports" class="action-link">
                            <i class="fas fa-chart-line"></i>
                            <span>Generate Reports</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <?php elseif ($section === 'orders'): ?>
            <div class="section-header">
                <h2>All Orders</h2>
            </div>
            <div class="table-card">
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
                        <?php foreach ($recent_orders as $order): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                            <td><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
                            <td>KES <?php echo number_format($order['total'], 2); ?></td>
                            <td><span class="status-badge <?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php elseif ($section === 'products'): ?>
            <div class="section-header">
                <h2>Products Management</h2>
            </div>
            <div class="info-card">
                <i class="fas fa-info-circle"></i>
                <p>Go to <a href="../admin/index.php?section=products">Admin Products</a> to manage products.</p>
            </div>
            
            <?php elseif ($section === 'inventory'): ?>
            <div class="section-header">
                <h2>Inventory Status</h2>
            </div>
            <div class="info-card">
                <i class="fas fa-warehouse"></i>
                <p>Inventory management coming soon.</p>
            </div>
            
            <?php elseif ($section === 'reports'): ?>
            <div class="section-header">
                <h2>Reports</h2>
            </div>
            <div class="info-card">
                <i class="fas fa-chart-bar"></i>
                <p>Reports generation coming soon.</p>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="../js/dashboard.js"></script>
</body>
</html>
