<?php
/**
 * Aluora GSL - Delivery Person Dashboard
 * Delivery Portal for managing deliveries
 */

require_once '../config.php';

// Check if delivery person is logged in
if (!isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

// Only allow delivery_person, admin, manager, and staff roles
if (!in_array($_SESSION['role'], ['admin', 'staff', 'manager', 'delivery_person'])) {
    header('Location: ' . getDashboardUrl());
    exit;
}

// Get current section
$section = $_GET['section'] ?? 'overview';

// Get data
try {
    $pdo = getDBConnection();
    
    // Delivery stats - orders that are shipped or pending delivery
    $pending_deliveries = $pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('shipped', 'out_for_delivery')")->fetchColumn();
    $completed_deliveries = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'delivered'")->fetchColumn();
    
    // Get assigned deliveries
    $deliveries = $pdo->query("SELECT o.*, u.first_name, u.last_name, u.phone 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        WHERE o.status IN ('shipped', 'out_for_delivery')
        ORDER BY o.updated_at DESC
        LIMIT 20")->fetchAll();
    
} catch (Exception $e) {
    error_log("Delivery Data Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Dashboard - Aluora GSL</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <style>
        .delivery-badge {
            background: linear-gradient(135deg, #e67e22, #d35400);
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
            <span class="delivery-badge">DELIVERY</span>
        </div>
        
        <nav class="sidebar-nav">
            <a href="index.php" class="<?php echo $section === 'overview' ? 'active' : ''; ?>">
                <i class="fas fa-th-large"></i>
                <span>Overview</span>
            </a>
            <a href="index.php?section=deliveries" class="<?php echo $section === 'deliveries' ? 'active' : ''; ?>">
                <i class="fas fa-truck"></i>
                <span>My Deliveries</span>
            </a>
            <a href="index.php?section=completed" class="<?php echo $section === 'completed' ? 'active' : ''; ?>">
                <i class="fas fa-check-circle"></i>
                <span>Completed</span>
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
            <h1>Delivery Dashboard</h1>
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
                    <div class="stat-icon pending" style="background: linear-gradient(135deg, #e67e22, #d35400);"><i class="fas fa-truck"></i></div>
                    <div class="stat-info">
                        <span class="stat-value"><?php echo $pending_deliveries; ?></span>
                        <span class="stat-label">Pending Deliveries</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orders" style="background: linear-gradient(135deg, #27ae60, #1e8449);"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-info">
                        <span class="stat-value"><?php echo $completed_deliveries; ?></span>
                        <span class="stat-label">Completed</span>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Pending Deliveries</h3>
                        <a href="index.php?section=deliveries" class="view-all">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($deliveries)): ?>
                        <p class="empty-state">No pending deliveries</p>
                        <?php else: ?>
                        <?php foreach (array_slice($deliveries, 0, 5) as $order): ?>
                        <div class="order-item">
                            <div class="order-info">
                                <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>
                                <span><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></span>
                                <span><?php echo htmlspecialchars($order['phone'] ?? 'No phone'); ?></span>
                            </div>
                            <div class="order-meta">
                                <span class="status-badge <?php echo $order['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <?php elseif ($section === 'deliveries'): ?>
            <div class="section-header">
                <h2>All Pending Deliveries</h2>
            </div>
            <div class="table-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deliveries as $order): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                            <td><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['phone'] ?? 'N/A'); ?></td>
                            <td>KES <?php echo number_format($order['total'], 2); ?></td>
                            <td><span class="status-badge <?php echo $order['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?></span></td>
                            <td>
                                <button class="btn btn-primary btn-sm" onclick="markDelivered(<?php echo $order['id']; ?>)">
                                    <i class="fas fa-check"></i> Mark Delivered
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php elseif ($section === 'completed'): ?>
            <div class="section-header">
                <h2>Completed Deliveries</h2>
            </div>
            <div class="info-card">
                <i class="fas fa-check-circle"></i>
                <p>Completed deliveries history coming soon.</p>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="../js/dashboard.js"></script>
    <script>
    function markDelivered(orderId) {
        if (confirm('Mark this order as delivered?')) {
            // Would call API to update order status
            alert('Delivery marked! (Demo)');
            location.reload();
        }
    }
    </script>
</body>
</html>
