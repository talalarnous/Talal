<?php
/**
 * Syr AiX - Main API Handler
 * Handles translation, orders, and data retrieval
 * Uses FREE APIs only (MyMemory for translation, Google Charts for QR)
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    // ==================== TRANSLATION API (FREE) ====================
    case 'translate':
        handleTranslation();
        break;
    
    // ==================== MENU DATA ====================
    case 'get_categories':
        getCategories();
        break;
    
    case 'get_products':
        getProducts();
        break;
    
    case 'get_product_details':
        getProductDetailsAPI();
        break;
    
    // ==================== ORDERS ====================
    case 'create_order':
        createOrderAPI();
        break;
    
    case 'get_orders':
        getOrders();
        break;
    
    case 'update_order_status':
        updateOrderStatus();
        break;
    
    // ==================== TABLES ====================
    case 'validate_table':
        validateTable();
        break;
    
    case 'get_qr_code':
        getQRCode();
        break;
    
    // ==================== SETTINGS ====================
    case 'get_settings':
        getSettings();
        break;
    
    default:
        jsonResponse(false, 'Invalid action', null, 400);
}

/**
 * Handle translation request using MyMemory API (FREE)
 */
function handleTranslation() {
    $text = $_POST['text'] ?? '';
    $targetLang = $_POST['target_lang'] ?? 'en';
    $sourceLang = $_POST['source_lang'] ?? 'ar';
    
    if (empty($text)) {
        jsonResponse(false, 'Text is required', null, 400);
    }
    
    $translated = freeTranslate($text, $targetLang, $sourceLang);
    
    jsonResponse(true, 'Translation successful', [
        'original' => $text,
        'translated' => $translated,
        'source_lang' => $sourceLang,
        'target_lang' => $targetLang
    ]);
}

/**
 * Get all categories with product count
 */
function getCategories() {
    $categories = getCategoriesWithCount();
    jsonResponse(true, 'Categories retrieved', $categories);
}

/**
 * Get products by category
 */
function getProducts() {
    $categoryId = $_GET['category_id'] ?? 0;
    
    if (!$categoryId) {
        jsonResponse(false, 'Category ID is required', null, 400);
    }
    
    $products = getProductsByCategory((int)$categoryId);
    jsonResponse(true, 'Products retrieved', $products);
}

/**
 * Get product details with images and addons
 */
function getProductDetailsAPI() {
    $productId = $_GET['product_id'] ?? 0;
    
    if (!$productId) {
        jsonResponse(false, 'Product ID is required', null, 400);
    }
    
    $product = getProductDetails((int)$productId);
    
    if (!$product) {
        jsonResponse(false, 'Product not found', null, 404);
    }
    
    jsonResponse(true, 'Product details retrieved', $product);
}

/**
 * Create new order
 */
function createOrderAPI() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, 'Method not allowed', null, 405);
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (empty($data['table_id']) || empty($data['items']) || !is_array($data['items'])) {
        jsonResponse(false, 'Invalid order data', null, 400);
    }
    
    // Get settings
    $settings = getRestaurantSettings();
    $mandatoryItems = getMandatoryItems();
    
    // Calculate totals
    $totals = calculateOrderTotals($data['items'], $settings['tax_rate'], $mandatoryItems);
    
    // Prepare order data
    $orderData = [
        'table_id' => (int)$data['table_id'],
        'table_number' => $data['table_number'] ?? 'Unknown',
        'items' => $data['items'],
        'subtotal' => $totals['subtotal'],
        'tax' => $totals['tax'],
        'mandatory_total' => $totals['mandatory'],
        'total' => $totals['total'],
        'notes' => $data['notes'] ?? '',
        'customer_name' => $data['customer_name'] ?? '',
        'customer_phone' => $data['customer_phone'] ?? ''
    ];
    
    // Create order
    $orderId = createOrder($orderData);
    
    if (!$orderId) {
        jsonResponse(false, 'Failed to create order', null, 500);
    }
    
    jsonResponse(true, 'Order created successfully', [
        'order_id' => $orderId,
        'total' => $totals['total'],
        'table_number' => $orderData['table_number']
    ]);
}

/**
 * Get orders for admin panel (with AJAX long polling support)
 */
function getOrders() {
    requireLogin();
    
    $pdo = getDBConnection();
    $lastUpdate = $_GET['last_update'] ?? '';
    $status = $_GET['status'] ?? 'all';
    
    // Build query
    $query = "SELECT o.*, t.table_number 
              FROM orders o 
              JOIN tables t ON o.table_id = t.id 
              WHERE 1=1";
    $params = [];
    
    if ($status !== 'all') {
        $query .= " AND o.status = ?";
        $params[] = $status;
    }
    
    // For long polling - only return if there are updates
    if (!empty($lastUpdate)) {
        $query .= " AND o.updated_at > ?";
        $params[] = $lastUpdate;
    }
    
    $query .= " ORDER BY o.created_at DESC LIMIT 50";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
    
    // Format orders
    foreach ($orders as &$order) {
        $order['items'] = json_decode($order['items_json'], true);
        unset($order['items_json']);
        $order['time_ago'] = timeAgo($order['created_at']);
    }
    
    // If no updates and last_update was provided, wait a bit (long polling)
    if (empty($orders) && !empty($lastUpdate)) {
        sleep(2); // Wait 2 seconds before responding
    }
    
    jsonResponse(true, 'Orders retrieved', $orders);
}

/**
 * Update order status
 */
function updateOrderStatus() {
    requireLogin();
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, 'Method not allowed', null, 405);
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    $orderId = $data['order_id'] ?? 0;
    $status = $data['status'] ?? '';
    
    $validStatuses = ['new', 'preparing', 'ready', 'completed', 'cancelled'];
    
    if (!$orderId || !in_array($status, $validStatuses)) {
        jsonResponse(false, 'Invalid order ID or status', null, 400);
    }
    
    $success = updateOrderStatus((int)$orderId, $status);
    
    if (!$success) {
        jsonResponse(false, 'Failed to update order status', null, 500);
    }
    
    jsonResponse(true, 'Order status updated', [
        'order_id' => $orderId,
        'status' => $status
    ]);
}

/**
 * Validate table token from QR code
 */
function validateTable() {
    $token = $_GET['token'] ?? '';
    
    if (empty($token)) {
        jsonResponse(false, 'Token is required', null, 400);
    }
    
    $pdo = getDBConnection();
    $parts = explode('_', $token);
    
    if (count($parts) < 2) {
        jsonResponse(false, 'Invalid token format', null, 400);
    }
    
    $tableId = (int)end($parts);
    
    $stmt = $pdo->prepare("SELECT id, table_number, status FROM tables WHERE token = ? AND status = 'active'");
    $stmt->execute([$token]);
    $table = $stmt->fetch();
    
    if (!$table) {
        jsonResponse(false, 'Invalid or inactive table', null, 404);
    }
    
    jsonResponse(true, 'Table validated', $table);
}

/**
 * Get QR code URL for a table (using Google Charts API - FREE)
 */
function getQRCode() {
    requireLogin();
    
    $tableId = $_GET['table_id'] ?? 0;
    
    if (!$tableId) {
        jsonResponse(false, 'Table ID is required', null, 400);
    }
    
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT id, table_number, token FROM tables WHERE id = ?");
    $stmt->execute([(int)$tableId]);
    $table = $stmt->fetch();
    
    if (!$table) {
        jsonResponse(false, 'Table not found', null, 404);
    }
    
    // Generate QR code URL with menu link
    $menuUrl = APP_URL . '/index.php?table=' . $table['token'];
    $qrCodeUrl = generateQRCode($menuUrl, 300);
    
    jsonResponse(true, 'QR code generated', [
        'table_id' => $table['id'],
        'table_number' => $table['table_number'],
        'qr_code_url' => $qrCodeUrl,
        'menu_url' => $menuUrl
    ]);
}

/**
 * Get restaurant settings
 */
function getSettings() {
    $settings = getRestaurantSettings();
    $mandatoryItems = getMandatoryItems();
    
    jsonResponse(true, 'Settings retrieved', [
        'restaurant' => $settings,
        'mandatory_items' => $mandatoryItems
    ]);
}

/**
 * Send JSON response
 */
function jsonResponse($success, $message, $data = null, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}
