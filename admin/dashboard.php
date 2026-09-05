<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$activePage = 'dashboard';

$totalOrders   = 0;
$pendingOrders = 0;
$totalStaff    = 0;
$totalItems    = 0;
$lowStockCount = 0;
$totalRevenue  = 0;

$orderCountResult = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM orders");
if ($orderCountResult) {
    $totalOrders = mysqli_fetch_assoc($orderCountResult)['cnt'];
}

$pendingCountResult = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM orders WHERE status = 'pending'");
if ($pendingCountResult) {
    $pendingOrders = mysqli_fetch_assoc($pendingCountResult)['cnt'];
}

$staffCountResult = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM staff");
if ($staffCountResult) {
    $totalStaff = mysqli_fetch_assoc($staffCountResult)['cnt'];
}

$itemCountResult = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM inventory_items");
if ($itemCountResult) {
    $totalItems = mysqli_fetch_assoc($itemCountResult)['cnt'];
}

$lowStockResult = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM inventory_items WHERE quantity_on_hand <= reorder_level");
if ($lowStockResult) {
    $lowStockCount = mysqli_fetch_assoc($lowStockResult)['cnt'];
}

$revenueResult = mysqli_query($conn, "SELECT SUM(quoted_price * quantity) AS total FROM orders WHERE status IN ('approved', 'preparing')");
if ($revenueResult) {
    $row = mysqli_fetch_assoc($revenueResult);
    $totalRevenue = $row['total'] ? $row['total'] : 0;
}

$ordersPerDay = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $ordersPerDay[$date] = 0;
}
$trendResult = mysqli_query($conn, "SELECT DATE(date_ordered) AS d, COUNT(*) AS cnt
    FROM orders
    WHERE date_ordered >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY DATE(date_ordered)");
if ($trendResult) {
    while ($row = mysqli_fetch_assoc($trendResult)) {
        if (isset($ordersPerDay[$row['d']])) {
            $ordersPerDay[$row['d']] = (int)$row['cnt'];
        }
    }
}
$trendLabels = array_map(function ($d) {
    return date('M d', strtotime($d));
}, array_keys($ordersPerDay));
$trendValues = array_values($ordersPerDay);

$statusLabels = [];
$statusValues = [];
$statusResult = mysqli_query($conn, "SELECT status, COUNT(*) AS cnt FROM orders GROUP BY status");
if ($statusResult) {
    while ($row = mysqli_fetch_assoc($statusResult)) {
        $statusLabels[] = ucfirst($row['status']);
        $statusValues[] = (int)$row['cnt'];
    }
}

$productLabels = [];
$productValues = [];
$productResult = mysqli_query($conn, "SELECT product_type, COUNT(*) AS cnt FROM orders GROUP BY product_type");
if ($productResult) {
    while ($row = mysqli_fetch_assoc($productResult)) {
        $productLabels[] = $row['product_type'];
        $productValues[] = (int)$row['cnt'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../font/css/all.min.css">
    <script src="chart.umd.js"></script>
    <style>
        :root{
            --ink:#1c1f26;
            --orange:#ff4a1c;
            --orange-light:#fff0eb;
            --muted:#6b7280;
            --border:#e6e7ea;
            --success:#1a9c5c;
            --warning:#d97706;
            --danger:#dc2626;
            --info:#0891b2;
            --shadow: 0 1px 3px rgba(28,31,38,0.06), 0 1px 2px rgba(28,31,38,0.04);
        }
        .admin-content *{box-sizing:border-box;}

        .stat-grid{
            display:grid;
            grid-template-columns:repeat(auto-fit, minmax(210px,1fr));
            gap:16px;
            margin-bottom:24px;
        }
        .stat-card{
            background:#fff;
            border:1px solid var(--border);
            border-left:4px solid var(--orange);
            border-radius:10px;
            padding:18px 20px;
            box-shadow:var(--shadow);
            transition:transform .15s ease, box-shadow .15s ease;
        }
        .stat-card:hover{transform:translateY(-2px); box-shadow:0 6px 14px rgba(28,31,38,0.10);}
        .stat-card .stat-icon{
            width:38px; height:38px; border-radius:8px;
            display:flex; align-items:center; justify-content:center;
            background:var(--orange-light); color:var(--orange);
            font-size:16px; margin-bottom:12px;
        }
        .stat-card.staff{border-left-color:var(--success);}
        .stat-card.staff .stat-icon{background:#e7f8ef; color:var(--success);}
        .stat-card.stock{border-left-color:var(--danger);}
        .stat-card.stock .stat-icon{background:#fdecec; color:var(--danger);}
        .stat-card.revenue{border-left-color:var(--info);}
        .stat-card.revenue .stat-icon{background:#e6f6fa; color:var(--info);}

        .stat-value{font-size:26px; font-weight:700; color:var(--ink); line-height:1;}
        .stat-value a{color:inherit; text-decoration:none;}
        .stat-label{margin-top:6px; font-size:13px; color:var(--muted); font-weight:500;}

        .charts-grid{
            display:grid;
            grid-template-columns: 2fr 1fr;
            gap:18px;
            margin-bottom:22px;
        }
        .charts-grid-secondary{
            display:grid;
            grid-template-columns: 1fr 1fr;
            gap:18px;
            margin-bottom:6px;
        }
        @media (max-width:900px){
            .charts-grid, .charts-grid-secondary{grid-template-columns:1fr;}
        }

        .panel{
            background:#fff;
            border:1px solid var(--border);
            border-radius:10px;
            padding:20px;
            box-shadow:var(--shadow);
        }
        .panel h2{
            margin:0 0 16px;
            font-size:15px;
            font-weight:700;
            color:var(--ink);
        }
        .panel .chart-wrap{position:relative; height:270px;}
        .panel .chart-wrap.small{height:230px;}

        table.orders-table{width:100%; border-collapse:collapse; font-size:13px;}
        table.orders-table th{
            text-align:left; padding:9px 8px; color:var(--muted);
            font-weight:600; border-bottom:1px solid var(--border); font-size:11px;
            text-transform:uppercase; letter-spacing:.03em;
        }
        table.orders-table td{padding:11px 8px; border-bottom:1px solid var(--border);}
        table.orders-table tr:last-child td{border-bottom:none;}

        .badge{
            display:inline-block; padding:3px 10px; border-radius:999px;
            font-size:11px; font-weight:700; text-transform:capitalize;
        }
        .badge.pending{background:#fef3e2; color:#d97706;}
        .badge.quoted{background:#e6f6fa; color:#0891b2;}
        .badge.preparing{background:var(--orange-light); color:var(--orange);}
        .badge.approved{background:#e7f8ef; color:#1a9c5c;}
        .badge.rejected, .badge.cancelled{background:#fdecec; color:#dc2626;}

        .empty-state{color:var(--muted); font-size:13px; text-align:center; padding:24px 0;}
    </style>
</head>
<body>

<div class="admin-wrapper">

<?php include 'sidebar.php'; ?>

    <div class="admin-content">
        <div class="admin-topbar">
            <div>
                <h1>Dashboard</h1>
                <p>Overview of orders, staff, and inventory.</p>
            </div>
        </div>

        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-receipt"></i></div>
                <div class="stat-value"><?php echo $totalOrders; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-clock"></i></div>
                <div class="stat-value"><?php echo $pendingOrders; ?></div>
                <div class="stat-label">Pending Quotations</div>
            </div>

            <div class="stat-card staff">
                <div class="stat-icon"><i class="fa-solid fa-user-tie"></i></div>
                <div class="stat-value"><?php echo $totalStaff; ?></div>
                <div class="stat-label">Staff Members</div>
            </div>

            <div class="stat-card stock">
                <div class="stat-icon"><i class="fa-solid fa-boxes-stacked"></i></div>
                <div class="stat-value">
                    <a href="inventory.php"><?php echo $lowStockCount; ?></a>
                </div>
                <div class="stat-label">Low Stock Items (<?php echo $totalItems; ?> total)</div>
            </div>

            <div class="stat-card revenue">
                <div class="stat-icon"><i class="fa-solid fa-peso-sign"></i></div>
                <div class="stat-value">₱<?php echo number_format($totalRevenue, 2); ?></div>
                <div class="stat-label">Revenue (Approved Orders)</div>
            </div>
        </div>

        <div class="charts-grid">
            <div class="panel">
                <h2>Orders Trend (Last 7 Days)</h2>
                <div class="chart-wrap">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
            <div class="panel">
                <h2>Orders by Status</h2>
                <div class="chart-wrap">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        <div class="charts-grid-secondary">
            <div class="panel">
                <h2>Orders by Product Type</h2>
                <div class="chart-wrap small">
                    <canvas id="productChart"></canvas>
                </div>
            </div>

            <div class="panel">
                <h2>Recent Orders</h2>
                <?php
                    $recentOrders = mysqli_query($conn, "SELECT o.*, c.full_name FROM orders o
                        LEFT JOIN clients c ON o.client_id = c.id
                        ORDER BY o.id DESC LIMIT 5");
                ?>
                <?php if ($recentOrders && mysqli_num_rows($recentOrders) > 0) { ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Client</th>
                            <th>Product</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($order = mysqli_fetch_assoc($recentOrders)) { ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['full_name'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($order['product_type']); ?></td>
                            <td><span class="badge <?php echo htmlspecialchars($order['status']); ?>"><?php echo htmlspecialchars($order['status']); ?></span></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
                <?php } else { ?>
                    <div class="empty-state">No orders yet.</div>
                <?php } ?>
            </div>
        </div>

    </div>

</div>

<script>
Chart.defaults.font.family = "Arial, Helvetica, sans-serif";
Chart.defaults.color = '#6b7280';

new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode($trendLabels); ?>,
        datasets: [{
            label: 'Orders',
            data: <?php echo json_encode($trendValues); ?>,
            borderColor: '#ff4a1c',
            backgroundColor: 'rgba(255,74,28,0.08)',
            fill: true,
            tension: 0.35,
            pointRadius: 4,
            pointBackgroundColor: '#ff4a1c',
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f0f1f3' } },
            x: { grid: { display: false } }
        }
    }
});

new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($statusLabels); ?>,
        datasets: [{
            data: <?php echo json_encode($statusValues); ?>,
            backgroundColor: ['#d97706', '#0891b2', '#ff4a1c', '#1a9c5c', '#dc2626', '#1c1f26'],
            borderWidth: 2,
            borderColor: '#ffffff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '65%',
        plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 10, padding: 14 } }
        }
    }
});

new Chart(document.getElementById('productChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($productLabels); ?>,
        datasets: [{
            label: 'Orders',
            data: <?php echo json_encode($productValues); ?>,
            backgroundColor: '#1c1f26',
            borderRadius: 6,
            maxBarThickness: 44
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f0f1f3' } },
            x: { grid: { display: false } }
        }
    }
});
</script>

</body>
</html>