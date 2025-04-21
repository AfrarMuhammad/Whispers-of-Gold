<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "Jewllery";
$port = 3307;

$conn = new mysqli($servername, $username, $password, $dbname, $port);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_SESSION['error'])) {
    session_destroy();
    header("Location: home.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jewelry Collections</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Playfair Display', serif; /* Applied globally */
            font-weight: 400; /* Lighter weight for body text */
            background-color: #1A2E35;
            color: #D7C4A8;
            line-height: 1.6;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 50px;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            background: rgba(26, 46, 53, 0.9);
        }

        .logo {
            font-size: 24px;
            font-weight: 700; /* Bolder for logo */
            color: #D7C4A8;
        }

        nav {
            display: flex;
            align-items: center;
        }

        nav .main-nav {
            display: flex;
           

 gap: 30px;
            margin: 0;
            padding: 0;
        }

        nav .main-nav a {
            text-decoration: none;
            color: #D7C4A8;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 14px;
        }

        nav .main-nav a:hover {
            color: #F3E5C3;
        }

        .showcase {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .showcase a {
            text-decoration: none;
            color: #D7C4A8;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 5px;
        }

        .showcase a:hover {
            color: #F3E5C3;
        }

        .showcase .new-label {
            background-color: #A52A2A;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 500;
            display: inline-block;
            margin-top: 5px;
        }

        .showcase .year {
            color: #F3E5C3;
            font-size: 12px;
            font-weight: 500;
        }

        .hero {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            background-color: #1A2E35;
            padding: 0 20px;
            position: relative;
            overflow: hidden;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero h1 {
            font-size: 60px;
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            color: #F3E5C3;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .hero p {
            font-size: 18px;
            font-weight: 400;
            color: #D7C4A8;
            max-width: 600px;
            margin: 0 auto 30px;
        }

        .collections {
            padding: 80px 20px;
            text-align: center;
            background-color: #1A2E35;
        }

        .collections h2 {
            font-size: 36px;
            font-weight: 700;
            color: #F3E5C3;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        .collections p {
            font-size: 16px;
            font-weight: 400;
            color: #D7C4A8;
            max-width: 600px;
            margin: 0 auto 40px;
        }

        .collection-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .collection-item {
            background-color: #2A4D55;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            position: relative;
        }

        .collection-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        .collection-item img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 15px 15px 0 0;
        }

        .collection-content {
            padding: 20px;
        }

        .collection-item h3 {
            color: #F3E5C3;
            margin: 0 0 10px;
            font-size: 22px;
            font-weight: 700;
        }

        .collection-item .description {
            color: #D7C4A8;
            font-size: 14px;
            font-weight: 400;
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .price {
            font-weight: 700;
            color: #F3E5C3;
            font-size: 18px;
        }

        .cta-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(165, 42, 42, 0.9);
            color: #F3E5C3;
            text-align: center;
            padding: 10px;
            opacity: 0;
            transform: translateY(100%);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .collection-item:hover .cta-overlay {
            opacity: 1;
            transform: translateY(0);
        }

        .cta-overlay span {
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
        }

        footer {
            background-color: #1A2E35;
            color: #D7C4A8;
            text-align: center;
            padding: 20px 0;
            width: 100%;
            position: relative;
        }

        footer p {
            font-size: 14px;
            font-weight: 400;
        }

        .stats {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-top: 20px;
            color: #D7C4A8;
        }

        .stats div {
            font-size: 16px;
            font-weight: 500;
        }

        .form-container {
            background: #2A4D55;
            padding: 20px;
            border-radius: 10px;
            max-width: 400px;
            margin: 20px auto;
            display: none;
            color: #D7C4A8;
        }

        .form-container h2 {
            font-size: 24px;
            font-weight: 700;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #D7C4A8;
            border-radius: 5px;
            background-color: #1A2E35;
            color: #D7C4A8;
            font-family: 'Playfair Display', serif;
            font-size: 14px;
            font-weight: 400;
        }

        .cta-button {
            padding: 10px 20px;
            background-color: #A52A2A;
            color: #F3E5C3;
            border: none;
            border-radius: 5px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
        }

        .cta-button:hover {
            background-color: #801A1A;
        }

        .success-message {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
            font-size: 14px;
            font-weight: 500;
        }

        .error-message {
            background-color: #f44336;
            color: white;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
            font-size: 14px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">Whispers of Gold</div>
        <nav>
            <div class="main-nav">
                <a href="#home">Home</a>
                <a href="#collections">Collection</a>
            </div>
            <div class="showcase">
                <a href="#">New</a>
                <a href="#">Pricing</a>
                <a href="#">My Time</a>
                <a href="#">Power</a>
                <a href="#">New Collection</a>
                <a href="#">Pretty</a>
                <div class="new-label">Jewelry</div>
                <div class="year">2023</div>
            </div>
        </nav>
    </header>

    <section class="hero" id="home">
        <div class="hero-content">
            <h1>Jewelry is not just worn, it's felt — a whisper of art against the skin.</h1>
            <p>Every piece is a reflection of craftsmanship and elegance. Browse our collection to find the one made for you.</p>
        </div>
    </section>

    <section class="collections" id="collections">
        <h2>Our Collections</h2>
        <p>Explore our stunning range of handcrafted jewelry pieces, designed with elegance and precision.</p>
        <div class="collection-grid">
            <div class="collection-item" onclick="window.location.href='index.php?id=1'">
                <img src="emeraldbliss.jpg" alt="Emerald Bliss">
                <div class="collection-content">
                    <h3>Emerald Bliss</h3>
                    <p class="description">A captivating emerald necklace that radiates timeless elegance, perfect for any occasion.</p>
                    <p class="price">$1,299.99</p>
                </div>
                <div class="cta-overlay">
                    <span>Reserve Now</span>
                </div>
            </div>
            <div class="collection-item" onclick="window.location.href='index.php?id=2'">
                <img src="sapphiredream.jpg" alt="Sapphire Dream">
                <div class="collection-content">
                    <h3>Sapphire Dream</h3>
                    <p class="description">Dazzling sapphire earrings that embody sophistication and grace.</p>
                    <p class="price">$899.99</p>
                </div>
                <div class="cta-overlay">
                    <span>Reserve Now</span>
                </div>
            </div>
            <div class="collection-item" onclick="window.location.href='index.php?id=3'">
                <img src="rubyglow.jpg" alt="Ruby Glow">
                <div class="collection-content">
                    <h3>Ruby Glow</h3>
                    <p class="description">A vibrant ruby ring that adds a touch of passion to your style.</p>
                    <p class="price">$999.99</p>
                </div>
                <div class="cta-overlay">
                    <span>Reserve Now</span>
                </div>
            </div>
            <div class="collection-item" onclick="window.location.href='index.php?id=4'">
                <img src="diamondfrost.jpg" alt="Diamond Frost">
                <div class="collection-content">
                    <h3>Diamond Frost</h3>
                    <p class="description">Exquisite diamond bracelet sparkling with unparalleled brilliance.</p>
                    <p class="price">$1,499.99</p>
                </div>
                <div class="cta-overlay">
                    <span>Reserve Now</span>
                </div>
            </div>
            <div class="collection-item" onclick="window.location.href='index.php?id=5'">
                <img src="pearlelegance.jpg" alt="Pearl Elegance">
                <div class="collection-content">
                    <h3>Pearl Elegance</h3>
                    <p class="description">Classic pearl necklace exuding understated luxury and charm.</p>
                    <p class="price">$799.99</p>
                </div>
                <div class="cta-overlay">
                    <span>Reserve Now</span>
                </div>
            </div>
            <div class="collection-item" onclick="window.location.href='index.php?id=6'">
                <img src="amethystdream.jpg" alt="Amethyst Dream">
                <div class="collection-content">
                    <h3>Amethyst Dream</h3>
                    <p class="description">Enchanting amethyst pendant that captivates with its deep purple hues.</p>
                    <p class="price">$599.99</p>
                </div>
                <div class="cta-overlay">
                    <span>Reserve Now</span>
                </div>
            </div>
            <div class="collection-item" onclick="window.location.href='index.php?id=7'">
                <img src="goldensunrise.jpg" alt="Golden Sunrise">
                <div class="collection-content">
                    <h3>Golden Sunrise</h3>
                    <p class="description">Radiant gold bangle inspired by the warmth of a new dawn.</p>
                    <p class="price">$899.99</p>
                </div>
                <div class="cta-overlay">
                    <span>Reserve Now</span>
                </div>
            </div>
            <div class="collection-item" onclick="window.location.href='index.php?id=8'">
                <img src="oceanblue.jpg" alt="Ocean Blue Topaz">
                <div class="collection-content">
                    <h3>Ocean Blue Topaz</h3>
                    <p class="description">Vivid topaz earrings reminiscent of serene ocean waves.</p>
                    <p class="price">$699.99</p>
                </div>
                <div class="cta-overlay">
                    <span>Reserve Now</span>
                </div>
            </div>
            <div class="collection-item" onclick="window.location.href='index.php?id=9'">
                <img src="diamondinfinity.jpg" alt="Diamond Infinity">
                <div class="collection-content">
                    <h3>Diamond Infinity</h3>
                    <p class="description">Timeless diamond necklace symbolizing eternal love and elegance.</p>
                    <p class="price">$1,299.99</p>
                </div>
                <div class="cta-overlay">
                    <span>Reserve Now</span>
                </div>
            </div>
        </div>
    </section>

    <div class="stats">
        <div>12k+</div>
        <div>16k+</div>
        <div>4k+</div>
    </div>

    <div id="login-form" class="form-container">
        <h2>Login</h2>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <form method="POST" action="home.php">
            <input type="hidden" name="action" value="login">
            <div class="form-group">
                <label for="login-email">Email</label>
                <input type="email" id="login-email" name="email" required>
            </div>
            <div class="form-group">
                <label for="login-password">Password</label>
                <input type="password" id="login-password" name="password" required>
            </div>
            <button type="submit" class="cta-button">Login</button>
        </form>
    </div>

    <div id="signup-form" class="form-container">
        <h2>Sign Up</h2>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <form method="POST" action="home.php">
            <input type="hidden" name="action" value="signup">
            <div class="form-group">
                <label for="signup-username">Username</label>
                <input type="text" id="signup-username" name="username" required>
            </div>
            <div class="form-group">
                <label for="signup-email">Email</label>
                <input type="email" id="signup-email" name="email" required>
            </div>
            <div class="form-group">
                <label for="signup-password">Password</label>
                <input type="password" id="signup-password" name="password" required>
            </div>
            <button type="submit" class="cta-button">Sign Up</button>
        </form>
    </div>

    <footer>
        <p>© 2025 Jewelry. All rights reserved.</p>
    </footer>

    <script>
        window.addEventListener('hashchange', showRelevantForm);
        window.addEventListener('load', showRelevantForm);

        function showRelevantForm() {
            const hash = window.location.hash;
            document.getElementById('login-form').style.display = hash === '#login' ? 'block' : 'none';
            document.getElementById('signup-form').style.display = hash === '#signup' ? 'block' : 'none';
        }
    </script>
</body>
</html>