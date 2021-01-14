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
     * @param \WP_Term $term
     * @param string $tableName
     */
    public function addWPTerm(\WP_Term $term, string $tableName): void
    {
        $existingTermId = $this->searchTermByWPTermId($term->term_id,$tableName);
        if($existingTermId !== false){
            return;
        }
        if($term->parent === 0){
            $data = [
                'wc_id' => $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug,
            ];
            try{
                $this->dbConnector->getManager()::table($tableName)->insertGetId($data);
            }catch (\Illuminate\Database\QueryException $e){}
        }else{
            $parentTerm = get_term($term->parent,$term->taxonomy);
            if($parentTerm instanceof \WP_Term){
                $existingParentId = $this->searchTermByWPTermId($parentTerm->term_id,$tableName);
                if($existingParentId !== false){
                    $data = [
                        'wc_id' => $term->term_id,
                        'name' => $term->name,
                        'slug' => $term->slug,
                        'parent_id' => $existingParentId
                    ];
                    $this->dbConnector->getManager()::table($tableName)->insertGetId($data);
                }else{
                    $this->addWPTerm($parentTerm,$tableName);
                }
            }
        }
    }

    /**
     * @param int $termId
     * @param string $tableName
     * @return bool|int
     */
    public function searchTermByWPTermId(int $termId, string $tableName)
    {
        $r = $this->dbConnector->getManager()::table($tableName)
            ->where('wc_id','=',$termId)
            ->get();
        if($r->count() === 0){
            return false;
        }
        return $r->get(0)->id;
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
