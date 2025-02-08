<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo WEBSITE_NAME; ?> - <?php echo $page_title ?? ''; ?></title>

    <?php
    // Read the current CSS file name from style/current
    $current_css_file = 'style/current';
    if (file_exists($current_css_file)) {
        $css_filename = trim(file_get_contents($current_css_file));
        if (!empty($css_filename) && file_exists('style/' . $css_filename)) {
            echo '<link rel="stylesheet" href="style/' . htmlspecialchars($css_filename) . '">';
        } else {
            // Fallback to minimal.css if the specified CSS file does not exist
            echo '<link rel="stylesheet" href="style/minimal.css">';
        }
    } else {
        // Fallback in case the current file does not exist
        echo '<link rel="stylesheet" href="style/minimal.css">';
    }
    ?>
</head>
<body>
<header>
    <img src="banner.jpg" alt="Banner Image">
    <nav>
        <ul>
            <?php
            $static_files = glob('static/*.{html,php}', GLOB_BRACE);
            foreach ($static_files as $file) {
                $path = basename($file);
                echo '<li><a href="/s/' . str_replace('.php', '', $path) . '">' . str_replace(['.html', '.php'], '', $path) . '</a></li>';
            }
            ?>
        </ul>
    </nav>
</header>
