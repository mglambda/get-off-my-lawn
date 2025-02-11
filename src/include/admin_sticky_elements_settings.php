<?php

// to be included in admin.php

// Fetch all sticky elements
$sticky_elements_sql = "SELECT * FROM `" . TABLE_PREFIX . "sticky_elements`";
$sticky_elements_result = $conn->query($sticky_elements_sql);

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
