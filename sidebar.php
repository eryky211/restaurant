<?php
// We assume session is already started by auth.php
$role = $_SESSION['role'] ?? '';
$username = $_SESSION['username'] ?? 'User';
?>
<nav class="sidebar">
    <h2>Kamrai System</h2>
    
    <div class="sidebar-links">
        <?php // Admin links
        if ($role === 'admin'): ?>
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="admin_menu.php">Manage Menu</a>
            <a href="admin_tables.php">Manage Tables</a>
            <a href="admin_users.php">Manage Users</a>
            <!-- === NEW LINK === -->
            <a href="admin_sales.php">Sales Report</a> 
            <hr class="sidebar-divider">
        <?php endif; ?>
        
        <?php // Waiter links
        if ($role === 'waiter' || $role === 'admin'): ?>
            <a href="waiter_dashboard.php">Tables View</a>
        <?php endif; ?>
        
        <?php // Kitchen links
        if ($role === 'kitchen' || $role === 'admin'): ?>
            <a href="kitchen_dashboard.php">Kitchen View</a>
        <?php endif; ?>

        <?php // Cashier links
        if ($role === 'cashier' || $role === 'admin'): ?>
            <a href="cashier_dashboard.php">Cashier View</a>
        <?php endif; ?>
    </div>

    <div class="sidebar-footer">
        <span>Logged in as: <strong><?php echo htmlspecialchars($username); ?></strong></span>
        <a href="logout.php" class="logout-link">Logout</a>
    </div>
</nav>

