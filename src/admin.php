<?php
include 'globals.php';
include_once 'db.php';
include_once 'gomllib.php';

$page_title = 'Admin';

// always scan for new links in the static directory
include 'scan_static.php';

// commit if requested
if (isset($_GET['commit'])) {
    include 'cronjob.php';
    header("Location: admin.php");
    exit();
}

// Fetch all posts
$sql = "SELECT * FROM `" . TABLE_PREFIX . "posts` ORDER BY created_at DESC";
$result = $conn->query($sql);




if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // some delete actions are handled without 'action' being set, due to the way HTML forms work
    if (isset($_POST['delete_link'])) {
        $delete_id = $_POST['delete_link'];
        $delete_sql = "DELETE FROM `" . TABLE_PREFIX . "navigation_links` WHERE id = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param('i', $delete_id);
        $stmt->execute();

        header("Location: admin.php");
        exit();
    } elseif (isset($_POST['delete_sticky'])) {
        $delete_id = $_POST['delete_sticky'];
        $delete_sql = "DELETE FROM `" . TABLE_PREFIX . "sticky_elements` WHERE id = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param('i', $delete_id);
        $stmt->execute();

        header("Location: admin.php");
        exit();
    }


    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'hide':
                $id = $_POST['id'];
                $action_sql = "UPDATE `" . TABLE_PREFIX . "posts` SET hidden = 1 WHERE id = ?";
                break;
            case 'unhide':
                $id = $_POST['id'];
                $action_sql = "UPDATE `" . TABLE_PREFIX . "posts` SET hidden = 0 WHERE id = ?";
                break;
            case 'delete':
                $id = $_POST['id'];

                // Delete tags associated with the post
                $tag_sql = "DELETE FROM `" . TABLE_PREFIX . "tags` WHERE post_id = ?";
                $stmt = $conn->prepare($tag_sql);
                $stmt->bind_param('i', $id);
                $stmt->execute();

                $action_sql = "DELETE FROM `" . TABLE_PREFIX . "posts` WHERE id = ?";
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
                header("Location: admin.php");
                exit();

            case 'update_sticky':
                // Handle sticky element updates
                foreach ($_POST['sticky_elements'] as $id => $data) {
                    $document_path = $data['document_path'];
                    $order = $data['order'];
                    $visibility = $data['visibility'];
                    $layout_position = $data['layout_position'];

                    $update_sql = "UPDATE `" . TABLE_PREFIX . "sticky_elements` SET document_path = ?, `order` = ?, visibility = ?, layout_position = ? WHERE id = ?";
                    $stmt = $conn->prepare($update_sql);
                    $stmt->bind_param('sissi', $document_path, $order, $visibility, $layout_position, $id);
                    $stmt->execute();
                }

                header("Location: admin.php");
                exit();
            case 'add_sticky_element':
                // Handle adding a new sticky element
                $document_path = $_POST['new_document_path'];
                $order = $_POST['new_order'];
                $visibility = $_POST['new_visibility'];
                $layout_position = $_POST['new_layout_position'];

                $insert_sql = "INSERT INTO `" . TABLE_PREFIX . "sticky_elements` (document_path, `order`, visibility, layout_position) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($insert_sql);
                $stmt->bind_param('siss', $document_path, $order, $visibility, $layout_position);
                $stmt->execute();

                header("Location: admin.php");
                exit();
            case 'add_link':
                // Handle adding a new link
                $url = $_POST['new_url'];
                $name = $_POST['new_name'];
                $ordering = $_POST['new_ordering'];

                $insert_sql = "INSERT INTO `" . TABLE_PREFIX . "navigation_links` (url, name, ordering) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($insert_sql);
                $stmt->bind_param('ssi', $url, $name, $ordering);
                $stmt->execute();

                header("Location: admin.php");
                exit();
            case 'apply_general_settings':
                update_globals_file($_POST['general_settings']);
                header("Location: admin.php");
                exit();
            case 'save_custom_css':
                // Handle saving custom CSS
                $custom_css = $_POST['custom_css'];
                file_put_contents('user_style.css', $custom_css);
                header("Location: admin.php");
                exit();
        }

        if (isset($action_sql)) {
            $stmt = $conn->prepare($action_sql);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            header("Location: admin.php");
            exit();
        }
    }
}

if (isset($_GET['preview'])) {
    include 'header.php';
    echo '<main>';
    display_post($conn, $_GET['preview']);
    echo '</main>';
    include 'footer.php';
    $conn->close();
    exit;
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
echo '<h3><a href="' . get_post_url_relative($row['title']) . '">' . htmlspecialchars($row['title']) . '</a></h3>';		
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
include 'include/admin_links_settings.php';

// Sticky Elements Section
echo '<h2>Sticky Elements</h2>';
echo '<p>Use sticky elements to add permanent features to your site, like a message of the day, or a custom navigation bar. These will be displayed on all pages, or just the index.</p>';
include 'include/admin_sticky_elements_settings.php';

// style section
echo '<h2>Style</h2>';
echo "<p>Choose a style below and apply to change the site's look and feel. Changes will be visible after you refresh. All styles are classless and will work with markdown or basic HTML. Thanks to <a href='https://github.com/dohliam/dropin-minimal-css'>dropin-minimal-css</a></p>";
include 'include/admin_style_settings.php';

// General Settings Section
echo '<h2>General Settings</h2>';
echo '<p>Changing these will write to globals.php, which you can also edit by hand.</p>';
echo '<br>';
include 'include/admin_general_settings.php';
?>
</main>

<?php
include 'footer.php';
$conn->close();
?>
