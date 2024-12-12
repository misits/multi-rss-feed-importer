<?php

if (!defined('ABSPATH')) exit;

?>

<div id="rss-fetch" class="rss-section">
    <div class="rss-process-section">
        <h2 class="rss-toggle">
            <span class="rss-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-cloud-download">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M12 18.004h-5.343c-2.572 -.004 -4.657 -2.011 -4.657 -4.487c0 -2.475 2.085 -4.482 4.657 -4.482c.393 -1.762 1.794 -3.2 3.675 -3.773c1.88 -.572 3.956 -.193 5.444 1c1.488 1.19 2.162 3.007 1.77 4.769h.99c1.38 0 2.573 .813 3.13 1.99" />
                    <path d="M19 16v6" />
                    <path d="M22 19l-3 3l-3 -3" />
                </svg>
                <?= __('Process RSS Feeds', 'multi-rss-feed-importer'); ?> <span class="desc"> - <?= __('Import data from the configured RSS feeds', 'multi-rss-feed-importer') ?>.</span>
            </span>
            <span class="rss-arrow">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-chevron-down">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M6 9l6 6l6 -6" />
                </svg>
            </span>
        </h2>
        <div class="rss-content">
            <div id="rss-progress-container" style="display: none;">
                <div class="rss-progress-bar">
                    <div class="rss-progress-fill"></div>
                </div>
                <div class="rss-progress-status"></div>
                <div class="rss-progress-log"></div>
            </div>

            <form method="post" action="" id="rss-process-form">
                <?php wp_nonce_field('rss_process_feeds', '_wpnonce_rss_process_feeds'); ?>
                <p class="description">
                    <?= __('Click the button below to process RSS feed data.', 'multi-rss-feed-importer'); ?>
                </p>
                <p>
                    <button type="button"
                        id="rss-process-button"
                        class="button button-primary">
                        <?= esc_html__('Process RSS Feeds', 'multi-rss-feed-importer'); ?>
                    </button>
                </p>
            </form>
        </div>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        const button = $('#rss-process-button');
        const progressContainer = $('#rss-progress-container');
        const progressBar = $('.rss-progress-fill');
        const progressStatus = $('.rss-progress-status');
        const progressLog = $('.rss-progress-log');

        button.on('click', function() {
            button.prop('disabled', true);
            progressContainer.show();
            progressLog.empty();
            processNextFeed(0);
        });

        function processNextFeed(currentFeed) {
            $.ajax({
                url: rssImporterData.ajaxurl,
                method: 'POST',
                data: {
                    action: 'fetch_rss_posts',
                    nonce: rssImporterData.nonce,
                    current_feed: currentFeed
                },
                success: function(response) {
                    if (response.success) {
                        const data = response.data;

                        if (data.total_feeds === 0) {
                            button.prop('disabled', false);
                            progressBar.css('width', '100%');
                            progressLog.append(
                                $('<div class="rss-log-error"></div>').text(data.message)
                            );
                            return;
                        }

                        progressBar.css('width', data.progress + '%');
                        progressStatus.text(data.progress + '% completed');

                        if (data.error) {
                            progressLog.append(
                                $('<div class="rss-log-error"></div>').text('Error: ' + data.error)
                            );
                        } else {
                            if (data.feedUrl) {
                                progressLog.append(
                                    $('<div class="rss-log-success"></div>').text('Processed: ' + data.feedUrl)
                                );
                            } 
                            
                        }

                        progressLog.scrollTop(progressLog[0].scrollHeight);

                        if (!data.done) {
                            setTimeout(() => {
                                processNextFeed(data.current_feed);
                            }, 500);
                        } else {
                            button.prop('disabled', false);
                            progressStatus.text(data.message);
                        }
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('AJAX Error:', {
                        status: jqXHR.status,
                        statusText: jqXHR.statusText,
                        responseText: jqXHR.responseText,
                        textStatus: textStatus,
                        errorThrown: errorThrown
                    });
                    progressLog.append(
                        $('<div class="rss-log-error"></div>').text(
                            `<?php esc_html_e('Network error occurred. Please try again. Status: ', 'multi-rss-feed-importer'); ?>${textStatus} (${jqXHR.status})`
                        )
                    );
                    button.prop('disabled', false);
                }
            });
        }
    });
</script>
