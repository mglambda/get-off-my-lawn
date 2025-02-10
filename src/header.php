<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo WEBSITE_NAME; ?> - <?php echo $page_title ?? ''; ?></title>

    <?php
    // Read the current CSS file name from styles/current
    $current_css_file = 'styles/current';
    if (file_exists($current_css_file)) {
        $css_filename = trim(file_get_contents($current_css_file));
        if (!empty($css_filename) && file_exists('styles/' . $css_filename)) {
            echo '<link rel="stylesheet" href="styles/' . htmlspecialchars($css_filename) . '">';
        } else {
            // Fallback to minimal.css if the specified CSS file does not exist
            echo '<link rel="stylesheet" href="styles/minimal.css">';
        }
    } else {
        // Fallback in case the current file does not exist
        echo '<link rel="stylesheet" href="styles/minimal.css">';
    }
    ?>
</head>
<body>

<header>
  <div>
<?php

// Check if there are any banner images in the folder
$banner_images = glob('banner_images/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
    $has_banner_images = !empty($banner_images);

    if ($has_banner_images) {
        // Select a random banner image
        $random_banner = $banner_images[array_rand($banner_images)];
        $alt_text = 'Banner Image';

        // Check if there is a corresponding .txt file for the alt-text
        $image_info = pathinfo($random_banner);
        $txt_file = 'banner_images/' . $image_info['filename'] . '.txt';
        if (file_exists($txt_file)) {
            $alt_text = trim(file_get_contents($txt_file));
        }

        echo '<img src="' . htmlspecialchars($random_banner) . '" alt="' . htmlspecialchars($alt_text) . '" style="width: 100%; height: auto;">';

        if (WEBSITE_NAME_OVERLAY_BANNER) {
            // Display the website name over the banner image
            echo '<div style="position: absolute; top: 50%; left: 20%; transform: translate(-50%); color: white; z-index: 1"><h1>' . htmlspecialchars(WEBSITE_NAME) . '</h1></div>';
        }
    } else {
        // No banner images, display the website name above the navigation if WEBSITE_NAME_SHOW is true
        if (WEBSITE_NAME_SHOW) {
            echo '<h1>' . htmlspecialchars(WEBSITE_NAME) . '</h1>';
        }
    }
    ?>

</div>
<nav>
    <ul>
        <?php
            include_once 'db.php';
    $sql = "SELECT * FROM `" . TABLE_PREFIX . "navigation_links` WHERE hidden = 0 ORDER BY ordering";
    $nav_links_result = $conn->query($sql);

    if ($nav_links_result->num_rows > 0) {
        while ($row = $nav_links_result->fetch_assoc()) {
            echo '<li style="display: inline;"><a href="' . htmlspecialchars($row['url']) . '">' . htmlspecialchars($row['name']) . '</a></li>';
        }
    }

    ?>
    </ul>
</nav>
</header>
