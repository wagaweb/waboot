<?php

namespace Waboot\addons\packages\catalog_custom_tables\inc;

class API
{
    /**
     * @var CatalogDB
     */
    private $catalogDB;

    public function __construct(CatalogDB $catalogDB = null, CapsuleWP $dbConnector = null)
    {
        if(!isset($catalogDB)){
            $catalogDB = isset($dbConnector) ? new CatalogDB($dbConnector) : new CatalogDB(new CapsuleWP());
        }
        $this->catalogDB = $catalogDB;
    }

    /**
     * @param int $resultsNumber
     * @param int $offset
     * @return int[]
     */
    public function getProductsIds(int $resultsNumber, int $offset = 0): array
    {
        return [];
    }

    /**
     * @param array $filters
     * @param int $resultsNumber
     * @param int $offset
     * @return int[]
     */
    public function getProductIdsByFilters(array $filters, int $resultsNumber, int $offset = 0): array
    {
        return [];
    }
}
