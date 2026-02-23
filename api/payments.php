<?php
/**
 * Aluora GSL - Payment API
 * Handles payment initiation, verification, and callbacks
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/Notifications.php';

function jsonResponse($data) {
    echo json_encode($data);
    exit;
}

// Check if user is logged in for user actions
function requireLogin() {
    if (!isLoggedIn()) {
        jsonResponse(['success' => false, 'message' => 'Please login first']);
    }
}

// Get payment methods
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_methods') {
    requireLogin();
    
    try {
        $gateway = new PaymentGateway();
        $methods = $gateway->getPaymentMethods();
        
        jsonResponse([
            'success' => true, 
            'methods' => $methods
        ]);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Get delivery zones
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_zones') {
    requireLogin();
    
    try {
        $gateway = new PaymentGateway();
        $zones = $gateway->getDeliveryZones();
        
        jsonResponse([
            'success' => true, 
            'zones' => $zones
        ]);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Calculate delivery fee
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'calculate_delivery') {
    requireLogin();
    
    $zone = $_GET['zone'] ?? '';
    $amount = floatval($_GET['amount'] ?? 0);
    
    if (empty($zone)) {
        jsonResponse(['success' => false, 'message' => 'Please select delivery zone']);
    }
    
    try {
        $gateway = new PaymentGateway();
        $result = $gateway->calculateDeliveryFee($zone, $amount);
        
        jsonResponse([
            'success' => true, 
            'fee' => $result['fee'],
            'zone' => $result['zone'],
            'estimated_days' => $result['estimated_days'],
            'free_shipping' => $result['free_shipping']
        ]);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Initiate payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'initiate') {
    requireLogin();
    
    $type = $_POST['type'] ?? 'order'; // order, tender, delivery
    $amount = floatval($_POST['amount'] ?? 0);
    $method = $_POST['method'] ?? '';
    $phone = $_POST['phone'] ?? $_SESSION['phone'] ?? '';
    $referenceId = intval($_POST['reference_id'] ?? 0);
    
    // Validation
    if ($amount <= 0) {
        jsonResponse(['success' => false, 'message' => 'Invalid amount']);
    }
    
    if (empty($method)) {
        jsonResponse(['success' => false, 'message' => 'Please select payment method']);
    }
    
    try {
        $gateway = new PaymentGateway();
        
        // Create transaction
        $transactionId = $gateway->createTransaction(
            $_SESSION['user_id'],
            $type,
            $amount,
            $method,
            $referenceId,
            $phone
        );
        
        // Process based on method
        if ($method === 'mpesa') {
            $result = $gateway->processMpesaSTK($phone, $amount, $transactionId);
        } elseif ($method === 'airtel') {
            $result = $gateway->processAirtelMoney($phone, $amount, $transactionId);
        } elseif (in_array($method, ['equity', 'kcb', 'cooperative', 'stanchart'])) {
            $result = $gateway->processBankTransfer($transactionId, $method);
        } else {
            $result = ['success' => true, 'message' => 'Payment initiated'];
        }
        
        jsonResponse([
            'success' => $result['success'],
            'message' => $result['message'],
            'transaction_id' => $transactionId,
            'bank_details' => $result['bank_details'] ?? null
        ]);
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Verify transaction status
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'verify') {
    requireLogin();
    
    $transactionId = $_GET['transaction_id'] ?? '';
    
    if (empty($transactionId)) {
        jsonResponse(['success' => false, 'message' => 'Transaction ID required']);
    }
    
    try {
        $gateway = new PaymentGateway();
        $transaction = $gateway->verifyTransaction($transactionId);
        
        if (!$transaction) {
            jsonResponse(['success' => false, 'message' => 'Transaction not found']);
        }
        
        jsonResponse([
            'success' => true,
            'transaction' => $transaction
        ]);
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()]);
    }
}

// M-Pesa Callback (STK Push response)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mpesa_callback') {
    try {
        $gateway = new PaymentGateway();
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Process callback data
        $resultCode = $data['Body']['stkCallback']['ResultCode'] ?? 0;
        $checkoutId = $data['Body']['stkCallback']['CheckoutRequestID'] ?? '';
        
        if ($resultCode === 0) {
            // Success
            $metadata = $data['Body']['stkCallback']['CallbackMetadata']['Item'] ?? [];
            
            $amount = '';
            $phone = '';
            $receipt = '';
            
            foreach ($metadata as $item) {
                if ($item['Name'] === 'Amount') $amount = $item['Value'];
                if ($item['Name'] === 'PhoneNumber') $phone = $item['Value'];
                if ($item['Name'] === 'MpesaReceiptNumber') $receipt = $item['Value'];
            }
            
            // Find and update transaction
            $stmt = getDBConnection()->prepare("SELECT * FROM transactions WHERE provider_reference = ?");
            $stmt->execute([$checkoutId]);
            $transaction = $stmt->fetch();
            
            if ($transaction) {
                $gateway->completeTransaction($transaction['transaction_id'], 'completed', $receipt);
            }
        } else {
            // Failed - find and update transaction
            $stmt = getDBConnection()->prepare("SELECT * FROM transactions WHERE provider_reference = ?");
            $stmt->execute([$checkoutId]);
            $transaction = $stmt->fetch();
            
            if ($transaction) {
                $gateway->completeTransaction($transaction['transaction_id'], 'failed', $checkoutId);
            }
        }
        
        echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Success']);
        
    } catch (Exception $e) {
        error_log("M-Pesa callback error: " . $e->getMessage());
        echo json_encode(['ResultCode' => 1, 'ResultDesc' => 'Error']);
    }
}

// Admin alerts
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'alerts') {
    // Check if admin
    if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
        jsonResponse(['success' => false, 'message' => 'Admin access required']);
    }
    
    try {
        $notifications = new NotificationSystem();
        $alerts = $notifications->getAdminAlerts(50, isset($_GET['unread']));
        $unreadCount = $notifications->getUnreadAlertCount();
        
        jsonResponse([
            'success' => true,
            'alerts' => $alerts,
            'unread_count' => $unreadCount
        ]);
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Mark alert as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_alert_read') {
    if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
        jsonResponse(['success' => false, 'message' => 'Admin access required']);
    }
    
    $alertId = intval($_POST['alert_id'] ?? 0);
    
    if (!$alertId) {
        jsonResponse(['success' => false, 'message' => 'Invalid alert ID']);
    }
    
    try {
        $notifications = new NotificationSystem();
        $notifications->markAlertRead($alertId);
        
        jsonResponse(['success' => true]);
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Default: Invalid action
jsonResponse(['success' => false, 'message' => 'Invalid action']);
