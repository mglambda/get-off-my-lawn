<?php

function display_post_row($conn, $row) {
?>
            <article>
                <h2><?php echo htmlspecialchars($row['title']); ?></h2>
                <p><em>Created: <?php echo htmlspecialchars($row['created_at']); ?> | Last Modified: <?php echo htmlspecialchars($row['updated_at']); ?></em></p>
                <p><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>

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
                            echo '<a href="/t/' . urlencode(str_replace(' ', '_', htmlspecialchars($tag_row['tag']))) . '">' . htmlspecialchars($tag_row['tag']) . '</a> ';
                        }
                    }
                    ?>
                </div>
            </article>
        <?php
}


function display_post($conn, $post_id) {
    $sql = "SELECT * FROM `" . TABLE_PREFIX . "posts` WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $post_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
display_post_row($conn, $row);
    } else {
        echo "<p>Post not found.</p>";
    }

    $stmt->close();
}

function display_post_by_title($conn, $title) {
    $sql = "SELECT * FROM `" . TABLE_PREFIX . "posts` WHERE title = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $title);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
display_post_row($conn, $row);
    } else {
        echo "<p>Post not found.</p>";
    }

    $stmt->close();
}
?>