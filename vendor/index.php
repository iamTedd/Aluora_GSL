<?php
/**
 * Aluora GSL - Vendor Dashboard
 * Vendor Portal for managing products and quotes
 */

require_once '../config.php';

// Check if vendor is logged in
if (!isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

// Only allow vendor, admin, manager, and staff roles
if (!in_array($_SESSION['role'], ['admin', 'staff', 'manager', 'vendor'])) {
    header('Location: ' . getDashboardUrl());
    exit;
}

// Get current section
$section = $_GET['section'] ?? 'overview';

// Get data
try {
    $pdo = getDBConnection();
    
    // Stats for vendor
    $my_products = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn();
    $low_stock = $pdo->query("SELECT COUNT(*) FROM products WHERE stock_quantity < 10 AND status = 'active'")->fetchColumn();
    $categories = $pdo->query("SELECT COUNT(*) FROM categories WHERE status = 'active'")->fetchColumn();
    
} catch (Exception $e) {
    error_log("Vendor Data Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Dashboard - Aluora GSL</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <style>
        .vendor-badge {
            background: linear-gradient(135deg, #9b59b6, #8e44ad);
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
            <span class="vendor-badge">VENDOR</span>
        </div>
        
        <nav class="sidebar-nav">
            <a href="index.php" class="<?php echo $section === 'overview' ? 'active' : ''; ?>">
                <i class="fas fa-th-large"></i>
                <span>Overview</span>
            </a>
            <a href="index.php?section=my-products" class="<?php echo $section === 'my-products' ? 'active' : ''; ?>">
                <i class="fas fa-box"></i>
                <span>My Products</span>
            </a>
            <a href="index.php?section=quotes" class="<?php echo $section === 'quotes' ? 'active' : ''; ?>">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Quotes</span>
            </a>
            <a href="index.php?section=orders" class="<?php echo $section === 'orders' ? 'active' : ''; ?>">
                <i class="fas fa-shopping-bag"></i>
                <span>Orders</span>
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
            <h1>Vendor Dashboard</h1>
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
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon products"><i class="fas fa-box"></i></div>
                    <div class="stat-info">
                        <span class="stat-value"><?php echo $my_products; ?></span>
                        <span class="stat-label">Active Products</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon stock"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="stat-info">
                        <span class="stat-value"><?php echo $low_stock; ?></span>
                        <span class="stat-label">Low Stock</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orders"><i class="fas fa-tags"></i></div>
                    <div class="stat-info">
                        <span class="stat-value"><?php echo $categories; ?></span>
                        <span class="stat-label">Categories</span>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <a href="index.php?section=my-products" class="action-link">
                            <i class="fas fa-box"></i>
                            <span>View My Products</span>
                        </a>
                        <a href="../admin/index.php?section=products" class="action-link">
                            <i class="fas fa-plus"></i>
                            <span>Add New Product</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <?php elseif ($section === 'my-products'): ?>
            <div class="section-header">
                <h2>My Products</h2>
            </div>
            <div class="info-card">
                <i class="fas fa-info-circle"></i>
                <p>View products in <a href="../admin/index.php?section=products">Admin Panel</a></p>
            </div>
            
            <?php elseif ($section === 'quotes'): ?>
            <div class="section-header">
                <h2>Quotes Management</h2>
            </div>
            <div class="info-card">
                <i class="fas fa-file-invoice-dollar"></i>
                <p>Quotes management coming soon.</p>
            </div>
            
            <?php elseif ($section === 'orders'): ?>
            <div class="section-header">
                <h2>Orders</h2>
            </div>
            <div class="info-card">
                <i class="fas fa-shopping-bag"></i>
                <p>Orders view coming soon.</p>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="../js/dashboard.js"></script>
</body>
</html>
