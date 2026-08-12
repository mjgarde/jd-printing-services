<?php
session_start();
require_once '../config/db.php';

$loginError = "";
$registerError = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login_submit'])) {
    $username = mysqli_real_escape_string($conn, $_POST['login_username']);
    $password = $_POST['login_password'];

    $sql = "SELECT * FROM clients WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $client = mysqli_fetch_assoc($result);

        if ($password == $client['password']) {
            $_SESSION['client_id'] = $client['id'];
            $_SESSION['client_username'] = $client['username'];
            echo "<script>alert('Login successful! Welcome back, " . $client['username'] . ".'); window.location.href='index.php';</script>";
            exit();
        } else {
            $loginError = "Incorrect password.";
        }
    } else {
        $loginError = "Username not found.";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_submit'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $age = mysqli_real_escape_string($conn, $_POST['age']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $username = mysqli_real_escape_string($conn, $_POST['reg_username']);
    $password = mysqli_real_escape_string($conn, $_POST['reg_password']);

    $check = mysqli_query($conn, "SELECT id FROM clients WHERE username = '$username'");

    if (mysqli_num_rows($check) > 0) {
        $registerError = "Username already taken.";
    } else {
        $sql = "INSERT INTO clients (full_name, age, address, email, phone, username, password)
                VALUES ('$full_name', '$age', '$address', '$email', '$phone', '$username', '$password')";

        if (mysqli_query($conn, $sql)) {
            $_SESSION['client_id'] = mysqli_insert_id($conn);
            $_SESSION['client_username'] = $username;
            echo "<script>alert('Registration successful! Welcome, " . $username . ".'); window.location.href='index.php';</script>";
            exit();
        } else {
            $registerError = "Something went wrong. Please try again.";
        }
    }
}

$isLoggedIn = isset($_SESSION['client_id']);

$pendingOrderCount = 0;
$deliveryCount = 0;
if ($isLoggedIn) {
    $client_id = $_SESSION['client_id'];
    $countResult = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM orders WHERE client_id = '$client_id' AND status IN ('pending', 'quoted')");
    if ($countResult) {
        $countRow = mysqli_fetch_assoc($countResult);
        $pendingOrderCount = (int) $countRow['cnt'];
    }

    $deliveryResult = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM orders WHERE client_id = '$client_id' AND fulfillment_method = 'delivery' AND status = 'approved'");
    if ($deliveryResult) {
        $deliveryRow = mysqli_fetch_assoc($deliveryResult);
        $deliveryCount = (int) $deliveryRow['cnt'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>JD Printing Services</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../font/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=IBM+Plex+Mono:wght@500;600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="colorbar"><span></span><span></span><span></span><span></span></div>

<?php if (isset($_GET['order']) && $_GET['order'] == 'success') { ?>
    <div class="top-msg" style="background-color:#14130f;">
        Order submitted successfully!
    </div>
<?php } ?>

<div class="navbar">
    <div class="logo">
        <img src="logo.png" alt="JD Printing Logo">
    </div>

    <div class="nav-buttons">
        <?php if ($isLoggedIn) { ?>
            <a href="pages/my_orders.php" class="cart-icon" aria-label="My Orders">
                <i class="fa-solid fa-cart-shopping"></i>
                <?php if ($pendingOrderCount > 0) { ?>
                    <span class="cart-badge"><?php echo $pendingOrderCount; ?></span>
                <?php } ?>
            </a>
            <a href="pages/my_deliveries.php" class="cart-icon" aria-label="My Deliveries">
                <i class="fa-solid fa-truck"></i>
                <?php if ($deliveryCount > 0) { ?>
                    <span class="cart-badge"><?php echo $deliveryCount; ?></span>
                <?php } ?>
            </a>
            <span class="greeting">HI, <?php echo strtoupper($_SESSION['client_username']); ?></span>
            <a href="logout.php" class="btn btn-outline"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        <?php } else { ?>
            <button class="btn btn-outline" onclick="openModal('loginModal')">Login</button>
            <button class="btn btn-dark" onclick="openModal('registerModal')">Sign Up</button>
        <?php } ?>
    </div>
</div>

<div class="hero">
    <div class="hero-image">
        <img src="background.png" alt="Printing Services">
    </div>
    <div class="hero-content">
        <h1>Print Your Design,<br><em>Track</em> Every Step</h1>
        <p>Submit your design online, get an instant price quote, and follow your order from production to pick-up or delivery.</p>
        <div class="hero-actions">
            <a href="#order" class="btn btn-dark btn-large">Start an Order</a>
            <a href="#how" class="btn btn-outline btn-large">How it Works</a>
        </div>
    </div>
</div>

<div class="section" id="order">
    <div class="order-grid">
        <div class="order-card" onclick="<?php echo $isLoggedIn ? "openModal('tarpaulinModal')" : "openModal('loginModal')"; ?>">
            <div class="order-card-media">
                <img src="1.png" alt="Tarpaulin">
            </div>
            <div class="order-card-body">
                <div class="ticket-row">
                    <h3>Tarpaulin</h3>
                </div>
                <p>Custom size tarpaulin printing for events and signage.</p>
                <button class="btn btn-dark">Order Now</button>
            </div>
        </div>

        <div class="order-card" onclick="<?php echo $isLoggedIn ? "openModal('stickerModal')" : "openModal('loginModal')"; ?>">
            <div class="order-card-media">
                <img src="2.png" alt="Sticker">
            </div>
            <div class="order-card-body">
                <div class="ticket-row">
                    <h3>Sticker</h3>
                </div>
                <p>Custom die-cut or sheet stickers for any design.</p>
                <button class="btn btn-dark">Order Now</button>
            </div>
        </div>
    </div>
</div>

<div class="section" id="services">
    <div class="section-head">
        <span class="eyebrow">What We Offer</span>
        <h2>Our Services</h2>
        <p>Everything you need to order, track, and receive your prints.</p>
    </div>
    <div class="service-grid">
        <div class="service-card">
            <div class="service-icon"><i class="fa-solid fa-image"></i></div>
            <h3>Custom Design Orders</h3>
            <p>Submit your own design and order details online.</p>
        </div>
        <div class="service-card">
            <div class="service-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
            <h3>Order Tracking</h3>
            <p>Check the real-time status of your order anytime.</p>
        </div>
        <div class="service-card">
            <div class="service-icon"><i class="fa-solid fa-truck"></i></div>
            <h3>Pick-up / Delivery</h3>
            <p>Get notified once your order is ready for release.</p>
        </div>
        <div class="service-card">
            <div class="service-icon"><i class="fa-solid fa-tag"></i></div>
            <h3>Instant Price Quotation</h3>
            <p>Automatic price computation based on size and material.</p>
        </div>
        <div class="service-card">
            <div class="service-icon"><i class="fa-solid fa-file-circle-check"></i></div>
            <h3>Quotation Approval</h3>
            <p>Confirm your price before production starts.</p>
        </div>
        <div class="service-card">
            <div class="service-icon"><i class="fa-solid fa-receipt"></i></div>
            <h3>Order History</h3>
            <p>View and reorder from your past transactions.</p>
        </div>
    </div>
</div>

<div class="section how-it-works" id="how">
    <div class="section-head">
        <h2>How to Order</h2>
        <p>Four easy steps from design to delivery.</p>
    </div>
    <div class="steps">
        <div class="step">
            <div class="step-number">1</div>
            <h4>Submit Design</h4>
            <p>Upload your design and choose size &amp; material.</p>
        </div>
        <div class="step">
            <div class="step-number">2</div>
            <h4>Confirm Quotation</h4>
            <p>Review the computed price and approve it.</p>
        </div>
        <div class="step">
            <div class="step-number">3</div>
            <h4>Production</h4>
            <p>Track your order while it's being printed.</p>
        </div>
        <div class="step">
            <div class="step-number">4</div>
            <h4>Pick-up / Delivery</h4>
            <p>Get your finished order on your scheduled date.</p>
        </div>
    </div>
</div>

<div class="section" id="location">
    <div class="section-head">
        <span class="eyebrow">Visit Us</span>
        <h2>Find Our Shop</h2>
        <p>Come see us in person or get directions straight to our door.</p>
    </div>
    <div class="location-wrap">
        <div class="location-map">
            <img src="map.png" alt="JD Print Advertising Services location map">
        </div>
        <div class="location-info">
            <h3>JD Print Advertising Services</h3>
            <div class="location-row">
                <i class="fa-solid fa-location-dot"></i>
                <p>Koronadal City, Philippines</p>
            </div>
            <div class="location-row">
                <i class="fa-solid fa-clock"></i>
                <p>Open daily &middot; Walk-ins welcome</p>
            </div>
            <div class="location-actions">
                <a href="https://www.google.com/maps/place/Jd+Print+Advertising+Services/@6.4979643,124.8418227,17z/data=!3m1!4b1!4m6!3m5!1s0x32f81893b8e79917:0xd55f04f13135c699!8m2!3d6.4979643!4d124.8443976!16s%2Fg%2F11rwnjtt__?entry=ttu&g_ep=EgoyMDI2MDgwMi4wIKXMDSoASAFQAw%3D%3D" target="_blank" rel="noopener" class="btn btn-dark btn-large">
                    <i class="fa-solid fa-diamond-turn-right"></i> Open in Google Maps
                </a>
            </div>
        </div>
    </div>
</div>

<footer>
    &copy; 2026 JD PRINTING SERVICES — ALL RIGHTS RESERVED
</footer>

<div class="modal-overlay" id="loginModal">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('loginModal')"><i class="fa-solid fa-xmark"></i></button>
        <h2>Welcome Back</h2>
        <p class="modal-sub">Login to track and manage your orders.</p>

        <?php if ($loginError != "") { ?>
            <div class="modal-error"><?php echo $loginError; ?></div>
        <?php } ?>

        <form method="POST" action="index.php">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="login_username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="login_password" required>
            </div>
            <button type="submit" name="login_submit" class="btn btn-dark modal-submit">Login</button>
        </form>

        <p class="modal-switch">Don't have an account? <span onclick="switchModal('loginModal','registerModal')">Sign up</span></p>
    </div>
</div>

<div class="modal-overlay" id="registerModal">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('registerModal')"><i class="fa-solid fa-xmark"></i></button>
        <h2>Create an Account</h2>
        <p class="modal-sub">Sign up to place and track your orders.</p>

        <?php if ($registerError != "") { ?>
            <div class="modal-error"><?php echo $registerError; ?></div>
        <?php } ?>

        <form method="POST" action="index.php">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Age</label>
                    <input type="number" name="age" min="1" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" required>
                </div>
            </div>

            <div class="form-group">
                <label>Address</label>
                <input type="text" name="address" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="reg_username" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="reg_password" required>
            </div>

            <button type="submit" name="register_submit" class="btn btn-dark modal-submit">Sign Up</button>
        </form>

        <p class="modal-switch">Already have an account? <span onclick="switchModal('registerModal','loginModal')">Login</span></p>
    </div>
</div>

<?php if ($isLoggedIn) { ?>
<div class="modal-overlay" id="tarpaulinModal">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('tarpaulinModal')"><i class="fa-solid fa-xmark"></i></button>
        <h2>Order Tarpaulin</h2>
        <p class="modal-sub">Upload your design and fill in the order details.</p>

        <form method="POST" action="pages/order_tarpaulin.php" enctype="multipart/form-data">
            <div class="form-group">
                <label>Design File</label>
                <label class="upload-box" for="designFile">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <p>Click to upload your design</p>
                    <span class="upload-filename" id="fileNameDisplayTarp"></span>
                </label>
                <input type="file" id="designFile" name="design_file" accept="image/*,.pdf" onchange="showFileName(this, 'fileNameDisplayTarp')">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Width (ft)</label>
                    <input type="number" name="width" step="0.1" min="1" required>
                </div>
                <div class="form-group">
                    <label>Height (ft)</label>
                    <input type="number" name="height" step="0.1" min="1" required>
                </div>
            </div>

            <div class="form-group">
                <label>Quantity</label>
                <input type="number" name="quantity" min="1" value="1" required>
            </div>

            <div class="form-group">
                <label>Pick-up or Delivery</label>
                <select name="fulfillment_method" required>
                    <option value="pickup">Pick-up at Shop</option>
                    <option value="delivery">Delivery</option>
                </select>
            </div>

            <div class="form-group">
                <label>Additional Notes</label>
                <textarea name="notes" placeholder="Eyelets, orientation, special instructions..."></textarea>
            </div>

            <button type="submit" class="btn btn-dark modal-submit">Submit Order</button>
        </form>
    </div>
</div>

<div class="modal-overlay" id="stickerModal">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('stickerModal')"><i class="fa-solid fa-xmark"></i></button>
        <h2>Order Sticker</h2>
        <p class="modal-sub">Upload your design and fill in the order details.</p>

        <form method="POST" action="pages/order_sticker.php" enctype="multipart/form-data">
            <div class="form-group">
                <label>Design File</label>
                <label class="upload-box" for="designFileSticker">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <p>Click to upload your design</p>
                    <span class="upload-filename" id="fileNameDisplaySticker"></span>
                </label>
                <input type="file" id="designFileSticker" name="design_file" accept="image/*,.pdf" onchange="showFileName(this, 'fileNameDisplaySticker')">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Width (in)</label>
                    <input type="number" name="width" step="0.1" min="1" required>
                </div>
                <div class="form-group">
                    <label>Height (in)</label>
                    <input type="number" name="height" step="0.1" min="1" required>
                </div>
            </div>

            <div class="form-group">
                <label>Shape</label>
                <select name="shape" required>
                    <option value="">Select shape</option>
                    <option value="square">Square</option>
                    <option value="rectangle">Rectangle</option>
                    <option value="circle">Circle</option>
                    <option value="die-cut">Die-Cut (Custom Shape)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Quantity</label>
                <input type="number" name="quantity" min="1" value="1" required>
            </div>

            <div class="form-group">
                <label>Pick-up or Delivery</label>
                <select name="fulfillment_method" required>
                    <option value="pickup">Pick-up at Shop</option>
                    <option value="delivery">Delivery</option>
                </select>
            </div>

            <div class="form-group">
                <label>Additional Notes</label>
                <textarea name="notes" placeholder="Finish, spacing, special instructions..."></textarea>
            </div>

            <button type="submit" class="btn btn-dark modal-submit">Submit Order</button>
        </form>
    </div>
</div>
<?php } ?>

<script>
function openModal(id) {
    document.getElementById(id).classList.add('active');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}

function switchModal(fromId, toId) {
    closeModal(fromId);
    openModal(toId);
}

function showFileName(input, displayId) {
    var display = document.getElementById(displayId);
    if (input.files.length > 0) {
        display.textContent = input.files[0].name;
    }
}

window.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
    }
});

<?php if ($loginError != "") { ?>
    openModal('loginModal');
<?php } ?>

<?php if ($registerError != "") { ?>
    openModal('registerModal');
<?php } ?>
</script>

</body>
</html>