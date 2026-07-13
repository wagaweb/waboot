<?php

namespace Waboot\inc\cli\product_import;

class ImportProductsCurrentRow
{
    /**
     * @var array
     */
    private $currentTerms;
    
    public function __construct()
    {}

    /**
     * @param int $termId
     * @param string $taxonomy
     * @return void
     */
    public function addTermById(int $termId, string $taxonomy): void
    {
        $this->currentTerms[$taxonomy][] = $termId;
    }

    /**
     * @param string $taxonomy
     * @return int|null
     */
    public function getFirstTermIdOfTaxonomy(string $taxonomy): ?int
    {
        if(!isset($this->currentTerms[$taxonomy])){
            return null;
        }
        if(empty($this->currentTerms[$taxonomy])){
            return null;
        }
        return $this->currentTerms[$taxonomy][0];
    }
}
