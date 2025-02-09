<?php
include 'globals.php';
include_once 'db.php';

$page_title = 'Tags';

// Check if the 'collapsed' GET variable is set and equals 'true'
$collapsed = isset($_GET['collapsed']) && $_GET['collapsed'] === 'true';

include 'header.php';
?>

<main>
    <?php
    // Fetch all tags and their post counts
    $sql = "SELECT tag, COUNT(*) as count FROM `" . TABLE_PREFIX . "tags` GROUP BY tag ORDER BY ";
    if (isset($_GET['sort']) && $_GET['sort'] == 'count') {
        $sql .= "count DESC";
    } else {
        $sql .= "tag ASC";
    }
    $result = $conn->query($sql);

    echo "<p>All tags:</p>";
    echo '<ul>';
    while ($row = $result->fetch_assoc()) {
        $tag_url = str_replace(' ', '_', $row['tag']);
        echo '<li><a href="/t/' . $tag_url . '">' . $row['tag'] . '</a> (' . $row['count'] . ')';
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
        echo '</li>';
    }
    echo '</ul>';

    // Toggle sort link
    if (isset($_GET['sort']) && $_GET['sort'] == 'count') {
        echo '<p><a href="?sort=alpha">Sort alphabetically</a></p>';
    } else {
        echo '<p><a href="?sort=count">Sort by post count</a></p>';
    }

    // Expand/Collapse toggle
    if ($collapsed) {
        echo '<p><a href="?collapsed=false">Expand posts</a></p>';
    } else {
        echo '<p><a href="?collapsed=true">Collapse posts</a></p>';
    }
    ?>
</main>

<?php
include 'footer.php';
$conn->close();
?>
