<?php

namespace Waboot\inc\core\cli;

class CSVRow
{
	/**
	 * @var array
	 */
	protected array $rowData;

	public function __construct(array $data)
	{
		$this->rowData = $data;
		$this->cleanRowData();
	}

	/**
	 * @return void
	 */
	public function cleanRowData(): void
	{
		array_walk($this->rowData, static function(&$value){
			$value = trim($value);
		});
	}

	/**
	 * @return array|null
	 */
	public function getRowData(): ?array
	{
		return $this->rowData;
	}

	/**
	 * @param string $key
	 * @return string|null
	 */
	public function getStringValue(string $key): ?string
	{
		if(!\is_array($this->getRowData()) || !array_key_exists($key,$this->getRowData())){
			return null;
		}
		return $this->getRowData()[$key];
	}

	/**
	 * @param string $key
	 * @return int|null
	 */
	public function getIntValue(string $key): ?int
	{
		$value = $this->getStringValue($key);
		if(!$value){
			return null;
		}
		return (int) $value;
	}

	/**
	 * @param string $key
	 * @return float|null
	 */
	public function getFloatValue(string $key): ?float
	{
		$value = $this->getStringValue($key);
		if(!$value){
			return null;
		}
		return (float) $value;
	}
}