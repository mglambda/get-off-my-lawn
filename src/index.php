<?php
include 'globals.php';
include 'db.php';

$page_title = 'Home';

// Fetch posts based on tags if provided
if (isset($_GET['tags'])) {
    $tags = explode(',', $_GET['tags']);
    $tag_conditions = implode(' OR ', array_map(fn($tag) => "`tag` LIKE '%" . trim($tag) . "%'", $tags));
    $sql = "SELECT p.* FROM `" . TABLE_PREFIX . "posts` p
            JOIN `" . TABLE_PREFIX . "tags` t ON p.id = t.post_id
            WHERE p.hidden = 0 AND ($tag_conditions)
            ORDER BY p.created_at DESC";
} else {
    $sql = "SELECT * FROM `" . TABLE_PREFIX . "posts` WHERE hidden = 0 ORDER BY created_at DESC";
}

$result = $conn->query($sql);

include 'header.php';
?>

<main>
    <?php
    if ($result->num_rows > 0) {
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
        echo '<p>No posts found.</p>';
    }
    ?>
</main>

<?php
include 'footer.php';
$conn->close();
?>
