<?php
include 'globals.php';
include_once 'db.php';
include_once 'gomllib.php';

$page_title = 'Home';

$sql = "SELECT * FROM `" . TABLE_PREFIX . "posts` WHERE hidden = 0 ORDER BY created_at DESC";
$result = $conn->query($sql);

include 'header.php';
?>

<main>
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
		display_post_row_short($conn, $row);
		echo '<br>';
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
