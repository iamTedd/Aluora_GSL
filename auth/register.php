<?php
/**
 * Aluora GSL - Registration Handler
 */

session_start();
header('Content-Type: application/json');

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo jsonResponse(['success' => false, 'message' => 'Invalid request method']);
}

$first_name = sanitize($_POST['first_name'] ?? '');
$last_name = sanitize($_POST['last_name'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');
$company = sanitize($_POST['company'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validation
$errors = [];

if (empty($first_name) || strlen($first_name) < 2) {
    $errors[] = 'First name must be at least 2 characters';
}

if (empty($last_name) || strlen($last_name) < 2) {
    $errors[] = 'Last name must be at least 2 characters';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address';
}

if (empty($phone) || strlen($phone) < 10) {
    $errors[] = 'Please enter a valid phone number';
}

if (empty($password) || strlen($password) < 6) {
    $errors[] = 'Password must be at least 6 characters';
}

if ($password !== $confirm_password) {
    $errors[] = 'Passwords do not match';
}

if (!isset($_POST['terms'])) {
    $errors[] = 'You must agree to the Terms & Privacy Policy';
}

if (!empty($errors)) {
    echo jsonResponse(['success' => false, 'message' => implode('. ', $errors)]);
}

try {
    $pdo = getDBConnection();
    
    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        echo jsonResponse(['success' => false, 'message' => 'An account with this email already exists']);
    }
    
    // Check if phone exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
    $stmt->execute([$phone]);
    
    if ($stmt->fetch()) {
        echo jsonResponse(['success' => false, 'message' => 'An account with this phone number already exists']);
    }
    
    // Create user
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, phone, company, password, role, status, email_verified) VALUES (?, ?, ?, ?, ?, ?, 'customer', 'active', 1)");
    $stmt->execute([$first_name, $last_name, $email, $phone, $company, $hashed_password]);
    
    $user_id = $pdo->lastInsertId();
    
    // Log activity
    logActivity($user_id, 'register', 'New user registered');
    
    // Send welcome notification
    createNotification($user_id, 'welcome', 'Welcome to Aluora GSL!', 'Thank you for registering. Start exploring our products!');
    
    echo jsonResponse(['success' => true, 'message' => 'Registration successful! Please login.']);
    
} catch (Exception $e) {
    error_log("Registration Error: " . $e->getMessage());
    echo jsonResponse(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
