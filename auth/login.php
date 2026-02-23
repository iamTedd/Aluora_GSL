<?php
/**
 * Aluora GSL - Login Handler
 */

session_start();
header('Content-Type: application/json');

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo jsonResponse(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$email = sanitize($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

if (empty($email) || empty($password)) {
    echo jsonResponse(['success' => false, 'message' => 'Please enter email and password']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo jsonResponse(['success' => false, 'message' => 'Invalid email or password']);
        exit;
    }
    
    if (!password_verify($password, $user['password'])) {
        echo jsonResponse(['success' => false, 'message' => 'Invalid email or password']);
        exit;
    }
    
    if ($user['status'] !== 'active') {
        echo jsonResponse(['success' => false, 'message' => 'Your account has been suspended. Please contact support.']);
        exit;
    }
    
    // Update last login
    $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$user['id']]);
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['last_name'] = $user['last_name'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['logged_in'] = true;
    
    // Redirect based on role
    switch ($user['role']) {
        case 'admin':
        case 'staff':
            $redirect_url = 'admin/index.php';
            break;
        case 'manager':
            $redirect_url = 'manager/index.php';
            break;
        case 'vendor':
            $redirect_url = 'vendor/index.php';
            break;
        case 'accountant':
            $redirect_url = 'accountant/index.php';
            break;
        case 'delivery_person':
            $redirect_url = 'delivery/index.php';
            break;
        case 'customer':
        default:
            $redirect_url = 'dashboard.php';
            break;
    }
    
    // Remember me
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        setcookie('remember_token', $token, time() + (86400 * 30), '/');
        // Store token in database (simplified)
    }
    
    // Log activity
    logActivity($user['id'], 'login', 'User logged in');
    
    echo jsonResponse(['success' => true, 'message' => 'Login successful!', 'redirect' => $redirect_url, 'user' => [
        'id' => $user['id'],
        'first_name' => $user['first_name'],
        'last_name' => $user['last_name'],
        'email' => $user['email'],
        'role' => $user['role']
    ]]);
    exit;
    
} catch (Exception $e) {
    error_log("Login Error: " . $e->getMessage());
    echo jsonResponse(['success' => false, 'message' => 'An error occurred. Please try again.']);
    exit;
}