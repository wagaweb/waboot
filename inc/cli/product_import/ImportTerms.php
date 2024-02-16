<?php

namespace Waboot\inc\cli\product_import;

use Waboot\inc\core\cli\AbstractCommand;
use Waboot\inc\core\cli\CLIRuntimeException;
use Waboot\inc\core\DBException;
use Waboot\inc\core\facades\Query;
use Waboot\inc\core\multilanguage\helpers\Polylang;
use function Waboot\inc\core\helpers\Waboot;

class ImportTerms extends AbstractCommand
{
    private string $tableName;
    private array $selectedTaxonomies = [];
    private ?string $defaultLang = null;

    public static function getCommandDescription(): array
    {
        $description = parent::getCommandDescription();
        $description['shortdesc'] = 'Import terms from reference table';
        $description['longdesc'] = '## EXAMPLES' . "\n\n" . 'wp wawoo:import-terms --table=foobar --taxonomies=tax1,tax2';
        $description['synopsis'][] = [
            'type' => 'assoc',
            'name' => 'table',
            'description' => 'Specifies the table name (default to: product_importer_terms_reference)',
            'default' => 'product_importer_terms_reference',
            'optional' => true,
        ];
        $description['synopsis'][] = [
            'type' => 'assoc',
            'name' => 'taxonomies',
            'description' => 'Specifies which taxonomies to import (comma separated)',
            'default' => '',
            'optional' => true,
        ];
        $description['synopsis'][] = [
            'type' => 'assoc',
            'name' => 'default-lang',
            'description' => 'Specifies the default language',
            'default' => '',
            'optional' => true,
        ];
        $description['synopsis'][] = [
            'type' => 'assoc',
            'name' => 'langs',
            'description' => 'Comma separated',
            'default' => '',
            'optional' => true,
        ];
        return $description;
    }

    /**
     * @param array $args
     * @param array $assoc_args
     * @return int
     */
    public function run(array $args, array $assoc_args): int
    {
        try{
            $tableName = $assoc_args['table'];
            if(\is_string($tableName) && $tableName !== ''){
                $this->tableName = $tableName;
            }else{
                $this->tableName = 'product_importer_terms_reference';
            }
            $selectedTaxonomies = isset($assoc_args['taxonomies']) ? explode(',',$assoc_args['taxonomies']) : [];
            if(!empty($selectedTaxonomies)){
                $this->selectedTaxonomies = $selectedTaxonomies;
            }
            if(Polylang::isPolylang()){
                $defaultLang = isset($assoc_args['default-lang']) ? $assoc_args['taxonomies'] : '';
                if($defaultLang === ''){
                    throw new CLIRuntimeException('Default lang cannot be empty in multi language environment');
                }
                if(!Polylang::langExists($defaultLang)){
                    throw new CLIRuntimeException('Default lang: '.$defaultLang.' does not exists');
                }
            }
            $this->createTerms();
            $this->success('Operation completed');
            return 0;
        }catch (\Exception | \Throwable $e){
            $this->error($e->getMessage(),false);
            return 1;
        }
    }

    /**
     * @return array
     * @throws \Waboot\inc\core\DBException
     */
    private function getTaxonomies(): array
    {
        $availableTaxonomiesResult = Query::on($this->tableName)->select(['taxonomy'])->distinct()->get()->toArray();
        $availableTaxonomies = wp_list_pluck($availableTaxonomiesResult,'taxonomy');
        if(!empty($this->selectedTaxonomies)){
            $availableTaxonomies = array_intersect($availableTaxonomies,$this->selectedTaxonomies);
        }
        return $availableTaxonomies;
    }

    /**
     * @throws DBException
     */
    private function createTerms()
    {
        foreach ($this->getTaxonomies() as $taxonomy){
            $this->log('Creating terms for taxonomy: '.$taxonomy);
            /*if(!taxonomy_exists($taxonomy)){
                $this->log('- ERROR: taxonomy does not exists');
                continue;
            }*/
            $termDBEntries = $this->getTermsToCreateForTaxonomy($taxonomy);
            /*
             * From this point on, inside $termDBEntries there is something like this
             * [
             *    [
             *       'code' => ..
             *       'description_it' => ...
             *       'description_en' => ...
             *       'parent_code' => ...
             *       'children' => ...
             *    ],
             *    [
             *       'code' => ..
             *       'description_it' => ...
             *       'description_en' => ...
             *       'parent_code' => ...
             *       'children' => ...
             *    ],
             * ]
             *
             * OR
             *
             * [
             *    [
             *       'code' => ..
             *       'description' => ...
             *       'parent_code' => ...
             *       'children' => ...
             *    ],
             *    [
             *       'code' => ..
             *       'description' => ...
             *       'parent_code' => ...
             *       'children' => ...
             *    ],
             * ]
             */
            if(empty($terms)){
                $this->log('- ERROR: no terms found for taxonomy');
                continue;
            }
            foreach ($termDBEntries as $DBEntry){
                try{
                    $this->createTerm($DBEntry,$taxonomy);
                }catch (\Exception | \Throwable $e){
                    $this->log('- ERROR: '.$e->getMessage());
                    continue;
                }
            }
        }
    }

    /**
     * @param array $DBEntry
     * @param string $taxonomy
     * @param array $localizedParentsIds an associative array where key are languages and values are term ids. If no multilang the only available key will be '*'
     * @return void
     */
    private function createTerm(array $DBEntry, string $taxonomy, array $localizedParentsIds = []): void
    {
        $createdTerms = [];
        if(Polylang::isPolylang()){
            $defLangTermName = $DBEntry['description_' . $this->defaultLang] ?? '';
            if($defLangTermName === ''){
                throw new CLIRuntimeException('Unable to find default language title inside the db entry');
            }
            $termNames = [];
            foreach ($DBEntry as $k => $v){
                if(strpos($k,'description')){
                    $termNames[$k] = $v;
                }
            }
            if(empty($termNames)){
                throw new CLIRuntimeException('Unable to find term description inside the db entry');
            }
            $termTranslations = [];
            foreach ($termNames as $key => $name){
                //Get the language from $key
                //...
                //Get the parent from $localizedParentsIds[$lang]
                //...
                //Create the term
                //...
                //$termTranslations[$lang] = $termId;
                //$createdTerms[$lang] = $termId;
                //pll_set_term_language()
            }
            pll_save_term_translations($termTranslations);
        }else{
            //Get the parent from $localizedParentsIds['*']
            //...
            //Create the term
            //...
            //$createdTerms['*'] = $termId;
        }
        if(isset($DBEntry['children']) && !empty($createdTerms)){
            foreach ($DBEntry['children'] as $childDBEntry){
                $this->createTerm($childDBEntry,$taxonomy,$createdTerms);
            }
        }
    }

    /**
     * @param string $taxonomy
     * @return array
     * @throws DBException
     */
    function getTermsToCreateForTaxonomy(string $taxonomy): array
    {
        $terms = $this->getAvailableTermsOfTaxonomy($taxonomy);
        if(empty($terms)){
            return [];
        }
        $termTree = $this->buildTermTree($terms);
        //$flattenTree = $this->flatTermTree($termTree);
        return $termTree;
    }

    /**
     * @param string $taxonomy
     * @return array
     * @throws DBException
     */
    private function getAvailableTermsOfTaxonomy(string $taxonomy): array
    {
        $availableTermsResults = Query::on($this->tableName)->select(['*'])->where('taxonomy',$taxonomy)->get()->toArray();
        if(!empty($availableTermsResults)){
            return array_values(json_decode(json_encode($availableTermsResults), true));
        }
        return [];
    }

    /**
     * @param array $elements
     * @param string $parentCode
     * @return array
     */
    private function buildTermTree(array $elements, string $parentCode = ''): array
    {
        $branch = [];
        foreach ($elements as $element) {
            if(!isset($element['parent_code'])){
                $element['parent_code'] = '';
            }
            if ($element['parent_code'] == $parentCode) {
                $children = $this->buildTermTree($elements, $element['code']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }
        return $branch;
    }

    /**
     * @param array $tree
     * @return array
     */
    private function flatTermTree(array $tree): array
    {
        $output = [];
        $flat = function($hierarchy) use (&$output,&$flat){
            foreach($hierarchy as $k => $t){
                $output[] = $t;
                if(isset($t['children'])){
                    $flat($t['children']);
                }
            }
        };
        $flat($tree);
        foreach($output as $k=>$v){
            if(isset($v['children'])){
                unset($output[$k]['children']);
            }
        }
        return $output;
    }
}