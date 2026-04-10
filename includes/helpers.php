<?php
/**
 * Syr AiX - Helper Functions
 * Common utility functions for the platform
 */

/**
 * Free Translation Function using MyMemory API (No API Key Required)
 * Supports Arabic, English, Turkish
 * 
 * @param string $text Text to translate
 * @param string $targetLang Target language code (en, tr, ar)
 * @param string $sourceLang Source language code (default: ar)
 * @return string Translated text
 */
function freeTranslate($text, $targetLang, $sourceLang = 'ar') {
    if (empty($text) || $targetLang == $sourceLang) {
        return $text;
    }
    
    // MyMemory API - Free translation service (no key required for limited usage)
    $langPair = $sourceLang . '|' . $targetLang;
    $encodedText = urlencode($text);
    $apiUrl = "https://api.mymemory.translated.net/get?q={$encodedText}&langpair={$langPair}";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200 && $response) {
        $data = json_decode($response, true);
        if (isset($data['responseData']['translatedText'])) {
            return $data['responseData']['translatedText'];
        }
    }
    
    // Fallback: Return original text if translation fails
    return $text;
}

/**
 * Translate to all three languages
 * 
 * @param string $text Original text (Arabic)
 * @return array ['ar' => ..., 'en' => ..., 'tr' => ...]
 */
function translateToAll($text) {
    return [
        'ar' => $text,
        'en' => freeTranslate($text, 'en', 'ar'),
        'tr' => freeTranslate($text, 'tr', 'ar')
    ];
}

/**
 * Generate QR Code using Google Charts API (Free, No Library Required)
 * 
 * @param string $data Data to encode in QR
 * @param int $size Size in pixels
 * @return string Image URL
 */
function generateQRCode($data, $size = 300) {
    $encodedData = urlencode($data);
    return "https://chart.googleapis.com/chart?chs={$size}x{$size}&cht=qr&chl={$encodedData}&choe=UTF-8";
}

/**
 * Download QR Code as PNG
 * 
 * @param string $data Data to encode
 * @param string $filename Output filename
 */
function downloadQRCode($data, $filename = 'qrcode.png') {
    $qrUrl = generateQRCode($data, 300);
    $imageData = file_get_contents($qrUrl);
    
    header('Content-Type: image/png');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo $imageData;
    exit;
}

/**
 * Sanitize input data
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Require login
 */
function requireLogin() {
    session_start();
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Get current language from session or default to Arabic
 */
function getCurrentLang() {
    session_start();
    return $_SESSION['lang'] ?? 'ar';
}

/**
 * Format price with currency symbol
 */
function formatPrice($price, $currency = '$') {
    return number_format((float)$price, 2) . ' ' . $currency;
}

/**
 * Calculate order totals including tax and mandatory items
 * 
 * @param array $items Order items
 * @param float $taxRate Tax rate percentage
 * @param array $mandatoryItems Mandatory items to add
 * @return array ['subtotal' => ..., 'tax' => ..., 'mandatory' => ..., 'total' => ...]
 */
function calculateOrderTotals($items, $taxRate, $mandatoryItems = []) {
    $subtotal = 0;
    
    foreach ($items as $item) {
        $subtotal += ($item['price'] * $item['quantity']);
        if (isset($item['addons'])) {
            foreach ($item['addons'] as $addon) {
                $subtotal += ($addon['price'] * $item['quantity']);
            }
        }
    }
    
    // Add mandatory items
    $mandatoryTotal = 0;
    foreach ($mandatoryItems as $mandItem) {
        $mandatoryTotal += $mandItem['price'];
    }
    
    $taxableAmount = $subtotal + $mandatoryTotal;
    $tax = $taxableAmount * ($taxRate / 100);
    $total = $taxableAmount + $tax;
    
    return [
        'subtotal' => $subtotal,
        'mandatory' => $mandatoryTotal,
        'tax' => $tax,
        'total' => $total
    ];
}

/**
 * Time ago function for orders
 */
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return 'منذ ' . $diff . ' ثانية';
    } elseif ($diff < 3600) {
        return 'منذ ' . floor($diff / 60) . ' دقيقة';
    } elseif ($diff < 86400) {
        return 'منذ ' . floor($diff / 3600) . ' ساعة';
    } else {
        return 'منذ ' . floor($diff / 86400) . ' يوم';
    }
}

/**
 * Generate random table token for QR security
 */
function generateTableToken($tableId) {
    return bin2hex(random_bytes(16)) . '_' . $tableId;
}

/**
 * Validate table token
 */
function validateTableToken($token, $pdo) {
    $parts = explode('_', $token);
    if (count($parts) != 2) {
        return false;
    }
    
    $tableId = (int)$parts[1];
    $stmt = $pdo->prepare("SELECT token FROM tables WHERE id = ? AND status = 'active'");
    $stmt->execute([$tableId]);
    $table = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $table && hash_equals($table['token'], $token);
}
