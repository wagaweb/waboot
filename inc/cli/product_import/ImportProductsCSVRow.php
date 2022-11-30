<?php

namespace Waboot\inc\cli\product_import;

use Waboot\inc\cli\utils\ImportExportCSVColumnHelpers;
use Waboot\inc\core\utils\Utilities;

class ImportProductsCSVRow
{
    /**
     * @var string
     */
    private $identifier;
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
     * @var array
     */
    private $rowData;
    /**
     * @var ImportProductsManifestFile
     */
    private $manifestFile;

    /**
     * @param array $data
     * @param ImportProductsManifestFile|null $manifestFile
     */
    public function __construct(array $data, ImportProductsManifestFile $manifestFile = null)
    {
        $this->rowData = $data;
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
            $standardizedColumnName = $this->getStandardizedColumnNameFromActualColumnName($columnName);
            if(!$standardizedColumnName){
                continue;
            }
            $isIdentifier = $standardizedColumnName === 'identifier';
            if($isIdentifier){
                $this->identifier = $this->parseColumnValue($columnValue);
            }
        }

        if(!\is_string($this->getIdentifier()) || $this->getIdentifier() === ''){
            throw new ImportProductsCSVRowException('La riga è priva di un identificatore valido');
        }

        $postsFields = $this->getPostFieldsStandardColumnNames();
        foreach ($data as $columnName => $columnValue){
            if($this->isColumnExcluded($columnName)){
                continue;
            }
            $standardizedColumnName = $this->getStandardizedColumnNameFromActualColumnName($columnName);
            if(!$standardizedColumnName){
                continue;
            }
            $isGroupID = $standardizedColumnName === 'groupId';
            $isPostField = in_array($standardizedColumnName,$postsFields,true);
            $isTaxonomy = ImportExportCSVColumnHelpers::isTaxonomyColumn($standardizedColumnName);
            $isAttribute = ImportExportCSVColumnHelpers::isAttributeColumn($standardizedColumnName);
            $isMeta = ImportExportCSVColumnHelpers::isMetaColumn($standardizedColumnName);
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
            }
        }

        $upsellsColumnName = $this->getActualColumnNameStandardizedColumnName('upsells');
        $crossSellsColumnName = $this->getActualColumnNameStandardizedColumnName('crossells');
        $upsells = $this->parseListColumnValue($data[$upsellsColumnName]);
        $crossSells = $this->parseListColumnValue($data[$crossSellsColumnName]);
        if(\is_array($upsells)){
            $this->upSells = $upsells;
        }
        if(\is_array($crossSells)){
            $this->crossSells = $crossSells;
        }
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
    private function getStandardizedColumnNameFromActualColumnName(string $columnName): ?string
    {
        if($this->hasManifestFile()){
            $standardizedColumnName = $this->manifestFile->getStandardNameOfAColumnName($columnName);
            if(!$standardizedColumnName){
                if(
                    \in_array($standardizedColumnName,$this->getPostFieldsStandardColumnNames(),true) ||
                    \in_array($standardizedColumnName,$this->getTaxonomyFieldsStandardColumnNames(),true) ||
                    \in_array($standardizedColumnName,$this->getAttributeFieldsStandardColumnNames(),true)
                ){
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
    private function isProductCategoryHierarchical(): bool
    {
        if(!isset($this->taxonomyFields['product_cat'])){
            return false;
        }
        return $this->taxonomyFields['product_cat']['hierarchical'] ?? true;
    }

    /**
     * @return bool
     */
    private function isBrandHierarchical(): bool
    {
        $brandTaxonomyName = $this->getBrandTaxonomyName();
        if(!$brandTaxonomyName){
            return false;
        }
        return $this->taxonomyFields[$brandTaxonomyName]['hierarchical'] ?? false;
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
    private function isIdentifierTheProductSKU(): bool
    {
        if($this->hasManifestFile()){
            $productIdentifier = $this->getManifestFile()->getSetting('product_identifier') ?? '_sku';
        }else{
            $productIdentifier = '_sku';
        }
        return $productIdentifier === '_sku';
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
        $sku = $this->metaFields['_sku'] ?? null;
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
        if(!isset($this->metaFields['_regular_price']) || $this->metaFields['_regular_price'] === ''){
            return null;
        }
        $regularPrice = str_replace(',','.',$this->metaFields['_regular_price']);
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
        if(!isset($this->metaFields['_stock']) || $this->metaFields['_stock'] === ''){
            return null;
        }
        return (int) $this->metaFields['_stock'];
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
        return $this->getSize() !== null;
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
        return $this->getColor();
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
        return $this->getCategory() !== null;
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
        return $this->getBrand() !== null;
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
    public function getMetaFields(): array
    {
        if(!isset($this->metaFields)){
            return $this->metaFields = [];
        }
        return $this->metaFields;
    }

    /**
     * @return bool
     */
    public function isSimpleProductRow(): bool
    {
        return $this->getGroupId() === '' || $this->getGroupId() === null;
    }
}
