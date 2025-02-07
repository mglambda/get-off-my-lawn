<?php
include 'globals.php';

// Database connection parameters
$host = DB_HOST;
$user = DB_USER;
$pass = DB_PASS;
$db_name = DB_NAME;

// Establish a connection to the MySQL server
$conn = new mysqli($host, $user, $pass);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the database exists
$sql = "CREATE DATABASE IF NOT EXISTS `$db_name`";
if (!$conn->query($sql)) {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($db_name);

// Create tables
$sql = "
CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "posts` (
    `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `hidden` TINYINT(1) DEFAULT 1
);

CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "tags` (
    `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `post_id` INT(11),
    `tag` VARCHAR(255)
);
";

if ($conn->multi_query($sql)) {
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->next_result());
}

$conn->close();
?>