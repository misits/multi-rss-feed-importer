<?php

namespace Multi_RSS_Feed_Importer;

use Multi_RSS_Feed_Importer\Logger;
use Exception;
use WP_Query;

class FeedLoader
{
    private $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * Enforce the post limit for a feed by deleting older posts.
     *
     * @param string $postType The post type.
     * @param string $guid The unique identifier for the feed.
     * @param int $limit The maximum number of posts allowed for this feed.
     * @return void
     */
    private function enforce_post_limit($link, $limit)
    {
        if ($limit <= 0) {
            return; // No limit to enforce
        }

        // Query posts for the specific feed
        $query = new WP_Query([
            'meta_key' => '_rss_imported_link',
            'meta_value' => $link,
            'posts_per_page' => -1,
            'orderby' => 'post_date',
            'order' => 'DESC',
            'fields' => 'ids',
        ]);

        if ($query->have_posts()) {
            $posts = $query->posts;

            // If the number of posts exceeds the limit, delete the oldest ones
            if (count($posts) > $limit) {
                $posts_to_delete = array_slice($posts, $limit);
                foreach ($posts_to_delete as $post_id) {
                    wp_delete_post($post_id, true);
                    Logger::log_message("Deleted post with ID {$post_id} to enforce limit for feed: {$link}");
                }
            }
        }
    }


    /**
     * Import posts from the RSS feed
     *
     * @param string $postType The post type to create posts under
     * @return void
     */
    public function import_posts($postType = 'post')
    {
        $feedData = $this->fetch_feed_data();

        if (!$feedData) {
            Logger::log_message("Failed to fetch data from feed: {$this->url}");
            return false;
        }

        foreach ($feedData as $item) {
            $this->create_post_from_item($item, $postType);
        }

        return true;
    }

    /**
     * Fetch data from the RSS feed
     *
     * @return array|null Parsed feed items
     */
    private function fetch_feed_data()
    {
        $response = wp_remote_get($this->url);

        if (is_wp_error($response)) {
            Logger::log_message("HTTP error: " . $response->get_error_message() . " for URL: {$this->url}");
            return null;
        }

        $limit = intval(get_option('rss_importer_feed_limits', 0));

        // Enforce the post limit after importing
        $this->enforce_post_limit($this->url, $limit);

        if (wp_remote_retrieve_response_code($response) !== 200) {
            Logger::log_message("Failed to fetch RSS feed: {$this->url} with HTTP code " . wp_remote_retrieve_response_code($response));
            return null;
        }

        $body = wp_remote_retrieve_body($response);
        $rss = @simplexml_load_string($body, "SimpleXMLElement", LIBXML_NOCDATA);

        if (!$rss || !isset($rss->channel->item)) {
            Logger::log_message("Failed to parse RSS feed or missing <item> elements: {$this->url}");
            return null;
        }

        $items = [];
        foreach ($rss->channel->item as $item) {
            $title = (string) $item->title;
            $link = (string) $item->link;
            $description = (string) $item->description;
            $pubDate = (string) $item->pubDate;
            $guid = (string) $item->guid;
            $url = $this->url;

            // Log each item's raw content for debugging
            error_log("Raw Item: " . print_r($item, true));

            // Check for required fields
            if (empty($title) || empty($guid) || empty($link)) {
                Logger::log_message("Skipping item due to missing title, guid, or link: " . json_encode([
                    'url' => $this->url,
                    'title' => $title,
                    'link' => $link ?? '',
                    'guid' => $guid ?? '',
                    'description' => $description ?? '',
                    'pubDate' => $pubDate ?? '',
                ]));
                continue;
            }

            $items[] = compact('title', 'link', 'description', 'pubDate', 'guid', 'url');
        }

        Logger::log_message("Fetched " . count($items) . " items from feed: {$this->url}");

        // slice the array to the global feed limit
        if ($limit > 0) {
            $items = array_slice($items, 0, $limit);
        }

        return $items;
    }


    /**
     * Create a post in WordPress from an RSS feed item
     *
     * @param array $item The RSS feed item
     * @param string $postType The post type to create posts under
     * @return void
     */
    private function create_post_from_item($item, $postType)
    {
        try {
            // Skip empty title or description
            if (empty($item['title'])) {
                Logger::log_message("Skipping item due to missing title: " . print_r($item, true));
                return false;
            }

            // Check if the post already exists
            $existing_post = get_posts([
                'post_type' => $postType,
                'meta_key' => '_rss_imported_url',
                'meta_value' => $item['guid'],
                'posts_per_page' => 1
            ]);

            if (!empty($existing_post)) {
                Logger::log_message("{$postType} already exists for URL: {$item['guid']}");

                // Update post
                $post_id = $existing_post[0]->ID;
                wp_update_post([
                    'ID' => $post_id,
                    'post_title' => sanitize_text_field($item['title']),
                    'post_content' => wp_kses_post($item['description']) ?? '',
                    'post_date' => date('Y-m-d H:i:s', strtotime($item['pubDate']) ?? current_time('mysql')),
                ]);


                Logger::log_message("{$postType} updated with ID {$post_id} for URL: {$item['guid']}");
                return true;
            }

            // Insert post
            $post_id = wp_insert_post([
                'post_title' => sanitize_text_field($item['title']),
                'post_content' => wp_kses_post($item['description']) ?? '',
                'post_type' => $postType,
                'post_status' => 'publish',
                'post_date' => date('Y-m-d H:i:s', strtotime($item['pubDate']) ?? current_time('mysql')),
            ]);

            if ($post_id) {
                add_post_meta($post_id, '_rss_imported_url', esc_url_raw($item['guid']));
                add_post_meta($post_id, '_rss_imported_link', $item['url']);
                Logger::log_message("{$postType} created with ID {$post_id} for URL: {$item['guid']}");
            } else {
                throw new Exception("Failed to insert post for URL: {$item['guid']}");
            }
        } catch (Exception $e) {
            Logger::log_message($e->getMessage());
        }

        return true;
    }
}
