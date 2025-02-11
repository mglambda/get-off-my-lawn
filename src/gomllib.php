<?php


function get_post_url_relative($post_title) {
return '/p/' . str_replace(' ', '-', $post_title);
}

function display_post_row($conn, $row)
{
    ?>
            <article>
                <h2><?php echo htmlspecialchars($row['title']); ?></h2>
                <p><em>Created: <?php echo htmlspecialchars($row['created_at']); ?> | Last Modified: <?php echo htmlspecialchars($row['updated_at']); ?></em></p>
				<br>
                <?php echo $row['content']; ?>

                <!-- Tags -->
                <div>
                    <?php
                        $tag_sql = "SELECT tag FROM `" . TABLE_PREFIX . "tags` WHERE post_id = ?";
    $tag_stmt = $conn->prepare($tag_sql);
    $tag_stmt->bind_param('i', $row['id']);
    $tag_stmt->execute();
    $tag_result = $tag_stmt->get_result();

    if ($tag_result->num_rows > 0) {
        echo '<br><p>Tags: ';
        while ($tag_row = $tag_result->fetch_assoc()) {
            echo '<a href="/t/' . urlencode(str_replace(' ', '_', htmlspecialchars($tag_row['tag']))) . '">' . htmlspecialchars($tag_row['tag']) . '</a> ';
        }
        echo '</p>';
    }
    ?>
                </div>
            </article>
        <?php
}

function display_post_row_short($conn, $row)
{
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

function display_post($conn, $post_id)
{
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

function display_post_by_title($conn, $title)
{
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


function _change_verbosity($verbosity, $direction=1) {
// direction 1 for up, -1 for down
$values = [0, 10, 100];
// find index for given verbosity
for($i=0; $i < count($values); $i++) {
$last_index = $i;
if($values[$i] >= $verbosity) {
break;
}
}

	return $values[max(0, ($last_index + $direction) % count($values))];
}

function increase_verbosity($verbosity) {
return _change_verbosity($verbosity, 1);
}

function decrease_verbosity($verbosity) {
return _change_verbosity($verbosity, -1);
}

function display_posts($conn, $verbosity=10) {
// gives a view of all unhidden posts in various forms
// verbosity = 0 means only post titles
// verbosity = 10 means title and abbreviated content, the default
// verbosity >= 100 means full post
$sql = "SELECT * FROM `" . TABLE_PREFIX . "posts` WHERE hidden = 0 ORDER BY created_at DESC";
$result = $conn->query($sql);

    if ($result->num_rows > 0) {
	if($verbosity==0) {
	echo '<ul>';
	}
	
        while ($row = $result->fetch_assoc()) {
		switch($verbosity) {
		case 0:
echo '<li><a href="' . get_post_url_relative($row['title']) . '">' . $row['title'] . '</a></li>';
break;
		case 10:
            display_post_row_short($conn, $row);
			break;
			case 100:
			// this is a full view
display_post_by_title($conn, $row['title']);
echo '<br>';
break;
			} // end switch
			if($verbosity == 0) {
			echo '</ul>';
			}

        }
    } else {
        echo '<p>No posts found.</p>';
    }
            echo '<br>';	
}

?>