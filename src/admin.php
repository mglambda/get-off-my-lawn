<?php
include 'globals.php';
include_once 'db.php';

$page_title = 'Admin';

// commit if requested
if(isset($_GET['commit'])) {
    include 'cronjob.php';
}

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
            case 'restyle':
                // Handle stylesheet change
                if (isset($_POST['style_file'])) {
                    $selected_style = basename($_POST['style_file']);
                    $current_css_file = 'style/current';
                    $valid_styles = glob('style/*.css');

                    if (in_array('style/' . $selected_style, $valid_styles)) {
                        file_put_contents($current_css_file, $selected_style);
                    } else {
                        // Fallback to minimal.css if the selected style is not valid
                        file_put_contents($current_css_file, 'minimal.css');
                    }
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

include 'header.php';
?>

<main>
    <h1>Admin Panel</h1>
    <?php
    echo '<h2>Posts</h2>';
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<article>';
            echo '<h3>' . $row['title'] . '</h3>';
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
        echo '<p>No posts found. Place files in the staging/ directory and press commit below to start blogging.</p>';
    }
    echo '<p>Commiting posts will read in all .txt files in the staging/ directory and either create a post or update an existing one. By convention, the title of the post will be the filename with hyphens replaced by spaces, and the last line of the file lists tags seperated by commas.</p>';
    echo '<form method="get"><button type="submit" name="commit" value="true">Commit Staged posts</button></form>';

    // Read the current CSS file name from style/current
    $current_css_file = 'style/current';
    if (file_exists($current_css_file)) {
        $css_filename = trim(file_get_contents($current_css_file));
        if (!empty($css_filename) && file_exists('style/' . $css_filename)) {
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
    $style_files = glob('style/*.css');
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