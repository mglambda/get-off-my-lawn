<?php
include 'globals.php';
include_once 'db.php';
include_once 'gomllib.php';

// Check if the 'collapsed' GET variable is set and equals 'true'
$collapsed = isset($_GET['collapsed']) && $_GET['collapsed'] === 'true';


if (isset($_GET['tags'])) {
    // Fetch tags from URL and replace underscores with spaces
    $tags = explode(' ', $_GET['tags']);
    $tags = array_map(fn ($tag) => str_replace('_', ' ', $tag), $tags);
    $page_title = 'Posts in ' . implode(', ', $tags);
} else {
    $page_title = 'Posts and Tags';
}

include 'header.php';
?>

<main>
    <?php

// toggles
echo '<ul>';
// Toggle sort link
if (!isset($_GET['tags'])) {
    // we only offer sorting if we show all tags
    if (isset($_GET['sort']) && $_GET['sort'] == 'count') {
        echo '<li style="display: inline"><a href="?sort=alpha">Sort alphabetically</a></li>';
    } else {
        echo '<li style="display: inline"><a href="?sort=count">Sort by post count</a></li>';
    }
}

// Expand/Collapse toggle
if ($collapsed) {
    echo '<li style="display: inline"><a href="?collapsed=false">Expand posts</a></li>';
} else {
    echo '<li style="display:inline"><a href="?collapsed=true">Collapse posts</a></li>';
}
echo '</ul>';

// viewing specific tags
if (isset($_GET['tags'])) {

    // Prepare SQL query to fetch posts associated with all tags
    $sql = "SELECT p.* FROM `" . TABLE_PREFIX . "posts` p
            JOIN `" . TABLE_PREFIX . "tags` t ON p.id = t.post_id
            WHERE p.hidden = 0 AND ";
    $conditions = [];
    foreach ($tags as $tag) {
        $conditions[] = "(t.tag = ?)";
    }
    $sql .= implode(' OR ', $conditions);
    $sql .= " GROUP BY p.id HAVING COUNT(DISTINCT t.tag) = " . count($tags);

    // Prepare the statement
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die('Error preparing SQL statement: ' . htmlspecialchars($conn->error));
    }

    // Bind parameters dynamically
    $types = str_repeat('s', count($tags)); // 's' for string
    $stmt->bind_param($types, ...$tags);

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<p>Showing posts tagged with: " . implode(', ', $tags) . "</p>";
        if (!$collapsed) {
            while ($row = $result->fetch_assoc()) {
                display_post_row_short($conn, $row);
            }
        } else {
            // collapse == true
            echo '<ul>';
            while ($row = $result->fetch_assoc()) {
                echo '<li><a href="/p/' . str_replace(' ', '-', $row['title']) . '">' . $row['title'] . '</a></li>';
            } // while
            echo '</ul>';
        }
    } else {
        echo "<p>No posts found for the tags: " . implode(', ', $tags) . "</p>";
    }

    $stmt->close();
} else {
    // List all tags and their post counts
    $sql = "SELECT tag, COUNT(*) as count FROM `" . TABLE_PREFIX . "tags` GROUP BY tag ORDER BY ";
    if (isset($_GET['sort']) && $_GET['sort'] == 'count') {
        $sql .= "count DESC";
    } else {
        $sql .= "tag ASC";
    }
    $result = $conn->query($sql);

    echo "<p>Showing posts for all tags:</p>";
    echo '<ul>';
    while ($row = $result->fetch_assoc()) {
        $tag_url = str_replace(' ', '_', $row['tag']);
        echo '<li><a href="/t/' . $tag_url . '">' . $row['tag'] . '</a> (' . $row['count'] . ')'; // open tag item
        if (!$collapsed) {
            // Fetch posts for the current tag
            $post_sql = "SELECT p.* FROM `" . TABLE_PREFIX . "posts` p
                         JOIN `" . TABLE_PREFIX . "tags` t ON p.id = t.post_id
                         WHERE p.hidden = 0 AND t.tag = ?
                         ORDER BY p.created_at DESC";
            $post_stmt = $conn->prepare($post_sql);
            $post_stmt->bind_param('s', $row['tag']);
            $post_stmt->execute();
            $post_result = $post_stmt->get_result();

            if ($post_result->num_rows > 0) {
                echo '<ul>';
                while ($post_row = $post_result->fetch_assoc()) {
                    echo '<li><a href="/p/' . str_replace(' ', '-', $post_row['title']) . '">' . $post_row['title'] . '</a></li>';
                }
                echo '</ul>';
            }
        }
        echo '</li>'; // closes tag item
    }
    echo '</ul>';
}


?>
</main>

<?php
include 'footer.php';
$conn->close();
?>
