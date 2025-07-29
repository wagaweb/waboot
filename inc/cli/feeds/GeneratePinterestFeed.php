<?php

namespace Waboot\inc\cli\feeds;

// https://business.pinterest.com/it/blog/how-to-optimize-product-feed/
// https://help.pinterest.com/en/business/article/before-you-get-started-with-catalogs#section-9441
// https://help.pinterest.com/sub/helpcenter/assets/pinterest_product_sample_xml_feed.xml.zip

class GeneratePinterestFeed extends GenerateGShoppingFeed
{
    protected string $customOutputFilename = 'pinterest-products-feed';
}