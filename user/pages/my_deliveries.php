<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['client_id'])) {
    header("Location: ../index.php");
    exit();
}

$client_id = $_SESSION['client_id'];

$stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE client_id = ? AND fulfillment_method = 'delivery' ORDER BY id DESC");
mysqli_stmt_bind_param($stmt, "i", $client_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$deliveries = [];
while ($row = mysqli_fetch_assoc($result)) {
    $deliveries[] = $row;
}

$statusMeta = [
    'pending'  => [
        'label' => 'Pending',
        'ink'   => 'ink-amber',
        'icon'  => 'fa-clock',
        'note'  => 'Waiting for quotation from our team.',
    ],
    'quoted'   => [
        'label' => 'Quoted',
        'ink'   => 'ink-blue',
        'icon'  => 'fa-hourglass-half',
        'note'  => 'Awaiting your confirmation before delivery can be scheduled.',
    ],
    'approved' => [
        'label' => 'Dispatch',
        'ink'   => 'ink-green',
        'icon'  => 'fa-truck',
        'note'  => 'Confirmed. Our team will coordinate the delivery schedule with you.',
    ],
];

$counts = ['all' => count($deliveries), 'pending' => 0, 'quoted' => 0, 'approved' => 0];
foreach ($deliveries as $d) {
    if (isset($counts[$d['status']])) {
        $counts[$d['status']]++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Deliveries — JD Printing Services</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../font/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=IBM+Plex+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        .docket-page {
            --paper: #EFEBE1;
            --paper-line: #D8D1C0;
            --stub: #E2DCCB;
            --ink: #211E18;
            --ink-soft: #6E6656;
            --press-red: #B0311D;
            --ink-amber: #92600E;
            --ink-amber-bg: #F0E0BC;
            --ink-blue: #234E70;
            --ink-blue-bg: #D9E3EA;
            --ink-green: #2C5C3F;
            --ink-green-bg: #D8E5D8;
            background:
                repeating-linear-gradient(0deg, transparent, transparent 39px, rgba(33,30,24,0.035) 39px, rgba(33,30,24,0.035) 40px),
                var(--paper);
            min-height: 100vh;
            padding: 48px 20px 100px;
            font-family: 'IBM Plex Mono', monospace;
            color: var(--ink);
        }

        .docket-shell {
            max-width: 820px;
            margin: 0 auto;
        }

        .docket-masthead {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-bottom: 3px solid var(--ink);
            padding-bottom: 18px;
            margin-bottom: 6px;
        }

        .docket-masthead h1 {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 800;
            font-size: 46px;
            letter-spacing: -0.01em;
            line-height: 0.9;
            margin: 0;
            text-transform: uppercase;
        }

        .docket-masthead .sub {
            font-size: 12px;
            color: var(--ink-soft);
            margin-top: 6px;
        }

        .docket-tally {
            text-align: right;
            font-size: 12px;
            color: var(--ink-soft);
            line-height: 1.6;
        }

        .docket-tally strong {
            font-size: 22px;
            color: var(--ink);
            display: block;
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 800;
        }

        .docket-filters {
            display: flex;
            gap: 0;
            margin: 22px 0 34px;
            border-bottom: 1px solid var(--paper-line);
            flex-wrap: wrap;
        }

        .docket-filters button {
            font-family: 'IBM Plex Mono', monospace;
            font-size: 12.5px;
            font-weight: 600;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            color: var(--ink-soft);
            padding: 10px 16px 10px 0;
            margin-right: 22px;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .docket-filters button.active {
            color: var(--ink);
            border-bottom-color: var(--press-red);
        }

        .docket-filters button:focus-visible {
            outline: 2px solid var(--press-red);
            outline-offset: 2px;
        }

        .docket-empty {
            border: 1px dashed var(--ink-soft);
            padding: 70px 24px;
            text-align: center;
            color: var(--ink-soft);
        }

        .docket-empty i {
            font-size: 24px;
            display: block;
            margin-bottom: 14px;
        }

        .docket-empty a {
            display: inline-block;
            margin-top: 16px;
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 700;
            text-transform: uppercase;
            background: var(--ink);
            color: var(--paper);
            padding: 10px 22px;
            text-decoration: none;
        }

        .docket-list {
            display: flex;
            flex-direction: column;
            gap: 22px;
        }

        .ticket {
            background: #FBF9F3;
            border: 1px solid var(--ink);
            display: grid;
            grid-template-columns: 108px 1px 1fr;
            position: relative;
            box-shadow: 3px 3px 0 rgba(33,30,24,0.12);
        }

        .ticket-stub {
            padding: 14px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: var(--stub);
        }

        .ticket-thumb {
            width: 100%;
            aspect-ratio: 1;
            border: 1px solid var(--ink);
            overflow: hidden;
            background: var(--paper);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ticket-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .ticket-thumb i {
            color: var(--ink-soft);
            font-size: 16px;
        }

        .ticket-code {
            font-size: 11px;
            color: var(--ink-soft);
            margin-top: 10px;
        }

        .ticket-perf {
            background-image: radial-gradient(circle, var(--paper) 3px, transparent 3px);
            background-size: 100% 16px;
            background-position: center;
        }

        .ticket-body {
            padding: 18px 22px 20px;
            font-family: 'IBM Plex Mono', monospace;
        }

        .ticket-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 14px;
        }

        .ticket-top h3 {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 26px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.01em;
            margin: 0;
            line-height: 1;
        }

        .stamp {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 800;
            font-size: 12.5px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            border: 2px solid;
            border-radius: 50%;
            width: 76px;
            height: 76px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            transform: rotate(-9deg);
            flex-shrink: 0;
            line-height: 1.1;
            position: relative;
        }

        .stamp::before {
            content: '';
            position: absolute;
            width: 66px;
            height: 66px;
            border: 1px solid;
            border-radius: 50%;
        }

        .stamp.ink-amber { color: var(--ink-amber); border-color: var(--ink-amber); }
        .stamp.ink-blue { color: var(--ink-blue); border-color: var(--ink-blue); }
        .stamp.ink-green { color: var(--ink-green); border-color: var(--ink-green); }

        .ticket-spec {
            margin: 14px 0 0;
            font-size: 13px;
            color: var(--ink-soft);
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
            gap: 6px 12px;
            border-top: 1px dashed var(--paper-line);
            padding-top: 12px;
        }

        .ticket-spec span b {
            display: block;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--ink-soft);
            font-weight: 500;
            margin-bottom: 2px;
        }

        .ticket-spec span {
            color: var(--ink);
            font-size: 13px;
        }

        .ticket-route {
            margin-top: 16px;
            padding-top: 14px;
            border-top: 1px dashed var(--paper-line);
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: var(--ink-soft);
        }

        .ticket-route i {
            color: var(--ink);
        }

        @media (max-width: 600px) {
            .docket-masthead {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .docket-tally {
                text-align: left;
            }

            .ticket {
                grid-template-columns: 72px 1px 1fr;
            }

            .ticket-body {
                padding: 16px;
            }

            .ticket-top h3 {
                font-size: 21px;
            }

            .stamp {
                width: 58px;
                height: 58px;
                font-size: 10px;
            }
        }
    </style>
</head>
<body>

<div class="colorbar"><span></span><span></span><span></span><span></span></div>

<div class="navbar">
    <div class="logo">
        <a href="../index.php"><img src="../logo.png" alt="JD Printing Logo"></a>
    </div>
</div>

<div class="docket-page">
    <div class="docket-shell">
        <div class="docket-masthead">
            <div>
                <h1>My Deliveries</h1>
                <p class="sub">JD Printing Services &middot; Dispatch Docket</p>
            </div>
            <div class="docket-tally">
                <strong><?php echo $counts['all']; ?></strong>
                for delivery
            </div>
        </div>

        <?php if ($counts['all'] > 0) { ?>
            <div class="docket-filters" role="tablist" aria-label="Filter deliveries by status">
                <button type="button" class="active" data-filter="all">All (<?php echo $counts['all']; ?>)</button>
                <button type="button" data-filter="pending">Pending (<?php echo $counts['pending']; ?>)</button>
                <button type="button" data-filter="quoted">Quoted (<?php echo $counts['quoted']; ?>)</button>
                <button type="button" data-filter="approved">Dispatch (<?php echo $counts['approved']; ?>)</button>
            </div>
        <?php } ?>

        <?php if (count($deliveries) === 0) { ?>
            <div class="docket-empty">
                <i class="fa-solid fa-truck"></i>
                <p>No delivery orders on file yet.</p>
                <a href="../index.php#order">Place an Order</a>
            </div>
        <?php } else { ?>
            <div class="docket-list" id="docketList">
                <?php foreach ($deliveries as $order) {
                    $status = $order['status'];
                    $meta = $statusMeta[$status] ?? $statusMeta['pending'];
                    $refCode = 'JD-' . str_pad($order['id'], 5, '0', STR_PAD_LEFT);
                ?>
                    <div class="ticket" data-status="<?php echo htmlspecialchars($status); ?>">
                        <div class="ticket-stub">
                            <div class="ticket-thumb">
                                <?php if (!empty($order['design_file'])) { ?>
                                    <img src="../../assets/uploads/designs/<?php echo htmlspecialchars($order['design_file']); ?>" alt="<?php echo htmlspecialchars($order['product_type']); ?> design preview">
                                <?php } else { ?>
                                    <i class="fa-solid fa-image"></i>
                                <?php } ?>
                            </div>
                            <div class="ticket-code"><?php echo $refCode; ?></div>
                        </div>

                        <div class="ticket-perf"></div>

                        <div class="ticket-body">
                            <div class="ticket-top">
                                <h3><?php echo htmlspecialchars($order['product_type']); ?></h3>
                                <div class="stamp <?php echo $meta['ink']; ?>"><?php echo htmlspecialchars($meta['label']); ?></div>
                            </div>

                            <div class="ticket-spec">
                                <span><b>Size</b><?php echo htmlspecialchars($order['width']); ?>ft &times; <?php echo htmlspecialchars($order['height']); ?>ft</span>
                                <?php if (!empty($order['shape'])) { ?>
                                    <span><b>Shape</b><?php echo htmlspecialchars(ucfirst($order['shape'])); ?></span>
                                <?php } ?>
                                <span><b>Qty</b><?php echo htmlspecialchars($order['quantity']); ?></span>
                            </div>

                            <div class="ticket-route">
                                <i class="fa-solid <?php echo $meta['icon']; ?>"></i>
                                <?php echo htmlspecialchars($meta['note']); ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>

<footer>
    &copy; 2026 JD PRINTING SERVICES — ALL RIGHTS RESERVED
</footer>

<script>
const filterButtons = document.querySelectorAll('.docket-filters button');
const tickets = document.querySelectorAll('.ticket');

filterButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        filterButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const filter = btn.dataset.filter;
        tickets.forEach(ticket => {
            ticket.style.display = (filter === 'all' || ticket.dataset.status === filter) ? 'grid' : 'none';
        });
    });
});
</script>

</body>
</html>