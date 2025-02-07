<?php

// only execute this once
if(!defined('WEBSITE_NAME')) {
// Website name
define('WEBSITE_NAME', 'My Blog');

// Database settings
define('DB_HOST', 'localhost');

// replace this with your user
define('DB_USER', 'example_user');

// replace this with your password
define('DB_PASS', 'example_password');

// replace this with your desired database. If this isn't already created by you or your webhost, going to setup.php will attempt to create it. This requires the appropriate privileges for the user defined above.
define('DB_NAME', 'goml_db');

// Table prefix
define('TABLE_PREFIX', 'goml_');
}

?>