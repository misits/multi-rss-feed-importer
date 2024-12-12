=== Multi RSS Feed Importer ===
Contributors: yourusername
Tags: rss, feed, import, custom post type, automation
Requires at least: 5.0
Tested up to: 6.4.2
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Import RSS feed items into WordPress custom post types with automated and manual options.

== Description ==
RSS Feed Importer is a WordPress plugin that allows you to import RSS feed items into custom post types. With both manual and automated options, the plugin helps you manage content from multiple RSS feeds effortlessly.

**Features:**

- Import RSS feed items into custom post types
- Update existing posts if they already exist
- Manage multiple RSS feeds via a simple interface
- Global limit on the number of posts per feed
- Automated imports via WP Cron
- Customizable settings for post types and cron intervals
- User-friendly admin UI with real-time progress tracking

Supported Fields:

- Title
- Link
- Description
- Publication Date
- GUID

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/multi-rss-feed-importer` directory, or install the plugin through the WordPress plugins screen.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Configure the plugin settings under 'Settings > RSS Feed Importer'.

== Usage ==

**Manual Import:**

1. Go to 'Tools > RSS Feed Importer'.
2. Enter your RSS feed URLs.
3. Select your post type and set optional global limits.
4. Click "Process RSS Feeds" to start the import.

**Automated Import:**

1. Go to 'Settings > RSS Feed Importer'.
2. Configure your RSS feed URLs and global limits.
3. Enable cron and set the desired schedule.
4. Save settings, and the plugin will handle periodic imports automatically.

== Frequently Asked Questions ==

= Can I limit the number of posts imported from a feed? =
Yes, you can set a global post limit that applies to all imported feed items.

= Does this plugin support custom post types? =
Yes, you can import feed items into any custom post type configured on your WordPress site.

= Can I update existing posts? =
Yes, the plugin automatically checks for existing posts and updates them if they match the feed item GUID.

= How do I enable automated imports? =
Enable the cron option in the settings and configure the interval for automatic imports.

== Changelog ==
= 1.0.0 =
- Initial release

== Upgrade Notice ==
= 1.0.0 =
- Initial release
