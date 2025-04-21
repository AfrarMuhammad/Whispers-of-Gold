<?php
session_start();

$servername = "my-mysql";
$username = "root";
$password = "root";
$dbname = "Jewllery";


$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'reserve') {
    if (!isset($_SESSION['email'])) {
        $_SESSION['error'] = "Please log in to make a reservation.";
        header("Location: login.php");
        exit();
    }

    $email = $_SESSION['email'];
    $jewelry_id = isset($_POST['jewelry_id']) ? (int)$_POST['jewelry_id'] : null;
    $reservation_date = isset($_POST['reservation_date']) && isset($_POST['time_slot']) 
        ? $_POST['reservation_date'] . ' ' . $_POST['time_slot'] . ':00' 
        : null;
    $special_requests = $_POST['special_requests'] ?? '';
    $time_slot_only = isset($_POST['time_slot']) ? $_POST['time_slot'] : null;

    error_log("Reservation attempt - email: $email, jewelry_id: $jewelry_id, date: $reservation_date, time_slot: $time_slot_only, special_requests: $special_requests");

    if (!$email || !$jewelry_id || !$reservation_date || !$time_slot_only) {
        $_SESSION['error'] = "Missing required fields.";
        header("Location: index.php");
        exit();
    }

    $user_id_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    if ($user_id_stmt === false) {
        $_SESSION['error'] = "Database error: Unable to fetch user.";
        header("Location: index.php");
        exit();
    }
    $user_id_stmt->bind_param("s", $email);
    $user_id_stmt->execute();
    $user_id_result = $user_id_stmt->get_result();

    if ($user_id_result->num_rows === 0) {
        $_SESSION['error'] = "User not found.";
        header("Location: index.php");
        exit();
    }

    $user = $user_id_result->fetch_assoc();
    $user_id = $user['id'];
    $user_id_stmt->close();

    $conn->begin_transaction();
    try {
        $check_stmt = $conn->prepare("SELECT name, status, stock FROM jewelry WHERE id = ? AND status = 'available' AND stock > 0");
        if ($check_stmt === false) {
            throw new Exception("Database error: Unable to check jewelry.");
        }
        $check_stmt->bind_param("i", $jewelry_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("Item not available or out of stock.");
        }
        $item = $result->fetch_assoc();
        $stock = $item['stock'];
        $item_name = $item['name'];
        $check_stmt->close();

        $check_reservation_stmt = $conn->prepare("SELECT COUNT(*) as count FROM reservations WHERE jewelry_id = ? AND reservation_date = ?");
        if ($check_reservation_stmt === false) {
            throw new Exception("Database error: Unable to check reservation.");
        }
        $check_reservation_stmt->bind_param("is", $jewelry_id, $reservation_date);
        $check_reservation_stmt->execute();
        $reservation_result = $check_reservation_stmt->get_result();
        $reservation_count = $reservation_result->fetch_assoc()['count'];
        $check_reservation_stmt->close();

        if ($reservation_count > 0) {
            throw new Exception("Time slot already reserved for $item_name.");
        }

        $stmt = $conn->prepare("INSERT INTO reservations (user_id, jewelry_id, reservation_date, time_slot, special_requests) VALUES (?, ?, ?, ?, ?)");
        if ($stmt === false) {
            throw new Exception("Database error: Unable to prepare reservation.");
        }
        $stmt->bind_param("iisss", $user_id, $jewelry_id, $reservation_date, $time_slot_only, $special_requests);
        if (!$stmt->execute()) {
            throw new Exception("Database error: Unable to insert reservation.");
        }
        $stmt->close();

        $new_stock = $stock - 1;
        $update_stmt = $conn->prepare("UPDATE jewelry SET stock = ?, status = IF(? = 0, 'out of stock', 'available') WHERE id = ?");
        if ($update_stmt === false) {
            throw new Exception("Database error: Unable to update stock.");
        }
        $update_stmt->bind_param("iii", $new_stock, $new_stock, $jewelry_id);
        if (!$update_stmt->execute()) {
            throw new Exception("Database error: Unable to update jewelry.");
        }
        $update_stmt->close();

        $conn->commit();
        $_SESSION['success'] = "Reservation successful for $item_name!";
        header("Location: successful.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Reservation failed: " . $e->getMessage();
        error_log("Reservation error: " . $e->getMessage());
        header("Location: index.php");
        exit();
    }
}

$selected_jewelry_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserve Your Jewelry</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background-color: #F3E5C3;
            color: #174E4F;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 50px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #174E4F;
        }

        nav ul {
            list-style: none;
            display: flex;
            gap: 20px;
            margin: 0;
            padding: 0;
        }

        nav a {
            text-decoration: none;
            color: #174E4F;
            font-weight: 500;
        }

        nav a:hover {
            color: #F3E5C3;
            background-color: #174E4F;
            padding: 5px 10px;
            border-radius: 3px;
        }

        .collections {
            padding: 100px 50px 50px;
            text-align: center;
            background-color: #fff;
        }

        .collections h2 {
            font-size: 32px;
            color: #174E4F;
            margin-bottom: 20px;
        }

        .collections p {
            font-size: 18px;
            color: #174E4F;
            max-width: 600px;
            margin: 0 auto 30px;
        }

        .collection-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            max-width: 1200px;
            margin: 30px auto;
        }

        .collection-item {
            background-color: #F3E5C3;
            padding: 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: transform 0.3s;
        }

        .collection-item:hover {
            transform: scale(1.05);
        }

        .collection-item img {
            width: 100%;
            height: auto;
            object-fit: cover;
            border-radius: 5px;
        }

        .collection-item h3 {
            color: #174E4F;
            margin: 10px 0;
            font-size: 18px;
        }

        .description {
            font-size: 14px;
            color: #0F3435;
            margin: 5px 0;
        }

        .price { font-weight: bold; }
        .stock { color: #0F3435; font-style: italic; }

        .reservation-form {
            display: <?php echo $selected_jewelry_id ? 'block' : 'none'; ?>;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 5px;
            z-index: 1002;
            width: 90%;
            max-width: 500px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #174E4F;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .cta-button {
            padding: 12px 25px;
            background-color: #174E4F;
            color: #F3E5C3;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }

        .cta-button:hover {
            background-color: #0F3435;
        }

        .close {
            color: #174E4F;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover { color: #0F3435; }

        .success-message {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .error-message {
            background-color: #f44336;
            color: white;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .time-slots {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }

        .time-slot {
            padding: 8px 15px;
            background-color: #F3E5C3;
            border-radius: 5px;
            cursor: pointer;
        }

        .time-slot:hover, .time-slot.selected {
            background-color: #174E4F;
            color: #F3E5C3;
        }

        footer {
            background-color: #174E4F;
            color: #F3E5C3;
            text-align: center;
            padding: 20px 0;
            width: 100%;
            position: relative;
            bottom: 0;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">Whisper Of Gold</div>
        <nav>
            <ul>
                <li><a href="home.php#home">Home</a></li>
                <li><a href="index.php">Reserve</a></li>
                <?php if (isset($_SESSION['email'])): ?>
                    <li><a href="#profile">Profile</a></li>
                    <li><a href="?logout=1">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="signup.php">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <section class="collections" id="collections">
        <h2>Our Collections</h2>
        <p>Explore our stunning range of handcrafted jewelry pieces, designed with elegance and precision.</p>
        <div class="collection-grid">
            <div class="collection-item" data-id="1" onclick="window.location.href='index.php?id=1'">
                <img src="emeraldbliss.jpg" alt="Emerald Bliss">
                <h3>Emerald Bliss</h3>
                <p class="price">$1,299.99</p>
                <p class="stock">Stock: 49</p>
            </div>
            <div class="collection-item" data-id="2" onclick="window.location.href='index.php?id=2'">
                <img src="sapphiredream.jpg" alt="Sapphire Dream">
                <h3>Sapphire Dream</h3>
                <p class="price">$899.99</p>
                <p class="stock">Stock: 50</p>
            </div>
            <div class="collection-item" data-id="3" onclick="window.location.href='index.php?id=3'">
                <img src="rubyglow.jpg" alt="Ruby Glow">
                <h3>Ruby Glow</h3>
                <p class="price">$999.99</p>
                <p class="stock">Stock: 49</p>
            </div>
            <div class="collection-item" data-id="4" onclick="window.location.href='index.php?id=4'">
                <img src="diamondfrost.jpg" alt="Diamond Frost">
                <h3>Diamond Frost</h3>
                <p class="price">$1,499.99</p>
                <p class="stock">Stock: 49</p>
            </div>
            <div class="collection-item" data-id="5" onclick="window.location.href='index.php?id=5'">
                <img src="pearlelegance.jpg" alt="Pearl Elegance">
                <h3>Pearl Elegance</h3>
                <p class="price">$799.99</p>
                <p class="stock">Stock: 50</p>
            </div>
            <div class="collection-item" data-id="6" onclick="window.location.href='index.php?id=6'">
                <img src="amethystdream.jpg" alt="Amethyst Dream">
                <h3>Amethyst Dream</h3>
                <p class="price">$599.99</p>
                <p class="stock">Stock: 50</p>
            </div>
            <div class="collection-item" data-id="7" onclick="window.location.href='index.php?id=7'">
                <img src="goldensunrise.jpg" alt="Golden Sunrise">
                <h3>Golden Sunrise</h3>
                <p class="price">$899.99</p>
                <p class="stock">Stock: 50</p>
            </div>
            <div class="collection-item" data-id="8" onclick="window.location.href='index.php?id=8'">
                <img src="oceanblue.jpg" alt="Ocean Blue Topaz">
                <h3>Ocean Blue Topaz</h3>
                <p class="price">$699.99</p>
                <p class="stock">Stock: 50</p>
            </div>
            <div class="collection-item" data-id="9" onclick="window.location.href='index.php?id=9'">
                <img src="diamondinfinity.jpg" alt="Diamond Infinity">
                <h3>Diamond Infinity</h3>
                <p class="price">$1,299.99</p>
                <p class="stock">Stock: 50</p>
            </div>
        </div>
    </section>

    <div id="reservation-form" class="reservation-form">
        <span class="close">×</span>
        <h2>Reserve Your Item</h2>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="action" value="reserve">
            <input type="hidden" name="jewelry_id" id="jewelry_id" value="<?php echo $selected_jewelry_id ? $selected_jewelry_id : ''; ?>">
            <input type="hidden" name="description" id="description" value="">
            <div class="form-group">
                <label for="reservation_date">Reservation Date</label>
                <input type="date" id="reservation_date" name="reservation_date" required>
            </div>
            <div class="form-group">
                <label>Time Slot (9 AM - 5 PM)</label>
                <div class="time-slots" id="time-slots">
                    <?php
                    for ($hour = 9; $hour <= 17; $hour++) {
                        $time = sprintf("%02d:00", $hour);
                        echo "<div class='time-slot' data-value='$time'>$time</div>";
                    }
                    ?>
                </div>
                <input type="hidden" name="time_slot" id="time_slot">
            </div>
            <div class="form-group">
                <label>Item Description</label>
                <p id="item-description" style="text-align: left; color: #174E4F;"></p>
            </div>
            <div class="form-group">
                <label for="special_requests">Special Requests</label>
                <textarea id="special_requests" name="special_requests" rows="4"></textarea>
            </div>
            <button type="submit" class="cta-button">Confirm Reservation</button>
        </form>
    </div>

    <footer>
        <p>© 2025 Jewelry. All rights reserved.</p>
    </footer>

    <script>
        window.addEventListener('load', function() {
            const jewelryId = '<?php echo $selected_jewelry_id; ?>';
            if (jewelryId) {
                document.getElementById('reservation-form').style.display = 'block';
                const descriptions = {
                    '1': 'A stunning emerald necklace featuring a 2-carat center stone surrounded by diamonds',
                    '2': 'Elegant sapphire earrings with white gold setting',
                    '3': 'Bold ruby ring with diamond accents',
                    '4': 'Delicate diamond bracelet with 18K white gold chain',
                    '5': 'Classic freshwater pearl necklace with sterling silver clasp',
                    '6': 'Stunning amethyst pendant with silver chain',
                    '7': '18K gold ring with citrine center stone',
                    '8': 'Blue topaz earrings with white gold setting',
                    '9': 'Infinity-designed diamond bracelet'
                };
                const description = descriptions[jewelryId] || 'No description available';
                document.getElementById('description').value = description;
                document.getElementById('item-description').textContent = description;
            }
        });

        document.querySelector('.reservation-form .close').addEventListener('click', function() {
            document.getElementById('reservation-form').style.display = 'none';
            window.location.href = 'index.php';
        });

        document.querySelectorAll('.time-slot').forEach(slot => {
            slot.addEventListener('click', function() {
                document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
                this.classList.add('selected');
                document.getElementById('time_slot').value = this.dataset.value;
            });
        });
    </script>
</body>
</html>

<?php
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$conn->close();
?>
