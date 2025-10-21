<?php

/**
 * @version 21102025
 */

namespace waboot\inc\cli\feeds;

use Waboot\inc\core\cli\CLIRuntimeException;

class GenerateFacebookFeed extends GenerateGShoppingFeed
{
    /**
     * @var string
     */
    protected $logFileName = 'facebook-feed-gen';
    protected string $customOutputFilename = 'facebook-products-feed';

    protected bool $doOverride;
    protected string $overrideCountry;
    protected array $overrideFields;

    public static function getCommandDescription(): array
    {
        $description = parent::getCommandDescription();
        $description['synopsis'][] = [
            'type' => 'flag',
            'name' => 'override',
            'description' => 'Use the override mode',
            'optional' => true,
        ];
        $description['synopsis'][] = [
            'type' => 'assoc',
            'name' => 'override-country',
            'description' => 'Specify the override country',
            'optional' => true,
        ];
        $description['synopsis'][] = [
            'type' => 'assoc',
            'name' => 'override-fields',
            'description' => 'Comma separated fields to override',
            'optional' => true,
        ];
        return $description;
    }

    protected function customInitialization(array $args, array $assoc_args): void
    {
        $this->doOverride = isset($assoc_args['override']);
        if(!$this->doOverride){
            return;
        }
        if(!isset($assoc_args['override-country']) || empty($assoc_args['override-country'])) {
            throw new CLIRuntimeException('override-country parameter is required.');
        }
        $this->overrideCountry = $assoc_args['override-country'];
        if(isset($assoc_args['override-fields']) && !empty($assoc_args['override-fields'])) {
            $overrideFields = explode(',',$assoc_args['override-fields']);
            if(is_array($overrideFields) && !empty($overrideFields)) {
                $overrideFields[] = 'id';
                $this->overrideFields = $overrideFields;
            }
        }
        add_filter('wawoo/cli/genfeeds/generate_record/record', function ($newRecord, \WC_Product $product, ?\WC_Product $parentProduct) {
            if(!empty($this->overrideFields)) {
                $fieldsToCmp = array_flip($this->overrideFields); // ['price' => 0, 'sale_price' => 1, 'id' => 2]
                $filteredRecord = array_intersect_key($newRecord, $fieldsToCmp);
                $filteredRecord['override'] = $this->overrideCountry;
                return $filteredRecord;
            }
            return $newRecord;
        },10,3);
        add_filter('wawoo/cli/genfeeds/xml_file_name', function (string $xmlFileName, array $cliArgs) {
            $xmlFileName = str_replace('.xml','-override-'.$this->overrideCountry.'.xml',$xmlFileName);
            return $xmlFileName;
        },10,2);
    }
}