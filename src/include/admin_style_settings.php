<?php

// to be included in admin.php

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
