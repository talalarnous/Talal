<?php
/**
 * Syr AiX - Admin Login Page
 * Secure admin authentication
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter username and password';
    } else {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Syr AiX - Admin Login</title>
    <style>
        :root {
            --bg-primary: #0a0a0a;
            --bg-secondary: #1a1a1a;
            --accent: #C0D906;
            --text-primary: #ffffff;
            --text-secondary: #b0b0b0;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .login-container {
            background: var(--bg-secondary);
            padding: 40px;
            border-radius: 20px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
        }
        
        .logo { text-align: center; margin-bottom: 30px; }
        .logo h1 { color: var(--accent); font-size: 2rem; margin-bottom: 5px; }
        .logo p { color: var(--text-secondary); font-size: 0.9rem; }
        
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: var(--text-secondary); font-size: 0.9rem; }
        
        input {
            width: 100%;
            padding: 12px 15px;
            background: var(--bg-primary);
            border: 2px solid var(--bg-primary);
            border-radius: 10px;
            color: var(--text-primary);
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        input:focus { outline: none; border-color: var(--accent); }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: var(--accent);
            border: none;
            border-radius: 10px;
            color: var(--bg-primary);
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover { background: #a8c206; transform: translateY(-2px); }
        
        .error-message {
            background: rgba(255, 71, 87, 0.1);
            color: #ff4757;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .watermark {
            text-align: center;
            margin-top: 30px;
            color: var(--text-secondary);
            font-size: 0.8rem;
            opacity: 0.6;
        }
        
        .watermark strong { color: var(--accent); }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>Syr AiX</h1>
            <p>Admin Dashboard</p>
        </div>
        
        <?php if ($error): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required placeholder="Enter username" value="admin">
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Enter password" value="admin123">
            </div>
            
            <button type="submit" class="btn-login">Login</button>
        </form>
        
        <div class="watermark">
            <strong>Syr AiX</strong> Where AI Meets Creativity
        </div>
    </div>
</body>
</html>
