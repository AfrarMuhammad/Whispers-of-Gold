<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1); 


const DB_CONFIG = [
    'host' => 'my-mysql',
    'username' => 'root',
    'password' => 'root',
    'database' => 'Jewllery',
    
];


function validate_csrf_token() {
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

try {
    
    $conn = new mysqli(
        DB_CONFIG['host'],
        DB_CONFIG['username'],
        DB_CONFIG['password'],
        DB_CONFIG['database'],
        
    );

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        
        if (!validate_csrf_token()) {
            throw new Exception("Invalid CSRF token");
        }

        // Sanitize and validate inputs
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $phoneno = filter_input(INPUT_POST, 'phoneno', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validation
        if (empty($username) || empty($phoneno) || empty($email) || empty($password) || empty($confirm_password)) {
            throw new Exception("All fields are required!");
        }

        if (!preg_match("/^[0-9]{10,15}$/", $phoneno)) {
            throw new Exception("Invalid phone number! Must be 10-15 digits.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format!");
        }

        if ($password !== $confirm_password) {
            throw new Exception("Passwords do not match!");
        }

        if (strlen($password) < 8) {
            throw new Exception("Password must be at least 8 characters long!");
        }

        
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR phoneno = ?");
        $check_stmt->bind_param("ss", $email, $phoneno);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception("Email or phone number already registered!");
        }
        $check_stmt->close();

        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (username, phoneno, email, password) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("ssss", $username, $phoneno, $email, $hashed_password);

        if ($stmt->execute()) {
            unset($_SESSION['csrf_token']); 
            echo "<script>alert('Registration successful!'); window.location.href='login.php';</script>";
            exit;
        }

        $stmt->close();
    }

} catch (Exception $e) {
    $error_message = $e->getMessage();
    echo "<script>alert('" . htmlspecialchars($error_message) . "'); window.location.href='signup.php';</script>";
    exit;
} finally {
    $conn->close();
}


$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
