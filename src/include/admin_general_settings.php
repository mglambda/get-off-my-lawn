<?php
// to be included in admin.php

echo '<form method="post">';
echo '<input type="hidden" name="action" value="apply_general_settings">';

$globals_content = file_get_contents('globals.php');
preg_match('/\/\*\s*General Settings\s*\*\//', $globals_content, $start_matches);
preg_match('/\/\*\s*End of General Settings\s*\*\//', $globals_content, $end_matches);

if ($start_matches && $end_matches) {
    $general_settings_block = substr($globals_content, strpos($globals_content, $start_matches[0]), strpos($globals_content, $end_matches[0]) - strpos($globals_content, $start_matches[0]) + strlen($end_matches[0]));
    $lines = explode("\n", $general_settings_block);

    foreach ($lines as $line) {
        if (preg_match('/define\(\'([^\']+)\',\s*\'(.*?)\'\s*\)/', $line, $matches)) {
            $constant_name = $matches[1];
            $constant_value = htmlspecialchars($matches[2], ENT_QUOTES);
            echo '<label for="' . $constant_name . '">' . $constant_name . '</label>';
            echo '<input type="text" id="' . $constant_name . '" name="general_settings[' . $constant_name . ']" value="' . $constant_value . '"><br>';

        } elseif (preg_match('/define\(\'([^\']+)\',\s*(true|false)\s*\)/', $line, $matches)) {
            $constant_name = $matches[1];
            $constant_value = $matches[2];
            echo '<label for="' . $constant_name . '">' . $constant_name . '</label>';
            echo '<input type="checkbox" id="' . $constant_name . '" name="general_settings[' . $constant_name . ']"' . ($constant_value == 'true' ? ' checked' : '') . '><br>';

        } elseif (preg_match('/define\(\'([^\']+)\',\s*(\d+)\s*\)/', $line, $matches)) {
            $constant_name = $matches[1];
            $constant_value = $matches[2];
            echo '<label for="' . $constant_name . '">' . $constant_name . '</label>';
            echo '<input type="number" id="' . $constant_name . '" name="general_settings[' . $constant_name . ']" value="' . $constant_value . '"><br>';
        }
    }
}

echo '<button type="submit">Apply</button>';
echo '</form>';

?>