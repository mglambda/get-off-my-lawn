<?php
// if this is accessed through the http.host.com/s/example route
// the following includes are in scope
// include 'globals.php';
// include 'header.php';
// you can get access to the DB and $conn variable with
// include 'db.php';
// You can reach this file also through http://host.com/static/example.php, which won't have any of the includes
?>

<p>
This is an example page. You can find these in /static, where you can add .php or .html files. These will be automatically linked in the header navigation. If you want the navigation bar to appear, you must include header.php. Similarly for footer.php etc. By default, you get an empty page, so you have full control.
</p>
