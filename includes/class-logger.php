<?php

namespace Multi_RSS_Feed_Importer;

class Logger
{
    /**
     * Log a message to a daily log file
     */
    public static function log_message($message)
    {
        $log_dir = RSS_IMPORTER_PLUGIN_DIR . 'logs/';
        if (!file_exists($log_dir)) {
            mkdir($log_dir, 0755, true);
        }

        $log_file = $log_dir . 'log_' . date('Y-m-d') . '.log';
        $timestamp = date('[Y-m-d H:i:s]');
        file_put_contents($log_file, $timestamp . ' ' . $message . PHP_EOL, FILE_APPEND);
    }

    /**
     * Get a list of all log files
     */
    public static function get_log_files()
    {
        $log_dir = RSS_IMPORTER_PLUGIN_DIR . 'logs/';
        if (!is_dir($log_dir)) {
            return [];
        }

        $files = array_diff(scandir($log_dir), ['.', '..']);
        $log_files = [];

        foreach ($files as $file) {
            $file_path = $log_dir . $file;
            if (is_file($file_path)) {
                $log_files[] = [
                    'name' => $file,
                    'size' => filesize($file_path),
                ];
            }
        }
        return $log_files;
    }

    /**
     * Get the content of a specific log file
     */
    public static function get_log_file_content($file_name)
    {
        $log_dir = RSS_IMPORTER_PLUGIN_DIR . 'logs/';
        $file_path = $log_dir . $file_name;

        if (file_exists($file_path) && is_readable($file_path)) {
            return file_get_contents($file_path);
        } else {
            return __('Invalid log file specified.', 'multi-rss-feed-importer');
        }
    }

    /**
     * Clear all log files
     */
    public static function clear_log_files()
    {
        $log_dir = RSS_IMPORTER_PLUGIN_DIR . 'logs/';
        if (!is_dir($log_dir)) {
            return;
        }

        $files = array_diff(scandir($log_dir), ['.', '..']);

        foreach ($files as $file) {
            $file_path = $log_dir . $file;
            if (is_file($file_path)) {
                unlink($file_path);
            }
        }

        echo '<div class="updated"><p>' . __('Log files cleared successfully.', 'multi-rss-feed-importer') . '</p></div>';
    }

    /**
     * Get the most recent log file
     */
    public static function get_last_log()
    {
        $log_dir = RSS_IMPORTER_PLUGIN_DIR . 'logs/';
        $files = array_diff(scandir($log_dir, SCANDIR_SORT_DESCENDING), ['.', '..']);

        foreach ($files as $file) {
            $file_path = $log_dir . $file;
            if (is_file($file_path)) {
                return $file;
            }
        }

        return '';
    }

    /**
     * Download a specific log file
     */
    public static function download_log_file($file_name)
    {
        $log_dir = RSS_IMPORTER_PLUGIN_DIR . 'logs/';
        $file_path = $log_dir . $file_name;

        if (file_exists($file_path) && is_readable($file_path)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
            header('Content-Length: ' . filesize($file_path));
            readfile($file_path);
            exit;
        } else {
            wp_die(__('Invalid log file specified.', 'multi-rss-feed-importer'), 'Error', ['back_link' => true]);
        }
    }

    /**
     * Delete a specific log file
     */
    public static function delete_log_file($file_name)
    {
        $log_dir = RSS_IMPORTER_PLUGIN_DIR . 'logs/';
        $file_path = $log_dir . $file_name;

        if (file_exists($file_path) && is_writable($file_path)) {
            unlink($file_path);
            echo '<div class="updated"><p>' . __('Log file deleted successfully.', 'multi-rss-feed-importer') . '</p></div>';
        } else {
            echo '<div class="error"><p>' . __('Failed to delete the log file.', 'multi-rss-feed-importer') . '</p></div>';
        }
    }
}