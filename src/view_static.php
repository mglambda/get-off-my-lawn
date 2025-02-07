<?php
include 'globals.php';

if (isset($_GET['page'])) {
    $filename = $_GET['page'] . '.html';
    if (file_exists('static/' . $filename)) {
        $page_title = str_replace('-', ' ', basename($filename, '.html'));
        include 'header.php';
        ?>
        <main>
            <?php
            readfile('static/' . $filename);
            ?>
        </main>

        <?php
        include 'footer.php';
    } else {
        echo "Page not found.";
    }
} else {
    echo "No page specified.";
}
?>