<?php
include 'globals.php';
include_once 'db.php';
include_once 'gomllib.php';

$page_title = 'Home';

include 'header.php';
?>

<main>
    <?php
display_posts($conn);
?>
</main>

<?php
include 'footer.php';
$conn->close();
?>
