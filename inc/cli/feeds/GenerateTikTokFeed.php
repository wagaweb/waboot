<?php

namespace waboot\inc\cli\feeds;

// https://ads.tiktok.com/help/article?aid=10001006&redirected=1

class GenerateTikTokFeed extends GenerateGShoppingFeed
{
    /**
     * @var string
     */
    protected $logFileName = 'tiktok-feed-gen';
    protected string $customOutputFilename = 'tiktok-products-feed';

    protected function customInitialization(): void
    {
        add_filter('wawoo/cli/genfeeds/generate_record/record', static function ($newRecord, \WC_Product $product, ?\WC_Product $parentProduct) {
            $newRecord['sku_id'] = $newRecord['id'];
            unset($newRecord['id']);
            return $newRecord;
        });
    }
}