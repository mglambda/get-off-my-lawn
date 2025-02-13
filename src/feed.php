<?php
include 'globals.php';

if (!RSS_PUBLISH_ENABLED) {
    echo '<p>RSS feed disabled.</p>';
    exit();
}

include_once 'db.php';

// Set the content type to XML
header('Content-Type: application/atom+xml; charset=UTF-8');

// Get the current server URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$server_url = $protocol . $_SERVER['HTTP_HOST'];

$sql = "SELECT p.* FROM `" . TABLE_PREFIX . "posts` p
        JOIN `" . TABLE_PREFIX . "tags` t ON p.id = t.post_id
        WHERE t.tag = 'rss' AND p.hidden = 0
        ORDER BY p.created_at DESC";
$result = $conn->query($sql);


function display_post_as_rss_item($row)
{
    ?>
        <entry>
            <title><?php echo htmlspecialchars($row['title']); ?></title>
<link href="<?php echo $server_url . get_post_url_relative($row['title']); ?>"/>			
<id><?php echo $server_url . get_post_url_relative($row['title']); ?></id>			
            <updated><?php echo date(DATE_ATOM, strtotime($row['created_at'])); ?></updated>
            <published><?php echo date(DATE_ATOM, strtotime($row['created_at'])); ?></published>
            <content type="html"><?php echo $row['content']; ?></content>
        </entry>
<?php
}
if ($result->num_rows > 0) {
    // we do this little dance with the first element to get the date the feed was updated
    $first_row = $result->fetch_assoc();
    $update_time = date(DATE_ATOM, strtotime($first_row['created_at']));

    echo '<?xml version="1.0" encoding="UTF-8"?>';
    ?>
<feed xmlns="http://www.w3.org/2005/Atom">
    <title><?php echo htmlspecialchars(WEBSITE_NAME); ?> Atom Feed</title>
    <link href="<?php echo $server_url; ?>" rel="alternate"/>
    <updated><?php echo $update_time; ?></updated>
    <id><?php echo $server_url; ?>/atom</id>
    <author>
        <name><?php echo htmlspecialchars(WEBSITE_NAME); ?></name>
    </author>

    <?php
// we print the first row
display_post_as_rss_item($first_row);
    // now we can use fetch_assoc in a loop for the rest of the rows, if there are more
    while ($row = $result->fetch_assoc()) {
        display_post_as_rss_item($row);
    }
    ?>
</feed>
<?php
} else {
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    ?>
<feed xmlns="http://www.w3.org/2005/Atom">
    <title><?php echo htmlspecialchars(WEBSITE_NAME); ?> Atom Feed</title>
    <link href="<?php echo $server_url; ?>" rel="alternate"/>
    <updated><?php echo date(DATE_ATOM, time()); ?></updated>
    <id><?php echo $server_url; ?>/atom</id>
    <author>
        <name><?php echo htmlspecialchars(WEBSITE_NAME); ?></name>
    </author>
</feed>
<?php
}

$conn->close();
?>
