# Multi RSS Feed Importer for WordPress

WordPress plugin to import and manage RSS feeds into custom post types.

## Features

- Import RSS feed items into custom post types
- Update existing posts if they already exist
- Manage multiple RSS feeds via a simple interface
- Global limit on the number of posts per feed
- Automated imports via WP Cron
- Customizable settings for post types and cron intervals
- User-friendly admin UI with real-time progress tracking

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher

## Installation

1. Download the plugin zip file
2. Upload the zip file to your WordPress site via Plugins > Add New
3. Activate the plugin
4. Configure settings under Settings > RSS Feed Importer

## Usage

### Manual Import

Use the RSS Feed Importer settings to add your feed URLs and configure options like post type, global feed limits, and cron intervals.

### Example Code Usage

If you need to extend functionality programmatically:

```php
use Multi_RSS_Feed_Importer\FeedLoader;

// Create a new loader instance
$loader = new FeedLoader('https://example.com/feed.xml');

// Import RSS feed items into a custom post type
$loader->import_posts('custom_post_type');
```

### Automated Import

Configure your RSS sources and cron settings in the WordPress admin under Settings > RSS Feed Importer.

## Development

### Plugin Structure

```bash
multi-rss-feed-importer/
├── admin/
│   ├── templates/
│   │   ├── rss-fetch.php
│   │   └── rss-settings.php
├── assets/
│   ├── css/
│   │   └── admin.css
│   └── js/
│       └── admin.js
├── includes/
│   ├── class-admin-page.php
│   ├── class-feed-loader.php
│   ├── class-logger.php
│   └── class-loader.php
├── languages/
├── logs/
├── tests/
├── uninstall.php
├── multi-rss-feed-importer.php
└── README.md
```

### Building from Source

1. Clone the repository.
2. Create a new branch for your feature.
3. Make your changes.
4. Test thoroughly.
5. Create a pull request.

## Contributing

1. Fork the repository.
2. Create a feature branch.
3. Commit your changes.
4. Push to the branch.
5. Create a new Pull Request.

## Settings Overview

1. **Custom Post Type**: Define which post type the RSS feed items should be imported into.
2. **RSS Feed URLs**: Enter one or more RSS feed URLs. Separate multiple URLs with a new line.
3. **Global Feed Limit**: Optionally set a limit for the total number of posts per feed.
4. **Cron Interval**: Configure how frequently RSS feeds should be processed.
5. **Enable Cron**: Enable or disable automated imports.

## Plugin Demo

### Admin Interface

- **Feed Management**: Add, edit, and delete RSS feed URLs.
- **Settings Panel**: Configure post type, global feed limits, and cron options.
- **Real-Time Import Logs**: View real-time import logs in the admin panel.

## License

GPLv2 or later
