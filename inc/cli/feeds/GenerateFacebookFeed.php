<?php

namespace waboot\inc\cli\feeds;

class GenerateFacebookFeed extends GenerateGShoppingFeed
{
    /**
     * @var string
     */
    protected $logFileName = 'facebook-feed-gen';
    protected string $customOutputFilename = 'facebook-products-feed';
}