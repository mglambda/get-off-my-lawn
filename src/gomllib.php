<?php


function post_uname_from_title($post_title)
{
    // replace special chars wit hhyphens
    $hyphen_uname = preg_replace("![^a-z0-9]+!i", "-", $post_title);
    // collapse hyphens
    $upper_uname = str_replace('--', '-', str_replace('---', '-', $hyphen_uname));
    return strtolower($upper_uname);
}

function get_post_url_relative($post_row)
{
    return "/p/" . $post_row['uname'];
}

function display_post_row($conn, $row)
{
    ?>
            <article>
                <h2><?php echo htmlspecialchars($row['title']); ?></h2>
                <p><em>Created: <?php echo htmlspecialchars($row['created_at']); ?> | Last Modified: <?php echo htmlspecialchars($row['updated_at']); ?></em></p>
				<br>
                <?php echo $row['content']; ?>

                <!-- Tags -->
                <div>
                    <?php
                        $tag_sql = "SELECT tag FROM `" . TABLE_PREFIX . "tags` WHERE post_id = ?";
    $tag_stmt = $conn->prepare($tag_sql);
    $tag_stmt->bind_param('i', $row['id']);
    $tag_stmt->execute();
    $tag_result = $tag_stmt->get_result();

    if ($tag_result->num_rows > 0) {
        echo '<br><p>Tags: ';
        while ($tag_row = $tag_result->fetch_assoc()) {
            echo '<a href="/t/' . urlencode(str_replace(' ', '_', htmlspecialchars($tag_row['tag']))) . '">' . htmlspecialchars($tag_row['tag']) . '</a> ';
        }
        echo '</p>';
    }
    ?>
                </div>
            </article>
        <?php
}

function abbreviate_content($content, $max_chars)
{
    // Find the position to truncate the content
    $truncate_pos = 0;
    $current_length = 0;

    // Use a stack to keep track of open tags
    $tag_stack = [];

    for ($i = 0; $i < strlen($content); $i++) {
        if ($content[$i] == '<') {
            // Start of an HTML tag
            $tag_start = $i;
            $tag_end = strpos($content, '>', $i);
            if ($tag_end === false) {
                break; // Malformed HTML
            }
            $tag = substr($content, $tag_start, $tag_end - $tag_start + 1);

            if (substr($tag, 1, 1) == '/') {
                // Closing tag
                array_pop($tag_stack);
            } else {
                // Opening tag
                $tag_name = substr($tag, 1, strpos($tag, ' ') - 1);
                $tag_stack[] = $tag_name;
            }

            $i = $tag_end; // Move past the tag
        } elseif ($content[$i] == ' ' && !empty($tag_stack)) {
            // Skip spaces inside tags
            continue;
        } else {
            // Regular character
            if (++$current_length > $max_chars) {
                $truncate_pos = $i;
                break;
            }
        }
    }

    // If we didn't find a truncation point, return the original content
    if ($truncate_pos == 0) {
        return $content;
    }

    // Find the last space before the truncation point to respect word boundaries
    $last_space = strrpos(substr($content, 0, $truncate_pos), ' ');
    if ($last_space === false) {
        $last_space = 0; // No spaces found, truncate at the beginning
    } else {
        $last_space += 1; // Include the space in the truncated content
    }

    // Close any open tags
    while (!empty($tag_stack)) {
        $content .= '</' . array_pop($tag_stack) . '>';
    }

    return substr($content, 0, $last_space) . '...';
}

function display_post_row_short($conn, $row)
{
    echo '<article>';
    echo '<h2><a href="/p/' . get_post_url_relative($row) . '">' . $row['title'] . '</a></h2>';
    if (strlen($row['content']) > 300) {
        echo '<p>' . abbreviate_content($row['content'], 300) . '</p>';
        echo '<p><a href="' . get_post_url_relative($row) . '">Read more</a></p>';
    } else {
        echo '<p>' . $row['content'] . '</p>';
    }
    echo '</article>';
}

function display_post($conn, $post_id)
{
    $sql = "SELECT * FROM `" . TABLE_PREFIX . "posts` WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $post_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        display_post_row($conn, $row);
    } else {
        echo "<p>Post not found.</p>";
    }

    $stmt->close();
}

function display_post_by_uname($conn, $uname)
{
    $sql = "SELECT * FROM `" . TABLE_PREFIX . "posts` WHERE uname = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $uname);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        display_post_row($conn, $row);
    } else {
        echo "<p>Post not found.</p>";
    }

    $stmt->close();
}


function _change_verbosity($verbosity, $direction = 1)
{
    // direction 1 for up, -1 for down
    $values = [0, 10, 100];
    // find index for given verbosity
    for ($i = 0; $i < count($values); $i++) {
        $last_index = $i;
        if ($values[$i] >= $verbosity) {
            break;
        }
    }

    return $values[max(0, ($last_index + $direction) % count($values))];
}

function increase_verbosity($verbosity)
{
    return _change_verbosity($verbosity, 1);
}

function decrease_verbosity($verbosity)
{
    return _change_verbosity($verbosity, -1);
}

function display_posts($conn, $verbosity = 10)
{
    // gives a view of all unhidden posts in various forms
    // verbosity = 0 means only post titles
    // verbosity = 10 means title and abbreviated content, the default
    // verbosity >= 100 means full post

    $posts_per_page = PAGINATION_POSTS_PER_PAGE;
    $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

    if ($posts_per_page <= 0) {
        $limit_clause = '';
    } else {
        $offset = ($current_page - 1) * $posts_per_page;
        $limit_clause = "LIMIT $offset, $posts_per_page";
    }

    $sql = "SELECT * FROM `" . TABLE_PREFIX . "posts` WHERE hidden = 0 ORDER BY created_at DESC $limit_clause";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        if ($verbosity == 0) {
            echo '<ul>';
        }

        while ($row = $result->fetch_assoc()) {
            switch ($verbosity) {
                case 0:
                    echo '<li><a href="' . get_post_url_relative($row) . '">' . $row['title'] . '</a></li>';
                    break;
                case 10:
                    display_post_row_short($conn, $row);
                    break;
                case 100:
                    // this is a full view
                    display_post_by_uname($conn, $row['uname']);
                    echo '<br>';
                    break;
            } // end switch
        }
        if ($verbosity == 0) {
            echo '</ul>';
        }

    } else {
        echo '<p>No posts found.</p>';
    }

    echo '<br>';

    // Pagination links
    if ($posts_per_page > 0) {
        $total_posts_sql = "SELECT COUNT(*) as total FROM `" . TABLE_PREFIX . "posts` WHERE hidden = 0";
        $total_result = $conn->query($total_posts_sql);
        $total_row = $total_result->fetch_assoc();
        $total_posts = $total_row['total'];
        $total_pages = ceil($total_posts / $posts_per_page);

        if ($total_pages > 1) {
            echo '<div style="    margin: 20px 0; text-align: center;">';
            if ($current_page > 1) {
                echo '<a href="?page=' . ($current_page - 1) . '">&laquo; Previous</a>';
            }

            for ($i = 1; $i <= $total_pages; $i++) {
                if ($i == $current_page) {
                    echo '<b>' . $i . '</b>';
                } else {
                    echo '<a href="?page=' . $i . '">' . $i . '</a>';
                }
            }

            if ($current_page < $total_pages) {
                echo '<a href="?page=' . ($current_page + 1) . '">Next &raquo;</a>';
            }
            echo '</div>';
        }
    }
}


function update_globals_file($new_values)
{
    $file_path = 'globals.php';
    $content = file_get_contents($file_path);

    // Find the general settings section
    $start_marker = '/* General Settings */';
    $end_marker = '/* End of General Settings */';

    $start_pos = strpos($content, $start_marker);
    $end_pos = strpos($content, $end_marker);

    if ($start_pos === false || $end_pos === false) {
        return false; // Markers not found
    }

    $general_settings_block = substr($content, $start_pos + strlen($start_marker), $end_pos - $start_pos - strlen($start_marker));

    // Parse the existing constants
    $lines = explode("\n", $general_settings_block);
    $new_lines = [];

    foreach ($lines as $line) {
        if (preg_match('/define\(\'([^\']+)\',\s*(.*?)\s*\)/', $line, $matches)) {
            $constant_name = $matches[1];
            $constant_value = isset($new_values[$constant_name]) ? $new_values[$constant_name] : $matches[2];

            if (is_numeric($constant_value) || in_array(strtolower($constant_value), ['true', 'false'])) {
                $new_lines[] = "define('{$constant_name}', {$constant_value});";
            } else {
                $new_lines[] = "define('{$constant_name}', {$constant_value});";
            }
        } elseif (preg_match('/define\(\'([^\']+)\',\s*(true|false)\s*\)/', $line, $matches)) {
            $constant_name = $matches[1];
            $constant_value = isset($new_values[$constant_name]) ? ($new_values[$constant_name] == 'on' ? 'true' : 'false') : $matches[2];

            $new_lines[] = "define('{$constant_name}', {$constant_value});";
        } else {
            $new_lines[] = $line;
        }
    }

    // Replace the general settings block with the new values
    $new_content = substr_replace($content, implode("\n", $new_lines), $start_pos + strlen($start_marker), $end_pos - $start_pos - strlen($start_marker));

    // Write the updated content back to globals.php
    file_put_contents($file_path, $new_content);
}

?>