<?php
include 'globals.php';
include_once 'db.php';

if (isset($_GET['post'])) {
    $title = str_replace('-', ' ', $_GET['post']);
    $sql = "SELECT * FROM `" . TABLE_PREFIX . "posts` WHERE title = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $title);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $page_title = $row['title'];
        include 'header.php';
        ?>
        <main>
            <article>
                <h1><?php echo $row['title']; ?></h1>
                <p><em>Created: <?php echo $row['created_at']; ?> | Last Modified: <?php echo $row['updated_at']; ?></em></p>
                <p><?php echo $row['content']; ?></p>

                <!-- Tags -->
                <div>
                    <?php
                    $tag_sql = "SELECT tag FROM `" . TABLE_PREFIX . "tags` WHERE post_id = ?";
                    $tag_stmt = $conn->prepare($tag_sql);
                    $tag_stmt->bind_param('i', $row['id']);
                    $tag_stmt->execute();
                    $tag_result = $tag_stmt->get_result();

                    if ($tag_result->num_rows > 0) {
                        while ($tag_row = $tag_result->fetch_assoc()) {
                            echo '<a href="/t/' . str_replace(' ', '_', urlencode($tag_row['tag'])) . '">' . $tag_row['tag'] . '</a> ';
                        }
                    }
                    ?>
                </div>
            </article>
        </main>

<?php
    } else {
	include 'header.php';
        echo "Post not found.";
    }

    $stmt->close();
    } else {
	include 'header.php';
    echo "No post specified.";
}

include 'footer.php';
$conn->close();
?>
