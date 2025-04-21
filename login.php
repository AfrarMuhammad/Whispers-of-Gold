<?php
session_start();


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "Jewllery"; 
$port = 3307;

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
} else {
    error_log("Database connection successful");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    error_log("Login attempt - Email: $email");

    if (empty($email) || empty($password)) {
        $error = "Email and password are required.";
        error_log("Error: Empty email or password");
    } else {
        
        $stmt = $conn->prepare("SELECT email, password FROM users WHERE email = ?");
        if ($stmt === false) {
            $error = "Database prepare error: " . $conn->error;
            error_log("Prepare failed: " . $conn->error);
        } else {
            $stmt->bind_param("s", $email);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                error_log("Query executed, rows found: " . $result->num_rows);

                if ($result->num_rows === 1) {
                    $user = $result->fetch_assoc();
                    
                    if (password_verify($password, $user['password'])) {
                        $_SESSION['email'] = $email;
                        error_log("Login successful for $email, redirecting to home.php");
                        
                        if (!headers_sent()) {
                            header("Location: home.php");
                            exit();
                        } else {
                            error_log("Headers already sent, cannot redirect");
                            echo "<p>Login successful, but redirect failed. <a href='home.php'>Click here to go to home</a></p>";
                        }
                    } else {
                        $error = "Invalid password.";
                        error_log("Invalid password for $email");
                    }
                } else {
                    $error = "No account found with that email.";
                    error_log("No user found with email: $email");
                }
            } else {
                $error = "Query execution failed: " . $stmt->error;
                error_log("Query execution failed: " . $stmt->error);
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Jewellery Shop Login</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
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

    .login-header h1 {
      font-size: 2.5rem;
      color: #F3E5C3;
      margin-bottom: 10px;
    }

    .login-header p {
      font-size: 1rem;
      color: #ddd;
      margin-bottom: 20px;
    }

    .login-body {
      margin: 20px 0;
    }

    .login-body p {
      margin: 12px 0;
    }

    .login-body p label {
      display: block;
      font-weight: 600;
      margin-bottom: 5px;
      color: #F3E5C3;
    }

    .login-body p input {
      width: 100%;
      padding: 14px;
      border: 2px solid #ccc;
      border-radius: 20px;
      font-size: 1rem;
      margin-top: 4px;
    }

    .login-body p input[type="submit"] {
      border: none;
      color: #D7EAE2;
      cursor: pointer;
      transition: step-end;
      background-size: 200%;
      background-image: linear-gradient(to right, #E84F5E, #5C0E14);
    }

    .login-body p input[type="submit"]:hover {
      background-position: -100% 0;
    }

    .login-footer p {
      color: #ddd;
      text-align: center;
      font-size: 0.9rem;
    }

    .login-footer p a {
      color: #F17141;
      text-decoration: none;
    }

    .login-footer p a:hover {
      text-decoration: underline;
    }

    .error-message {
      color: #f44336;
      text-align: center;
      margin-bottom: 15px;
    }
  </style>
</head>
<body>
  <div class="outer-box">
    <div class="inner-box">
      <header class="login-header">
        <h1>Login</h1>
        <p>Please enter your credentials.</p>
      </header>
      <main class="login-body">
        <?php if (isset($error)): ?>
          <p class="error-message"><?php echo $error; ?></p>
        <?php endif; ?>
        <form action="login.php" method="POST">
          <p>
            <label for="username">Username</label>
            <input type="email" name="email" placeholder="Enter your email" required>
          </p>
          <p>
            <label for="password">Password</label>
            <input type="password" name="password" placeholder="Enter your password" required>
          </p>
          <p>
            <input type="submit" value="Login">
          </p>
        </form>
      </main>
      <footer class="login-footer">
        <p>Don't have an account? 
          <a href="signup.php" onclick="console.log('Redirecting to signup.php'); return true;">Sign Up</a>
        </p>
      </footer>
    </div>
  </div>
</body>
</html>