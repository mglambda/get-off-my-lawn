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
$page_title = 'Posts';
    include 'header.php';
	if(isset($_GET['verbosity'])) {
	$verbosity = $_GET['verbosity'];
	}else {
	$verbosity=0;
	}

echo '<p>Showing all Posts.</p>';
echo '<a href="?verbosity=' . increase_verbosity($verbosity) . '">Expand Posts</a>';
echo '<br>';
echo '<a href="?verbosity=' . decrease_verbosity($verbosity) . '">Collapse Posts</a>';
echo '<br>';

display_posts($conn, $verbosity);
}

include 'footer.php';
$conn->close();
