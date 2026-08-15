<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    background-color: #f4f5f7;
    color: #222;
}

.admin-wrapper {
    display: flex;
    min-height: 100vh;
}

.admin-sidebar {
    width: 240px;
    background-color: #1c1f26;
    color: #ffffff;
    flex-shrink: 0;
}

.sidebar-brand {
    padding: 22px 20px;
    border-bottom: 1px solid #2a2e37;
}

.brand-text {
    display: block;
    font-size: 18px;
    font-weight: bold;
    color: #ffffff;
}

.brand-sub {
    display: block;
    font-size: 12px;
    color: #ffffff;
    margin-top: 2px;
    opacity: 0.6;
}

.sidebar-menu {
    list-style: none;
    padding: 10px 0;
}

.sidebar-menu li a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 20px;
    color: #ffffff;
    text-decoration: none;
    font-size: 14px;
    opacity: 0.65;
    border-left: 2px solid transparent;
}

.sidebar-menu li a i {
    width: 16px;
    text-align: center;
    font-size: 14px;
    color: #ffffff;
}

.sidebar-menu li.active a {
    opacity: 1;
    border-left: 2px solid #ff4a1c;
}

.menu-label {
    padding: 14px 20px 6px;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #ffffff;
    opacity: 0.5;
}

.menu-divider {
    border-top: 1px solid #2a2e37;
    margin: 10px 0;
}

.logout-link {
    color: #ff6b6b !important;
}

.logout-link i {
    color: #ff6b6b !important;
}

.admin-content {
    flex: 1;
    padding: 30px;
}

.admin-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 26px;
}

.admin-topbar h1 {
    font-size: 22px;
    font-weight: 700;
}

.admin-topbar p {
    font-size: 13px;
    color: #666666;
    margin-top: 2px;
}
</style>

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