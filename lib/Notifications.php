<?php
/**
 * Aluora GSL - Payment & Notification System
 * Handles M-Pesa, Airtel Money, Bank Payments, SMS & Email
 */

require_once __DIR__ . '/../config.php';

class PaymentGateway {
    private $pdo;
    private $settings = [];
    
    public function __construct() {
        $this->pdo = getDBConnection();
        $this->loadSettings();
    }
    
    // Load payment settings
    private function loadSettings() {
        $stmt = $this->pdo->query("SELECT setting_key, setting_value FROM payment_settings");
        while ($row = $stmt->fetch()) {
            $this->settings[$row['setting_key']] = $row['setting_value'];
        }
    }
    
    // Get setting value
    public function getSetting($key, $default = null) {
        return $this->settings[$key] ?? $default;
    }
    
    // Generate transaction ID
    public function generateTransactionId($prefix = 'TXN') {
        return $prefix . '-' . date('Ymd') . '-' . strtoupper(uniqid());
    }
    
    // Create transaction
    public function createTransaction($userId, $type, $amount, $paymentMethod, $referenceId = null, $phone = null) {
        $transactionId = $this->generateTransactionId();
        
        $stmt = $this->pdo->prepare("INSERT INTO transactions 
            (transaction_id, user_id, type, reference_id, amount, payment_method, phone, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
        
        $stmt->execute([
            $transactionId, 
            $userId, 
            $type, 
            $referenceId, 
            $amount, 
            $paymentMethod, 
            $phone
        ]);
        
        // Send notification
        $this->sendTransactionNotifications($transactionId, $userId, $amount, $paymentMethod, 'created');
        
        // Create admin alert
        $this->createAdminAlert('payment', 'New Payment Initiated', 
            "New payment of KSh " . number_format($amount, 2) . " via " . ucfirst($paymentMethod), 
            'high', 'transaction', $this->pdo->lastInsertId());
        
        return $transactionId;
    }
    
    // Process M-Pesa STK Push
    public function processMpesaSTK($phone, $amount, $transactionId) {
        $shortcode = $this->getSetting('mpesa_shortcode');
        $consumerKey = $this->getSetting('mpesa_consumer_key');
        $consumerSecret = $this->getSetting('mpesa_consumer_secret');
        $passkey = $this->getSetting('mpesa_passkey');
        $environment = $this->getSetting('mpesa_environment', 'sandbox');
        
        if (empty($consumerKey) || empty($consumerSecret)) {
            return ['success' => false, 'message' => 'M-Pesa not configured'];
        }
        
        // Get access token
        $tokenUrl = $environment === 'sandbox' 
            ? 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials'
            : 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
        
        $credentials = base64_encode($consumerKey . ':' . $consumerSecret);
        
        $ch = curl_init($tokenUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Basic $credentials"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        
        $tokenData = json_decode($response, true);
        
        if (!isset($tokenData['access_token'])) {
            return ['success' => false, 'message' => 'Failed to get M-Pesa token'];
        }
        
        // STK Push
        $timestamp = date('YmdHis');
        $password = base64_encode($shortcode . $passkey . $timestamp);
        
        $stkUrl = $environment === 'sandbox'
            ? 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest'
            : 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
        
        $callbackUrl = $this->getSetting('site_url', 'https://www.aluoragsl.com') . '/api/mpesa_callback.php';
        
        $stkData = [
            'BusinessShortCode' => $shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerBuyGoodsOnline',
            'Amount' => $amount,
            'PartyA' => $phone,
            'PartyB' => $shortcode,
            'PhoneNumber' => $phone,
            'CallBackURL' => $callbackUrl,
            'AccountReference' => $transactionId,
            'TransactionDesc' => 'Aluora GSL Payment'
        ];
        
        $ch = curl_init($stkUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $tokenData['access_token'],
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($stkData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if (isset($result['ResponseCode'])) {
            // Update transaction with provider reference
            $stmt = $this->pdo->prepare("UPDATE transactions SET provider_reference = ? WHERE transaction_id = ?");
            $stmt->execute([$result['CheckoutRequestID'], $transactionId]);
            
            return ['success' => true, 'message' => 'STK Push sent', 'reference' => $result['CheckoutRequestID']];
        }
        
        return ['success' => false, 'message' => $result['errorMessage'] ?? 'STK Push failed'];
    }
    
    // Process Airtel Money
    public function processAirtelMoney($phone, $amount, $transactionId) {
        $clientId = $this->getSetting('airtel_client_id');
        $clientSecret = $this->getSetting('airtel_client_secret');
        $environment = $this->getSetting('airtel_environment', 'sandbox');
        
        if (empty($clientId) || empty($clientSecret)) {
            return ['success' => false, 'message' => 'Airtel Money not configured'];
        }
        
        // Get token
        $tokenUrl = $environment === 'sandbox'
            ? 'https://openapiuat.airtel.africa/auth/oauth2/token'
            : 'https://openapi.airtel.africa/auth/oauth2/token';
        
        $ch = curl_init($tokenUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'client_credentials'
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        
        $tokenData = json_decode($response, true);
        
        if (!isset($tokenData['access_token'])) {
            return ['success' => false, 'message' => 'Failed to get Airtel token'];
        }
        
        // Collection request
        $collectionUrl = $environment === 'sandbox'
            ? 'https://openapiuat.airtel.africa/v1/offnet/dr'
            : 'https://openapi.airtel.africa/v1/offnet/dr';
        
        $reference = 'TXN' . time();
        
        $data = [
            'reference' => $reference,
            'subscriber' => [
                'country' => 'KEN',
                'msisdn' => $phone
            ],
            'transaction' => [
                'amount' => $amount,
                'currency' => 'KES',
                'description' => 'Aluora GSL Payment'
            ]
        ];
        
        $ch = curl_init($collectionUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $tokenData['access_token'],
            "Content-Type: application/json",
            "X-Country: KEN",
            "X-Currency: KES"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if (isset($result['data']['transaction']['status']) && $result['data']['transaction']['status'] === 'SUCCESS') {
            $stmt = $this->pdo->prepare("UPDATE transactions SET provider_reference = ? WHERE transaction_id = ?");
            $stmt->execute([$reference, $transactionId]);
            
            return ['success' => true, 'message' => 'Payment initiated', 'reference' => $reference];
        }
        
        return ['success' => false, 'message' => $result['error']['message'] ?? 'Payment failed'];
    }
    
    // Process Bank Transfer (creates payment reference)
    public function processBankTransfer($transactionId, $bankSlug) {
        $banks = [
            'equity' => ['name' => 'Equity Bank', 'account' => '1234567890', 'branch' => 'CBD'],
            'kcb' => ['name' => 'KCB', 'account' => '0987654321', 'branch' => 'Nairobi'],
            'cooperative' => ['name' => 'Co-operative Bank', 'account' => '5678901234', 'branch' => 'Westlands'],
            'stanchart' => ['name' => 'Standard Chartered', 'account' => '1122334455', 'branch' => 'Nairobi']
        ];
        
        if (!isset($banks[$bankSlug])) {
            return ['success' => false, 'message' => 'Invalid bank'];
        }
        
        $bank = $banks[$bankSlug];
        
        // Update transaction
        $stmt = $this->pdo->prepare("UPDATE transactions SET provider_reference = ? WHERE transaction_id = ?");
        $stmt->execute([$bankSlug, $transactionId]);
        
        return [
            'success' => true,
            'message' => 'Bank transfer details generated',
            'bank_details' => [
                'bank_name' => $bank['name'],
                'account_number' => $bank['account'],
                'branch' => $bank['branch'],
                'reference' => $transactionId
            ]
        ];
    }
    
    // Verify transaction
    public function verifyTransaction($transactionId) {
        $stmt = $this->pdo->prepare("SELECT * FROM transactions WHERE transaction_id = ?");
        $stmt->execute([$transactionId]);
        return $stmt->fetch();
    }
    
    // Complete transaction (callback from payment provider)
    public function completeTransaction($transactionId, $status, $providerRef = null) {
        $stmt = $this->pdo->prepare("UPDATE transactions SET 
            status = ?, 
            provider_reference = COALESCE(?, provider_reference),
            completed_at = CASE WHEN ? = 'completed' THEN NOW() ELSE completed_at END
            WHERE transaction_id = ?");
        
        $stmt->execute([$status, $providerRef, $status, $transactionId]);
        
        $transaction = $this->verifyTransaction($transactionId);
        
        if ($status === 'completed' && $transaction) {
            // Update order/tender status
            if ($transaction['type'] === 'order') {
                $this->updateOrderPayment($transaction['reference_id']);
            } elseif ($transaction['type'] === 'tender') {
                $this->updateTenderPayment($transaction['reference_id']);
            }
            
            // Send success notifications
            $this->sendTransactionNotifications($transactionId, $transaction['user_id'], 
                $transaction['amount'], $transaction['payment_method'], 'completed');
            
            // Admin alert
            $this->createAdminAlert('payment', 'Payment Completed', 
                "Payment of KSh " . number_format($transaction['amount'], 2) . " completed", 
                'medium', 'transaction', $transaction['id']);
        }
        
        return ['success' => true];
    }
    
    // Update order after payment
    private function updateOrderPayment($orderId) {
        $stmt = $this->pdo->prepare("UPDATE orders SET payment_status = 'paid', status = 'processing' WHERE id = ?");
        $stmt->execute([$orderId]);
    }
    
    // Update tender after payment
    private function updateTenderPayment($tenderId) {
        $stmt = $this->pdo->prepare("UPDATE tenders SET status = 'accepted' WHERE id = ?");
        $stmt->execute([$tenderId]);
    }
    
    // Send transaction notifications
    private function sendTransactionNotifications($transactionId, $userId, $amount, $method, $status) {
        $notification = new NotificationSystem();
        
        // Get user details
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) return;
        
        $amountFormatted = 'KSh ' . number_format($amount, 2);
        
        if ($status === 'created') {
            $subject = 'Payment Initiated - ' . $transactionId;
            $message = "Your payment of {$amountFormatted} via " . ucfirst($method) . " has been initiated.\n\n";
            $message .= "Transaction ID: {$transactionId}\n";
            $message .= "Status: Pending\n\n";
            $message .= "You will receive a confirmation once payment is processed.";
            
            $smsMessage = "ALUORA GSL: Payment of {$amountFormatted} initiated. Ref: {$transactionId}. ";
            $smsMessage .= $method === 'mpesa' ? "Check your phone for STK push." : "Use reference: {$transactionId}";
            
        } elseif ($status === 'completed') {
            $subject = 'Payment Confirmed - ' . $transactionId;
            $message = "Your payment of {$amountFormatted} has been confirmed!\n\n";
            $message .= "Transaction ID: {$transactionId}\n";
            $message .= "Status: Completed\n\n";
            $message .= "Thank you for your payment.";
            
            $smsMessage = "ALUORA GSL: Payment of {$amountFormatted} confirmed! Ref: {$transactionId}. Thank you!";
        } else {
            $subject = 'Payment Failed - ' . $transactionId;
            $message = "Your payment of {$amountFormatted} could not be processed.\n\n";
            $message .= "Transaction ID: {$transactionId}\n";
            $message .= "Status: Failed\n\n";
            $message .= "Please try again or contact support.";
            
            $smsMessage = "ALUORA GSL: Payment of {$amountFormatted} failed. Ref: {$transactionId}. Please try again.";
        }
        
        // Send email
        $notification->sendEmail($userId, $user['email'], $subject, $message, 'transaction');
        
        // Send SMS
        if ($user['phone']) {
            $notification->sendSMS($userId, $user['phone'], $smsMessage, 'transaction');
        }
    }
    
    // Create admin alert
    public function createAdminAlert($type, $title, $message, $priority = 'medium', $refType = null, $refId = null) {
        $stmt = $this->pdo->prepare("INSERT INTO admin_alerts 
            (type, title, message, priority, reference_type, reference_id) 
            VALUES (?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([$type, $title, $message, $priority, $refType, $refId]);
    }
    
    // Get payment methods
    public function getPaymentMethods() {
        $stmt = $this->pdo->query("SELECT * FROM payment_methods WHERE is_active = 1 ORDER BY sort_order");
        return $stmt->fetchAll();
    }
    
    // Get delivery zones
    public function getDeliveryZones() {
        $stmt = $this->pdo->query("SELECT * FROM delivery_zones WHERE is_active = 1 ORDER BY base_fee");
        return $stmt->fetchAll();
    }
    
    // Calculate delivery fee
    public function calculateDeliveryFee($zoneSlug, $orderAmount) {
        $stmt = $this->pdo->prepare("SELECT * FROM delivery_zones WHERE slug = ? AND is_active = 1");
        $stmt->execute([$zoneSlug]);
        $zone = $stmt->fetch();
        
        if (!$zone) return ['fee' => 0, 'free' => false];
        
        $freeShipping = $zone['free_shipping_threshold'] && $orderAmount >= $zone['free_shipping_threshold'];
        
        return [
            'fee' => $freeShipping ? 0 : $zone['base_fee'],
            'zone' => $zone['name'],
            'estimated_days' => $zone['estimated_days'],
            'free_shipping' => $freeShipping
        ];
    }
}

class NotificationSystem {
    private $pdo;
    private $settings = [];
    
    public function __construct() {
        $this->pdo = getDBConnection();
        $this->loadSettings();
    }
    
    private function loadSettings() {
        $stmt = $this->pdo->query("SELECT setting_key, setting_value FROM payment_settings");
        while ($row = $stmt->fetch()) {
            $this->settings[$row['setting_key']] = $row['setting_value'];
        }
    }
    
    // Send Email
    public function sendEmail($userId, $email, $subject, $body, $type = 'general', $referenceId = null) {
        // Log the email attempt
        $stmt = $this->pdo->prepare("INSERT INTO email_log 
            (user_id, email, subject, body, type, reference_id, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        
        $stmt->execute([$userId, $email, $subject, $body, $type, $referenceId]);
        $logId = $this->pdo->lastInsertId();
        
        // If no real email provider configured, simulate success
        if (empty($this->settings['smtp_host'])) {
            $stmt = $this->pdo->prepare("UPDATE email_log SET status = 'sent', sent_at = NOW() WHERE id = ?");
            $stmt->execute([$logId]);
            
            return ['success' => true, 'simulated' => true, 'log_id' => $logId];
        }
        
        // Real email sending (SMTP)
        try {
            $result = $this->sendSMTPEmail($email, $subject, $body);
            
            if ($result['success']) {
                $stmt = $this->pdo->prepare("UPDATE email_log SET 
                    status = 'sent', 
                    sent_at = NOW(),
                    provider_message_id = ? 
                    WHERE id = ?");
                $stmt->execute([$result['message_id'] ?? '', $logId]);
            } else {
                $stmt = $this->pdo->prepare("UPDATE email_log SET 
                    status = 'failed', 
                    error_message = ? 
                    WHERE id = ?");
                $stmt->execute([$result['message'], $logId]);
            }
            
            return $result;
        } catch (Exception $e) {
            $stmt = $this->pdo->prepare("UPDATE email_log SET status = 'failed', error_message = ? WHERE id = ?");
            $stmt->execute([$e->getMessage(), $logId]);
            
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // Send via SMTP
    private function sendSMTPEmail($to, $subject, $body) {
        $host = $this->settings['smtp_host'] ?? '';
        $port = $this->settings['smtp_port'] ?? 587;
        $username = $this->settings['smtp_username'] ?? '';
        $password = $this->settings['smtp_password'] ?? '';
        $fromEmail = $this->settings['smtp_from_email'] ?? 'noreply@aluoragsl.com';
        $fromName = $this->settings['smtp_from_name'] ?? 'Aluora GSL';
        
        if (empty($host)) {
            return ['success' => false, 'message' => 'SMTP not configured'];
        }
        
        // Simple mail function for demonstration
        // In production, use PHPMailer or similar library
        $headers = "From: {$fromName} <{$fromEmail}>\r\n";
        $headers .= "Reply-To: {$fromEmail}\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        // Wrap body in HTML template
        $htmlBody = $this->getEmailTemplate($subject, $body);
        
        $result = mail($to, $subject, $htmlBody, $headers);
        
        if ($result) {
            return ['success' => true, 'message_id' => time()];
        }
        
        return ['success' => false, 'message' => 'Failed to send email'];
    }
    
    // Email HTML Template
    private function getEmailTemplate($subject, $content) {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$subject}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden;">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #1a5f4a 0%, #2a8a6b 100%); padding: 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px;">Aluora GSL</h1>
                            <p style="color: #ffffff; margin: 10px 0 0 0; opacity: 0.9;">General Suppliers Limited</p>
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #1a5f4a; margin: 0 0 20px 0; font-size: 22px;">{$subject}</h2>
                            <div style="color: #333333; line-height: 1.6; font-size: 15px;">
                                <pre style="font-family: Arial, sans-serif; white-space: pre-wrap; margin: 0;">{$content}</pre>
                            </div>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f8f8; padding: 20px 30px; text-align: center;">
                            <p style="color: #888888; margin: 0; font-size: 13px;">
                                Aluora General Suppliers Limited<br>
                                www.aluoragsl.com<br>
                                aluoragsl@gmail.com | +254-715-173-207
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }
    
    // Send SMS
    public function sendSMS($userId, $phone, $message, $type = 'general', $referenceId = null) {
        // Format phone number
        $phone = $this->formatPhone($phone);
        
        // Log the SMS attempt
        $stmt = $this->pdo->prepare("INSERT INTO sms_log 
            (user_id, phone, message, type, reference_id, status) 
            VALUES (?, ?, ?, ?, ?, 'pending')");
        
        $stmt->execute([$userId, $phone, $message, $type, $referenceId]);
        $logId = $this->pdo->lastInsertId();
        
        // Check if SMS provider is configured
        $provider = $this->settings['sms_provider'] ?? '';
        
        if (empty($provider)) {
            // Simulate success for demo
            $stmt = $this->pdo->prepare("UPDATE sms_log SET status = 'sent' WHERE id = ?");
            $stmt->execute([$logId]);
            
            return ['success' => true, 'simulated' => true, 'log_id' => $logId];
        }
        
        // Send via provider
        try {
            if ($provider === 'twilio') {
                $result = $this->sendTwilioSMS($phone, $message);
            } elseif ($provider === 'africastalking') {
                $result = $this->sendAfricastalkingSMS($phone, $message);
            } else {
                $result = ['success' => false, 'message' => 'Unknown provider'];
            }
            
            if ($result['success']) {
                $stmt = $this->pdo->prepare("UPDATE sms_log SET 
                    status = 'sent', 
                    provider = ?,
                    provider_message_id = ? 
                    WHERE id = ?");
                $stmt->execute([$provider, $result['message_id'] ?? '', $logId]);
            } else {
                $stmt = $this->pdo->prepare("UPDATE sms_log SET status = 'failed' WHERE id = ?");
                $stmt->execute([$logId]);
            }
            
            return $result;
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // Format phone number
    private function formatPhone($phone) {
        // Remove any non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Add country code if missing
        if (substr($phone, 0, 1) === '0') {
            $phone = '254' . substr($phone, 1);
        } elseif (substr($phone, 0, 3) !== '254') {
            $phone = '254' . $phone;
        }
        
        return $phone;
    }
    
    // Send via Twilio
    private function sendTwilioSMS($phone, $message) {
        $sid = $this->settings['sms_api_key'] ?? '';
        $token = $this->settings['sms_api_secret'] ?? '';
        $from = $this->settings['sms_sender_id'] ?? '';
        
        if (empty($sid) || empty($token)) {
            return ['success' => false, 'message' => 'Twilio not configured'];
        }
        
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'To' => '+' . $phone,
            'From' => $from,
            'Body' => $message
        ]);
        curl_setopt($ch, CURLOPT_USERPWD, $sid . ':' . $token);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if (isset($result['sid'])) {
            return ['success' => true, 'message_id' => $result['sid']];
        }
        
        return ['success' => false, 'message' => $result['message'] ?? 'Failed to send SMS'];
    }
    
    // Send via Africastalking
    private function sendAfricastalkingSMS($phone, $message) {
        $apiKey = $this->settings['sms_api_key'] ?? '';
        $username = $this->settings['sms_api_secret'] ?? '';
        $from = $this->settings['sms_sender_id'] ?? '';
        
        if (empty($apiKey) || empty($username)) {
            return ['success' => false, 'message' => 'Africastalking not configured'];
        }
        
        $url = 'https://api.africastalking.com/version1/messaging';
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'username' => $username,
            'to' => '+' . $phone,
            'message' => $message,
            'from' => $from
        ]);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['ApiKey: ' . $apiKey]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if (isset($result['SMSMessageData']['Recipients'])) {
            return ['success' => true, 'message_id' => $result['SMSMessageData']['Recipients'][0]['messageId'] ?? ''];
        }
        
        return ['success' => false, 'message' => 'Failed to send SMS'];
    }
    
    // Get admin alerts
    public function getAdminAlerts($limit = 20, $unreadOnly = false) {
        $sql = "SELECT * FROM admin_alerts WHERE is_dismissed = 0";
        
        if ($unreadOnly) {
            $sql .= " AND is_read = 0";
        }
        
        $sql .= " ORDER BY priority DESC, created_at DESC LIMIT ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll();
    }
    
    // Mark alert as read
    public function markAlertRead($alertId) {
        $stmt = $this->pdo->prepare("UPDATE admin_alerts SET is_read = 1 WHERE id = ?");
        $stmt->execute([$alertId]);
    }
    
    // Dismiss alert
    public function dismissAlert($alertId) {
        $stmt = $this->pdo->prepare("UPDATE admin_alerts SET is_dismissed = 1 WHERE id = ?");
        $stmt->execute([$alertId]);
    }
    
    // Get unread alert count
    public function getUnreadAlertCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM admin_alerts WHERE is_read = 0 AND is_dismissed = 0");
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
}
