<?php

if (!defined('ABSPATH')) exit;

// Verify we have the required data
if (!isset($post_types) || !isset($rss_importer_feeds) || !isset($selected_post_type) || !isset($selected_cron_interval) || !isset($cron_intervals) || !isset($rss_importer_cron_enabled) || !isset($rss_importer_limit) || !isset($last_sync) || !isset($next_sync)) {
    return;
}
?>

<div id="rss-settings" class="rss-section">
    <div class="rss-section-head">
        <div class="flex gap-10">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-rss">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M5 19m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                <path d="M4 4a16 16 0 0 1 16 16" />
                <path d="M4 11a9 9 0 0 1 9 9" />
            </svg>
            <h1><?= __('RSS Feed Importer', 'multi-rss-feed-importer') ?></h1>
        </div>
        <p><?= __('Use this tool to load data from RSS feeds into WordPress.', 'multi-rss-feed-importer') ?></p>
        <p><?= __('Last Sync:', 'multi-rss-feed-importer') ?> <strong><?= $last_sync ?></strong></p>
        <?php if ($next_sync) : ?>
            <p><?= __('Next Sync:', 'multi-rss-feed-importer') ?> <strong><?= date('Y-m-d H:i:s', $next_sync) ?></strong></p>
        <?php endif; ?>
    </div>
    <h2 class="rss-toggle">
        <span class="rss-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-adjustments-alt">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M4 8h4v4h-4z" />
                <path d="M6 4l0 4" />
                <path d="M6 12l0 8" />
                <path d="M10 14h4v4h-4z" />
                <path d="M12 4l0 10" />
                <path d="M12 18l0 2" />
                <path d="M16 5h4v4h-4z" />
                <path d="M18 4l0 1" />
                <path d="M18 9l0 11" />
            </svg>
            <?= __('Settings', 'multi-rss-feed-importer') ?> <span class="desc"> - <?= __('Configure the RSS importer', 'multi-rss-feed-importer') ?>.</span>
        </span>
        <span class="rss-arrow">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-chevron-down">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M6 9l6 6l6 -6" />
            </svg>
        </span>
    </h2>
    <div class="rss-content">
        <form method="post" class="flex flex-col gap-10">
            <!-- nonce field -->
            <?php wp_nonce_field('rss_save_settings', '_wpnonce_rss_save_settings'); ?>

            <div class="form-group">
                <label for="rss_post_type"><?= __('Custom Post Type', 'multi-rss-feed-importer') ?>:</label>
                <select name="rss_post_type" id="rss_post_type">
                    <?php foreach ($post_types as $post_type) : ?>
                        <option value="<?php echo esc_attr($post_type->name); ?>"
                            <?php selected($selected_post_type, $post_type->name); ?>>
                            <?php echo esc_html($post_type->label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="rss_feeds"><?= __('RSS Feed URLs', 'multi-rss-feed-importer') ?>:</label>
                <textarea name="rss_feeds" id="rss_feeds" rows="5" class="large-text"><?php echo esc_textarea(implode("\n", $rss_importer_feeds)); ?></textarea>
                <p class="description">Separate multiple URLs with a new line.</p>
            </div>

            <div class="form-group">
                <label for="rss_cron_interval"><?= __('Cron Interval', 'multi-rss-feed-importer') ?>:</label>
                <select name="rss_cron_interval" id="rss_cron_interval">
                    <?php foreach ($cron_intervals as $interval => $label) : ?>
                        <option value="<?php echo esc_attr($interval); ?>" <?php selected($selected_cron_interval, $interval); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="rss_cron_enabled"><?= __('Enable Cron', 'multi-rss-feed-importer') ?>:</label>
                <label class="switch">
                    <input type="checkbox" name="rss_cron_enabled" id="rss_cron_enabled" value="1" <?php checked($rss_importer_cron_enabled, 1); ?>>
                    <span class="slider round"></span>
                </label>
            </div>

            <div class="form-group">
                <label for="rss_feed_limit"><?= __('Global Feed Limit (Optional)', 'multi-rss-feed-importer') ?>:</label>
                <input type="number" name="rss_feed_limit" id="rss_feed_limit" value="<?php echo esc_attr($rss_importer_limit ?? ''); ?>" class="small-text">
                <p class="description">
                    <?= __('Set a global limit for the total number of posts across all feeds. Leave empty or use 0 for no limit.', 'multi-rss-feed-importer') ?>
                </p>
            </div>

            <div class="flex items-center gap-10 buttons">
                <button type="submit" name="rss_save_settings" class="button button-primary"><?= __('Save Settings', 'multi-rss-feed-importer') ?></button>
            </div>
        </form>
    </div>
</div>