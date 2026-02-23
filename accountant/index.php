<?php
/**
 * Aluora GSL - Accountant Dashboard
 * Accountant Portal for financial management
 */

require_once '../config.php';

// Check if accountant is logged in
if (!isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

// Only allow accountant, admin, manager, and staff roles
if (!in_array($_SESSION['role'], ['admin', 'staff', 'manager', 'accountant'])) {
    header('Location: ' . getDashboardUrl());
    exit;
}

// Get current section
$section = $_GET['section'] ?? 'overview';

// Get data
try {
    $pdo = getDBConnection();
    
    // Financial stats
    $total_revenue = $pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE payment_status = 'paid'")->fetchColumn();
    $pending_payments = $pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE payment_status = 'pending'")->fetchColumn();
    $total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $paid_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE payment_status = 'paid'")->fetchColumn();
    
} catch (Exception $e) {
    error_log("Accountant Data Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accountant Dashboard - Aluora GSL</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <style>
        .accountant-badge {
            background: linear-gradient(135deg, #27ae60, #1e8449);
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
            <span class="accountant-badge">ACCOUNTANT</span>
        </div>
        
        <nav class="sidebar-nav">
            <a href="index.php" class="<?php echo $section === 'overview' ? 'active' : ''; ?>">
                <i class="fas fa-th-large"></i>
                <span>Overview</span>
            </a>
            <a href="index.php?section=revenue" class="<?php echo $section === 'revenue' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i>
                <span>Revenue</span>
            </a>
            <a href="index.php?section=invoices" class="<?php echo $section === 'invoices' ? 'active' : ''; ?>">
                <i class="fas fa-file-invoice"></i>
                <span>Invoices</span>
            </a>
            <a href="index.php?section=payments" class="<?php echo $section === 'payments' ? 'active' : ''; ?>">
                <i class="fas fa-credit-card"></i>
                <span>Payments</span>
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
            <h1>Accountant Dashboard</h1>
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
                    <div class="stat-icon orders" style="background: linear-gradient(135deg, #27ae60, #1e8449);"><i class="fas fa-money-bill-wave"></i></div>
                    <div class="stat-info">
                        <span class="stat-value">KES <?php echo number_format($total_revenue, 0); ?></span>
                        <span class="stat-label">Total Revenue</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon pending" style="background: linear-gradient(135deg, #f39c12, #e67e22);"><i class="fas fa-clock"></i></div>
                    <div class="stat-info">
                        <span class="stat-value">KES <?php echo number_format($pending_payments, 0); ?></span>
                        <span class="stat-label">Pending Payments</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orders"><i class="fas fa-shopping-bag"></i></div>
                    <div class="stat-info">
                        <span class="stat-value"><?php echo $total_orders; ?></span>
                        <span class="stat-label">Total Orders</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon products" style="background: linear-gradient(135deg, #3498db, #2980b9);"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-info">
                        <span class="stat-value"><?php echo $paid_orders; ?></span>
                        <span class="stat-label">Paid Orders</span>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <a href="index.php?section=revenue" class="action-link">
                            <i class="fas fa-chart-line"></i>
                            <span>View Revenue</span>
                        </a>
                        <a href="index.php?section=payments" class="action-link">
                            <i class="fas fa-credit-card"></i>
                            <span>Manage Payments</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <?php elseif ($section === 'revenue'): ?>
            <div class="section-header">
                <h2>Revenue Overview</h2>
            </div>
            <div class="info-card">
                <i class="fas fa-chart-line"></i>
                <p>Revenue analytics coming soon.</p>
            </div>
            
            <?php elseif ($section === 'invoices'): ?>
            <div class="section-header">
                <h2>Invoices</h2>
            </div>
            <div class="info-card">
                <i class="fas fa-file-invoice"></i>
                <p>Invoice management coming soon.</p>
            </div>
            
            <?php elseif ($section === 'payments'): ?>
            <div class="section-header">
                <h2>Payments</h2>
            </div>
            <div class="info-card">
                <i class="fas fa-credit-card"></i>
                <p>Payment management coming soon.</p>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="../js/dashboard.js"></script>
</body>
</html>
