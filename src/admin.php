<?php
include 'globals.php';
include 'db.php';

$page_title = 'Admin';

// Fetch all posts
$sql = "SELECT * FROM `" . TABLE_PREFIX . "posts` ORDER BY created_at DESC";
$result = $conn->query($sql);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'hide':
                $id = $_POST['id'];
                $sql = "UPDATE `" . TABLE_PREFIX . "posts` SET hidden = 1 WHERE id = ?";
                break;
            case 'unhide':
                $id = $_POST['id'];
                $sql = "UPDATE `" . TABLE_PREFIX . "posts` SET hidden = 0 WHERE id = ?";
                break;
            case 'delete':
                $id = $_POST['id'];
                $sql = "DELETE FROM `" . TABLE_PREFIX . "posts` WHERE id = ?";
                break;
        }

        if (isset($sql)) {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            header("Location: admin.php");
            exit();
        }
    }
}

include 'header.php';
?>

<main>
    <h1>Admin Panel</h1>
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<article>';
            echo '<h2>' . $row['title'] . '</h2>';
            echo '<p><em>Created: ' . $row['created_at'] . ' | Last Modified: ' . $row['updated_at'] . '</em></p>';
            echo '<form method="post">';
            echo '<input type="hidden" name="id" value="' . $row['id'] . '">';
            if ($row['hidden']) {
                echo '<button type="submit" name="action" value="unhide">Unhide</button>';
            } else {
                echo '<button type="submit" name="action" value="hide">Hide</button>';
            }
            echo '<button type="submit" name="action" value="delete">Delete</button>';
            echo '</form>';
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