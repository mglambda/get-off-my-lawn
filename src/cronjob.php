<?php

include 'globals.php';
include_once 'db.php';

// Include Parsedown library
require_once 'include/parsedown/Parsedown.php';

$Parsedown = new Parsedown();

// Scan staging directory for new or updated files
$staging_dir = 'staging/';
$files = glob($staging_dir . '*.{txt,md}', GLOB_BRACE);

foreach ($files as $file) {
    $filename = basename($file);
    // Remove the extension to get the title
    $title = pathinfo($filename, PATHINFO_FILENAME);
    // uname has just lowercase alphanumerics and hyphens
    $uname = post_uname_from_title($title);

    // Read file content
    $content = file_get_contents($file);
    $lines = explode("\n", $content);
    $tags_line = array_pop($lines);
    $tags = array_map('trim', explode(',', $tags_line));
    $tags = array_filter($tags, fn ($tag) => !empty($tag));


    // prepare content based on file extension
    if (pathinfo($file, PATHINFO_EXTENSION) === 'md') {
        $content = $Parsedown->text(implode("\n", $lines));
    } else {
        $content = implode("\n", $lines);
    }

    // Check if post exists
    $sql = "SELECT * FROM `" . TABLE_PREFIX . "posts` WHERE uname = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $uname);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update existing post
        $row = $result->fetch_assoc();
        $post_id = $row['id'];

        $sql = "UPDATE `" . TABLE_PREFIX . "posts` SET content = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $content, $post_id);
    } else {
        // Insert new post

        $sql = "INSERT INTO `" . TABLE_PREFIX . "posts` (uname, title, content) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sss', $uname, $title, $content);
    }

    if ($stmt->execute()) {
        // Insert tags
        $post_id = $stmt->insert_id ?: $row['id'];
        // Delete tags associated with the post before inserting the new ones
        $tag_delete_sql = "DELETE FROM `" . TABLE_PREFIX . "tags` WHERE post_id = ?";
        $stmt = $conn->prepare($tag_delete_sql);
        $stmt->bind_param('i', $post_id);
        $stmt->execute();


        foreach ($tags as $tag) {
            $tag_sql = "INSERT INTO `" . TABLE_PREFIX . "tags` (post_id, tag) VALUES (?, ?)";
            $tag_stmt = $conn->prepare($tag_sql);
            $tag_stmt->bind_param('is', $post_id, strtolower(trim($tag)));
            $tag_stmt->execute();
        }
    }

    $stmt->close();
}
