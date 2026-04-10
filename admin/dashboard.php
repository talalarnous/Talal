<?php
/**
 * Syr AiX - Admin Dashboard
 * Main dashboard with statistics and charts (FREE: Chart.js)
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';
requireLogin();

$stats = getDashboardStats();
$topProducts = getTopSellingProducts(5);
$revenueData = getRevenueByDay();
$settings = getRestaurantSettings();
$lang = $_SESSION['lang'] ?? 'ar';
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Syr AiX</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --bg-primary: #0a0a0a;
            --bg-secondary: #1a1a1a;
            --bg-card: #252525;
            --accent: #C0D906;
            --text-primary: #ffffff;
            --text-secondary: #b0b0b0;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: '<?php echo $lang === 'ar' ? "Cairo" : "Poppins"; ?>', sans-serif; background: var(--bg-primary); color: var(--text-primary); }
        
        .sidebar { position: fixed; width: 250px; height: 100vh; background: var(--bg-secondary); padding: 20px; overflow-y: auto; }
        .sidebar h2 { color: var(--accent); margin-bottom: 30px; text-align: center; }
        .nav-link { display: flex; align-items: center; gap: 10px; padding: 12px 15px; color: var(--text-secondary); text-decoration: none; border-radius: 10px; margin-bottom: 5px; transition: all 0.3s; }
        .nav-link:hover, .nav-link.active { background: var(--accent); color: var(--bg-primary); }
        .nav-link i { font-size: 1.2rem; }
        
        .main-content { margin-left: 250px; padding: 30px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header h1 { font-size: 1.8rem; }
        .user-info { display: flex; align-items: center; gap: 15px; }
        .logout-btn { padding: 8px 20px; background: #ff4757; color: white; border: none; border-radius: 8px; cursor: pointer; text-decoration: none; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: var(--bg-card); padding: 25px; border-radius: 15px; border-left: 4px solid var(--accent); }
        .stat-card h3 { color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 10px; }
        .stat-card .value { font-size: 2rem; font-weight: 700; color: var(--accent); }
        
        .charts-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 30px; }
        .chart-card { background: var(--bg-card); padding: 20px; border-radius: 15px; }
        .chart-card h3 { margin-bottom: 20px; color: var(--accent); }
        
        @media (max-width: 1024px) { .charts-grid { grid-template-columns: 1fr; } }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); transition: transform 0.3s; } .main-content { margin-left: 0; } }
    </style>
</head>
<body>
    <aside class="sidebar">
        <h2>Syr AiX</h2>
        <nav>
            <a href="dashboard.php" class="nav-link active"><i class="ri-dashboard-line"></i> Dashboard</a>
            <a href="orders.php" class="nav-link"><i class="ri-shopping-cart-line"></i> Orders</a>
            <a href="products.php" class="nav-link"><i class="ri-menu-line"></i> Products</a>
            <a href="categories.php" class="nav-link"><i class="ri-folder-line"></i> Categories</a>
            <a href="tables.php" class="nav-link"><i class="ri-qr-code-line"></i> Tables</a>
            <a href="settings.php" class="nav-link"><i class="ri-settings-line"></i> Settings</a>
            <a href="../index.php" target="_blank" class="nav-link"><i class="ri-external-link-line"></i> View Menu</a>
            <a href="logout.php" class="nav-link" style="color: #ff4757;"><i class="ri-logout-box-line"></i> Logout</a>
        </nav>
    </aside>

    <main class="main-content">
        <div class="header">
            <h1>📊 Dashboard</h1>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Orders Today</h3>
                <div class="value"><?php echo $stats['orders_today']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Revenue Today</h3>
                <div class="value"><?php echo number_format($stats['revenue_today'], 2); ?> <?php echo $settings['currency']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Pending Orders</h3>
                <div class="value"><?php echo $stats['pending_orders']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Products</h3>
                <div class="value"><?php echo $stats['total_products']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Active Tables</h3>
                <div class="value"><?php echo $stats['active_tables']; ?></div>
            </div>
        </div>

        <div class="charts-grid">
            <div class="chart-card">
                <h3>📈 Revenue (Last 7 Days)</h3>
                <canvas id="revenueChart"></canvas>
            </div>
            <div class="chart-card">
                <h3>🏆 Top Products</h3>
                <canvas id="topProductsChart"></canvas>
            </div>
        </div>

        <div class="watermark" style="text-align: center; padding: 20px; color: var(--text-secondary); opacity: 0.6;">
            <strong>Syr AiX</strong> Where AI Meets Creativity
        </div>
    </main>

    <script>
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($revenueData, 'date')); ?>,
                datasets: [{
                    label: 'Revenue',
                    data: <?php echo json_encode(array_column($revenueData, 'revenue')); ?>,
                    borderColor: '#C0D906',
                    backgroundColor: 'rgba(192, 217, 6, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#333' } },
                    x: { grid: { color: '#333' } }
                }
            }
        });

        // Top Products Chart
        const topCtx = document.getElementById('topProductsChart').getContext('2d');
        new Chart(topCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($topProducts, 'name_' . $lang)); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($topProducts, 'total_sold')); ?>,
                    backgroundColor: ['#C0D906', '#2ed573', '#1e90ff', '#ff4757', '#ffa502']
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    </script>
</body>
</html>
