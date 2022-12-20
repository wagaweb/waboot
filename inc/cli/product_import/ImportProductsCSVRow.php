<?php

namespace Waboot\inc\cli\product_import;

use Waboot\inc\cli\utils\ImportExportCSVColumnHelpers;
use Waboot\inc\core\cli\CSVRow;
use Waboot\inc\core\utils\Utilities;

class ImportProductsCSVRow extends CSVRow
{
    /**
     * @var string
     */
    private $identifier;
    private ?string $productIdentifierColumnName;
    /**
     * @var string
     */
    private $groupId;
    /**
     * @var string
     */
    private $brandTaxonomyName;
    /**
     * @var string
     */
    private $colorAttributeTaxonomyName;
    /**
     * @var string
     */
    private $sizeAttributeTaxonomyName;
    /**
     * @var array
     */
    private $postFields;
    /**
     * @var array
     */
    private $metaFields;
    /**
     * @var array
     */
    private $taxonomyFields;
    /**
     * @var array
     */
    private $attributeFields;
    /**
     * @var string[]
     */
    private $crossSells;
    /**
     * @var string[]
     */
    private $upSells;
    /**
     * @var ImportProductsManifestFile
     */
    private $manifestFile;

    /**
     * @param array $data
     * @param string $productIdentifierColumnName
     * @param ImportProductsManifestFile|null $manifestFile
     */
    public function __construct(array $data, string $productIdentifierColumnName, ImportProductsManifestFile $manifestFile = null)
    {
        parent::__construct($data);
        $this->productIdentifierColumnName = $productIdentifierColumnName;
        $this->manifestFile = $manifestFile;
    }

    /**
     * @return void
     * @throws ImportProductsCSVRowException
     */
    public function parseRowData(): void
    {
        $data = $this->rowData;

        //Initial checks
        foreach ($data as $columnName => $columnValue){
            $isIdentifier = $columnName === $this->productIdentifierColumnName;
            if($isIdentifier){
                $this->identifier = $this->parseColumnValue($columnValue);
                break;
            }
        }

        if(!\is_string($this->getIdentifier()) || $this->getIdentifier() === ''){
            throw new ImportProductsCSVRowException('La riga è priva di un identificatore valido');
        }

        $postsFields = $this->getPostFieldsStandardColumnNames();
        $upsellsColumnName = $this->getActualColumnNameStandardizedColumnName('upsells');
        $crossSellsColumnName = $this->getActualColumnNameStandardizedColumnName('crossells');
        foreach ($data as $columnName => $columnValue){
            if($this->isColumnExcluded($columnName)){
                continue;
            }
            $standardizedColumnName = $this->getStandardizedColumnNameFromActualColumnName($columnName);
            if(!$standardizedColumnName){
                continue;
            }
            $isIdentifier = $columnName === $this->productIdentifierColumnName;
            if($isIdentifier){
                continue;
            }
            $isGroupID = $standardizedColumnName === 'groupId';
            $isPostField = in_array($standardizedColumnName,$postsFields,true);
            $isTaxonomy = ImportExportCSVColumnHelpers::isTaxonomyColumn($standardizedColumnName);
            $isAttribute = ImportExportCSVColumnHelpers::isAttributeColumn($standardizedColumnName);
            $isMeta = ImportExportCSVColumnHelpers::isMetaColumn($standardizedColumnName);
            $isUpsells = $columnName === $upsellsColumnName;
            $isCrossSells = $columnName === $crossSellsColumnName;
            if($isGroupID){
                $this->groupId = $this->parseColumnValue($columnValue);
            }elseif($isPostField){
                $this->postFields[$standardizedColumnName] = $this->parseColumnValue($columnValue);
            }elseif($isTaxonomy){
                $taxInfo = ImportExportCSVColumnHelpers::getTaxonomyInfoFromColumnName($standardizedColumnName);
                if($taxInfo !== null){
                    $this->taxonomyFields[$taxInfo['taxonomy']] = [
                        'value' => $this->parseColumnValue($columnValue),
                        'hierarchical' => $taxInfo['hierarchical']
                    ];
                }
            }elseif($isAttribute){
                $attrInfo = ImportExportCSVColumnHelpers::getAttributeInfoFromColumnName($standardizedColumnName);
                if($attrInfo !== null){
                    $this->attributeFields[$attrInfo['taxonomy']] = [
                        'value' => $this->parseColumnValue($columnValue),
                        'variations' => $attrInfo['variations']
                    ];
                }
            }elseif($isMeta){
                $metaInfo = ImportExportCSVColumnHelpers::getMetaInfoFromColumnName($standardizedColumnName);
                if($metaInfo !== null){
                    $this->metaFields[] = [
                        'key' => $metaInfo['key'],
                        'value' => $this->parseColumnValue($columnValue),
                        'assign_to' => $metaInfo['assign_to']
                    ];
                }
            }elseif($isUpsells){
                $upsells = $this->parseListColumnValue($data[$upsellsColumnName]);
                if(\is_array($upsells)){
                    $this->upSells = $upsells;
                }
            }elseif($isCrossSells){
                $crossSells = $this->parseListColumnValue($data[$crossSellsColumnName]);
                if(\is_array($crossSells)){
                    $this->crossSells = $crossSells;
                }
            }
        }
    }

    /**
     * @return array
     */
    private function getReservedFieldsStandardColumnNames(): array
    {
        return apply_filters('wawoo/product_importer/csv_row/posts_fields',[
            'groupId',
            'upsells',
            'crossells'
        ]);
    }

    /**
     * @return array
     */
    private function getReserveMetaFieldNames(): array
    {
        return apply_filters('wawoo/product_importer/csv_row/reserved_meta_fields_name',[
            '_regular_price',
            '_stock',
            '_stock_status',
            '_sku'
        ]);
    }

    /**
     * @return array
     */
    private function getPostFieldsStandardColumnNames(): array
    {
        return apply_filters('wawoo/product_importer/csv_row/posts_fields',[
            'post_title',
            'post_name',
            'post_content',
            'post_excerpt',
        ]);
    }

    /**
     * @param string $columnName
     * @return string|null
     */
    public function getStandardizedColumnNameFromActualColumnName(string $columnName): ?string
    {
        if($this->hasManifestFile()){
            $standardizedColumnName = $this->manifestFile->getStandardNameOfAColumnName($columnName);
            if(!$standardizedColumnName){
                if(\in_array($standardizedColumnName,$this->getPostFieldsStandardColumnNames(),true)){
                    return $standardizedColumnName;
                }
            }
        }else{
            $standardizedColumnName = $columnName;
        }
        return $standardizedColumnName;
    }

    /**
     * @param string $fieldName
     * @return string
     */
    private function getActualColumnNameStandardizedColumnName(string $fieldName): string
    {
        if($this->hasManifestFile()){
            $currentColumnName = $this->getManifestFile()->getColumnNameFromStandardName($fieldName);
            if($currentColumnName !== null){
                return $currentColumnName;
            }
        }
        return $fieldName;
    }

    /**
     * @return bool
     */
    private function hasManifestFile(): bool
    {
        return isset($this->manifestFile);
    }

    /**
     * @return ImportProductsManifestFile|null
     */
    public function getManifestFile(): ?ImportProductsManifestFile
    {
        return $this->manifestFile;
    }

    /**
     * @return bool
     */
    public function isProductCategoryHierarchical(): bool
    {
        if(!isset($this->taxonomyFields['product_cat'])){
            return false;
        }
        //return $this->isTaxonomyHierarchical($brandTaxonomyName);
        return $this->taxonomyFields['product_cat']['hierarchical'] ?? true;
    }

    /**
     * @return bool
     */
    public function isBrandHierarchical(): bool
    {
        $brandTaxonomyName = $this->getBrandTaxonomyName();
        if(!$brandTaxonomyName){
            return false;
        }
        return $this->isTaxonomyHierarchical($brandTaxonomyName);
    }

    /**
     * @return bool
     */
    private function isSizeAttributeForVariations(): bool
    {
        $sizeTaxonomyName = $this->getSizeAttributeTaxonomyName();
        if(!$sizeTaxonomyName){
            return true;
        }
        return $this->attributeFields[$sizeTaxonomyName]['variations'] ?? true;
    }

    /**
     * @return bool
     */
    private function isColorAttributeForVariations(): bool
    {
        $colorTaxonomyName = $this->getColorAttributeTaxonomyName();
        if(!$colorTaxonomyName){
            return true;
        }
        return $this->attributeFields[$colorTaxonomyName]['variations'] ?? true;
    }

    /**
     * @param $columnName
     * @return bool
     */
    private function isColumnExcluded($columnName): bool
    {
        if($this->hasManifestFile()){
            return $this->getManifestFile()->isExcludedColumn($columnName);
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isIdentifierTheProductSKU(): bool
    {
        if($this->hasManifestFile()){
            $identifierStandardColumnName = $this->getManifestFile()->getStandardNameOfAColumnName($this->productIdentifierColumnName);
            return $identifierStandardColumnName === 'meta:_sku';
        }
        return $this->productIdentifierColumnName === 'meta:_sku';
    }

    /**
     * @param $value
     * @return string|null
     */
    private function parseColumnValue($value): ?string
    {
        if(\is_string($value)){
            return trim($value);
        }
        return null;
    }

    /**
     * @param string $value
     * @param string $separator
     * @return array
     */
    private function parseListColumnValue(string $value, string $separator = ','): ?array
    {
        if($value === ''){
            return null;
        }
        return array_map('trim',explode($separator,$value));
    }

    /**
     * @return string|null
     */
    public function getIdentifier(): ?string
    {
        return $this->identifier ?? null;
    }

    /**
     * @return string|null
     */
    public function getSku(): ?string
    {
        if($this->isIdentifierTheProductSKU()){
            return $this->getIdentifier();
        }
        $sku = $this->getMetaField('_sku');
        if(\is_string($sku) && $sku !== ''){
            return $sku;
        }
        //Try to generate a SKU
        $title = $this->getTitle();
        if(\is_string($title) && $title !== ''){
            $sku = sanitize_title_with_dashes($title);
            if(!$this->isSimpleProductRow()){
                $sku .= '-'.Utilities::getRandomString();
            }
            return $sku;
        }
        //Just return a random string
        return Utilities::getRandomString();
    }

    /**
     * @return string|null
     */
    public function getGroupId(): ?string
    {
        return $this->groupId ?? null;
    }

    /**
     * @return string|null
     * @depecated
     */
    public function getParentSku(): ?string
    {
        return $this->getGroupId();
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->postFields['post_title'] ?? null;
    }

    /**
     * @return string|null
     */
    public function getSlug(): ?string
    {
        return $this->postFields['post_name'] ?? null;
    }

    /**
     * @return float
     */
    public function getRegularPrice(): ?float
    {
        $regularePrice = $this->getMetaField('_regular_price');
        if($regularePrice === null || $regularePrice === ''){
            return null;
        }
        $regularPrice = str_replace(',','.',$regularePrice);
        return (float) $regularPrice;
    }

    /**
     * @return bool
     */
    public function hasRegularPrice(): bool
    {
        return $this->getRegularPrice() !== null;
    }

    /**
     * @return int|null
     */
    public function getStock(): ?int
    {
        $stock = $this->getMetaField('_stock');
        if($stock === null || $stock === ''){
            return null;
        }
        return (int) $stock;
    }

    /**
     * @return string
     */
    public function getStockStatus(): string
    {
        return $this->getStock() === 0 ? 'outofstock' : 'instock';
    }

    /**
     * @return bool
     */
    public function hasStock(): bool
    {
        return $this->getStock() !== null;
    }

    /**
     * @return string
     */
    public function getShortDescription(): string
    {
        return $this->postFields['post_excerpt'] ?? '';
    }

    /**
     * @return string
     */
    public function getLongDescription(): string
    {
        return $this->postFields['post_content'] ?? '';
    }

    /**
     * @return null|string
     */
    public function getBrandTaxonomyName(): ?string
    {
        if(!isset($this->brandTaxonomyName) && $this->hasManifestFile()){
            $brandTaxonomyName = $this->getManifestFile()->getSetting('brand_taxonomy_name');
            if(\is_string($brandTaxonomyName) && $brandTaxonomyName !== ''){
                $this->brandTaxonomyName = $brandTaxonomyName;
            }
        }
        return $this->brandTaxonomyName;
    }

    /**
     * @param string $brandTaxonomyName
     */
    public function setBrandTaxonomyName(string $brandTaxonomyName): void
    {
        $this->brandTaxonomyName = $brandTaxonomyName;
    }

    /**
     * @return null|string
     */
    public function getColorAttributeTaxonomyName(): ?string
    {
        if(!isset($this->colorAttributeTaxonomyName) && $this->hasManifestFile()){
            $colorAttributeTaxonomyName = $this->getManifestFile()->getSetting('color_taxonomy_name');
            if(\is_string($colorAttributeTaxonomyName) && $colorAttributeTaxonomyName !== ''){
                $this->colorAttributeTaxonomyName = $colorAttributeTaxonomyName;
            }
        }
        return $this->colorAttributeTaxonomyName;
    }

    /**
     * @param string $colorAttributeTaxonomyName
     */
    public function setColorAttributeTaxonomyName(string $colorAttributeTaxonomyName): void
    {
        $this->colorAttributeTaxonomyName = $colorAttributeTaxonomyName;
    }

    /**
     * @return null|string
     */
    public function getSizeAttributeTaxonomyName(): ?string
    {
        if(!isset($this->sizeAttributeTaxonomyName) && $this->hasManifestFile()){
            $sizeAttributeTaxonomyName = $this->getManifestFile()->getSetting('size_taxonomy_name');
            if(\is_string($sizeAttributeTaxonomyName) && $sizeAttributeTaxonomyName !== ''){
                $this->sizeAttributeTaxonomyName = $sizeAttributeTaxonomyName;
            }
        }
        return $this->sizeAttributeTaxonomyName;
    }

    /**
     * @param string $sizeAttributeTaxonomyName
     */
    public function setSizeAttributeTaxonomyName(string $sizeAttributeTaxonomyName): void
    {
        $this->sizeAttributeTaxonomyName = $sizeAttributeTaxonomyName;
    }

    /**
     * @return string|null
     */
    public function getSize(): ?string
    {
        $sizeTaxonomyName = $this->getSizeAttributeTaxonomyName();
        if(!$sizeTaxonomyName){
            return null;
        }
        return $this->attributeFields[$sizeTaxonomyName]['value'] ?? null;
    }

    /**
     * @return bool
     */
    public function hasSize(): bool
    {
        return $this->getSize() !== null && $this->getSize() !== '';
    }

    /**
     * @return string|null
     */
    public function getColor(): ?string
    {
        $colorTaxonomyName = $this->getColorAttributeTaxonomyName();
        if(!$colorTaxonomyName){
            return null;
        }
        return $this->attributeFields[$colorTaxonomyName]['value'] ?? null;
    }

    /**
     * @return bool
     */
    public function hasColor(): bool
    {
        return $this->getColor() !== null && $this->getColor() !== '';
    }

    /**
     * @param string $taxonomyName
     * @return string|null
     */
    public function getAttribute(string $taxonomyName): ?string
    {
        return $this->attributeFields[$taxonomyName]['value'] ?? null;
    }

    /**
     * @param string $taxonomyName
     * @return bool
     */
    public function hasAttribute(string $taxonomyName): bool
    {
        return $this->getAttribute($taxonomyName) !== null && $this->getAttribute($taxonomyName) !== '';
    }

    /**
     * @return string|null
     */
    public function getCategory(): ?string
    {
        return $this->taxonomyFields['product_cat']['value'] ?? null;
    }

    /**
     * @return bool
     */
    public function hasCategory(): bool
    {
        return $this->getCategory() !== null && $this->getCategory() !== '';
    }

    /**
     * @return string|null
     */
    public function getBrand(): ?string
    {
        $brandTaxonomyName = $this->getBrandTaxonomyName();
        if(!$brandTaxonomyName){
            return null;
        }
        return $this->taxonomyFields[$brandTaxonomyName]['value'] ?? null;
    }

    /**
     * @return bool
     */
    public function hasBrand(): bool
    {
        return $this->getBrand() !== null && $this->getBrand() !== '';
    }

    /**
     * @param string $taxonomyName
     * @return bool
     */
    public function isTaxonomyHierarchical(string $taxonomyName): bool
    {
        //return $this->taxonomyFields[$brandTaxonomyName]['hierarchical'] ?? false;
        return isset($this->taxonomyFields[$taxonomyName],$this->taxonomyFields[$taxonomyName]['hierarchical']) && $this->taxonomyFields[$taxonomyName]['hierarchical'];
    }

    /**
     * @return string[]
     */
    public function getUpsells(): array
    {
        if(!isset($this->upSells)){
            $this->upSells = [];
        }
        return $this->upSells;
    }

    /**
     * @return string[]
     */
    public function getCrossSells(): array
    {
        if(!isset($this->crossSells)){
            $this->crossSells = [];
        }
        return $this->crossSells;
    }

    /**
     * @return array
     */
    public function getTaxonomies(): array
    {
        if(!isset($this->taxonomyFields) || !\is_array($this->taxonomyFields)){
            $this->taxonomyFields = [];
        }
        return $this->taxonomyFields;
    }

    /**
     * @return array
     */
    public function getCustomAttributes(): array
    {
        if(!isset($this->attributeFields) || !\is_array($this->attributeFields)){
            $this->attributeFields = [];
        }
        return array_filter($this->attributeFields, fn($taxonomyName) => !\in_array($taxonomyName,[$this->sizeAttributeTaxonomyName,$this->colorAttributeTaxonomyName],true), ARRAY_FILTER_USE_KEY);
    }

    /**
     * @return array
     */
    public function getAttributesForVariableAndSimpleProducts(): array
    {
        return array_filter($this->getCustomAttributes(), fn($attributeData) => $attributeData['variations'] === false);
    }

    /**
     * @return array
     */
    public function getMetaFields(): array
    {
        if(!isset($this->metaFields)){
            $this->metaFields = [];
        }
        return $this->metaFields;
    }

    /**
     * @return array
     */
    public function getCustomMetaFields(): array
    {
        $metaFields = $this->getMetaFields();
        return array_filter($metaFields,fn($meta) => !\in_array($meta['key'],$this->getReserveMetaFieldNames()));
    }

    /**
     * @param string $metaKey
     * @return string|null
     */
    public function getMetaField(string $metaKey): ?string
    {
        $metas = $this->getMetaFields();
        $metaValue = null;
        foreach ($metas as $metaFieldData){
            if($metaFieldData['key'] === $metaKey){
                $metaValue = $metaFieldData['value'];
                break;
            }
        }
        return $metaValue;
    }

    /**
     * @return bool
     */
    public function isSimpleProductRow(): bool
    {
        return $this->getGroupId() === '' || $this->getGroupId() === null;
    }
}
