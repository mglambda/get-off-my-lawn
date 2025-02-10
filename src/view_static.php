<?php

include 'globals.php';

if (isset($_GET['page'])) {
    $filename = $_GET['page'] . '.php';
    var_dump($filename);
    if (file_exists('static/' . $filename)) {
        $page_title = str_replace('-', ' ', basename($filename, '.html'));
        include 'header.php';
        echo "<main>";
        readfile('static/' . $filename);
        echo "</main";
    } else {
        include 'header.php';
        echo "<main><p>Page not found: " . $filename . "</p></main>";
    }
} else {
    include 'header.php';
    echo "<main><p>No page specified.</p></main>";
}

include 'footer.php';
