<?php
include 'db.php';
include 'globals.php';

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