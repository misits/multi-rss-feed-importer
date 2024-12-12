<?php
/**
 * Plugin Name: Multi RSS Feed Importer
 * Description: Import and manage posts from multiple RSS feed URLs.
 * Version: 1.0.0
 * Requires at least: 5.2
 * Requires PHP: 8.0
 * Author: Martin IS IT Services
 * Author URI: https://misits.ch
 * Text Domain: multi-rss-feed-importer
 * Domain Path: /languages
*/

namespace Multi_RSS_Feed_Importer;

if (!defined('ABSPATH')) exit;

require 'utils/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$updateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/misits/multi-rss-feed-importer',
    __FILE__,
    'multi-rss-feed-importer'
);

/**
 * Main plugin class
 */
final class Multi_RSS_Feed_Importer {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->define_constants();
        $this->register_autoloader();
        $this->init_hooks();
    }

    private function define_constants() {
        define('RSS_IMPORTER_VERSION', '1.0.0');
        define('RSS_IMPORTER_PLUGIN_FILE', __FILE__);
        define('RSS_IMPORTER_PLUGIN_DIR', plugin_dir_path(__FILE__));
        define('RSS_IMPORTER_PLUGIN_URL', plugin_dir_url(__FILE__));
    }

    private function register_autoloader() {
        spl_autoload_register(function ($class) {
            $prefix = 'Multi_RSS_Feed_Importer\\';
            $base_dir = plugin_dir_path(__FILE__) . 'includes/';
        
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                return;
            }
        
            $relative_class = substr($class, $len);
            $file_name = 'class-' . strtolower(str_replace('_', '-', 
                preg_replace('/([a-z])([A-Z])/', '$1-$2', $relative_class)
            )) . '.php';
        
            $file = $base_dir . $file_name;
        
            if (file_exists($file)) {
                require $file;
            }
        });
    }

    private function init_hooks() {
        add_action('plugins_loaded', [$this, 'init_plugin']);
        if (is_admin()) {
            add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        }
    }

    public function init_plugin() {
        load_plugin_textdomain('multi-rss-feed-importer', false, dirname(plugin_basename(__FILE__)) . '/languages');
        Loader::init();
    }

    public function enqueue_admin_assets($hook) {
        if (!$this->is_plugin_page($hook)) return;

        wp_enqueue_style('rss-importer-admin', RSS_IMPORTER_PLUGIN_URL . 'assets/css/admin.css', [], RSS_IMPORTER_VERSION);
        wp_enqueue_script('rss-importer-admin', RSS_IMPORTER_PLUGIN_URL . 'assets/js/admin.js', ['jquery'], RSS_IMPORTER_VERSION, true);

        wp_localize_script('rss-importer-admin', 'rssImporter', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('rss_importer_nonce'),
        ]);
    }

    private function is_plugin_page($hook) {
        $plugin_pages = ['toplevel_page_multi-rss-feed-importer'];
        return in_array($hook, $plugin_pages);
    }
}

// Initialize the plugin
function rss_importer_init() {
    return Multi_RSS_Feed_Importer::get_instance();
}
rss_importer_init();
