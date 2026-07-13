<?php

namespace Waboot\inc\feeds;

use Waboot\inc\cli\feeds\AbstractGenerateFeeds;

function renderFeedToolsAdminSubPage(): void {
    $registeredFeeds = getRegisteredFeeds();
    ?>
    <div class="wrap">
        <h1>WaWoo Feeds</h1>
        <?php if(empty($registeredFeeds)): ?>
            <p><?php _e('No feeds found', LANG_TEXTDOMAIN) ?></p>
        <?php else: ?>
            <h2><?php _e('Registered feeds', LANG_TEXTDOMAIN) ?></h2>
            <?php foreach ($registeredFeeds as $feed): ?>
                <?php
                /**
                 * @var RegisteredFeed $feed
                 */
                $name = $feed->name ?? basename($feed->path);
                ?>
                <h4><?php echo $name; ?></h4>
                <p>
                    <span><strong>URL:</strong>&nbsp;</span><span><?php echo $feed->getUrl() ?></span><br />
                    <?php $stats = $feed->getCLICommandStats(); ?>
                    <?php if(isset($stats['in_progress']) && $stats['in_progress']): ?>
                        <span><strong>In progress:</strong>&nbsp;</span><span>yes</span><br />
                    <?php else: ?>
                        <span><strong>In progress:</strong>&nbsp;</span><span>no</span><br />
                    <?php endif; ?>
                    <span><strong>Last started at:</strong>&nbsp;</span><span><?php echo $stats['last_started_at'] ?? '' ?></span><br />
                    <span><strong>Last ended at:</strong>&nbsp;</span><span><?php echo $stats['last_ended_at'] ?? '' ?></span><br />
                </p>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * @return array
 */
function getRegisteredFeeds(): array {
    $registeredFeedsArray = apply_filters('wawoo/feeds/registered_feeds', []);
    if(!\is_array($registeredFeedsArray) || empty($registeredFeedsArray)){
        return [];
    }
    $registeredFeeds = [];
    foreach($registeredFeedsArray as $feedParam){
        if(!isset($feedParam['path']) || !\is_string($feedParam['path'])){
            continue;
        }
        $rF = new RegisteredFeed($feedParam['path']);
        $rF->populateProperties($feedParam);
        $registeredFeeds[] = $rF;
    }
    return $registeredFeeds;
}

/**
 * @param array $params
 * @return void
 */
function registerFeed(array $params): void
{
    if(!isset($params['path']) || !\is_string($params['path'])){
        return;
    }
    add_filter('wawoo/feeds/registered_feeds', static function(array $feeds) use ($params) {
        $feeds[] = $params;
        return $feeds;
    });
}