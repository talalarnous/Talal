<?php
/**
 * Syr AiX - Customer Menu Page
 * Main landing page and interactive menu for customers
 * Uses FREE libraries: Glide.js, AOS, SweetAlert2, Chart.js
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';

// Get table token from URL
$tableToken = $_GET['table'] ?? '';
$tableInfo = null;

// Validate table if token exists
if (!empty($tableToken)) {
    $pdo = getDBConnection();
    $parts = explode('_', $tableToken);
    if (count($parts) >= 2) {
        $tableId = (int)end($parts);
        $stmt = $pdo->prepare("SELECT id, table_number, status FROM tables WHERE token = ? AND status = 'active'");
        $stmt->execute([$tableToken]);
        $tableInfo = $stmt->fetch();
    }
}

// Get restaurant settings
$settings = getRestaurantSettings();
$mandatoryItems = getMandatoryItems();

// Determine language
$lang = $_SESSION['lang'] ?? 'ar';
$isRTL = ($lang === 'ar');

// Get translations for UI
$uiTranslations = [
    'ar' => [
        'welcome' => $settings['welcome_message_ar'],
        'start_order' => 'ابدأ الطلب الآن',
        'menu' => 'القائمة',
        'categories' => 'الأقسام',
        'add_to_cart' => 'أضف للسلة',
        'view_details' => 'التفاصيل',
        'cart' => 'السلة',
        'total' => 'الإجمالي',
        'subtotal' => 'المجموع',
        'tax' => 'الضريبة',
        'mandatory' => 'بنود إلزامية',
        'checkout' => 'تأكيد الطلب وطلب الحساب',
        'table' => 'الطاولة',
        'language' => 'اللغة',
        'close' => 'إغلاق',
        'quantity' => 'الكمية',
        'ingredients' => 'المكونات',
        'health_info' => 'معلومات صحية',
        'calories' => 'سعرات حرارية',
        'addons' => 'إضافات',
        'notes' => 'ملاحظات',
        'order_success' => 'تم إرسال طلبك بنجاح!',
        'order_failed' => 'حدث خطأ في إرسال الطلب',
        'empty_cart' => 'السلة فارغة',
        'currency' => $settings['currency']
    ],
    'en' => [
        'welcome' => $settings['welcome_message_en'],
        'start_order' => 'Start Order Now',
        'menu' => 'Menu',
        'categories' => 'Categories',
        'add_to_cart' => 'Add to Cart',
        'view_details' => 'Details',
        'cart' => 'Cart',
        'total' => 'Total',
        'subtotal' => 'Subtotal',
        'tax' => 'Tax',
        'mandatory' => 'Mandatory Items',
        'checkout' => 'Confirm Order & Request Bill',
        'table' => 'Table',
        'language' => 'Language',
        'close' => 'Close',
        'quantity' => 'Quantity',
        'ingredients' => 'Ingredients',
        'health_info' => 'Health Info',
        'calories' => 'Calories',
        'addons' => 'Add-ons',
        'notes' => 'Notes',
        'order_success' => 'Your order has been sent successfully!',
        'order_failed' => 'Failed to send order',
        'empty_cart' => 'Cart is empty',
        'currency' => $settings['currency']
    ],
    'tr' => [
        'welcome' => $settings['welcome_message_tr'],
        'start_order' => 'Siparişi Başlat',
        'menu' => 'Menü',
        'categories' => 'Kategoriler',
        'add_to_cart' => 'Sepete Ekle',
        'view_details' => 'Detaylar',
        'cart' => 'Sepet',
        'total' => 'Toplam',
        'subtotal' => 'Ara Toplam',
        'tax' => 'Vergi',
        'mandatory' => 'Zorunlu Ürünler',
        'checkout' => 'Siparişi Onayla ve Hesap İste',
        'table' => 'Masa',
        'language' => 'Dil',
        'close' => 'Kapat',
        'quantity' => 'Miktar',
        'ingredients' => 'İçindekiler',
        'health_info' => 'Sağlık Bilgisi',
        'calories' => 'Kalori',
        'addons' => 'Ekstralar',
        'notes' => 'Notlar',
        'order_success' => 'Siparişiniz başarıyla gönderildi!',
        'order_failed' => 'Sipariş gönderilemedi',
        'empty_cart' => 'Sepet boş',
        'currency' => $settings['currency']
    ]
];

$t = $uiTranslations[$lang];
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $isRTL ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $settings['restaurant_name_' . $lang]; ?> - Syr AiX</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- AOS Animation CSS -->
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
    
    <!-- Glide.js Slider CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Glide.js/3.6.0/css/glide.core.min.css">
    
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --bg-primary: #0a0a0a;
            --bg-secondary: #1a1a1a;
            --bg-card: #252525;
            --text-primary: #ffffff;
            --text-secondary: #b0b0b0;
            --accent: #C0D906;
            --accent-hover: #a8c206;
            --danger: #ff4757;
            --success: #2ed573;
            --border-radius: 12px;
            --shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: <?php echo $isRTL ? "'Cairo', " : "'Poppins', "; ?>sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
            overflow-x: hidden;
        }
        
        /* Language Switcher */
        .lang-switcher {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            display: flex;
            gap: 8px;
        }
        
        .lang-btn {
            padding: 8px 16px;
            background: var(--bg-secondary);
            border: 2px solid var(--accent);
            border-radius: 20px;
            color: var(--text-primary);
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .lang-btn:hover, .lang-btn.active {
            background: var(--accent);
            color: var(--bg-primary);
        }
        
        /* Landing Page */
        .landing-page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 40px 20px;
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
        }
        
        .landing-logo {
            width: 150px;
            height: 150px;
            margin-bottom: 30px;
            object-fit: contain;
        }
        
        .landing-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: var(--accent);
        }
        
        .landing-welcome {
            font-size: 1.3rem;
            color: var(--text-secondary);
            margin-bottom: 40px;
            max-width: 500px;
        }
        
        .start-btn {
            padding: 18px 50px;
            background: var(--accent);
            color: var(--bg-primary);
            border: none;
            border-radius: 50px;
            font-size: 1.2rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 30px rgba(192, 217, 6, 0.3);
        }
        
        .start-btn:hover {
            background: var(--accent-hover);
            transform: translateY(-3px);
            box-shadow: 0 12px 40px rgba(192, 217, 6, 0.4);
        }
        
        /* Menu Page */
        .menu-page {
            display: none;
            padding-bottom: 100px;
        }
        
        .menu-header {
            background: var(--bg-secondary);
            padding: 20px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: var(--shadow);
        }
        
        .category-filter {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding: 10px 0;
            scrollbar-width: thin;
            scrollbar-color: var(--accent) var(--bg-secondary);
        }
        
        .category-filter::-webkit-scrollbar {
            height: 6px;
        }
        
        .category-filter::-webkit-scrollbar-thumb {
            background: var(--accent);
            border-radius: 3px;
        }
        
        .cat-btn {
            padding: 10px 20px;
            background: var(--bg-card);
            border: 2px solid transparent;
            border-radius: 25px;
            color: var(--text-primary);
            white-space: nowrap;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
        }
        
        .cat-btn:hover, .cat-btn.active {
            border-color: var(--accent);
            color: var(--accent);
        }
        
        /* Products Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .product-card {
            background: var(--bg-card);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
        }
        
        .product-slider {
            position: relative;
            height: 200px;
            background: var(--bg-secondary);
        }
        
        .glide__slide img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .product-info {
            padding: 20px;
        }
        
        .product-name {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--accent);
        }
        
        .product-desc {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .product-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .product-price {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--accent);
        }
        
        .product-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-icon {
            padding: 10px 15px;
            background: var(--bg-secondary);
            border: none;
            border-radius: 8px;
            color: var(--text-primary);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-icon:hover {
            background: var(--accent);
            color: var(--bg-primary);
        }
        
        .btn-add {
            padding: 10px 20px;
            background: var(--accent);
            border: none;
            border-radius: 8px;
            color: var(--bg-primary);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-add:hover {
            background: var(--accent-hover);
        }
        
        /* Floating Cart */
        .floating-cart {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--accent);
            color: var(--bg-primary);
            padding: 15px 30px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 8px 30px rgba(192, 217, 6, 0.4);
            z-index: 999;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .floating-cart:hover {
            transform: translateX(-50%) translateY(-5px);
        }
        
        .cart-count {
            background: var(--bg-primary);
            color: var(--accent);
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }
        
        .cart-total {
            font-weight: 700;
            font-size: 1.1rem;
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 2000;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .modal-content {
            background: var(--bg-card);
            border-radius: var(--border-radius);
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }
        
        .modal-header {
            padding: 20px;
            border-bottom: 1px solid var(--bg-secondary);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-close {
            background: none;
            border: none;
            color: var(--text-primary);
            font-size: 1.5rem;
            cursor: pointer;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        /* Cart Modal */
        .cart-items {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 20px;
        }
        
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid var(--bg-secondary);
        }
        
        .cart-item-info {
            flex: 1;
        }
        
        .cart-item-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .qty-btn {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: none;
            background: var(--bg-secondary);
            color: var(--text-primary);
            cursor: pointer;
            font-weight: 700;
        }
        
        .cart-totals {
            background: var(--bg-secondary);
            padding: 20px;
            border-radius: var(--border-radius);
            margin-top: 20px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .total-row.final {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--accent);
            border-top: 2px solid var(--accent);
            padding-top: 15px;
            margin-top: 15px;
        }
        
        .checkout-btn {
            width: 100%;
            padding: 15px;
            background: var(--accent);
            border: none;
            border-radius: var(--border-radius);
            color: var(--bg-primary);
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s ease;
        }
        
        .checkout-btn:hover {
            background: var(--accent-hover);
        }
        
        /* Footer Watermark */
        .watermark {
            text-align: center;
            padding: 20px;
            color: var(--text-secondary);
            font-size: 0.8rem;
            opacity: 0.6;
        }
        
        .watermark strong {
            color: var(--accent);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .landing-title {
                font-size: 2rem;
            }
            
            .products-grid {
                grid-template-columns: 1fr;
            }
            
            .floating-cart {
                width: 90%;
                justify-content: space-between;
            }
        }
    </style>
</head>
<body>
    <!-- Language Switcher -->
    <div class="lang-switcher">
        <button class="lang-btn <?php echo $lang === 'ar' ? 'active' : ''; ?>" onclick="setLanguage('ar')">عربي</button>
        <button class="lang-btn <?php echo $lang === 'en' ? 'active' : ''; ?>" onclick="setLanguage('en')">EN</button>
        <button class="lang-btn <?php echo $lang === 'tr' ? 'active' : ''; ?>" onclick="setLanguage('tr')">TR</button>
    </div>

    <!-- Landing Page -->
    <div class="landing-page" id="landingPage">
        <?php if ($settings['logo']): ?>
        <img src="<?php echo htmlspecialchars($settings['logo']); ?>" alt="Logo" class="landing-logo" data-aos="zoom-in">
        <?php endif; ?>
        
        <h1 class="landing-title" data-aos="fade-up"><?php echo $settings['restaurant_name_' . $lang]; ?></h1>
        <p class="landing-welcome" data-aos="fade-up" data-aos-delay="100"><?php echo $t['welcome']; ?></p>
        
        <button class="start-btn" data-aos="zoom-in" data-aos-delay="200" onclick="showMenu()">
            <?php echo $t['start_order']; ?>
        </button>
    </div>

    <!-- Menu Page -->
    <div class="menu-page" id="menuPage">
        <div class="menu-header">
            <h2 style="color: var(--accent); margin-bottom: 10px;"><?php echo $settings['restaurant_name_' . $lang]; ?></h2>
            <?php if ($tableInfo): ?>
            <span style="color: var(--text-secondary);"><?php echo $t['table']; ?> #<?php echo htmlspecialchars($tableInfo['table_number']); ?></span>
            <?php endif; ?>
            
            <div class="category-filter" id="categoryFilter">
                <!-- Categories will be loaded here -->
            </div>
        </div>
        
        <div class="products-grid" id="productsGrid">
            <!-- Products will be loaded here -->
        </div>
        
        <div class="watermark">
            <strong>Syr AiX</strong> Where AI Meets Creativity
        </div>
    </div>

    <!-- Floating Cart Button -->
    <div class="floating-cart" id="floatingCart" style="display: none;" onclick="openCartModal()">
        <span><?php echo $t['cart']; ?></span>
        <div class="cart-count" id="cartCount">0</div>
        <div class="cart-total" id="cartTotal">0.00 <?php echo $t['currency']; ?></div>
    </div>

    <!-- Product Details Modal -->
    <div class="modal" id="productModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalProductName"></h3>
                <button class="modal-close" onclick="closeModal('productModal')">&times;</button>
            </div>
            <div class="modal-body" id="modalProductBody">
                <!-- Product details will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Cart Modal -->
    <div class="modal" id="cartModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><?php echo $t['cart']; ?></h3>
                <button class="modal-close" onclick="closeModal('cartModal')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="cart-items" id="cartItems">
                    <!-- Cart items will be rendered here -->
                </div>
                
                <div class="cart-totals">
                    <div class="total-row">
                        <span><?php echo $t['subtotal']; ?></span>
                        <span id="cartSubtotal">0.00 <?php echo $t['currency']; ?></span>
                    </div>
                    <div class="total-row">
                        <span><?php echo $t['mandatory']; ?></span>
                        <span id="cartMandatory">0.00 <?php echo $t['currency']; ?></span>
                    </div>
                    <div class="total-row">
                        <span><?php echo $t['tax']; ?> (<?php echo $settings['tax_rate']; ?>%)</span>
                        <span id="cartTax">0.00 <?php echo $t['currency']; ?></span>
                    </div>
                    <div class="total-row final">
                        <span><?php echo $t['total']; ?></span>
                        <span id="cartFinalTotal">0.00 <?php echo $t['currency']; ?></span>
                    </div>
                </div>
                
                <textarea placeholder="<?php echo $t['notes']; ?>" id="orderNotes" style="width: 100%; padding: 10px; margin-top: 15px; background: var(--bg-secondary); border: none; border-radius: 8px; color: var(--text-primary); resize: vertical;"></textarea>
                
                <button class="checkout-btn" onclick="checkout()">
                    <?php echo $t['checkout']; ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Glide.js/3.6.0/js/glide.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Initialize
        const lang = '<?php echo $lang; ?>';
        const isRTL = <?php echo $isRTL ? 'true' : 'false'; ?>;
        const tableId = <?php echo $tableInfo ? (int)$tableInfo['id'] : 'null'; ?>;
        const tableNumber = '<?php echo $tableInfo ? htmlspecialchars($tableInfo['table_number']) : ''; ?>';
        const taxRate = <?php echo $settings['tax_rate']; ?>;
        const currency = '<?php echo $t['currency']; ?>';
        
        const mandatoryItems = <?php echo json_encode($mandatoryItems); ?>;
        const translations = <?php echo json_encode($t); ?>;
        
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        let categories = [];
        let currentCategory = null;
        
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true
        });
        
        // Set language
        function setLanguage(newLang) {
            fetch('api.php?action=get_settings')
                .then(res => res.json())
                .then(data => {
                    localStorage.setItem('lang', newLang);
                    window.location.reload();
                });
        }
        
        // Show menu
        function showMenu() {
            document.getElementById('landingPage').style.display = 'none';
            document.getElementById('menuPage').style.display = 'block';
            loadCategories();
        }
        
        // Load categories
        function loadCategories() {
            fetch('api.php?action=get_categories')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        categories = data.data;
                        renderCategories();
                        if (categories.length > 0) {
                            loadProducts(categories[0].id);
                        }
                    }
                });
        }
        
        // Render categories
        function renderCategories() {
            const container = document.getElementById('categoryFilter');
            container.innerHTML = categories.map((cat, index) => `
                <button class="cat-btn ${index === 0 ? 'active' : ''}" 
                        onclick="loadProducts(${cat.id}, this)">
                    ${cat.icon || ''} ${cat.name_<?php echo $lang; ?>} (${cat.product_count})
                </button>
            `).join('');
        }
        
        // Load products
        function loadProducts(categoryId, btn = null) {
            currentCategory = categoryId;
            
            // Update active button
            if (btn) {
                document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
            }
            
            fetch(`api.php?action=get_products&category_id=${categoryId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        renderProducts(data.data);
                        initSliders();
                    }
                });
        }
        
        // Render products
        function renderProducts(products) {
            const container = document.getElementById('productsGrid');
            container.innerHTML = products.map(product => {
                const images = product.images ? product.images.split('|') : [];
                const hasDiscount = product.discount_price && product.discount_price < product.price;
                const price = hasDiscount ? product.discount_price : product.price;
                
                return `
                    <div class="product-card" data-aos="fade-up">
                        <div class="product-slider glide_${product.id}">
                            <div class="glide__track" data-glide-el="track">
                                <ul class="glide__slides">
                                    ${images.length > 0 ? images.map(img => `
                                        <li class="glide__slide">
                                            <img src="${img}" alt="${product.name_<?php echo $lang; ?>}">
                                        </li>
                                    `).join('') : '<li class="glide__slide"><div style="height:200px;display:flex;align-items:center;justify-content:center;color:var(--text-secondary);">No Image</div></li>'}
                                </ul>
                            </div>
                        </div>
                        <div class="product-info">
                            <h3 class="product-name">${product.name_<?php echo $lang; ?>}</h3>
                            <p class="product-desc">${product.description_<?php echo $lang; ?> || ''}</p>
                            <div class="product-footer">
                                <span class="product-price">${price.toFixed(2)} ${currency}</span>
                                <div class="product-actions">
                                    <button class="btn-icon" onclick="showProductDetails(${product.id})">📋</button>
                                    <button class="btn-add" onclick="addToCart(${product.id}, '${product.name_<?php echo $lang; ?>}', ${price})">
                                        ${translations.add_to_cart}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }
        
        // Initialize sliders
        function initSliders() {
            categories.forEach(cat => {
                // Sliders will be initialized per product card
            });
            
            document.querySelectorAll('[class^="glide_"]').forEach((el, index) => {
                new Glide(el, {
                    type: 'carousel',
                    perView: 1,
                    autoplay: 3000
                }).mount();
            });
        }
        
        // Show product details
        function showProductDetails(productId) {
            fetch(`api.php?action=get_product_details&product_id=${productId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const product = data.data;
                        document.getElementById('modalProductName').textContent = product.name_<?php echo $lang; ?>;
                        
                        let html = '';
                        
                        // Images
                        if (product.images && product.images.length > 0) {
                            html += `<div style="margin-bottom: 20px;">`;
                            product.images.forEach(img => {
                                html += `<img src="${img}" style="width: 100%; border-radius: 8px; margin-bottom: 10px;">`;
                            });
                            html += `</div>`;
                        }
                        
                        // Description
                        if (product.description_<?php echo $lang; ?>) {
                            html += `<p style="margin-bottom: 15px;">${product.description_<?php echo $lang; ?>}</p>`;
                        }
                        
                        // Ingredients
                        if (product.ingredients_<?php echo $lang; ?>) {
                            html += `<div style="margin-bottom: 15px;">
                                <strong style="color: var(--accent);">${translations.ingredients}:</strong>
                                <p>${product.ingredients_<?php echo $lang; ?>}</p>
                            </div>`;
                        }
                        
                        // Health info
                        if (product.health_info_<?php echo $lang; ?> || product.calories) {
                            html += `<div style="margin-bottom: 15px;">
                                <strong style="color: var(--accent);">${translations.health_info}:</strong>
                                <p>${product.health_info_<?php echo $lang; ?> || ''}</p>
                                ${product.calories ? `<p>🔥 ${translations.calories}: ${product.calories}</p>` : ''}
                            </div>`;
                        }
                        
                        // Addons
                        if (product.addons && product.addons.length > 0) {
                            html += `<div style="margin-bottom: 20px;">
                                <strong style="color: var(--accent);">${translations.addons}:</strong>
                                ${product.addons.map(addon => `
                                    <label style="display: block; margin: 10px 0; padding: 10px; background: var(--bg-secondary); border-radius: 8px;">
                                        <input type="checkbox" class="addon-checkbox" value='${JSON.stringify(addon)}'>
                                        ${addon.name_<?php echo $lang; ?>} (+${addon.price.toFixed(2)} ${currency})
                                    </label>
                                `).join('')}
                            </div>`;
                        }
                        
                        // Quantity and Add to Cart
                        html += `<div style="display: flex; gap: 10px; align-items: center; margin-top: 20px;">
                            <label>${translations.quantity}:</label>
                            <input type="number" id="productQty" value="1" min="1" style="width: 60px; padding: 8px; background: var(--bg-secondary); border: none; border-radius: 8px; color: var(--text-primary); text-align: center;">
                            <button class="btn-add" onclick="addToCartFromModal(${product.id}, '${product.name_<?php echo $lang; ?>}', ${product.price})" style="flex: 1;">
                                ${translations.add_to_cart}
                            </button>
                        </div>`;
                        
                        document.getElementById('modalProductBody').innerHTML = html;
                        document.getElementById('productModal').style.display = 'flex';
                    }
                });
        }
        
        // Add to cart
        function addToCart(productId, productName, price) {
            const existingItem = cart.find(item => item.product_id === productId && !item.selectedAddons);
            
            if (existingItem) {
                existingItem.quantity++;
            } else {
                cart.push({
                    product_id: productId,
                    name: productName,
                    price: price,
                    quantity: 1,
                    selectedAddons: []
                });
            }
            
            saveCart();
            updateCartUI();
            
            Swal.fire({
                icon: 'success',
                title: '✓',
                text: translations.order_success,
                timer: 1500,
                showConfirmButton: false,
                background: 'var(--bg-card)',
                color: 'var(--text-primary)'
            });
        }
        
        // Add to cart from modal
        function addToCartFromModal(productId, productName, price) {
            const qty = parseInt(document.getElementById('productQty').value) || 1;
            const addonCheckboxes = document.querySelectorAll('.addon-checkbox:checked');
            const selectedAddons = Array.from(addonCheckboxes).map(cb => JSON.parse(cb.value));
            
            const existingItem = cart.find(item => 
                item.product_id === productId && 
                JSON.stringify(item.selectedAddons) === JSON.stringify(selectedAddons)
            );
            
            if (existingItem) {
                existingItem.quantity += qty;
            } else {
                cart.push({
                    product_id: productId,
                    name: productName,
                    price: price,
                    quantity: qty,
                    selectedAddons: selectedAddons
                });
            }
            
            saveCart();
            updateCartUI();
            closeModal('productModal');
            
            Swal.fire({
                icon: 'success',
                title: '✓',
                text: translations.order_success,
                timer: 1500,
                showConfirmButton: false,
                background: 'var(--bg-card)',
                color: 'var(--text-primary)'
            });
        }
        
        // Save cart to localStorage
        function saveCart() {
            localStorage.setItem('cart', JSON.stringify(cart));
        }
        
        // Update cart UI
        function updateCartUI() {
            const count = cart.reduce((sum, item) => sum + item.quantity, 0);
            const subtotal = cart.reduce((sum, item) => {
                let itemTotal = item.price * item.quantity;
                if (item.selectedAddons) {
                    itemTotal += item.selectedAddons.reduce((s, a) => s + (a.price * item.quantity), 0);
                }
                return sum + itemTotal;
            }, 0);
            
            const mandatoryTotal = mandatoryItems.reduce((sum, item) => sum + parseFloat(item.price), 0);
            const taxableAmount = subtotal + mandatoryTotal;
            const tax = taxableAmount * (taxRate / 100);
            const total = taxableAmount + tax;
            
            document.getElementById('cartCount').textContent = count;
            document.getElementById('cartTotal').textContent = total.toFixed(2) + ' ' + currency;
            
            if (count > 0) {
                document.getElementById('floatingCart').style.display = 'flex';
            } else {
                document.getElementById('floatingCart').style.display = 'none';
            }
        }
        
        // Open cart modal
        function openCartModal() {
            renderCartItems();
            updateCartTotals();
            document.getElementById('cartModal').style.display = 'flex';
        }
        
        // Render cart items
        function renderCartItems() {
            const container = document.getElementById('cartItems');
            
            if (cart.length === 0) {
                container.innerHTML = `<p style="text-align: center; color: var(--text-secondary); padding: 40px;">${translations.empty_cart}</p>`;
                return;
            }
            
            container.innerHTML = cart.map((item, index) => `
                <div class="cart-item">
                    <div class="cart-item-info">
                        <strong>${item.name}</strong>
                        ${item.selectedAddons && item.selectedAddons.length > 0 ? 
                            '<br><small style="color: var(--text-secondary);">' + 
                            item.selectedAddons.map(a => '+' + a.name_<?php echo $lang; ?>).join(', ') + 
                            '</small>' : ''}
                        <br><small style="color: var(--accent);">${item.price.toFixed(2)} ${currency}</small>
                    </div>
                    <div class="cart-item-controls">
                        <button class="qty-btn" onclick="updateCartItem(${index}, -1)">-</button>
                        <span>${item.quantity}</span>
                        <button class="qty-btn" onclick="updateCartItem(${index}, 1)">+</button>
                        <button class="qty-btn" style="background: var(--danger);" onclick="removeCartItem(${index})">×</button>
                    </div>
                </div>
            `).join('');
        }
        
        // Update cart item quantity
        function updateCartItem(index, change) {
            cart[index].quantity += change;
            if (cart[index].quantity <= 0) {
                cart.splice(index, 1);
            }
            saveCart();
            renderCartItems();
            updateCartTotals();
            updateCartUI();
        }
        
        // Remove cart item
        function removeCartItem(index) {
            cart.splice(index, 1);
            saveCart();
            renderCartItems();
            updateCartTotals();
            updateCartUI();
        }
        
        // Update cart totals
        function updateCartTotals() {
            const subtotal = cart.reduce((sum, item) => {
                let itemTotal = item.price * item.quantity;
                if (item.selectedAddons) {
                    itemTotal += item.selectedAddons.reduce((s, a) => s + (a.price * item.quantity), 0);
                }
                return sum + itemTotal;
            }, 0);
            
            const mandatoryTotal = mandatoryItems.reduce((sum, item) => sum + parseFloat(item.price), 0);
            const taxableAmount = subtotal + mandatoryTotal;
            const tax = taxableAmount * (taxRate / 100);
            const total = taxableAmount + tax;
            
            document.getElementById('cartSubtotal').textContent = subtotal.toFixed(2) + ' ' + currency;
            document.getElementById('cartMandatory').textContent = mandatoryTotal.toFixed(2) + ' ' + currency;
            document.getElementById('cartTax').textContent = tax.toFixed(2) + ' ' + currency;
            document.getElementById('cartFinalTotal').textContent = total.toFixed(2) + ' ' + currency;
        }
        
        // Checkout
        function checkout() {
            if (cart.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    text: translations.empty_cart,
                    background: 'var(--bg-card)',
                    color: 'var(--text-primary)'
                });
                return;
            }
            
            if (!tableId) {
                Swal.fire({
                    icon: 'error',
                    text: 'Please scan the QR code from your table',
                    background: 'var(--bg-card)',
                    color: 'var(--text-primary)'
                });
                return;
            }
            
            const notes = document.getElementById('orderNotes').value;
            
            // Calculate totals
            const subtotal = cart.reduce((sum, item) => {
                let itemTotal = item.price * item.quantity;
                if (item.selectedAddons) {
                    itemTotal += item.selectedAddons.reduce((s, a) => s + (a.price * item.quantity), 0);
                }
                return sum + itemTotal;
            }, 0);
            
            const mandatoryTotal = mandatoryItems.reduce((sum, item) => sum + parseFloat(item.price), 0);
            const taxableAmount = subtotal + mandatoryTotal;
            const tax = taxableAmount * (taxRate / 100);
            const total = taxableAmount + tax;
            
            const orderData = {
                table_id: tableId,
                table_number: tableNumber,
                items: cart.map(item => ({
                    product_id: item.product_id,
                    name: item.name,
                    price: item.price,
                    quantity: item.quantity,
                    addons: item.selectedAddons,
                    subtotal: (item.price + (item.selectedAddons ? item.selectedAddons.reduce((s, a) => s + a.price, 0) : 0)) * item.quantity
                })),
                subtotal: subtotal,
                tax: tax,
                mandatory_total: mandatoryTotal,
                total: total,
                notes: notes
            };
            
            fetch('api.php?action=create_order', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(orderData)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    cart = [];
                    saveCart();
                    updateCartUI();
                    closeModal('cartModal');
                    
                    Swal.fire({
                        icon: 'success',
                        title: '✓',
                        text: translations.order_success,
                        background: 'var(--bg-card)',
                        color: 'var(--text-primary)'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        text: data.message || translations.order_failed,
                        background: 'var(--bg-card)',
                        color: 'var(--text-primary)'
                    });
                }
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    text: translations.order_failed,
                    background: 'var(--bg-card)',
                    color: 'var(--text-primary)'
                });
            });
        }
        
        // Close modal
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Close modals on outside click
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
        
        // Initialize cart UI on load
        updateCartUI();
    </script>
</body>
</html>
