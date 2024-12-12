<?php

namespace Multi_RSS_Feed_Importer;

use Multi_RSS_Feed_Importer\AdminPage;
use \Exception;
use \WP_Query;

class Loader
{
    /**
     * Initialize the plugin
     */
    public static function init()
    {
        // Register AJAX actions
        self::register_ajax_actions();

        // Load required files
        self::load_files();

        // Register hooks
        self::register_hooks();

        // Run cron if enabled
        if (get_option('rss_importer_cron_enabled', 0)) {
            self::register_cron();
        }

        return true;
    }

    /**
     * Load required plugin files
     */
    private static function load_files()
    {
        $plugin_dir = dirname(plugin_dir_path(__FILE__));

        // Load main classes
        require_once $plugin_dir . '/includes/class-feed-loader.php';
        require_once $plugin_dir . '/includes/class-logger.php';
        require_once $plugin_dir . '/includes/class-admin-page.php';

        // Load admin handlers if in admin
        if (is_admin()) {
            require_once ABSPATH . 'wp-admin/includes/template.php';
        }
    }

    /**
     * Register WordPress hooks
     */
    private static function register_hooks()
    {
        add_action('admin_menu', [__CLASS__, 'register_admin_menu']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_assets']);
    }

    /**
     * Register the admin menu
     */
    public static function register_admin_menu()
    {
        add_menu_page(
            'RSS Feed Importer',
            'RSS Importer',
            'manage_options',
            'multi-rss-feed-importer',
            [__CLASS__, 'render_admin_page'],
            'dashicons-rss'
        );
    }

    /**
     * Render the admin page
     */
    public static function render_admin_page()
    {
        AdminPage::render();
    }

    /**
     * Enqueue admin assets
     */
    public static function enqueue_admin_assets($hook)
    {
        if ('toplevel_page_multi-rss-feed-importer' !== $hook) {
            return;
        }

        $plugin_dir_url = plugin_dir_url(dirname(__FILE__));

        wp_enqueue_style(
            'rss-importer-admin-styles',
            $plugin_dir_url . 'assets/css/admin.css',
            [],
            filemtime(dirname(__FILE__, 2) . '/assets/css/admin.css')
        );

        wp_enqueue_script(
            'rss-importer-admin-script',
            $plugin_dir_url . 'assets/js/admin.js',
            ['jquery'],
            filemtime(dirname(__FILE__, 2) . '/assets/js/admin.js'),
            true
        );

        wp_localize_script('rss-importer-admin-script', 'rssImporterData', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('rss_importer_nonce')
        ]);
    }

    /**
     * Register actions for AJAX requests
     */
    public static function register_ajax_actions()
    {
        add_action('wp_ajax_fetch_rss_posts', function() {
            check_ajax_referer('rss_importer_nonce', 'nonce');

            $feeds = get_option('rss_importer_feeds', []);
            if (empty($feeds)) {
                wp_send_json_error(['message' => __('No RSS feeds configured.', 'multi-rss-feed-importer')]);
                return;
            }

            $current_feed = isset($_POST['current_feed']) ? intval($_POST['current_feed']) : 0;
            if ($current_feed >= count($feeds)) {
                wp_send_json_success(['done' => true, 'message' => __('All feeds processed.', 'multi-rss-feed-importer')]);
                return;
            }

            $feed_url = $feeds[$current_feed];
            $error = null;

            try {
                $feed_loader = new FeedLoader($feed_url);
                $result = $feed_loader->import_posts();
                if (!$result) {
                    $error = sprintf(__('Failed to process feed: %s', 'multi-rss-feed-importer'), $feed_url);
                }
            } catch (Exception $e) {
                $error = sprintf(__('Error processing feed %s: %s', 'multi-rss-feed-importer'), $feed_url, $e->getMessage());
            }

            wp_send_json_success([
                'done' => false,
                'current_feed' => $current_feed + 1,
                'total_feeds' => count($feeds),
                'progress' => round(($current_feed + 1) / count($feeds) * 100),
                'feedUrl' => $feed_url,
                'error' => $error
            ]);
        });
    }

    /**
     * Register cron job
     */
    public static function register_cron()
    {
        if (!wp_next_scheduled('rss_importer_cron_hook')) {
            wp_schedule_event(time(), get_option('rss_importer_cron_interval', 'hourly'), 'rss_importer_cron_hook');
        }

        add_action('rss_importer_cron_hook', function() {
            $feeds = get_option('rss_importer_feeds', []);
            if (empty($feeds)) {
                return;
            }

            foreach ($feeds as $feed_url) {
                try {
                    $feed_loader = new FeedLoader($feed_url);
                    $feed_loader->import_posts();
                } catch (Exception $e) {
                    error_log(sprintf(__('Error processing feed %s: %s', 'multi-rss-feed-importer'), $feed_url, $e->getMessage()));
                }
            }

            update_option('rss_importer_last_sync', date('Y-m-d H:i:s'));
        });
    }
}