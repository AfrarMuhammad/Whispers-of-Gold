<?php
ob_start(); // Start output buffering to prevent header issues
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "Jewllery";
$port = 3307;

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Connection failed. Please try again later.");
}

// Check if user is logged in
if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    $_SESSION['error'] = "Please log in to view your reservation.";
    error_log("No session email, redirecting to login.php");
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

// Fetch the most recent reservation for the logged-in user
$user_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
if ($user_stmt === false) {
    $_SESSION['error'] = "Database error: Unable to fetch user.";
    error_log("Prepare failed (user fetch): " . $conn->error);
    header("Location: index.php");
    exit();
}
$user_stmt->bind_param("s", $email);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows === 0) {
    $_SESSION['error'] = "User not found.";
    error_log("User not found for email: $email");
    header("Location: index.php");
    exit();
}

$user = $user_result->fetch_assoc();
$user_id = $user['id'];
$user_stmt->close();

// Fetch the latest reservation
$reservation_stmt = $conn->prepare("
    SELECT r.id, r.jewelry_id, r.reservation_date, r.time_slot, r.special_requests, j.name AS jewelry_name, j.description AS jewelry_description
    FROM reservations r
    JOIN jewelry j ON r.jewelry_id = j.id
    WHERE r.user_id = ?
    ORDER BY r.reservation_date DESC
    LIMIT 1
");
if ($reservation_stmt === false) {
    $_SESSION['error'] = "Database error: Unable to fetch reservation.";
    error_log("Prepare failed (reservation fetch): " . $conn->error);
    header("Location: index.php");
    exit();
}
$reservation_stmt->bind_param("i", $user_id);
$reservation_stmt->execute();
$reservation_result = $reservation_stmt->get_result();

$reservation = null;
if ($reservation_result->num_rows > 0) {
    $reservation = $reservation_result->fetch_assoc();
} else {
    $_SESSION['error'] = "No recent reservation found.";
    error_log("No reservation found for user_id: $user_id");
    header("Location: index.php");
    exit();
}
$reservation_stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Receipt</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;700&display=swap">
    <style>
        body {
            margin: 0;
            font-family: 'Cormorant Garamond', serif;
            line-height: 1.6;
            background-color: #174E4F; /* Dark teal background */
            color: #F5E8C7; /* Light beige text */
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .header {
            padding: 20px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #174E4F;
        }

        .brand {
            font-size: 24px;
            font-weight: 400;
            color: #F5E8C7;
        }

        .nav-links a {
            text-decoration: none;
            color: #F5E8C7;
            font-size: 16px;
            margin-left: 20px;
            font-weight: 400;
        }

        .nav-links a:hover {
            color: #D4A373; /* Light gold hover color */
        }

        .receipt-section {
            padding: 50px;
            text-align: center;
            max-width: 800px;
            margin: 0 auto;
            flex: 1;
        }

        .receipt-section h2 {
            font-size: 48px;
            font-weight: 700;
            color: #F5E8C7;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        .receipt-section p {
            font-size: 18px;
            color: #D4A373; /* Light gold for subtext */
            margin-bottom: 30px;
        }

        .receipt-details {
            background-color: rgba(245, 232, 199, 0.1); /* Semi-transparent beige */
            padding: 20px;
            border-radius: 5px;
            text-align: left;
            color: #F5E8C7;
        }

        .receipt-details h3 {
            font-size: 24px;
            color: #F5E8C7;
            margin-bottom: 15px;
        }

        .receipt-details p {
            font-size: 16px;
            color: #F5E8C7;
            margin: 10px 0;
        }

        .receipt-details strong {
            color: #D4A373; /* Light gold for emphasis */
        }

        .cta-button {
            display: inline-block;
            padding: 12px 25px;
            background-color: #D4A373;
            color: #174E4F;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 700;
            margin-top: 20px;
            font-family: 'Cormorant Garamond', serif;
        }

        .cta-button:hover {
            background-color: #C68E58; /* Darker gold hover */
        }

        .error-message {
            background-color: #A63F3F; /* Reddish error color */
            color: #F5E8C7;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        footer {
            background-color: #174E4F;
            color: #F5E8C7;
            text-align: center;
            padding: 20px 0;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">Whispers of Gold</div>
        <div class="nav-links">
            <a href="home.php">HOME</a>
            <a href="index.php">COLL</a>
        </div>
    </div>

    <section class="receipt-section">
        <h2>Reservation Successful</h2>
        <p>Thank you for your reservation! </p>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <div class="receipt-details">
            <h3>Booking Details</h3>
            <p><strong>Booking ID:</strong> <?php echo htmlspecialchars($reservation['id']); ?></p>
            <p><strong>Booked By:</strong> <?php echo htmlspecialchars($email); ?></p>
            <p><strong>Jewelry Item:</strong> <?php echo htmlspecialchars($reservation['jewelry_name']); ?></p>
            <p><strong>Description:</strong> <?php echo htmlspecialchars($reservation['jewelry_description'] ?: 'No description available'); ?></p>
            <p><strong>Reservation Date:</strong> <?php echo htmlspecialchars(date('F j, Y', strtotime($reservation['reservation_date']))); ?></p>
            <p><strong>Time Slot:</strong> <?php echo htmlspecialchars($reservation['time_slot']); ?></p>
            <p><strong>Special Requests:</strong> <?php echo htmlspecialchars($reservation['special_requests'] ?: 'None'); ?></p>
        </div>
        <a href="index.php" class="cta-button">Make Another Reservation</a>
    </section>

    <footer>
        <p>© 2025 Whispers of Gold. All rights reserved.</p>
    </footer>

    <script>
        // Handle navigation (no changes needed)
    </script>
</body>
</html>

<?php
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

ob_end_flush(); // Flush output buffer
?>