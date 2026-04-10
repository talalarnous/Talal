# 🍽️ Syr AiX - Digital Menu SaaS Platform

**Syr AiX Where AI Meets Creativity**

منصة SaaS متكاملة لإنشاء وإدارة القوائم الرقمية التفاعلية للمطاعم والكافيهات

---

## ✨ المميزات Features

### للعملاء (Customers):
- ✅ صفحة هبوط احترافية مع اختيار اللغة (عربي/English/Türkçe)
- ✅ منيو تفاعلي مع فلترة حسب التصنيفات
- ✅ سلايدر صور للمنتجات (Glide.js)
- ✅ سلة تسوق ذكية مع حفظ محلي (localStorage)
- ✅ حساب تلقائي: subtotal + ضريبة + بنود إلزامية
- ✅ نافذة تفاصيل المنتج (Modal)
- ✅ طلب الحساب بضغطة زر
- ✅ أنيميشن AOS على جميع العناصر

### للإدارة (Admin):
- ✅ Dashboard مع إحصائيات ورسوم بيانية (Chart.js)
- ✅ CRUD كامل للأصناف والتصنيفات والطاولات
- ✅ رفع صور متعددة لكل منتج
- ✅ **ترجمة تلقائية مجانية** (MyMemory API - لا يحتاج مفتاح)
- ✅ نظام طلبات حية بثلاث حالات (جديد، قيد التحضير، جاهز)
- ✅ تحديث AJAX كل 5 ثواني (Long Polling)
- ✅ توليد QR Code مجاني (Google Charts API)
- ✅ بنود إجبارية تضاف تلقائياً للفواتير

---

## 🛠️ التقنيات المستخدمة (FREE Only)

| التقنية | الوظيفة |
|---------|---------|
| PHP 8+ | Back-end |
| MySQL | Database |
| Chart.js | الرسوم البيانية |
| Glide.js | سلايدر الصور |
| AOS | أنيميشن التمرير |
| SweetAlert2 | رسائل التنبيه |
| RemixIcon | الأيقونات |
| MyMemory API | الترجمة المجانية |
| Google Charts API | QR Code المجاني |

---

## 📁 هيكل الملفات

```
/workspace/
├── index.php              # صفحة العميل الرئيسية
├── api.php                # واجهة API
├── database.sql           # ملف قاعدة البيانات
├── includes/
│   ├── config.php         # إعدادات قاعدة البيانات
│   └── helpers.php        # دوال مساعدة (ترجمة، QR، إلخ)
└── admin/
    ├── login.php          # تسجيل الدخول
    ├── dashboard.php      # لوحة التحكم
    ├── orders.php         # إدارة الطلبات
    ├── products.php       # إدارة الأصناف
    ├── categories.php     # إدارة التصنيفات
    ├── tables.php         # إدارة الطاولات
    ├── settings.php       # الإعدادات
    └── logout.php         # تسجيل الخروج
```

---

## 🚀 التثبيت Installation

### 1. استيراد قاعدة البيانات:
```bash
mysql -u root -p syr_aix_menu < database.sql
```

### 2. تعديل ملف الإعدادات:
افتح `includes/config.php` وعدل بيانات الاتصال:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'syr_aix_menu');
define('DB_USER', 'root');
define('DB_PASS', '');
define('APP_URL', 'http://yoursite.com');
```

### 3. رفع الملفات للاستضافة:
انقل جميع الملفات إلى مجلد public_html في استضافة Hostinger

### 4. تسجيل الدخول:
- URL: `yoursite.com/admin/login.php`
- المستخدم: `admin`
- كلمة المرور: `admin123`

---

## 🔑 مفاتيح API المجانية

### الترجمة (MyMemory):
- لا يحتاج مفتاح API
- مجاني حتى 1000 كلمة/يوم
- الرابط: `https://api.mymemory.translated.net`

### QR Code (Google Charts):
- لا يحتاج مفتاح API
- مجاني تماماً
- الرابط: `https://chart.googleapis.com/chart`

---

## 📊 قاعدة البيانات

### الجداول:
1. `admins` - مدراء النظام
2. `settings` - إعدادات المطعم
3. `mandatory_items` - البنود الإلزامية
4. `categories` - تصنيفات المنيو
5. `products` - الأصناف
6. `product_images` - صور المنتجات
7. `product_addons` - الإضافات
8. `tables` - الطاولات
9. `orders` - الطلبات
10. `order_items` - عناصر الطلب
11. `analytics` - التحليلات

---

## 🎨 الهوية البصرية

- **الخلفية**: أسود (#0a0a0a) + رمادي داكن (#1a1a1a)
- **اللون الرئيسي**: أخضر ليموني (#C0D906)
- **الخطوط**: Cairo (عربي), Poppins (إنجليزي)

---

## 📞 الدعم

لأي استفسار أو دعم فني:
- Email: support@syr-aix.com
- Website: www.syr-aix.com

---

**© 2024 Syr AiX. All Rights Reserved.**
