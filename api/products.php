<?php
/**
 * Aluora GSL - Products API
 */

require_once '../config.php';

header('Content-Type: application/json');

try {
    $pdo = getDBConnection();
    
    $featured = isset($_GET['featured']) && $_GET['featured'] == 1;
    $category = $_GET['category'] ?? '';
    $search = $_GET['search'] ?? '';
    $limit = (int)($_GET['limit'] ?? 20);
    $offset = (int)($_GET['offset'] ?? 0);
    
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.status = 'active'";
    $params = [];
    
    if ($featured) {
        $sql .= " AND p.featured = 1";
    }
    
    if (!empty($category)) {
        $sql .= " AND c.slug = ?";
        $params[] = $category;
    }
    
    if (!empty($search)) {
        $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $sql .= " ORDER BY p.created_at DESC LIMIT $limit OFFSET $offset";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
    // Add badge based on stock
    foreach ($products as &$product) {
        if ($product['stock_quantity'] == 0) {
            $product['badge'] = 'Out of Stock';
        } elseif ($product['stock_quantity'] < $product['low_stock_threshold']) {
            $product['badge'] = 'Low Stock';
        } elseif ($product['featured']) {
            $product['badge'] = 'Featured';
        } else {
            $product['badge'] = 'In Stock';
        }
        
        $product['in_stock'] = $product['stock_quantity'] > 0;
    }
    
    echo json_encode(['success' => true, 'products' => $products, 'count' => count($products)]);
    
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Failed to load products']);
}
