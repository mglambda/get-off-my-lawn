    <?php
    include_once 'db.php';

// Retrieve and include sticky elements for the bottom position
    $sticky_elements_sql = "SELECT * FROM `" . TABLE_PREFIX . "sticky_elements` WHERE visibility = 'all_pages' AND layout_position = 'bottom' ORDER BY `order`";
    $sticky_elements_result = $conn->query($sticky_elements_sql);

    if ($sticky_elements_result->num_rows > 0) {
        while ($sticky_element = $sticky_elements_result->fetch_assoc()) {
            $document_path = $sticky_element['document_path'];
            if (file_exists($document_path)) {
                include $document_path;
            }
        }
    }

    ?>
<footer>
<hr>
<p><small>&copy; <?php echo date('Y') . " " . WEBSITE_NAME; ?> All rights reserved. Powered by <a href='https://github.com/mglambda/get-off-my-lawn'>get-off-my-lawn</a>.<a href="/admin.php">Admin</a>.</small></p>
</footer>
</body>
</html>

