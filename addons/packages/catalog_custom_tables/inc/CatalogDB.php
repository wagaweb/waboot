<?php

namespace Waboot\addons\packages\catalog_custom_tables\inc;

class CatalogDB
{
    /**
     * @var CapsuleWP
     */
    private $dbConnector;

    public function __construct(CapsuleWP $dbConnector = null)
    {
        if($dbConnector === null) {
            $dbConnector = new CapsuleWP();
        }
        $this->dbConnector = $dbConnector;
    }

    /**
     * @param $sku
     * @return int|bool
     */
    public function searchProductIdBySku($sku)
    {
        $r = $this->dbConnector->getManager()::table(WB_CUSTOM_PRODUCTS_TABLE)
            ->where('sku', '=', $sku)
            ->get();
        if($r->count() === 0){
            return false;
        }
        return $r->get(0)->id;
    }

    /**
     * @param $slug
     * @return int|bool
     */
    public function searchCategoryIdBySlug($slug)
    {
        $r = $this->dbConnector->getManager()::table(WB_CUSTOM_CATEGORIES_TABLE)
            ->where('slug', '=', $slug)
            ->get();
        if($r->count() === 0){
            return false;
        }
        return $r->get(0)->id;
    }

    /**
     * @param int $productId
     * @return bool|\stdClass
     */
    public function getProductRecordById(int $productId)
    {
        $r = $this->dbConnector->getManager()::table(WB_CUSTOM_PRODUCTS_TABLE)
            ->where('id', '=', $productId)
            ->get();
        if($r->count() === 0){
            return false;
        }
        return $r->get(0);
    }

    /**
     * @throws CatalogDBException
     */
    public function truncateNNTables(): void
    {
        try{
            $this->dbConnector->getManager()::table(WB_CUSTOM_PRODUCTS_CATEGORIES_TABLE)->truncate();
        }catch (\Illuminate\Database\QueryException $e){
            throw new CatalogDBException($e->getMessage());
        }catch (\RuntimeException $e){
            throw new CatalogDBException($e->getMessage());
        }
    }
}
