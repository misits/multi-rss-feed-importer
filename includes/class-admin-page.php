<?php

namespace Multi_RSS_Feed_Importer;

class AdminPage {
    /**
     * Get data for the admin template
     */
    private static function get_template_data()
    {
        $selected_post_type = get_option('rss_importer_post_type', 'post');
        $selected_cron_interval = get_option('rss_importer_cron_interval', 'hourly');
        $rss_importer_limit = get_option('rss_importer_feed_limits', 0);
        $last_sync = get_option('rss_importer_last_sync', 'Never');
        $next_sync = wp_next_scheduled('rss_importer_cron_hook') ?: false;

        return [
            'post_types' => get_post_types(['public' => true], 'objects'),
            'selected_post_type' => $selected_post_type,
            'rss_importer_limit' => intval($rss_importer_limit),
            'rss_importer_cron_enabled' => get_option('rss_importer_cron_enabled', 0),
            'rss_importer_feeds' => get_option('rss_importer_feeds', []),
            'selected_cron_interval' => $selected_cron_interval,
            'cron_intervals' => [
                'hourly' => __('Hourly', 'multi-rss-feed-importer'),
                'twicedaily' => __('Twice Daily', 'multi-rss-feed-importer'),
                'daily' => __('Daily', 'multi-rss-feed-importer'),
                'weekly' => __('Weekly', 'multi-rss-feed-importer'),
            ],
            'last_sync' => $last_sync,
            'next_sync' => $next_sync,
        ];
    }

    /**
     * Render the admin page
     */
    public static function render()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Get plugin root directory
        $plugin_dir = dirname(dirname(__FILE__));

        // Include handlers
        require_once $plugin_dir . '/admin/handlers/feed-handler.php';

        // Get template data
        $data = self::get_template_data();

        // Include main template
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/templates/admin-template.php';
    }
}