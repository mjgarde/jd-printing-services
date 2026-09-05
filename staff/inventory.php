<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['staff_id'])) {
    header("Location: login.php");
    exit();
}

$activePage = 'inventory';

$result = mysqli_query($conn, "SELECT * FROM inventory_items ORDER BY item_name");
$items = [];
while ($row = mysqli_fetch_assoc($result)) {
    $items[] = $row;
}

$lowStockCount = 0;
foreach ($items as $it) {
    if ($it['quantity_on_hand'] <= $it['reorder_level']) {
        $lowStockCount++;
    }
}

$icons = [
    'Tarpaulin' => 'fa-scroll',
    'Sticker Paper' => 'fa-tags',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory — Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../font/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="staff.css">
    <style>
        :root {
            --inventory-line: #E7E9EC;
            --inventory-line-soft: #F0F1F3;
            --inventory-ink: #14171C;
            --inventory-soft: #6B7280;
            --inventory-surface: #FFFFFF;
            --inventory-canvas: #F7F8FA;
            --inventory-accent: #2454FF;
            --inventory-accent-soft: #EAF0FF;
            --inventory-danger: #C0362C;
            --inventory-danger-soft: #FBEAE8;
            --inventory-ok: #1A8A5F;
            --inventory-ok-soft: #E4F5EE;
        }

        .inventory-panel {
            font-family: 'Inter', sans-serif;
            color: var(--inventory-ink);
        }

        .inventory-toast {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--inventory-ok-soft);
            color: var(--inventory-ok);
            border: 1px solid #BFE4D3;
            border-radius: 10px;
            padding: 11px 15px;
            font-size: 13.5px;
            font-weight: 500;
            margin-bottom: 20px;
        }

        .inventory-summary {
            display: flex;
            border: 1px solid var(--inventory-line);
            border-radius: 14px;
            overflow: hidden;
            margin-bottom: 28px;
            background: var(--inventory-surface);
        }

        .inventory-summary-cell {
            flex: 1;
            padding: 18px 22px;
            border-right: 1px solid var(--inventory-line);
        }

        .inventory-summary-cell:last-child {
            border-right: none;
        }

        .inventory-summary-cell span {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--inventory-soft);
            display: block;
            margin-bottom: 8px;
        }

        .inventory-summary-cell strong {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.02em;
            display: flex;
            align-items: baseline;
            gap: 8px;
            color: var(--inventory-ink);
        }

        .inventory-summary-cell.alert strong {
            color: var(--inventory-danger);
        }

        .inventory-summary-cell strong small {
            font-size: 13px;
            font-weight: 500;
            color: var(--inventory-soft);
        }

        .inventory-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
        }

        .inventory-toolbar h2 {
            font-size: 16px;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.01em;
            color: var(--inventory-ink);
        }

        .inventory-toolbar p {
            margin: 3px 0 0;
            font-size: 12.5px;
            color: var(--inventory-soft);
        }

        button.inventory-btn {
            font-family: 'Inter', sans-serif;
            font-size: 13.5px;
            font-weight: 600;
            border: none;
            border-radius: 9px;
            padding: 11px 20px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            line-height: 1.2;
        }

        button.inventory-btn-primary {
            background: var(--inventory-ink) !important;
            color: #FFFFFF !important;
        }

        button.inventory-btn-primary:hover {
            background: var(--inventory-accent) !important;
        }

        button.inventory-btn-ghost {
            background: var(--inventory-canvas) !important;
            color: var(--inventory-ink) !important;
            border: 1px solid var(--inventory-line) !important;
        }

        button.inventory-btn-ghost:hover {
            background: var(--inventory-line-soft) !important;
        }

        .inventory-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 16px;
        }

        .inventory-card {
            background: var(--inventory-surface);
            border: 1px solid var(--inventory-line);
            border-radius: 16px;
            padding: 22px;
        }

        .inventory-card-icon {
            width: 42px;
            height: 42px;
            border-radius: 11px;
            background: var(--inventory-accent-soft);
            color: var(--inventory-accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            margin-bottom: 16px;
        }

        .inventory-card.low .inventory-card-icon {
            background: var(--inventory-danger-soft);
            color: var(--inventory-danger);
        }

        .inventory-card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 10px;
        }

        .inventory-card-top h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: -0.01em;
            color: var(--inventory-ink);
        }

        .inventory-badge-low {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 700;
            color: var(--inventory-danger);
            background: var(--inventory-danger-soft);
            border-radius: 999px;
            padding: 4px 10px;
            white-space: nowrap;
        }

        .inventory-card-qty {
            font-size: 34px;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin: 16px 0 2px;
            line-height: 1;
            color: var(--inventory-ink);
        }

        .inventory-card-qty span {
            font-size: 14px;
            font-weight: 500;
            color: var(--inventory-soft);
            margin-left: 4px;
        }

        .inventory-card-reorder {
            font-size: 12.5px;
            color: var(--inventory-soft);
            margin-bottom: 16px;
        }

        .inventory-level-bar {
            height: 7px;
            border-radius: 999px;
            background: var(--inventory-canvas);
            overflow: hidden;
            margin-bottom: 18px;
        }

        .inventory-level-bar-fill {
            height: 100%;
            border-radius: 999px;
            background: var(--inventory-ok);
        }

        .inventory-card.low .inventory-level-bar-fill {
            background: var(--inventory-danger);
        }

        button.inventory-card-add {
            width: 100%;
            font-family: 'Inter', sans-serif;
            font-size: 13.5px;
            font-weight: 600;
            border: 1px solid var(--inventory-line) !important;
            background: var(--inventory-canvas) !important;
            color: var(--inventory-ink) !important;
            border-radius: 9px;
            padding: 11px;
            cursor: pointer;
            justify-content: center;
        }

        button.inventory-card-add:hover {
            background: var(--inventory-line-soft) !important;
            border-color: var(--inventory-accent) !important;
            color: var(--inventory-accent) !important;
        }

        .inventory-empty {
            border: 1px dashed var(--inventory-line);
            border-radius: 14px;
            padding: 60px 24px;
            text-align: center;
            color: var(--inventory-soft);
        }

        .inventory-empty i {
            font-size: 24px;
            margin-bottom: 12px;
            display: block;
        }

        div.inventory-modal-backdrop {
            display: none;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100vw;
            height: 100vh;
            margin: 0 !important;
            background: rgba(15, 17, 21, 0.55) !important;
            align-items: center;
            justify-content: center;
            z-index: 9999 !important;
            padding: 20px;
            box-sizing: border-box;
        }

        div.inventory-modal-backdrop.open {
            display: flex !important;
        }

        div.inventory-modal-backdrop .inventory-modal {
            background: #FFFFFF !important;
            border-radius: 16px;
            width: 100%;
            max-width: 400px;
            padding: 26px;
            box-shadow: 0 20px 50px rgba(15, 17, 21, 0.25);
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        .inventory-modal h3 {
            margin: 0 0 4px;
            font-size: 17px;
            font-weight: 700;
            letter-spacing: -0.01em;
            color: var(--inventory-ink);
        }

        .inventory-modal p {
            margin: 0 0 20px;
            font-size: 13px;
            color: var(--inventory-soft);
        }

        .inventory-field {
            margin-bottom: 16px;
        }

        .inventory-field label {
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            margin-bottom: 7px;
            color: var(--inventory-ink);
        }

        .inventory-modal input {
            width: 100% !important;
            font-family: 'Inter', sans-serif !important;
            font-size: 14px !important;
            padding: 11px 13px !important;
            border: 1.5px solid var(--inventory-line) !important;
            border-radius: 10px !important;
            box-sizing: border-box !important;
            background: var(--inventory-canvas) !important;
            color: var(--inventory-ink) !important;
            transition: border-color 0.15s ease, background 0.15s ease;
            margin: 0 !important;
        }

        .inventory-modal input::placeholder {
            color: #A2A8B4;
        }

        .inventory-modal input:hover {
            border-color: #C7CBD3 !important;
        }

        .inventory-modal input:focus {
            outline: none !important;
            border-color: var(--inventory-accent) !important;
            background: #FFFFFF !important;
            box-shadow: 0 0 0 3px var(--inventory-accent-soft) !important;
        }

        .inventory-field-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .inventory-modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 22px;
        }

        @media (max-width: 560px) {
            .inventory-summary {
                flex-direction: column;
            }

            .inventory-summary-cell {
                border-right: none;
                border-bottom: 1px solid var(--inventory-line);
            }

            .inventory-summary-cell:last-child {
                border-bottom: none;
            }
        }
    </style>
</head>
<body>

<div class="staff-wrapper">

    <?php include 'sidebar.php'; ?>

    <div class="staff-content">
        <div class="staff-topbar">
            <div>
                <h1>Inventory</h1>
                <p>Monitor material stock. Deduction happens automatically once an order is completed.</p>
            </div>
        </div>

        <div class="inventory-panel">
            <?php if (isset($_GET['added'])) { ?>
                <div class="inventory-toast"><i class="fa-solid fa-circle-check"></i> Item added to inventory.</div>
            <?php } elseif (isset($_GET['restocked'])) { ?>
                <div class="inventory-toast"><i class="fa-solid fa-circle-check"></i> Stock updated.</div>
            <?php } ?>

            <div class="inventory-summary">
                <div class="inventory-summary-cell">
                    <span>Materials Tracked</span>
                    <strong><?php echo count($items); ?></strong>
                </div>
                <div class="inventory-summary-cell <?php echo $lowStockCount > 0 ? 'alert' : ''; ?>">
                    <span>Low Stock</span>
                    <strong>
                        <?php echo $lowStockCount; ?>
                        <?php if ($lowStockCount > 0) { ?><small>needs restock</small><?php } ?>
                    </strong>
                </div>
            </div>

            <div class="inventory-toolbar">
                <div>
                    <h2>Stock Monitoring</h2>
                    <p>Live material levels across all tracked items.</p>
                </div>
                <button type="button" class="inventory-btn inventory-btn-primary" onclick="document.getElementById('addItemModal').classList.add('open')">
                    <i class="fa-solid fa-plus"></i> Add Item
                </button>
            </div>

            <?php if (count($items) === 0) { ?>
                <div class="inventory-empty">
                    <i class="fa-solid fa-boxes-stacked"></i>
                    <p>No materials tracked yet. Add your first item to get started.</p>
                </div>
            <?php } else { ?>
                <div class="inventory-cards">
                    <?php foreach ($items as $item) {
                        $isLow = $item['quantity_on_hand'] <= $item['reorder_level'];
                        $maxRef = max($item['quantity_on_hand'], $item['reorder_level'] * 2, 1);
                        $pct = min(100, ($item['quantity_on_hand'] / $maxRef) * 100);
                        $icon = $icons[$item['item_name']] ?? 'fa-box';
                    ?>
                        <div class="inventory-card <?php echo $isLow ? 'low' : ''; ?>">
                            <div class="inventory-card-icon"><i class="fa-solid <?php echo $icon; ?>"></i></div>
                            <div class="inventory-card-top">
                                <h3><?php echo htmlspecialchars($item['item_name']); ?></h3>
                                <?php if ($isLow) { ?>
                                    <span class="inventory-badge-low"><i class="fa-solid fa-triangle-exclamation"></i> Low</span>
                                <?php } ?>
                            </div>
                            <div class="inventory-card-qty">
                                <?php echo number_format($item['quantity_on_hand'], 2); ?>
                                <span><?php echo htmlspecialchars($item['unit']); ?></span>
                            </div>
                            <div class="inventory-card-reorder">Reorder threshold: <?php echo number_format($item['reorder_level'], 2); ?> <?php echo htmlspecialchars($item['unit']); ?></div>
                            <div class="inventory-level-bar">
                                <div class="inventory-level-bar-fill" style="width: <?php echo $pct; ?>%;"></div>
                            </div>
                            <button type="button" class="inventory-card-add" onclick="openStockModal(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars(addslashes($item['item_name'])); ?>', '<?php echo htmlspecialchars($item['unit']); ?>')">
                                <i class="fa-solid fa-plus"></i> Add Stock
                            </button>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </div>

</div>

<div class="inventory-modal-backdrop" id="addItemModal">
    <div class="inventory-modal">
        <h3>Add Inventory Item</h3>
        <p>Register a new material to track.</p>
        <form method="POST" action="inventory_action.php">
            <div class="inventory-field">
                <label>Item Name</label>
                <input type="text" name="item_name" required>
            </div>
            <div class="inventory-field-row">
                <div class="inventory-field">
                    <label>Unit</label>
                    <input type="text" name="unit" placeholder="sqft" required>
                </div>
                <div class="inventory-field">
                    <label>Starting Quantity</label>
                    <input type="number" step="0.01" min="0" name="quantity_on_hand" value="0" required>
                </div>
            </div>
            <div class="inventory-field">
                <label>Reorder Level</label>
                <input type="number" step="0.01" min="0" name="reorder_level" value="0" required>
            </div>
            <div class="inventory-modal-actions">
                <button type="button" class="inventory-btn inventory-btn-ghost" onclick="document.getElementById('addItemModal').classList.remove('open')">Cancel</button>
                <button type="submit" name="add_item" class="inventory-btn inventory-btn-primary">Add Item</button>
            </div>
        </form>
    </div>
</div>

<div class="inventory-modal-backdrop" id="stockModal">
    <div class="inventory-modal">
        <h3>Add Stock</h3>
        <p id="stockModalItem"></p>
        <form method="POST" action="inventory_action.php">
            <input type="hidden" name="item_id" id="stockItemId">
            <div class="inventory-field">
                <label>Quantity to Add (<span id="stockModalUnit"></span>)</label>
                <input type="number" step="0.01" min="0.01" name="quantity" required>
            </div>
            <div class="inventory-modal-actions">
                <button type="button" class="inventory-btn inventory-btn-ghost" onclick="document.getElementById('stockModal').classList.remove('open')">Cancel</button>
                <button type="submit" name="restock" class="inventory-btn inventory-btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
function openStockModal(id, name, unit) {
    document.getElementById('stockItemId').value = id;
    document.getElementById('stockModalItem').textContent = name;
    document.getElementById('stockModalUnit').textContent = unit;
    document.getElementById('stockModal').classList.add('open');
}
</script>

</body>
</html>