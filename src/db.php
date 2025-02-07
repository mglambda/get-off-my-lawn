<?php
include 'globals.php';

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>Warning: Database connection failed. If you haven't already, please visit <a href='setup.php'>setup.php</a> to create the database and install the necessary tables.</p>";
    throw $e;
}
?>
