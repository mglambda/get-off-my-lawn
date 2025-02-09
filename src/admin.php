<?php
include 'globals.php';
include_once 'db.php';

$page_title = 'Admin';

// always scan for new links in the static directory
include 'scan_static.php';

// commit if requested
if(isset($_GET['commit'])) {
    include 'cronjob.php';
}

// Fetch all posts
$sql = "SELECT * FROM `" . TABLE_PREFIX . "posts` ORDER BY created_at DESC";
$result = $conn->query($sql);

// Fetch all links
$links_sql = "SELECT * FROM `" . TABLE_PREFIX . "navigation_links` ORDER BY ordering";
$links_result = $conn->query($links_sql);

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

                // Delete tags associated with the post
                $tag_sql = "DELETE FROM `" . TABLE_PREFIX . "tags` WHERE post_id = ?";
                $stmt = $conn->prepare($tag_sql);
                $stmt->bind_param('i', $id);
                $stmt->execute();

                $sql = "DELETE FROM `" . TABLE_PREFIX . "posts` WHERE id = ?";
                break;
            case 'restyle':
                // Handle stylesheet change
                if (isset($_POST['style_file'])) {
                    $selected_style = basename($_POST['style_file']);
                    $current_css_file = 'styles/current';
                    $valid_styles = glob('styles/*.css');

                    if (in_array('styles/' . $selected_style, $valid_styles)) {
                        file_put_contents($current_css_file, $selected_style);
                    } else {
                        // Fallback to minimal.css if the selected style is not valid
                        file_put_contents($current_css_file, 'minimal.css');
                    }
                }
                header("Location: admin.php");
                exit();
            case 'apply_links':
                // Handle link changes
                foreach ($_POST['links'] as $link_id => $data) {
                    $hidden = isset($data['hidden']) ? 1 : 0;
                    $url = $data['url'];
                    $name = $data['name'];
                    $ordering = $data['ordering'];

                    $update_sql = "UPDATE `" . TABLE_PREFIX . "navigation_links` SET hidden = ?, url = ?, name = ?, ordering = ? WHERE id = ?";
                    $stmt = $conn->prepare($update_sql);
                    $stmt->bind_param('issii', $hidden, $url, $name, $ordering, $link_id);
                    $stmt->execute();
                }

                if (isset($_POST['delete_link'])) {
                    $delete_id = $_POST['delete_link'];
                    $delete_sql = "DELETE FROM `" . TABLE_PREFIX . "navigation_links` WHERE id = ?";
                    $stmt = $conn->prepare($delete_sql);
                    $stmt->bind_param('i', $delete_id);
                    $stmt->execute();
                }

                header("Location: admin.php");
                exit();
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

if (isset($_GET['preview'])) {
    $post_id = $_GET['preview'];
    $preview_sql = "SELECT * FROM `" . TABLE_PREFIX . "posts` WHERE id = ?";
    $preview_stmt = $conn->prepare($preview_sql);
    $preview_stmt->bind_param('i', $post_id);
    $preview_stmt->execute();
    $preview_result = $preview_stmt->get_result();

    if ($preview_result->num_rows > 0) {
        $preview_row = $preview_result->fetch_assoc();
        include 'header.php';
        ?>
        <main>
            <article>
                <h1><?php echo htmlspecialchars($preview_row['title']); ?></h1>
                <p><em>Created: <?php echo htmlspecialchars($preview_row['created_at']); ?> | Last Modified: <?php echo htmlspecialchars($preview_row['updated_at']); ?></em></p>
                <p><?php echo nl2br(htmlspecialchars($preview_row['content'])); ?></p>

                <!-- Tags -->
                <div>
                    <?php
                    $tag_sql = "SELECT tag FROM `" . TABLE_PREFIX . "tags` WHERE post_id = ?";
                    $tag_stmt = $conn->prepare($tag_sql);
                    $tag_stmt->bind_param('i', $preview_row['id']);
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
        </main>

        <?php
        include 'footer.php';
        $conn->close();
        exit;
    } else {
        echo "Post not found.";
    }
}

include 'header.php';
?>

<main>
    <h1>Admin Panel</h1>

    <?php
	// posts
    echo '<h2>Posts</h2>';
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<article>';
            echo '<h3>' . htmlspecialchars($row['title']) . '</h3>';
            echo '<p><em>Created: ' . htmlspecialchars($row['created_at']) . ' | Last Modified: ' . htmlspecialchars($row['updated_at']) . '</em></p>';
			echo '<form id="preview_form" method="get"></form>';
            echo '<form method="post">';
            echo '<input type="hidden" name="id" value="' . $row['id'] . '">';
            if ($row['hidden']) {
                echo '<button type="submit" name="action" value="unhide">Unhide</button>';
                echo '<button form="preview_form" type="submit" name="preview" value="' . $row['id'] . '">Preview</button>';				
            } else {
                echo '<button type="submit" name="action" value="hide">Hide</button>';
            }
            echo '<button type="submit" name="action" value="delete">Delete</button>';

            echo '</form>';
            echo '</article>';
        }
    } else {
        echo '<p>No posts found. Place files in the staging/ directory and press commit below to start blogging.</p>';
    }
    echo '<p>Commiting posts will read in all .txt files in the staging/ directory and either create a post or update an existing one. By convention, the title of the post will be the filename with hyphens replaced by spaces, and the last line of the file lists tags seperated by commas.</p>';
    echo '<form method="get"><button type="submit" name="commit" value="true">Commit Staged posts</button></form>';

    // Links Section
    echo '<h2>Navigation Links</h2>';
	echo '<p>Links appear in the navigation bar in the header at the top of the page, provided they are unhidden. Pages found in the static/ folder are automatically added as links when you visit this page.</p>';
    if ($links_result->num_rows > 0) {
        echo '<form method="post">';
        while ($link = $links_result->fetch_assoc()) {
            echo '<article>';
            echo '<input type="hidden" name="links[' . $link['id'] . '][id]" value="' . $link['id'] . '">';
            echo '<label><input type="checkbox" name="links[' . $link['id'] . '][hidden]"' . ($link['hidden'] ? ' checked' : '') . '> Hidden</label>';
            echo '<label for="url_' . $link['id'] . '">URL:</label>';
            echo '<input type="text" id="url_' . $link['id'] . '" name="links[' . $link['id'] . '][url]" value="' . htmlspecialchars($link['url']) . '">';
            echo '<label for="name_' . $link['id'] . '">Name:</label>';
            echo '<input type="text" id="name_' . $link['id'] . '" name="links[' . $link['id'] . '][name]" value="' . htmlspecialchars($link['name']) . '">';
            echo '<label for="ordering_' . $link['id'] . '">Ordering:</label>';
            echo '<select id="ordering_' . $link['id'] . '" name="links[' . $link['id'] . '][ordering]">';
            for ($i = 1; $i <= $links_result->num_rows; $i++) {
                echo '<option value="' . $i . '"' . ($link['ordering'] == $i ? ' selected' : '') . '>' . $i . '</option>';
            }
            echo '</select>';
            echo '<button type="submit" name="action" value="delete_link" formaction="?delete_link=' . $link['id'] . '">Delete</button>';
            echo '</article>';
        }
        echo '<button type="submit" name="action" value="apply_links">Apply</button>';
        echo '</form>';
    } else {
        echo '<p>No links found.</p>';
    }

    // style
    // Read the current CSS file name from styles/current
    $current_css_file = 'styles/current';
    if (file_exists($current_css_file)) {
        $css_filename = trim(file_get_contents($current_css_file));
        if (!empty($css_filename) && file_exists('styles/' . $css_filename)) {
            $default_style = $css_filename;
        } else {
            // Fallback to minimal.css if the specified CSS file does not exist
            $default_style = 'minimal.css';
        }
    } else {
        // Fallback in case the current file does not exist
        $default_style = 'minimal.css';
    }

    echo '<h2>Style</h2>';
    echo "<p>Choose a style below and apply to change the site's style. Changes will be visible after you refresh.</p>";
    echo '<form method="post">';
    echo '<label for="style_file">Stylesheet</label><select id="style_file" name="style_file">';
    $style_files = glob('styles/*.css');
    foreach ($style_files as $file) {
        $name = str_replace('.css', '', basename($file));
        if (basename($file) == $default_style) {
            echo "<option value='$file' selected>$name</option>";
        } else {
            echo "<option value='$file'>$name</option>";
        }
    }
    echo '</select><br><button type="submit" name="action" value="restyle">Apply</button></form>';

    ?>
</main>

<?php
include 'footer.php';
$conn->close();
?>




