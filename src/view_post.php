<?php
include 'globals.php';
include_once 'db.php';
include_once 'gomllib.php';

if (isset($_GET['post'])) {
    $title = str_replace('-', ' ', $_GET['post']);
        $page_title = $title;
        include 'header.php';	
	echo '<main>';
    display_post_by_title($conn, $title);
	echo '</main>';
} else {
    include 'header.php';
    echo "No post specified.";
}

include 'footer.php';
$conn->close();
?>
