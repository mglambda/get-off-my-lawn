<?php

/**********************************************************************
* globals.php														  *
* 																	  *
* You should edit this file before visiting yourdomain.com/setup.php. *
* Afterwards, you can edit the general settings at the bottom of the  *
* admin page at yourdomain.com/admin.php							  *
**********************************************************************/


if (!defined('WEBSITE_NAME')) {
    /* General Settings */

    // The Website name, displayed in page titles and in the RSS feed.
    define('WEBSITE_NAME', 'My Blog');

    // Wether the website name is displayed at the top of the page at all.
    define('WEBSITE_NAME_SHOW', 'on');

    // Wether the website name is displayed over the banner image.
    define('WEBSITE_NAME_OVERLAY_BANNER', 'on');

    // If true, yourdomain.com/rss/ generates an RSS feed (atom format), which is advertised in the website header.
    define('RSS_PUBLISH_ENABLED', 'on');

    // How many posts should be displayed per page before starting to paginate. Set to 0 or lower to disable pagination entirely.
    define('PAGINATION_POSTS_PER_PAGE', 20);

    // Should setup.php prefill the database with some example content? Set to false if you don't want Example post, message of the day etc. to be included in the website when you run setup.php. This also won't happen if your database already contains entries for the relevant tables.
    define('SETUP_CREATE_EXAMPLES', 'on');

    /* End of General Settings */

    /* Authentication Settings */

    // The ADMIN_USER is the login used to access the yourdomain.com/admin.php site, where you can commit posts and change various settings.
    define('ADMIN_USER', 'root');

    // The ADMIN_PASSWORD is the corresponding password to the ADMIN_USER. Both user and password will be written to a passwd file when you visit the setup.php page.
    define('ADMIN_PASSWORD', '1q84');

    /* Database Settings */

    // The machine on which the mysql instance runs. This is usually localhost.
    define('DB_HOST', 'localhost');

    // replace this with your mysql user
    define('DB_USER', 'example_user');

    // replace this with your mysql user password
    define('DB_PASS', 'example_password');

    // replace this with your desired database. If this isn't already created by you or your webhost, visiting setup.php will attempt to create it. This requires the appropriate privileges for the user defined above.
    define('DB_NAME', 'goml_db');

    // The table prefix will be prepended to all tables created in the database. This can be useful if you are restricted by your webhost to only have one database.
    define('TABLE_PREFIX', 'goml_');
}
