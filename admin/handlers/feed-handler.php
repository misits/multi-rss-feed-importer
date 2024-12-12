<?php

namespace Multi_RSS_Feed_Importer;

if (!defined('ABSPATH')) exit;

use Multi_RSS_Feed_Importer\FeedLoader;
use \Exception;

function add_rss_importer_notice($message, $type = 'success', $id = '') {
    $id = !empty($id) ? $id : 'rss_importer_' . uniqid();
    $class = 'notice notice-' . $type . ' settings-error is-dismissible';
    add_settings_error(
        'rss_importer_messages',
        $id,
        $message,
        $class
    );
}

if (isset($_POST['rss_save_settings']) && wp_verify_nonce($_POST['_wpnonce_rss_save_settings'], 'rss_save_settings')) {
    $selected_post_type = sanitize_text_field($_POST['rss_post_type']);
    $selected_cron = isset($_POST['rss_cron_enabled']) ? 1 : 0;
    $selected_cron_interval = sanitize_text_field($_POST['rss_cron_interval']);
    
    $feeds_input = $_POST['rss_feeds'] ?? '';
    $feeds = array_filter(array_map('esc_url_raw', preg_split('/[\r\n]+/', $feeds_input, -1, PREG_SPLIT_NO_EMPTY)), function ($url) {
        return filter_var($url, FILTER_VALIDATE_URL); // Ensure only valid URLs are included
    });
    
    $limits_input = $_POST['rss_feed_limit'] ?? '';

    update_option('rss_importer_post_type', $selected_post_type);
    update_option('rss_importer_cron_enabled', $selected_cron);
    update_option('rss_importer_cron_interval', $selected_cron_interval);
    update_option('rss_importer_feeds', $feeds);
    update_option('rss_importer_feed_limits', intval($limits_input));

    add_rss_importer_notice(__('Settings saved.', 'multi-rss-feed-importer'));
}

if (isset($_POST['rss_process_feeds']) && wp_verify_nonce($_POST['_wpnonce_rss_process_feeds'], 'rss_process_feeds')) {
    $feeds = get_option('rss_importer_feeds', []);
    $post_type = get_option('rss_importer_post_type', 'post');

    if (empty($feeds) || empty($post_type)) {
        add_rss_importer_notice(__('Please configure feeds and post type settings first.', 'multi-rss-feed-importer'), 'error');
    } else {
        try {
            $processed = 0;
            $errors = [];

            foreach ($feeds as $feed_url) {
                try {
                    $loader = new FeedLoader($feed_url);
                    $loader->import_posts();
                    $processed++;
                } catch (Exception $e) {
                    $errors[] = sprintf(
                        __('Error processing feed %s: %s', 'multi-rss-feed-importer'),
                        $feed_url,
                        $e->getMessage()
                    );
                }
            }

            if ($processed > 0) {
                add_rss_importer_notice(
                    sprintf(
                        __('Successfully processed %d feeds.', 'multi-rss-feed-importer'),
                        $processed
                    )
                );

                update_option('rss_importer_last_sync', date('Y-m-d H:i:s'));
            }

            if (!empty($errors)) {
                foreach ($errors as $error) {
                    add_rss_importer_notice($error, 'error');
                }
            }

            if ($processed === 0 && empty($errors)) {
                add_rss_importer_notice(
                    __('No feeds were processed.', 'multi-rss-feed-importer'),
                    'warning'
                );
            }
        } catch (Exception $e) {
            add_rss_importer_notice(
                sprintf(
                    __('Error: %s', 'multi-rss-feed-importer'),
                    $e->getMessage()
                ),
                'error'
            );
        }
    }
}