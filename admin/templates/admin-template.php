<?php

if (!defined('ABSPATH')) exit;

// Ensure we have the data
if (!isset($data) || !is_array($data)) {
    return;
}

// Extract data to make variables available to templates
extract($data);
?>

<div class="wrap" id="rss-importer">
    <?php
    // Show admin notices
    settings_errors('rss_importer_messages');
    ?>

    <div id="rss-sections">
        <?php
        // Include each section with proper scope
        require dirname(__FILE__) . '/sections/rss-settings.php';
        require dirname(__FILE__) . '/sections/rss-fetch.php';
        ?>
    </div>
</div>