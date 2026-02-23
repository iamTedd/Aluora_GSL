<?php
/**
 * Aluora GSL - User Dashboard
 * Customer Portal for managing orders, tenders, and tickets
 */

require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        $pdo = getDBConnection();
        
        switch ($_POST['action']) {
            // Cancel Order
            case 'cancel_order':
                $order_id = (int)$_POST['order_id'];
                $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ? AND user_id = ?");
                $stmt->execute([$order_id, $_SESSION['user_id']]);
                echo jsonResponse(['success' => true, 'message' => 'Order cancelled successfully!']);
                break;
            
            // Update Profile
            case 'update_profile':
                $first_name = sanitize($_POST['first_name']);
                $last_name = sanitize($_POST['last_name']);
                $phone = sanitize($_POST['phone']);
                $company = sanitize($_POST['company']);
                
                $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ?, company = ? WHERE id = ?");
                $stmt->execute([$first_name, $last_name, $phone, $company, $_SESSION['user_id']]);
                
                $_SESSION['first_name'] = $first_name;
                $_SESSION['last_name'] = $last_name;
                
                echo jsonResponse(['success' => true, 'message' => 'Profile updated successfully!']);
                break;
            
            // Get user orders
            case 'get_orders':
                $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
                $stmt->execute([$_SESSION['user_id']]);
                $orders = $stmt->fetchAll();
                
                foreach ($orders as &$order) {
                    $items_stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
                    $items_stmt->execute([$order['id']]);
                    $order['items'] = $items_stmt->fetchAll();
                }
                
                echo jsonResponse(['success' => true, 'orders' => $orders]);
                break;
            
            // Get user tenders
            case 'get_tenders':
                $stmt = $pdo->prepare("SELECT * FROM tenders WHERE user_id = ? ORDER BY created_at DESC");
                $stmt->execute([$_SESSION['user_id']]);
                $tenders = $stmt->fetchAll();
                echo jsonResponse(['success' => true, 'tenders' => $tenders]);
                break;
            
            // Get user tickets
            case 'get_tickets':
                $stmt = $pdo->prepare("SELECT * FROM support_tickets WHERE user_id = ? ORDER BY created_at DESC");
                $stmt->execute([$_SESSION['user_id']]);
                $tickets = $stmt->fetchAll();
                echo jsonResponse(['success' => true, 'tickets' => $tickets]);
                break;
                
            default:
                echo jsonResponse(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        error_log("Dashboard AJAX Error: " . $e->getMessage());
        echo jsonResponse(['success' => false, 'message' => 'An error occurred']);
    }
    exit;
}

// Get current section
$section = $_GET['section'] ?? 'overview';

// Get user data
try {
    $pdo = getDBConnection();
    
    // Get user info
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    // Stats
    $order_count = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?")->fetchColumn();
    $tender_count = $pdo->prepare("SELECT COUNT(*) FROM tenders WHERE user_id = ?")->fetchColumn();
    $ticket_count = $pdo->prepare("SELECT COUNT(*) FROM support_tickets WHERE user_id = ?")->fetchColumn();
    
    // Recent orders
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$_SESSION['user_id']]);
    $recent_orders = $stmt->fetchAll();
    
    // Recent tenders
    $stmt = $pdo->prepare("SELECT * FROM tenders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$_SESSION['user_id']]);
    $recent_tenders = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Dashboard Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - Aluora GSL</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <!-- Sidebar -->
    <aside class="dashboard-sidebar">
        <div class="sidebar-header">
            <a href="index.php" class="logo">
                <i class="fas fa-box-open"></i>
                <span>Aluora<span>GSL</span></span>
            </a>
        </div>
        
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="<?php echo $section === 'overview' ? 'active' : ''; ?>">
                <i class="fas fa-th-large"></i>
                <span>Overview</span>
            </a>
            <a href="dashboard.php?section=orders" class="<?php echo $section === 'orders' ? 'active' : ''; ?>">
                <i class="fas fa-shopping-bag"></i>
                <span>My Orders</span>
            </a>
            <a href="dashboard.php?section=tenders" class="<?php echo $section === 'tenders' ? 'active' : ''; ?>">
                <i class="fas fa-file-contract"></i>
                <span>My Tenders</span>
            </a>
            <a href="dashboard.php?section=tickets" class="<?php echo $section === 'tickets' ? 'active' : ''; ?>">
                <i class="fas fa-ticket-alt"></i>
                <span>Support Tickets</span>
            </a>
            <a href="dashboard.php?section=profile" class="<?php echo $section === 'profile' ? 'active' : ''; ?>">
                <i class="fas fa-user"></i>
                <span>My Profile</span>
            </a>
        </nav>
        
        <div class="sidebar-footer">
            <a href="logout.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="dashboard-main">
        <!-- Header -->
        <header class="dashboard-header">
            <h1><?php 
                echo ucfirst($section); 
                if ($section === 'overview') echo ' Dashboard';
            ?></h1>
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
            <?php if ($section === 'overview'): ?>
            <!-- Overview -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon orders"><i class="fas fa-shopping-bag"></i></div>
                    <div class="stat-info">
                        <span class="stat-value"><?php echo $order_count; ?></span>
                        <span class="stat-label">Total Orders</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon tenders"><i class="fas fa-file-contract"></i></div>
                    <div class="stat-info">
                        <span class="stat-value"><?php echo $tender_count; ?></span>
                        <span class="stat-label">Tenders Submitted</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon tickets"><i class="fas fa-ticket-alt"></i></div>
                    <div class="stat-info">
                        <span class="stat-value"><?php echo $ticket_count; ?></span>
                        <span class="stat-label">Support Tickets</span>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Recent Orders</h3>
                        <a href="dashboard.php?section=orders" class="view-all">View All</a>
                    </div>
                    <div class="card-body" id="recentOrders">
                        <!-- Orders loaded via AJAX -->
                        <div class="loading"><i class="fas fa-spinner fa-spin"></i> Loading...</div>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Recent Tenders</h3>
                        <a href="dashboard.php?section=tenders" class="view-all">View All</a>
                    </div>
                    <div class="card-body" id="recentTenders">
                        <!-- Tenders loaded via AJAX -->
                        <div class="loading"><i class="fas fa-spinner fa-spin"></i> Loading...</div>
                    </div>
                </div>
            </div>
            
            <?php elseif ($section === 'orders'): ?>
            <!-- Orders -->
            <div class="section-header">
                <h2>My Orders</h2>
            </div>
            
            <div class="orders-list" id="ordersList">
                <div class="loading"><i class="fas fa-spinner fa-spin"></i> Loading your orders...</div>
            </div>
            
            <?php elseif ($section === 'tenders'): ?>
            <!-- Tenders -->
            <div class="section-header">
                <h2>My Tenders</h2>
                <button class="btn btn-primary" onclick="openModal('newTenderModal')">
                    <i class="fas fa-plus"></i> New Tender
                </button>
            </div>
            
            <div class="tenders-list" id="tendersList">
                <div class="loading"><i class="fas fa-spinner fa-spin"></i> Loading your tenders...</div>
            </div>
            
            <?php elseif ($section === 'tickets'): ?>
            <!-- Support Tickets -->
            <div class="section-header">
                <h2>Support Tickets</h2>
                <button class="btn btn-primary" onclick="openModal('newTicketModal')">
                    <i class="fas fa-plus"></i> New Ticket
                </button>
            </div>
            
            <div class="tickets-list" id="ticketsList">
                <div class="loading"><i class="fas fa-spinner fa-spin"></i> Loading your tickets...</div>
            </div>
            
            <?php elseif ($section === 'profile'): ?>
            <!-- Profile -->
            <div class="section-header">
                <h2>My Profile</h2>
            </div>
            
            <div class="profile-card">
                <form id="profileForm" onsubmit="updateProfile(event)">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group full">
                            <label>Company Name</label>
                            <input type="text" name="company" value="<?php echo htmlspecialchars($user['company'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- New Tender Modal -->
    <div class="modal" id="newTenderModal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal('newTenderModal')"><i class="fas fa-times"></i></button>
            <div class="modal-header">
                <h2><i class="fas fa-file-contract"></i> Submit New Tender</h2>
            </div>
            <form onsubmit="submitTenderFromDashboard(event)">
                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" required placeholder="What do you need?">
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category">
                        <option value="">Select category</option>
                        <option value="office">Office Supplies</option>
                        <option value="cleaning">Cleaning Supplies</option>
                        <option value="safety">Safety Equipment</option>
                        <option value="industrial">Industrial Tools</option>
                        <option value="electronics">Electronics</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Quantity</label>
                    <input type="text" name="quantity" placeholder="e.g., 100 pieces">
                </div>
                <div class="form-group">
                    <label>Budget Range</label>
                    <input type="text" name="budget" placeholder="e.g., KES 50,000 - 100,000">
                </div>
                <div class="form-group">
                    <label>Deadline</label>
                    <input type="date" name="deadline">
                </div>
                <div class="form-group">
                    <label>Description *</label>
                    <textarea name="description" rows="4" required placeholder="Describe your requirements in detail..."></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-outline" onclick="closeModal('newTenderModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Tender</button>
                </div>
            </form>
        </div>
    </div>

    <!-- New Ticket Modal -->
    <div class="modal" id="newTicketModal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal('newTicketModal')"><i class="fas fa-times"></i></button>
            <div class="modal-header">
                <h2><i class="fas fa-ticket-alt"></i> Create Support Ticket</h2>
            </div>
            <form onsubmit="submitTicketFromDashboard(event)">
                <div class="form-group">
                    <label>Subject *</label>
                    <input type="text" name="subject" required placeholder="Brief description of your issue">
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category">
                        <option value="general">General Inquiry</option>
                        <option value="order">Order Related</option>
                        <option value="product">Product Inquiry</option>
                        <option value="payment">Payment</option>
                        <option value="technical">Technical Issue</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Priority</label>
                    <select name="priority">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Message *</label>
                    <textarea name="message" rows="4" required placeholder="Describe your issue in detail..."></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-outline" onclick="closeModal('newTicketModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Ticket</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/dashboard.js"></script>
</body>
</html>
