<div class="admin-sidebar">
    <div class="sidebar-brand">
        <span class="brand-text">JD Printing</span>
        <span class="brand-sub">Admin Panel</span>
    </div>

    <ul class="sidebar-menu">
        <li class="<?php echo $activePage === 'dashboard' ? 'active' : ''; ?>">
            <a href="dashboard.php">
                <i class="fa-solid fa-gauge"></i>
                <span class="menu-text">Dashboard</span>
            </a>
        </li>

        <li class="menu-label">Management</li>

        <li class="<?php echo $activePage === 'staff' ? 'active' : ''; ?>">
            <a href="staff.php">
                <i class="fa-solid fa-user-tie"></i>
                <span class="menu-text">Staff</span>
            </a>
        </li>

        <li class="<?php echo $activePage === 'inventory' ? 'active' : ''; ?>">
            <a href="inventory.php">
                <i class="fa-solid fa-boxes-stacked"></i>
                <span class="menu-text">Inventory</span>
            </a>
        </li>

        <li class="<?php echo $activePage === 'reports' ? 'active' : ''; ?>">
            <a href="reports.php">
                <i class="fa-solid fa-chart-line"></i>
                <span class="menu-text">Reports</span>
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