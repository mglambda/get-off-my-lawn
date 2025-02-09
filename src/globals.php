<?php

// only execute this once
if(!defined('WEBSITE_NAME')) {


// General Settings

// Website name
define('WEBSITE_NAME', 'My Blog');

// Wether the website name is displayed at the top of the page at all
define('WEBSITE_NAME_SHOW', true);

// Wether the website name is displayed over the banner image
define('WEBSITE_NAME_OVERLAY_BANNER', true);

// The ADMIN_USER is the login used to access the yourdomain.com/admin.php site, where you can commit posts and change various settings.
define('ADMIN_USER', 'root');

// The ADMIN_PASSWORD is the corresponding password to the ADMIN_USER. Both user and password will be written to a passwd file when you visit the setup.php page.
define('ADMIN_PASSWORD', '1q84');
// Database settings

// The machine on which the mysql instance runs. This is usually localhost.
define('DB_HOST', 'localhost');

// replace this with your mysql user
define('DB_USER', 'example_user');

// replace this with your mysql user password
define('DB_PASS', 'example_password');

// replace this with your desired database. If this isn't already created by you or your webhost, going to setup.php will attempt to create it. This requires the appropriate privileges for the user defined above.
define('DB_NAME', 'goml_db');

// The table prefix will be prepended to all tables created in the database. This can be useful if you are restricted by your webhost to only have one database.
define('TABLE_PREFIX', 'goml_');
}

?>