<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Kamrai Restaurant</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="homepage-body">
    <div class="homepage-container">
        <h1>Welcome to Kamrai Restaurant</h1>
        <p>Your new digital ordering system.</p>
        
        <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
            
            <p>Welcome back, <strong><?php echo htmlspecialchars($_SESSION["username"]); ?>!</strong></p>
            
            <?php // Provide correct link based on role
                $dashboard_link = 'index.php';
                switch ($_SESSION["role"]) {
                    case 'waiter': $dashboard_link = 'waiter_dashboard.php'; break;
                    case 'kitchen': $dashboard_link = 'kitchen_dashboard.php'; break;
                    case 'cashier': $dashboard_link = 'cashier_dashboard.php'; break;
                    case 'admin': $dashboard_link = 'admin_dashboard.php'; break;
                }
            ?>
            <a href="<?php echo $dashboard_link; ?>" class="btn-link">Go to Your Dashboard</a>
            <a href="logout.php" class="btn-link-secondary">Logout</a>

        <?php else: ?>
            
            <p>Staff members, please log in to access your dashboard.</p>
            <a href="login.php" class="btn-link">Staff Login</a>
            
        <?php endif; ?>
        
    </div>
</body>
</html>