<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$activePage = 'reports';

$productTypes = [];
$typeQuery = "SELECT DISTINCT product_type FROM orders ORDER BY product_type";
$typeResult = mysqli_query($conn, $typeQuery);
while ($row = mysqli_fetch_assoc($typeResult)) {
    $productTypes[] = $row['product_type'];
}

$lowStockResult = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM inventory_items WHERE quantity_on_hand <= reorder_level");
$lowStockCount = 0;
if ($lowStockResult) {
    $lowStockCount = mysqli_fetch_assoc($lowStockResult)['cnt'];
}

$endDate = date('Y-m-d');
$startDate = date('Y-m-d', strtotime('-30 days'));

$start = $_GET['start_date'] ?? $startDate;
$end = $_GET['end_date'] ?? $endDate;
$typeFilter = isset($_GET['product_type']) && $_GET['product_type'] !== '' ? $_GET['product_type'] : null;

$safeStart = mysqli_real_escape_string($conn, $start);
$safeEnd = mysqli_real_escape_string($conn, $end);

$whereClause = "((fulfillment_method = 'delivery' AND status = 'preparing' AND delivery_status = 'completed')
                 OR (fulfillment_method = 'pickup' AND status IN ('approved', 'preparing')))
                AND quoted_price IS NOT NULL AND DATE(date_ordered) BETWEEN '$safeStart' AND '$safeEnd'";
if ($typeFilter) {
    $whereClause .= " AND product_type = '" . mysqli_real_escape_string($conn, $typeFilter) . "'";
}

$totalQuery = "SELECT COUNT(*) AS total_orders, SUM(quoted_price) AS total_revenue, AVG(quoted_price) AS avg_order FROM orders WHERE $whereClause";
$totalResult = mysqli_query($conn, $totalQuery);
$totals = mysqli_fetch_assoc($totalResult);
$totalOrders = $totals['total_orders'] ?? 0;
$totalRevenue = $totals['total_revenue'] ?? 0;
$avgOrder = $totals['avg_order'] ?? 0;

$trendQuery = "SELECT DATE(date_ordered) AS sale_date, SUM(quoted_price) AS daily_total, COUNT(*) AS daily_orders
               FROM orders WHERE $whereClause GROUP BY DATE(date_ordered) ORDER BY sale_date";
$trendResult = mysqli_query($conn, $trendQuery);
$dates = [];
$revenues = [];
while ($row = mysqli_fetch_assoc($trendResult)) {
    $dates[] = date('M d', strtotime($row['sale_date']));
    $revenues[] = (float) $row['daily_total'];
}

$distQuery = "SELECT product_type, SUM(quoted_price) AS total_revenue FROM orders WHERE $whereClause GROUP BY product_type ORDER BY total_revenue DESC";
$distResult = mysqli_query($conn, $distQuery);
$types = [];
$amounts = [];
while ($row = mysqli_fetch_assoc($distResult)) {
    $types[] = $row['product_type'];
    $amounts[] = (float) $row['total_revenue'];
}

$tableQuery = "SELECT product_type, COUNT(*) AS order_count, SUM(quoted_price) AS total_revenue, AVG(quoted_price) AS avg_price
               FROM orders WHERE $whereClause GROUP BY product_type ORDER BY total_revenue DESC";
$tableResult = mysqli_query($conn, $tableQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin – Sales Reports</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../font/css/all.min.css">
    <script src="../js/chart.umd.js"></script>
    <style>
        :root{
            --ink:#1c1f26;
            --orange:#ff4a1c;
            --orange-light:#fff0eb;
            --muted:#6b7280;
            --border:#e6e7ea;
            --shadow: 0 1px 3px rgba(28,31,38,0.06), 0 1px 2px rgba(28,31,38,0.04);
        }
        .admin-content *{box-sizing:border-box;}

        .report-filters{
            background:#fff;
            border:1px solid var(--border);
            border-radius:10px;
            box-shadow:var(--shadow);
            padding:18px 22px;
            margin-bottom:24px;
            display:flex;
            flex-wrap:wrap;
            align-items:flex-end;
            gap:18px;
        }
        .filter-group{display:flex; flex-direction:column; gap:6px;}
        .filter-group label{
            font-size:11px; font-weight:700; text-transform:uppercase;
            letter-spacing:.04em; color:var(--muted);
        }
        .filter-group input, .filter-group select{
            padding:9px 13px;
            border:1.5px solid var(--border);
            border-radius:8px;
            font-size:14px;
            background:#f9fafb;
            color:var(--ink);
            font-family:inherit;
            min-width:165px;
        }
        .filter-group input:focus, .filter-group select:focus{
            outline:none;
            border-color:var(--orange);
            background:#fff;
            box-shadow:0 0 0 3px var(--orange-light);
        }
        .btn-apply{
            background:var(--ink);
            color:#fff;
            border:none;
            border-radius:8px;
            padding:10px 24px;
            font-weight:700;
            cursor:pointer;
            font-size:14px;
            transition:background .15s ease;
        }
        .btn-apply:hover{background:var(--orange);}

        .report-summary{
            display:grid;
            grid-template-columns:repeat(auto-fit, minmax(190px,1fr));
            gap:16px;
            margin-bottom:24px;
        }
        .report-card{
            background:#fff;
            border:1px solid var(--border);
            border-left:4px solid var(--orange);
            border-radius:10px;
            box-shadow:var(--shadow);
            padding:18px 20px;
            transition:transform .15s ease, box-shadow .15s ease;
        }
        .report-card:hover{transform:translateY(-2px); box-shadow:0 6px 14px rgba(28,31,38,0.10);}
        .report-card .icon{
            width:34px; height:34px; border-radius:8px;
            display:flex; align-items:center; justify-content:center;
            background:var(--orange-light); color:var(--orange);
            font-size:14px; margin-bottom:10px;
        }
        .report-card .number{font-size:24px; font-weight:700; color:var(--ink); line-height:1;}
        .report-card .label{margin-top:6px; font-size:12px; color:var(--muted); font-weight:500;}

        .chart-grid{
            display:grid;
            grid-template-columns:2fr 1fr;
            gap:18px;
            margin-bottom:24px;
        }
        .chart-box{
            background:#fff;
            border:1px solid var(--border);
            border-radius:10px;
            box-shadow:var(--shadow);
            padding:20px;
        }
        .chart-box h3{
            margin:0 0 14px;
            font-size:14px;
            font-weight:700;
            color:var(--ink);
            display:flex;
            align-items:center;
            gap:8px;
        }
        .chart-box h3 i{color:var(--orange);}
        .chart-wrap{position:relative; height:270px;}

        .report-table-wrap{
            background:#fff;
            border:1px solid var(--border);
            border-radius:10px;
            box-shadow:var(--shadow);
            padding:20px;
            overflow-x:auto;
        }
        .report-table-wrap h3{
            margin:0 0 14px;
            font-size:14px;
            font-weight:700;
            color:var(--ink);
            display:flex;
            align-items:center;
            gap:8px;
        }
        .report-table-wrap h3 i{color:var(--orange);}
        .report-table{width:100%; border-collapse:collapse; font-size:13px;}
        .report-table th{
            text-align:left;
            padding:10px 12px 10px 0;
            font-size:11px;
            font-weight:700;
            text-transform:uppercase;
            letter-spacing:.04em;
            color:var(--muted);
            border-bottom:1px solid var(--border);
        }
        .report-table td{padding:11px 12px 11px 0; border-bottom:1px solid var(--border);}
        .report-table tr:last-child td{border-bottom:none;}
        .report-table .amount{font-weight:700; color:var(--orange);}

        .no-data{text-align:center; color:var(--muted); padding:40px 0; font-size:13px;}

        @media (max-width:900px){
            .chart-grid{grid-template-columns:1fr;}
        }
        @media (max-width:768px){
            .report-filters{flex-direction:column; align-items:stretch;}
            .filter-group{width:100%;}
            .filter-group input, .filter-group select{width:100%;}
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <?php include 'sidebar.php'; ?>
    <div class="admin-content">
        <div class="admin-topbar">
            <div>
                <h1>Sales Reports</h1>
                <p>Filter and visualise sales data across product types.</p>
            </div>
        </div>

        <form method="GET" action="" id="filterForm" class="report-filters">
            <div class="filter-group">
                <label for="start_date">Start Date</label>
                <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start); ?>">
            </div>
            <div class="filter-group">
                <label for="end_date">End Date</label>
                <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end); ?>">
            </div>
            <div class="filter-group">
                <label for="product_type">Product Type</label>
                <select id="product_type" name="product_type">
                    <option value="">All Types</option>
                    <?php foreach ($productTypes as $type): ?>
                        <option value="<?php echo htmlspecialchars($type); ?>" <?php echo ($typeFilter === $type) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($type); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <button type="submit" class="btn-apply"><i class="fa-solid fa-filter"></i> Apply</button>
            </div>
        </form>

        <div class="report-summary">
            <div class="report-card">
                <div class="icon"><i class="fa-solid fa-receipt"></i></div>
                <div class="number"><?php echo number_format($totalOrders); ?></div>
                <div class="label">Total Orders</div>
            </div>
            <div class="report-card">
                <div class="icon"><i class="fa-solid fa-peso-sign"></i></div>
                <div class="number">₱<?php echo number_format($totalRevenue, 2); ?></div>
                <div class="label">Total Revenue</div>
            </div>
            <div class="report-card">
                <div class="icon"><i class="fa-solid fa-chart-simple"></i></div>
                <div class="number">₱<?php echo number_format($avgOrder, 2); ?></div>
                <div class="label">Avg Order Value</div>
            </div>
            <div class="report-card">
                <div class="icon"><i class="fa-solid fa-boxes-stacked"></i></div>
                <div class="number"><?php echo $lowStockCount; ?></div>
                <div class="label">Low Stock Items</div>
            </div>
        </div>

        <div class="chart-grid">
            <div class="chart-box">
                <h3><i class="fa-solid fa-chart-line"></i> Daily Sales Trend</h3>
                <div class="chart-wrap">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
            <div class="chart-box">
                <h3><i class="fa-solid fa-chart-pie"></i> Revenue by Product</h3>
                <div class="chart-wrap">
                    <canvas id="distChart"></canvas>
                </div>
            </div>
        </div>

        <div class="report-table-wrap">
            <h3><i class="fa-solid fa-table"></i> Breakdown by Product Type</h3>
            <?php if (mysqli_num_rows($tableResult) > 0) { ?>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Product Type</th>
                            <th>Orders</th>
                            <th>Total Revenue</th>
                            <th>Average Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($tableResult)) { ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($row['product_type']); ?></strong></td>
                                <td><?php echo $row['order_count']; ?></td>
                                <td class="amount">₱<?php echo number_format($row['total_revenue'], 2); ?></td>
                                <td>₱<?php echo number_format($row['avg_price'], 2); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <div class="no-data">No sales data found for the selected filters.</div>
            <?php } ?>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const dates = <?php echo json_encode($dates); ?>;
    const revenues = <?php echo json_encode($revenues); ?>;
    const types = <?php echo json_encode($types); ?>;
    const amounts = <?php echo json_encode($amounts); ?>;

    Chart.defaults.font.family = "Arial, Helvetica, sans-serif";
    Chart.defaults.color = '#6b7280';

    const ctx1 = document.getElementById('trendChart').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: 'Daily Revenue (₱)',
                data: revenues,
                borderColor: '#ff4a1c',
                backgroundColor: 'rgba(255,74,28,0.08)',
                borderWidth: 2,
                fill: true,
                tension: 0.35,
                pointRadius: 4,
                pointBackgroundColor: '#ff4a1c'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f0f1f3' },
                    ticks: { callback: (value) => '₱' + value.toFixed(0) }
                },
                x: { grid: { display: false } }
            }
        }
    });

    const distBox = document.getElementById('distChart').parentElement;
    if (types.length > 0) {
        const ctx2 = document.getElementById('distChart').getContext('2d');
        const colors = ['#ff4a1c', '#1c1f26', '#0891b2', '#1a9c5c', '#d97706', '#9333ea'];
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: types,
                datasets: [{
                    data: amounts,
                    backgroundColor: colors.slice(0, types.length),
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 10, padding: 14, font: { size: 12 } }
                    }
                }
            }
        });
    } else {
        distBox.innerHTML = '<p class="no-data">No data to display.</p>';
    }
});
</script>
</body>
</html>