<?php
include 'globals.php';

// Database connection parameters
$host = DB_HOST;
$user = DB_USER;
$pass = DB_PASS;
$db_name = DB_NAME;

// Establish a connection to the MySQL server
$conn = new mysqli($host, $user, $pass);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the database exists
$sql = "CREATE DATABASE IF NOT EXISTS `$db_name`";
if (!$conn->query($sql)) {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($db_name);

// Create tables
$sql = "
CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "posts` (
    `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `hidden` TINYINT(1) DEFAULT 1
);

CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "tags` (
    `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `post_id` INT(11),
    `tag` VARCHAR(255)
);
";

if ($conn->multi_query($sql)) {
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->next_result());
}

$conn->close();

// Read the existing .htaccess file
$htaccess_path = '.htaccess';
if (file_exists($htaccess_path)) {
    $htaccess_content = file_get_contents($htaccess_path);
} else {
    $htaccess_content = "";
}

// Define the new content to be added or updated
$htpasswd_path = __DIR__ . '/.htpasswd';

// make sure this file exists so realpath doesn't crap out
if(!file_exists($htpasswd_path)) {
								 file_put_contents($htpasswd_path, '');
								 }
								 
$absolute_htpasswd_path = realpath($htpasswd_path);

$new_htaccess_content = "
### begin of code generated by setup.php

# Restrict access to admin.php
<FilesMatch \"admin\.php$\">
    AuthType Basic
    AuthName \"Restricted Area\"
    AuthUserFile \"{$absolute_htpasswd_path}\"
    Require valid-user
</FilesMatch>

### end of code generated by setup.php
";

// Check if the new content already exists in the .htaccess file
$start_marker = "### begin of code generated by setup.php";
$end_marker = "### end of code generated by setup.php";

if (strpos($htaccess_content, $start_marker) !== false && strpos($htaccess_content, $end_marker) !== false) {
    // Remove the existing content between the markers
    $htaccess_content = preg_replace("/{$start_marker}(.*?){$end_marker}/s", "", $htaccess_content);
}

// Append the new content to the .htaccess file
$final_htaccess_content = $htaccess_content . "\n" . $new_htaccess_content;

// Write the updated .htaccess content back to the file
file_put_contents($htaccess_path, $final_htaccess_content);

// Create or update the .htpasswd file
$htpasswd_content = ADMIN_USER . ":" . crypt(ADMIN_PASSWORD, base64_encode(ADMIN_PASSWORD));
file_put_contents($htpasswd_path, $htpasswd_content);

if(!file_exists('.htaccess') {
echo "<p>Oops! There was a problem during setup: .htaccess file not found in document root. Are you sure you copied all the files, including hidden ones? Copying .htaccess from the git repository and then rerunning setup.php should fix this issue.</p>";
} else {
echo "<p>Alright, looks like everything is set up. You may now <a href='/'>proceed</a>.</p>";
}
?>
