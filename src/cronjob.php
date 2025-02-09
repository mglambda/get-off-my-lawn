<?php
include 'globals.php';
include_once 'db.php';

// Scan staging directory for new or updated files
$staging_dir = 'staging/';
$files = glob($staging_dir . '*.txt');

foreach ($files as $file) {
    $filename = basename($file);
	// dropping last 4 chars works because the glob above guarantees that all files end in '.txt'
    $title = substr(str_replace('-', ' ', $filename), 0, -4);

    // Read file content
    $content = file_get_contents($file);
    $lines = explode("\n", $content);
    $tags_line = array_pop($lines);
    $tags = array_map('trim', explode(',', $tags_line));
    $tags = array_filter($tags, fn($tag) => !empty($tag));

    // Check if post exists
    $sql = "SELECT * FROM `" . TABLE_PREFIX . "posts` WHERE title = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $title);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update existing post
        $row = $result->fetch_assoc();
        $post_id = $row['id'];
        $sql = "UPDATE `" . TABLE_PREFIX . "posts` SET content = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', implode("\n", $lines), $post_id);
    } else {
        // Insert new post
        $sql = "INSERT INTO `" . TABLE_PREFIX . "posts` (title, content) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $title, implode("\n", $lines));
    }

    if ($stmt->execute()) {
        // Insert tags
        $post_id = $stmt->insert_id ?: $row['id'];
        foreach ($tags as $tag) {
            $tag_sql = "INSERT INTO `" . TABLE_PREFIX . "tags` (post_id, tag) VALUES (?, ?)";
            $tag_stmt = $conn->prepare($tag_sql);
            $tag_stmt->bind_param('is', $post_id, strtolower(trim($tag)));
            $tag_stmt->execute();
        }
    }

    $stmt->close();
}


$conn->close();
?>
