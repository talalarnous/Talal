<?php
/**
 * Syr AiX - Database Configuration
 * Connect to MySQL database with PDO
 */

// Database credentials (Update these for your hosting)
define('DB_HOST', 'localhost');
define('DB_NAME', 'syr_aix_menu');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application settings
define('APP_NAME', 'Syr AiX');
define('APP_URL', 'http://localhost/syr-aix');
define('ADMIN_EMAIL', 'admin@syr-aix.com');

// Error reporting (Disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Timezone
date_default_timezone_set('Asia/Damascus');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Get PDO database connection
 * @return PDO Database connection
 */
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log error and show friendly message
            error_log("Database connection failed: " . $e->getMessage());
            die(json_encode([
                'success' => false,
                'error' => 'Database connection failed. Please check configuration.'
            ]));
        }
    }
    
    return $pdo;
}

/**
 * Get restaurant settings
 * @return array Restaurant settings
 */
function getRestaurantSettings() {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM settings LIMIT 1");
    $settings = $stmt->fetch();
    
    if (!$settings) {
        // Default settings if none exist
        return [
            'id' => 1,
            'restaurant_name_ar' => 'مطعمنا',
            'restaurant_name_en' => 'Our Restaurant',
            'restaurant_name_tr' => 'Restoranımız',
            'logo' => null,
            'tax_rate' => 10,
            'currency' => '$',
            'theme_color' => '#C0D906',
            'welcome_message_ar' => 'أهلاً بكم في مطعمنا',
            'welcome_message_en' => 'Welcome to Our Restaurant',
            'welcome_message_tr' => 'Restoranımıza Hoş Geldiniz',
            'phone' => '',
            'email' => '',
            'address_ar' => '',
            'address_en' => '',
            'address_tr' => ''
        ];
    }
    
    return $settings;
}

/**
 * Get mandatory items
 * @return array Mandatory items
 */
function getMandatoryItems() {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM mandatory_items WHERE active = 1 ORDER BY sort_order");
    return $stmt->fetchAll();
}

/**
 * Get all categories with product count
 * @return array Categories
 */
function getCategoriesWithCount() {
    $pdo = getDBConnection();
    $lang = $_SESSION['lang'] ?? 'ar';
    $nameCol = "name_{$lang}";
    
    $stmt = $pdo->prepare("
        SELECT c.*, 
               COUNT(p.id) as product_count
        FROM categories c
        LEFT JOIN products p ON c.id = p.category_id AND p.active = 1
        WHERE c.active = 1
        GROUP BY c.id
        ORDER BY c.sort_order, c.id
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get products by category
 * @param int $categoryId Category ID
 * @return array Products
 */
function getProductsByCategory($categoryId) {
    $pdo = getDBConnection();
    $lang = $_SESSION['lang'] ?? 'ar';
    
    $stmt = $pdo->prepare("
        SELECT p.*, 
               GROUP_CONCAT(pi.image_url ORDER BY pi.sort_order SEPARATOR '|') as images
        FROM products p
        LEFT JOIN product_images pi ON p.id = pi.product_id
        WHERE p.category_id = ? AND p.active = 1
        GROUP BY p.id
        ORDER BY p.sort_order, p.id
    ");
    $stmt->execute([$categoryId]);
    return $stmt->fetchAll();
}

/**
 * Get product details with images and addons
 * @param int $productId Product ID
 * @return array|null Product details
 */
function getProductDetails($productId) {
    $pdo = getDBConnection();
    $lang = $_SESSION['lang'] ?? 'ar';
    
    // Get product
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND active = 1");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if (!$product) {
        return null;
    }
    
    // Get images
    $stmt = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id = ? ORDER BY sort_order");
    $stmt->execute([$productId]);
    $product['images'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Get addons
    $stmt = $pdo->prepare("SELECT * FROM product_addons WHERE product_id = ? AND active = 1 ORDER BY sort_order");
    $stmt->execute([$productId]);
    $product['addons'] = $stmt->fetchAll();
    
    return $product;
}

/**
 * Create order
 * @param array $data Order data
 * @return int|false Order ID or false on failure
 */
function createOrder($data) {
    $pdo = getDBConnection();
    
    try {
        $pdo->beginTransaction();
        
        // Insert order
        $stmt = $pdo->prepare("
            INSERT INTO orders (table_id, table_number, items_json, subtotal, tax_amount, mandatory_total, total_amount, status, notes, customer_name, customer_phone)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'new', ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['table_id'],
            $data['table_number'],
            json_encode($data['items'], JSON_UNESCAPED_UNICODE),
            $data['subtotal'],
            $data['tax'],
            $data['mandatory_total'],
            $data['total'],
            $data['notes'] ?? '',
            $data['customer_name'] ?? '',
            $data['customer_phone'] ?? ''
        ]);
        
        $orderId = $pdo->lastInsertId();
        
        // Insert order items
        $stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, addons_json, subtotal)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($data['items'] as $item) {
            $stmt->execute([
                $orderId,
                $item['product_id'],
                $item['name'],
                $item['quantity'],
                $item['price'],
                json_encode($item['addons'] ?? [], JSON_UNESCAPED_UNICODE),
                $item['subtotal']
            ]);
        }
        
        $pdo->commit();
        return $orderId;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Order creation failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Update order status
 * @param int $orderId Order ID
 * @param string $status New status
 * @return bool Success
 */
function updateOrderStatus($orderId, $status) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
    return $stmt->execute([$status, $orderId]);
}

/**
 * Get dashboard statistics
 * @return array Statistics
 */
function getDashboardStats() {
    $pdo = getDBConnection();
    
    // Today's stats
    $today = date('Y-m-d');
    
    $stats = [];
    
    // Total orders today
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = ?");
    $stmt->execute([$today]);
    $stats['orders_today'] = (int)$stmt->fetchColumn();
    
    // Revenue today
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE DATE(created_at) = ? AND status != 'cancelled'");
    $stmt->execute([$today]);
    $stats['revenue_today'] = (float)$stmt->fetchColumn();
    
    // Pending orders
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE status IN ('new', 'preparing')");
    $stmt->execute();
    $stats['pending_orders'] = (int)$stmt->fetchColumn();
    
    // Total products
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE active = 1");
    $stmt->execute();
    $stats['total_products'] = (int)$stmt->fetchColumn();
    
    // Active tables
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tables WHERE status = 'active'");
    $stmt->execute();
    $stats['active_tables'] = (int)$stmt->fetchColumn();
    
    return $stats;
}

/**
 * Get top selling products
 * @param int $limit Number of products
 * @return array Top products
 */
function getTopSellingProducts($limit = 5) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT p.name_ar, p.name_en, p.name_tr, SUM(oi.quantity) as total_sold
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        GROUP BY oi.product_id
        ORDER BY total_sold DESC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

/**
 * Get revenue by day (last 7 days)
 * @return array Revenue data
 */
function getRevenueByDay() {
    $pdo = getDBConnection();
    $stmt = $pdo->query("
        SELECT DATE(created_at) as date, 
               COUNT(*) as orders, 
               SUM(total_amount) as revenue
        FROM orders 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ");
    return $stmt->fetchAll();
}
