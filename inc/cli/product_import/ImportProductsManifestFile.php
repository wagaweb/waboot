<?php

namespace Waboot\inc\cli\product_import;

class ImportProductsManifestFile
{
    /**
     * @var string
     */
    private $pathName;
    /**
     * @var array
     */
    private $jsonData;
    /**
     * @var array
     */
    private $columns;
    /**
     * @var array
     */
    private $excludedColumns;
    /**
     * @var array
     */
    private $columnsNameMapping;
    /**
     * @var array
     */
    private $settings;

    /**
     * @param string $pathName
     * @throws ImportProductsManifestFileException
     */
    public function __construct(string $pathName)
    {
        if(!\is_file($pathName)){
            throw new ImportProductsManifestFileException(sprintf('Unable to find the file: %s',$pathName));
        }
        $this->pathName = $pathName;
        $this->parseFile();
    }

    /**
     * @return string
     */
    public function getPathName(): string
    {
        return $this->pathName;
    }

    /**
     * @return void
     * @throws ImportProductsManifestFileException
     */
    private function parseFile(): void
    {
        $data = \json_decode($this->pathName, true);
        if (\JSON_ERROR_NONE !== \json_last_error()) {
            throw new ImportProductsManifestFileException('json_decode error: ' . \json_last_error_msg());
        }
        $this->jsonData = $data;
        if(isset($data['settings'])){
            $this->excludedColumns = $data['settings'];
        }
        if(isset($data['exclude_columns'])){
            $this->excludedColumns = $data['exclude_columns'];
        }
        if(isset($data['columns'])){
            $this->columns = $data['columns'];
        }
    }

    /**
     * @return array
     */
    public function getColumns(): array
    {
        if(!isset($this->columns)){
            $this->columns = [];
        }
        return $this->columns;
    }

    /**
     * @return array
     */
    public function getExcludedColumns(): array
    {
        if(!isset($this->excludedColumns)){
            $this->excludedColumns = [];
        }
        return $this->excludedColumns;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function isExcludedColumn(string $name): bool
    {
        return \in_array($name,$this->getExcludedColumns(),true);
    }

    /**
     * @param string $standardName
     * @return string|null
     */
    public function getColumnNameFromStandardName(string $standardName): ?string
    {
        return $this->columnsNameMapping[$standardName] ?? null;
    }

    /**
     * @param string $columnName
     * @return string|null
     */
    public function getStandardNameOfAColumnName(string $columnName): ?string
    {
        if(empty($this->getColumns())){
            return null;
        }
        foreach ($this->getColumns() as $columnData){
            if(!isset($columnData['name'],$columnData['standard_name'])){
                continue;
            }
            if($columnData['name'] === $columnName){
                return $columnData['standard_name'];
            }
        }
        return null;
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        if(!isset($this->settings)){
            $this->settings = [];
        }
        return $this->settings;
    }

    /**
     * @param string $settingName
     * @return string|null
     */
    public function getSetting(string $settingName): ?string
    {
        return $this->getSettings()[$settingName] ?? null;
    }
}
