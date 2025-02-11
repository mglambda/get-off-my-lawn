<?php

// to be included in admin.php

// Fetch all links
$links_sql = "SELECT * FROM `" . TABLE_PREFIX . "navigation_links` ORDER BY ordering";
$links_result = $conn->query($links_sql);

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

    // Add new link form
    echo '<h3>Add New Link</h3>';
    echo '<form method="post">';
    echo '<label for="new_url">URL:</label>';
    echo '<input type="text" id="new_url" name="new_url">';
    echo '<label for="new_name">Name:</label>';
    echo '<input type="text" id="new_name" name="new_name">';
    echo '<label for="new_ordering">Ordering:</label>';
    echo '<select id="new_ordering" name="new_ordering">';
    for ($i = 1; $i <= $links_result->num_rows + 1; $i++) {
        echo '<option value="' . $i . '">' . $i . '</option>';
    }
    echo '</select>';
    echo '<button type="submit" name="action" value="add_link">Add Link</button>';
    echo '</form>';
} else {
    echo '<p>No links found.</p>';

    // Add new link form
    echo '<h3>Add New Link</h3>';
    echo '<form method="post">';
    echo '<label for="new_url">URL:</label>';
    echo '<input type="text" id="new_url" name="new_url">';
    echo '<label for="new_name">Name:</label>';
    echo '<input type="text" id="new_name" name="new_name">';
    echo '<label for="new_ordering">Ordering:</label>';
    echo '<select id="new_ordering" name="new_ordering">';
    echo '<option value="1">1</option>';
    echo '</select>';
    echo '<button type="submit" name="action" value="add_link">Add Link</button>';
    echo '</form>';
}
