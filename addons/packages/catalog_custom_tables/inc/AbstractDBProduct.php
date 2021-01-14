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
     * @return string|null
     */
    public function getSku(): ?string
    {
        return $this->sku;
    }

    /**
     * @return string|null
     */
    public function getParentSku(): ?string
    {
        return $this->parentSku;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }
}