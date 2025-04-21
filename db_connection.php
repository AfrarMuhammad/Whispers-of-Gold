
<?php
$servername = "my-mysql";
$username = "root";
$password = "root";
$dbname = "Jewllery";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
