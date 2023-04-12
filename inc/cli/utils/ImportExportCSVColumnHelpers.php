<?php

namespace Waboot\inc\cli\utils;

class ImportExportCSVColumnHelpers
{
    public const METADATA_MODIFIER_ONLY_PARENT = 'parent';
    public const METADATA_MODIFIER_ONLY_VARIATION = 'variations';
    public const METADATA_MODIFIER_BOTH_PARENT_AND_VARIATIONS = 'both';

    /**
     * @param string $columnName
     * @return bool
     */
    static function isTaxonomyColumn(string $columnName): bool
    {
        return strpos($columnName,'taxonomy:') === 0;
    }

    /**
     * @param string $columnName
     * @return bool
     */
    static function isAttributeColumn(string $columnName): bool
    {
        return strpos($columnName,'attribute:') === 0;
    }

    /**
     * @param string $columnName
     * @return bool
     */
    static function isMetaColumn(string $columnName): bool
    {
        return strpos($columnName,'meta:') === 0;
    }

    /**
     * @param string $columnName
     * @return array|null
     */
    static function getTaxonomyInfoFromColumnName(string $columnName): ?array
    {
        $info = [];
        $taxonomyRegEx = preg_match('|taxonomy:([_a-zA-Z0-9]+)|',$columnName,$matches);
        if(isset($matches) && count($matches) > 1){
            $info['taxonomy'] = $matches[1];
        }else{
            return null;
        }
        if(strpos($columnName,':hierarchical') !== false){
            $info['hierarchical'] = true;
        }else{
            $info['hierarchical'] = false;
        }
        return $info;
    }

    /**
     * @param string $columnName
     * @return array|null
     */
    static function getAttributeInfoFromColumnName(string $columnName): ?array
    {
        $info = [];
        $attributeRegEx = preg_match('|attribute:([_a-zA-Z0-9-]+)|',$columnName,$matches);
        if(isset($matches) && count($matches) > 1){
            $info['taxonomy'] = $matches[1];
        }else{
            return null;
        }
        if(strpos($columnName,':variations') !== false){
            $info['variations'] = true;
        }else{
            $info['variations'] = false;
        }
        return $info;
    }

    /**
     * @param string $columnName
     * @return array|null
     */
    static function getMetaInfoFromColumnName(string $columnName): ?array
    {
        $info = [];
        $metaKeyRegEx = preg_match('/meta:([_a-zA-Z0-9]+):?(variations|parent|both)?/',$columnName,$matches);
        if(isset($matches) && count($matches) > 1){
            $info['key'] = $matches[1];
        }else{
            return null;
        }
        if(isset($matches[2]) && \in_array($matches[2],[self::METADATA_MODIFIER_BOTH_PARENT_AND_VARIATIONS,self::METADATA_MODIFIER_ONLY_PARENT,self::METADATA_MODIFIER_ONLY_VARIATION])){
            $info['assign_to'] = $matches[2];
        }else{
            $info['assign_to'] = self::METADATA_MODIFIER_ONLY_PARENT;
        }
        return $info;
    }
}