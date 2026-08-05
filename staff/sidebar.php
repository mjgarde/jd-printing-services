<div class="staff-sidebar">
    <div class="sidebar-brand">
        <span class="brand-text">JD Printing</span>
        <span class="brand-sub">Staff Panel</span>
    </div>

    <ul class="sidebar-menu">
        <li class="<?php echo $activePage === 'dashboard' ? 'active' : ''; ?>">
            <a href="dashboard.php">
                <i class="fa-solid fa-gauge"></i>
                <span class="menu-text">Dashboard</span>
            </a>
        </li>

        <li class="menu-label">Management</li>

        <li class="<?php echo $activePage === 'orders' ? 'active' : ''; ?>">
            <a href="orders.php">
                <i class="fa-solid fa-receipt"></i>
                <span class="menu-text">Order</span>
            </a>
        </li>

        <li class="<?php echo $activePage === 'inventory' ? 'active' : ''; ?>">
            <a href="inventory.php">
                <i class="fa-solid fa-boxes-stacked"></i>
                <span class="menu-text">Inventory</span>
            </a>
        </li>

        <li class="<?php echo $activePage === 'delivery' ? 'active' : ''; ?>">
            <a href="delivery.php">
                <i class="fa-solid fa-truck"></i>
                <span class="menu-text">Delivery</span>
            </a>
        </li>

        <li class="menu-divider"></li>

        <li>
            <a href="logout.php" class="logout-link">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span class="menu-text">Logout</span>
            </a>
        </li>
    </ul>
</div>