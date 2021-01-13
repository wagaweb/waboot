<?php

namespace Waboot\addons\packages\catalog_custom_tables\inc;

class AbstractDBProduct
{
    /**
     * @var string
     */
    protected $sku;
    /**
     * @var string
     */
    protected $parentSku;
    /**
     * @var string
     */
    protected $slug;
    /**
     * @var string
     */
    protected $title;
    /**
     * @var CapsuleWP
     */
    protected $dbConnector;
    /**
     * @var CatalogDB
     */
    protected $dbManager;

    /**
     * @return string
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * @return string
     */
    public function getParentSku(): string
    {
        return $this->parentSku;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }
}