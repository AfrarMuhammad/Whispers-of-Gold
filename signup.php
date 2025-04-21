<?php
session_start(); 
$_SESSION['csrf_token'] = bin2hex(random_bytes(32)); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jewellery Shop Signup</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Lato', sans-serif;
        }

        body {
            font-family: 'Lato', sans-serif;
        }

        .outer-box {
            width: 100vw;
            height: 100vh;
            background: #F3E5C3;
            position: relative;
            overflow: hidden;
        }

        .inner-box {
            width: 550px;
            padding: 350px 50px;
            margin: 0 auto;
            position: relative;
            margin-right: 0%;
            top: 45%;
            transform: translateY(-50%);
            background: #174E4F;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            z-index: 2;
        }

        .signup-header h1 {
            font-size: 2.5rem;
            color: #F3E5C3;
            margin-bottom: 10px;
        }

        .signup-header p {
            font-size: 1rem;
            color: #ddd;
            margin-bottom: 20px;
        }

        .signup-body {
            margin: 20px 0;
        }

        .signup-body p {
            margin: 12px 0;
        }

        .signup-body p label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
            color: #F3E5C3;
        }

        .signup-body p input {
            width: 100%;
            padding: 14px;
            border: 2px solid #ccc;
            border-radius: 20px;
            font-size: 1rem;
            margin-top: 4px;
        }

        .signup-body p input[type="submit"] {
            border: none;
            color: #D7EAE2;
            cursor: pointer;
            background-size: 200%;
            background-image: linear-gradient(to right, #E84F5E, #5C0E14);
            padding: 14px;
            margin-top: 20px;
            transition: background-position 0.4s ease;
        }

        .signup-body p input[type="submit"]:hover {
            background-position: -100% 0;
        }

        .signup-footer p {
            color: #ddd;
            text-align: center;
            font-size: 0.9rem;
        }

        .signup-footer p a {
            color: #F17141;
            text-decoration: none;
        }

        .signup-footer p a:hover {
            text-decoration: underline;
        }

        .error-message {
            color: #f44336;
            text-align: center;
            margin-bottom: 15px;
        }

        @media (max-width: 600px) {
            .inner-box {
                width: 90%;
                padding: 200px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="outer-box">
        <div class="inner-box">
            <header class="signup-header">
                <h1>Sign Up</h1>
                <p>Please enter your credentials.</p>
            </header>
            <main class="signup-body">
                <?php if (isset($error)): ?>
                    <p class="error-message"><?php echo $error; ?></p>
                <?php endif; ?>
                <form action="process_signup.php" method="POST" onsubmit="return validateForm()" role="form">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <p>
                        <label for="username">Name <span aria-hidden="true">*</span></label>
                        <input type="text" id="username" name="username" 
                               placeholder="Enter your name" 
                               aria-required="true" 
                               required>
                    </p>
                    <p>
                        <label for="email">Email ID <span aria-hidden="true">*</span></label>
                        <input type="email" id="email" name="email" 
                               placeholder="Enter your email" 
                               aria-required="true" 
                               required>
                    </p>
                    <p>
                        <label for="phone">Phone Number <span aria-hidden="true">*</span></label>
                        <input type="tel" id="phone" name="phoneno" 
                               placeholder="Enter your phone number" 
                               pattern="[0-9]{10}" 
                               aria-required="true" 
                               required>
                    </p>
                    <p>
                        <label for="password">Password <span aria-hidden="true">*</span></label>
                        <input type="password" id="password" name="password" 
                               placeholder="Enter your password" 
                               aria-required="true" 
                               required>
                    </p>
                    <p>
                        <label for="confirm-password">Confirm Password <span aria-hidden="true">*</span></label>
                        <input type="password" id="confirm-password" name="confirm_password" 
                               placeholder="Confirm your password" 
                               aria-required="true" 
                               required>
                    </p>
                    <p>
                        <input type="submit" value="Create Account" aria-label="Create Account">
                    </p>
                </form>
            </main>
            <footer class="signup-footer">
                <p>Already have an account? <a href="login.php">Login</a></p>
            </footer>
        </div>
    </div>

    <script>
        function validateForm() {
            const phone = document.getElementById("phone").value;
            const password = document.getElementById("password").value;
            const confirmPassword = document.getElementById("confirm-password").value;
            const email = document.getElementById("email").value;
            const phonePattern = /^[0-9]{10}$/;
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!phonePattern.test(phone)) {
                alert("Please enter a valid 10-digit phone number.");
                return false;
            }

            if (!emailPattern.test(email)) {
                alert("Please enter a valid email address.");
                return false;
            }

            if (password !== confirmPassword) {
                alert("Passwords do not match.");
                return false;
            }

            if (password.length < 8) {
                alert("Password must be at least 8 characters long.");
                return false;
            }

            return true;
        }
    </script>
</body>
</html>