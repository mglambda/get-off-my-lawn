<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo WEBSITE_NAME; ?> - <?php echo $page_title ?? ''; ?></title>
    <link rel="stylesheet" href="style.css">
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
                echo '<li><a href="/s/' . str_replace('.php', '', $filename) . '">' . str_replace(['.html', '.php'], '', $path) . '</a></li>';
            }
            ?>
        </ul>
    </nav>
</header>