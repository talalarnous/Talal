/**
 * Syr AiX - Client-Side JavaScript
 * Digital Menu SaaS Platform
 */

// ============================================
// GLOBAL STATE
// ============================================

let cart = [];
let currentLanguage = 'ar';
let restaurantId = null;
let tableId = null;

// ============================================
// INITIALIZATION
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

function initializeApp() {
    loadCartFromStorage();
    setupEventListeners();
    updateCartDisplay();
    initializeGlideSliders();
}

// ============================================
// EVENT LISTENERS
// ============================================

function setupEventListeners() {
    // Language switcher
    document.querySelectorAll('.lang-switch').forEach(btn => {
        btn.addEventListener('click', handleLanguageChange);
    });
    
    // Category filter
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.addEventListener('click', handleCategoryFilter);
    });
    
    // Add to cart buttons
    document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
        btn.addEventListener('click', handleAddToCart);
    });
    
    // Product detail modal
    document.querySelectorAll('.product-detail-btn').forEach(btn => {
        btn.addEventListener('click', handleProductDetail);
    });
    
    // Cart toggle
    const cartToggle = document.querySelector('.cart-toggle');
    if (cartToggle) {
        cartToggle.addEventListener('click', toggleCart);
    }
    
    // Close cart
    const closeCart = document.querySelector('.close-cart');
    if (closeCart) {
        closeCart.addEventListener('click', closeCartSidebar);
    }
    
    // Checkout button
    const checkoutBtn = document.querySelector('.checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', handleCheckout);
    }
    
    // Modal close
    document.querySelectorAll('.modal-close, .modal-overlay').forEach(el => {
        el.addEventListener('click', closeModal);
    });
    
    // Quantity buttons in cart
    document.addEventListener('click', handleCartQuantity);
}

// ============================================
// LANGUAGE HANDLING
// ============================================

function handleLanguageChange(e) {
    e.preventDefault();
    const lang = e.target.dataset.lang;
    currentLanguage = lang;
    
    // Save to session storage
    sessionStorage.setItem('language', lang);
    
    // Update body class for RTL/LTR
    document.body.classList.remove('rtl', 'ltr');
    if (lang === 'ar') {
        document.body.classList.add('rtl');
    } else {
        document.body.classList.add('ltr');
    }
    
    // Reload page with language parameter
    const url = new URL(window.location);
    url.searchParams.set('lang', lang);
    window.location.href = url.toString();
}

function setLanguage(lang) {
    currentLanguage = lang || 'ar';
    document.body.classList.remove('rtl', 'ltr');
    if (currentLanguage === 'ar') {
        document.body.classList.add('rtl');
    } else {
        document.body.classList.add('ltr');
    }
}

// ============================================
// CART FUNCTIONALITY
// ============================================

function loadCartFromStorage() {
    const savedCart = localStorage.getItem('syraix_cart');
    if (savedCart) {
        cart = JSON.parse(savedCart);
    }
}

function saveCartToStorage() {
    localStorage.setItem('syraix_cart', JSON.stringify(cart));
}

function handleAddToCart(e) {
    e.preventDefault();
    const productId = e.target.dataset.productId;
    const productName = e.target.dataset.productName;
    const productPrice = parseFloat(e.target.dataset.productPrice);
    const productImage = e.target.dataset.productImage || '/assets/images/placeholder.jpg';
    
    addToCart(productId, productName, productPrice, productImage);
}

function addToCart(productId, name, price, image, quantity = 1, addons = []) {
    const existingItem = cart.find(item => item.id === productId);
    
    if (existingItem) {
        existingItem.quantity += quantity;
        if (addons.length > 0) {
            existingItem.addons = [...existingItem.addons, ...addons];
        }
    } else {
        cart.push({
            id: productId,
            name: name,
            price: price,
            image: image,
            quantity: quantity,
            addons: addons
        });
    }
    
    saveCartToStorage();
    updateCartDisplay();
    showNotification('تمت الإضافة إلى السلة', 'success');
}

function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId);
    saveCartToStorage();
    updateCartDisplay();
}

function updateCartItemQuantity(productId, quantity) {
    const item = cart.find(item => item.id === productId);
    if (item) {
        if (quantity <= 0) {
            removeFromCart(productId);
        } else {
            item.quantity = quantity;
            saveCartToStorage();
            updateCartDisplay();
        }
    }
}

function handleCartQuantity(e) {
    if (e.target.classList.contains('qty-increase')) {
        const itemId = e.target.dataset.itemId;
        const item = cart.find(i => i.id === itemId);
        if (item) {
            updateCartItemQuantity(itemId, item.quantity + 1);
        }
    }
    
    if (e.target.classList.contains('qty-decrease')) {
        const itemId = e.target.dataset.itemId;
        const item = cart.find(i => i.id === itemId);
        if (item) {
            updateCartItemQuantity(itemId, item.quantity - 1);
        }
    }
    
    if (e.target.classList.contains('qty-remove')) {
        const itemId = e.target.dataset.itemId;
        removeFromCart(itemId);
    }
}

function updateCartDisplay() {
    const cartItemsContainer = document.querySelector('.cart-items');
    const cartCount = document.querySelector('.cart-count');
    const cartSubtotal = document.querySelector('.cart-subtotal');
    const cartTax = document.querySelector('.cart-tax');
    const cartMandatory = document.querySelector('.cart-mandatory');
    const cartTotal = document.querySelector('.cart-total-amount');
    
    if (!cartItemsContainer) return;
    
    // Update cart count badge
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    if (cartCount) {
        cartCount.textContent = totalItems;
        cartCount.style.display = totalItems > 0 ? 'block' : 'none';
    }
    
    // Render cart items
    if (cart.length === 0) {
        cartItemsContainer.innerHTML = '<p class="text-muted text-center">السلة فارغة</p>';
    } else {
        cartItemsContainer.innerHTML = cart.map(item => `
            <div class="cart-item" data-item-id="${item.id}">
                <img src="${item.image}" alt="${item.name}" class="cart-item-image">
                <div class="cart-item-details">
                    <h4 class="cart-item-name">${item.name}</h4>
                    <p class="cart-item-price">$${item.price.toFixed(2)}</p>
                    <div class="cart-item-quantity">
                        <button class="qty-btn qty-decrease" data-item-id="${item.id}">-</button>
                        <span>${item.quantity}</span>
                        <button class="qty-btn qty-increase" data-item-id="${item.id}">+</button>
                        <button class="qty-btn qty-remove" data-item-id="${item.id}" style="margin-right:auto;color:var(--danger-color)">×</button>
                    </div>
                </div>
            </div>
        `).join('');
    }
    
    // Calculate totals
    calculateTotals();
}

function calculateTotals() {
    const taxRate = parseFloat(document.querySelector('[data-tax-rate]')?.dataset.taxRate || 0);
    const mandatoryItemsTotal = parseFloat(document.querySelector('[data-mandatory-total]')?.dataset.mandatoryTotal || 0);
    
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const taxAmount = subtotal * (taxRate / 100);
    const total = subtotal + taxAmount + mandatoryItemsTotal;
    
    const cartSubtotal = document.querySelector('.cart-subtotal .amount');
    const cartTax = document.querySelector('.cart-tax .amount');
    const cartMandatory = document.querySelector('.cart-mandatory .amount');
    const cartTotal = document.querySelector('.cart-total-amount .amount');
    
    if (cartSubtotal) cartSubtotal.textContent = `$${subtotal.toFixed(2)}`;
    if (cartTax) cartTax.textContent = `$${taxAmount.toFixed(2)}`;
    if (cartMandatory) cartMandatory.textContent = `$${mandatoryItemsTotal.toFixed(2)}`;
    if (cartTotal) cartTotal.textContent = `$${total.toFixed(2)}`;
    
    return { subtotal, taxAmount, mandatoryItemsTotal, total };
}

function toggleCart() {
    const cartSidebar = document.querySelector('.cart-sidebar');
    if (cartSidebar) {
        cartSidebar.classList.toggle('open');
    }
}

function closeCartSidebar() {
    const cartSidebar = document.querySelector('.cart-sidebar');
    if (cartSidebar) {
        cartSidebar.classList.remove('open');
    }
}

// ============================================
// CHECKOUT
// ============================================

async function handleCheckout(e) {
    e.preventDefault();
    
    if (cart.length === 0) {
        showNotification('السلة فارغة!', 'error');
        return;
    }
    
    const totals = calculateTotals();
    const notes = document.querySelector('#order-notes')?.value || '';
    
    const orderData = {
        table_id: tableId,
        items: cart,
        subtotal: totals.subtotal,
        tax_amount: totals.taxAmount,
        mandatory_items_total: totals.mandatoryItemsTotal,
        total_amount: totals.total,
        notes: notes
    };
    
    try {
        const response = await fetch('/api/orders/create.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(orderData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('تم إرسال الطلب بنجاح!', 'success');
            cart = [];
            saveCartToStorage();
            updateCartDisplay();
            closeCartSidebar();
            
            // Redirect to success page or show confirmation
            setTimeout(() => {
                window.location.href = `/client/order-success.php?order_id=${result.order_id}`;
            }, 2000);
        } else {
            showNotification(result.message || 'حدث خطأ في معالجة الطلب', 'error');
        }
    } catch (error) {
        console.error('Checkout error:', error);
        showNotification('حدث خطأ في الاتصال بالخادم', 'error');
    }
}

// ============================================
// PRODUCT DETAILS MODAL
// ============================================

function handleProductDetail(e) {
    e.preventDefault();
    const productId = e.target.dataset.productId;
    
    fetch(`/api/products/get.php?id=${productId}`)
        .then(response => response.json())
        .then(product => {
            showProductModal(product);
        })
        .catch(error => {
            console.error('Error loading product details:', error);
            showNotification('فشل تحميل تفاصيل المنتج', 'error');
        });
}

function showProductModal(product) {
    const modal = document.querySelector('.product-modal');
    if (!modal) return;
    
    const modalBody = modal.querySelector('.modal-body');
    modalBody.innerHTML = `
        <div class="glide" id="product-slider-${product.id}">
            <div class="glide__track" data-glide-el="track">
                <ul class="glide__slides">
                    ${product.images.map(img => `
                        <li class="glide__slide">
                            <img src="/uploads/${img}" alt="${product.name_ar}">
                        </li>
                    `).join('')}
                </ul>
            </div>
            <div class="glide__bullets" data-glide-el="controls[nav]"></div>
        </div>
        
        <h3 class="product-name">${getFieldTranslation(product.name_ar, product.name_en, product.name_tr)}</h3>
        <p class="product-description">${getFieldTranslation(product.description_ar, product.description_en, product.description_tr)}</p>
        
        ${product.ingredients_ar ? `
            <div class="product-section">
                <h4>المكونات</h4>
                <p>${getFieldTranslation(product.ingredients_ar, product.ingredients_en, product.ingredients_tr)}</p>
            </div>
        ` : ''}
        
        ${product.health_info ? `
            <div class="product-section">
                <h4>معلومات صحية</h4>
                <p>${product.health_info}</p>
            </div>
        ` : ''}
        
        <div class="product-price-section">
            <span class="product-price">$${product.offer_price || product.price}</span>
            ${product.offer_price ? `<span class="old-price">$${product.price}</span>` : ''}
        </div>
        
        ${product.addons && product.addons.length > 0 ? `
            <div class="addons-section">
                <h4>إضافات</h4>
                ${product.addons.map(addon => `
                    <label class="addon-item">
                        <input type="checkbox" name="addon_${addon.id}" value="${addon.id}" data-price="${addon.price}">
                        ${getFieldTranslation(addon.name_ar, addon.name_en, addon.name_tr)} (+$${addon.price})
                    </label>
                `).join('')}
            </div>
        ` : ''}
    `;
    
    // Initialize Glide slider
    new Glide(`#product-slider-${product.id}`, {
        type: 'carousel',
        perView: 1,
        autoplay: 3000
    }).mount();
    
    // Show modal
    modal.classList.add('active');
    
    // Setup add to cart button in modal
    const addToCartBtn = modal.querySelector('.modal-add-to-cart');
    if (addToCartBtn) {
        addToCartBtn.onclick = () => {
            const selectedAddons = Array.from(modal.querySelectorAll('.addon-item input:checked')).map(input => ({
                id: input.value,
                price: parseFloat(input.dataset.price)
            }));
            
            addToCart(product.id, getFieldTranslation(product.name_ar, product.name_en, product.name_tr), 
                     parseFloat(product.offer_price || product.price), 
                     `/uploads/${product.images[0]}`, 1, selectedAddons);
            closeModal();
        };
    }
}

function closeModal() {
    document.querySelectorAll('.modal-overlay').forEach(modal => {
        modal.classList.remove('active');
    });
}

// ============================================
// CATEGORY FILTERING
// ============================================

function handleCategoryFilter(e) {
    e.preventDefault();
    const categoryId = e.target.dataset.categoryId;
    
    // Update active state
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    e.target.classList.add('active');
    
    // Filter products
    document.querySelectorAll('.product-card').forEach(card => {
        if (categoryId === 'all' || card.dataset.categoryId === categoryId) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// ============================================
// GLIDE.JS INITIALIZATION
// ============================================

function initializeGlideSliders() {
    document.querySelectorAll('.product-glide').forEach(glideEl => {
        new Glide(glideEl, {
            type: 'carousel',
            perView: 1,
            autoplay: 3000
        }).mount();
    });
}

// ============================================
// NOTIFICATIONS (SweetAlert2 wrapper)
// ============================================

function showNotification(message, type = 'info') {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: type,
            title: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            background: '#1a1a1a',
            color: '#ffffff',
            customClass: {
                popup: 'swal-custom-popup'
            }
        });
    } else {
        alert(message);
    }
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

function getFieldTranslation(ar, en, tr) {
    switch (currentLanguage) {
        case 'en':
            return en || ar;
        case 'tr':
            return tr || ar;
        default:
            return ar;
    }
}

function formatPrice(price) {
    return '$' + parseFloat(price).toFixed(2);
}

// ============================================
// AJAX LONG POLLING FOR LIVE ORDERS (Admin)
// ============================================

function startLiveOrdersPolling() {
    const ordersContainer = document.querySelector('.live-orders-container');
    if (!ordersContainer) return;
    
    function fetchOrders() {
        fetch('/api/orders/live.php')
            .then(response => response.json())
            .then(orders => {
                renderLiveOrders(orders);
                setTimeout(fetchOrders, 5000); // Poll every 5 seconds
            })
            .catch(error => {
                console.error('Error fetching live orders:', error);
                setTimeout(fetchOrders, 10000); // Retry after 10 seconds on error
            });
    }
    
    fetchOrders();
}

function renderLiveOrders(orders) {
    const columns = {
        new: document.querySelector('.orders-column[data-status="new"]'),
        preparing: document.querySelector('.orders-column[data-status="preparing"]'),
        ready: document.querySelector('.orders-column[data-status="ready"]')
    };
    
    Object.values(columns).forEach(col => {
        if (col) col.innerHTML = '';
    });
    
    orders.forEach(order => {
        const orderCard = createOrderCard(order);
        if (columns[order.order_status]) {
            columns[order.order_status].appendChild(orderCard);
        }
    });
}

function createOrderCard(order) {
    const card = document.createElement('div');
    card.className = 'card order-card';
    card.innerHTML = `
        <div class="order-header">
            <span class="order-number">#${order.id}</span>
            <span class="table-number">طاولة ${order.table_number}</span>
        </div>
        <div class="order-items">
            ${order.items.map(item => `
                <div class="order-item">
                    <span class="quantity">${item.quantity}x</span>
                    <span class="name">${item.product_name}</span>
                </div>
            `).join('')}
        </div>
        <div class="order-footer">
            <span class="total">$${order.total_amount}</span>
            <span class="time">${formatTime(order.created_at)}</span>
        </div>
    `;
    return card;
}

function formatTime(timestamp) {
    const date = new Date(timestamp);
    return date.toLocaleTimeString('ar-SY', { hour: '2-digit', minute: '2-digit' });
}

// Export functions for external use
window.SyrAiX = {
    addToCart,
    removeFromCart,
    updateCartItemQuantity,
    toggleCart,
    closeCartSidebar,
    showNotification,
    setLanguage
};
