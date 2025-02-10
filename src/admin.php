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

// Fetch all links
$links_sql = "SELECT * FROM `" . TABLE_PREFIX . "navigation_links` ORDER BY ordering";
$links_result = $conn->query($links_sql);


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
        echo '<button type="submit" name="delete_link" value="' . $link['id'] . '">Delete</button>';
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



// Sticky Elements Section
// Fetch all sticky elements
$sticky_elements_sql = "SELECT * FROM `" . TABLE_PREFIX . "sticky_elements`";
$sticky_elements_result = $conn->query($sticky_elements_sql);

echo '<h2>Sticky Elements</h2>';
echo '<p>Use sticky elements to add permanent features to your site, like a message of the day, or a custom navigation bar. These will be displayed on all pages, or just the index.</p>';
if ($sticky_elements_result->num_rows > 0) {
    echo '<form method="post">';
    while ($sticky_element = $sticky_elements_result->fetch_assoc()) {
        echo '<article>';
        echo '<input type="hidden" name="sticky_elements[' . $sticky_element['id'] . '][id]" value="' . $sticky_element['id'] . '">';
        echo '<label for="document_path_' . $sticky_element['id'] . '">Document Path:</label>';
        echo '<input type="text" id="document_path_' . $sticky_element['id'] . '" name="sticky_elements[' . $sticky_element['id'] . '][document_path]" value="' . htmlspecialchars($sticky_element['document_path']) . '">';
        echo '<label for="order_' . $sticky_element['id'] . '">Order:</label>';
        echo '<input type="number" id="order_' . $sticky_element['id'] . '" name="sticky_elements[' . $sticky_element['id'] . '][order]" value="' . htmlspecialchars($sticky_element['order']) . '">';
        echo '<label for="visibility_' . $sticky_element['id'] . '">Visibility:</label>';
        echo '<select id="visibility_' . $sticky_element['id'] . '" name="sticky_elements[' . $sticky_element['id'] . '][visibility]">';
        echo '<option value="all_pages"' . ($sticky_element['visibility'] == 'all_pages' ? ' selected' : '') . '>All Pages</option>';
        echo '<option value="index_only"' . ($sticky_element['visibility'] == 'index_only' ? ' selected' : '') . '>Index Only</option>';
        echo '</select>';
        echo '<label for="layout_position_' . $sticky_element['id'] . '">Layout Position:</label>';
        echo '<select id="layout_position_' . $sticky_element['id'] . '" name="sticky_elements[' . $sticky_element['id'] . '][layout_position]">';
        echo '<option value="top"' . ($sticky_element['layout_position'] == 'top' ? ' selected' : '') . '>Top</option>';
        echo '<option value="bottom"' . ($sticky_element['layout_position'] == 'bottom' ? ' selected' : '') . '>Bottom</option>';
        echo '<option value="float_left"' . ($sticky_element['layout_position'] == 'float_left' ? ' selected' : '') . '>Float Left</option>';
        echo '<option value="float_right"' . ($sticky_element['layout_position'] == 'float_right' ? ' selected' : '') . '>Float Right</option>';
        echo '</select>';
        echo '<button type="submit" name="delete_sticky" value="' . $sticky_element['id'] . '">Delete</button>';
        echo '</article>';
    }
    echo '<button type="submit" name="action" value="update_sticky">Update Sticky Elements</button>';
    echo '</form>';

    // Add new sticky element form
    echo '<h3>Add New Sticky Element</h3>';
    echo '<form method="post">';
    echo '<label for="new_document_path">Document Path:</label>';
    echo '<input type="text" id="new_document_path" name="new_document_path">';
    echo '<label for="new_order">Order:</label>';
    echo '<input type="number" id="new_order" name="new_order">';
    echo '<label for="new_visibility">Visibility:</label>';
    echo '<select id="new_visibility" name="new_visibility">';
    echo '<option value="all_pages">All Pages</option>';
    echo '<option value="index_only">Index Only</option>';
    echo '</select>';
    echo '<label for="new_layout_position">Layout Position:</label>';
    echo '<select id="new_layout_position" name="new_layout_position">';
    echo '<option value="top">Top</option>';
    echo '<option value="bottom">Bottom</option>';
    echo '<option value="float_left">Float Left</option>';
    echo '<option value="float_right">Float Right</option>';
    echo '</select>';
    echo '<button type="submit" name="action" value="add_sticky_element">Add Sticky Element</button>';
    echo '</form>';
} else {
    echo '<p>No sticky elements found.</p>';

    // Add new sticky element form
    echo '<h3>Add New Sticky Element</h3>';
    echo '<form method="post">';
    echo '<label for="new_document_path">Document Path:</label>';
    echo '<input type="text" id="new_document_path" name="new_document_path">';
    echo '<label for="new_order">Order:</label>';
    echo '<input type="number" id="new_order" name="new_order">';
    echo '<label for="new_visibility">Visibility:</label>';
    echo '<select id="new_visibility" name="new_visibility">';
    echo '<option value="all_pages">All Pages</option>';
    echo '<option value="index_only">Index Only</option>';
    echo '</select>';
    echo '<label for="new_layout_position">Layout Position:</label>';
    echo '<select id="new_layout_position" name="new_layout_position">';
    echo '<option value="top">Top</option>';
    echo '<option value="bottom">Bottom</option>';
    echo '<option value="float_left">Float Left</option>';
    echo '<option value="float_right">Float Right</option>';
    echo '</select>';
    echo '<button type="submit" name="action" value="add_sticky_element">Add Sticky Element</button>';
    echo '</form>';
}


// style section
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
