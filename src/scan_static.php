<?php

include 'globals.php';
include_once 'db.php';

// Scan static directory for PHP files
$static_dir = 'static/';
$files = glob($static_dir . '*.php');

foreach ($files as $file) {
    $filename = basename($file);
    $url = '/s/' . str_replace('.php', '', $filename);
    $name = str_replace(['-', '_'], ' ', str_replace('.php', '', $filename));

    // Check if the link already exists
    $sql = "SELECT * FROM `" . TABLE_PREFIX . "navigation_links` WHERE url = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $url);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // Determine the maximum ordering value
        $max_ordering_sql = "SELECT MAX(ordering) AS max_ordering FROM `" . TABLE_PREFIX . "navigation_links`";
        $max_ordering_stmt = $conn->prepare($max_ordering_sql);
        $max_ordering_stmt->execute();
        $max_ordering_result = $max_ordering_stmt->get_result();
        $max_ordering_row = $max_ordering_result->fetch_assoc();
        $new_ordering = $max_ordering_row['max_ordering'] + 1;

        // Insert new link
        $sql = "INSERT INTO `" . TABLE_PREFIX . "navigation_links` (url, name, ordering, hidden) VALUES (?, ?, ?, 0)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssi', $url, $name, $new_ordering);
        $stmt->execute();
    }

    $stmt->close();
}
