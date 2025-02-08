<?php
include 'globals.php';
include_once 'db.php';

$page_title = 'Tags';
include 'header.php';
?>

<main>
    <?php
// viewing specific tags
if (isset($_GET['tags'])) {
// Fetch tags from URL and replace underscores with spaces
    $tags = explode(' ', $_GET['tags']);
    $tags = array_map(fn($tag) => str_replace('_', ' ', $tag), $tags);

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
        while ($row = $result->fetch_assoc()) {
            echo '<article>';
            echo '<h2><a href="/p/' . str_replace(' ', '-', $row['title']) . '">' . $row['title'] . '</a></h2>';
            if (strlen($row['content']) > 300) {
                echo '<p>' . substr($row['content'], 0, 300) . '...</p>';
                echo '<p><a href="/' . str_replace(' ', '-', $row['title']) . '">Read more</a></p>';
            } else {
                echo '<p>' . $row['content'] . '</p>';
            }
            echo '</article>';
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

    echo "<p>All tags:</p>";
    echo '<ul>';
    while ($row = $result->fetch_assoc()) {
        $tag_url = str_replace(' ', '_', $row['tag']);
        echo '<li><a href="/t/' . $tag_url . '">' . $row['tag'] . '</a> (' . $row['count'] . ')</li>';
    }
    echo '</ul>';

    // Toggle sort link
    if (isset($_GET['sort']) && $_GET['sort'] == 'count') {
        echo '<p><a href="?sort=alpha">Sort alphabetically</a></p>';
    } else {
        echo '<p><a href="?sort=count">Sort by post count</a></p>';
    }
}

    ?>
</main>

<?php
include 'footer.php';
$conn->close();
?>
