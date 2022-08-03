<?php

namespace Waboot\addons\packages\shop_rules\rule_params;

use Waboot\addons\packages\shop_rules\ShopRuleException;

class Sale
{
    public const SALE_CRITERIA_CUMULATIVE = 'cumulative';
    public const SALE_CRITERIA_REPLACEMENT = 'replacement';

    public const SALE_TYPE_PERCENTAGE = 'percentage';
    public const SALE_TYPE_FLAT = 'flat';

    /**
     * @var float
     */
    protected $saleValue = 1;
    /**
     * @var string
     */
    protected $saleType = self::SALE_TYPE_PERCENTAGE;
    /**
     * @var string
     */
    protected $saleCriteria = self::SALE_CRITERIA_CUMULATIVE;

    public function __construct()
    {
    }

    /**
     * @throws ShopRuleException
     */
    static function fromArray(array $array): self {
        $sale = new self();

        $value = $array['saleValue'] ?? null;
        if ($value !== null) {
            if (!is_numeric($value)) {
                throw new ShopRuleException('Sale value must be numeric');
            }

            $sale->setSaleValue($value);
        }

        $type = $array['saleType'] ?? null;
        if ($type !== null) {
            if (!self::isValidSaleType($type)) {
                throw new ShopRuleException('Sale type is not valid');
            }

            $sale->setSaleType($type);
        }

        $criteria = $array['saleCriteria'] ?? null;
        if ($criteria !== null) {
            if (!self::isValidSaleCriteria($criteria)) {
                throw new ShopRuleException('Sale criteria is not valid');
            }

            $sale->setSaleCriteria($criteria);
        }

        return $sale;
    }

    public function getSaleValue(): float
    {
        return $this->saleValue;
    }

    public function setSaleValue(float $saleValue): void
    {
        $this->saleValue = $saleValue;
    }

    public function getSaleType(): string
    {
        return $this->saleType;
    }

    public function setSaleType(string $saleType): void
    {
        $this->saleType = $saleType;
    }

    public function getSaleCriteria(): string
    {
        return $this->saleCriteria;
    }

    public function setSaleCriteria(string $saleCriteria): void
    {
        $this->saleCriteria = $saleCriteria;
    }

    public static function isValidSaleCriteria($value): bool
    {
        return \in_array($value, [self::SALE_CRITERIA_CUMULATIVE, self::SALE_CRITERIA_REPLACEMENT], true);
    }

    public static function isValidSaleType($value): bool
    {
        return \in_array($value, [self::SALE_TYPE_FLAT, self::SALE_TYPE_PERCENTAGE], true);
    }

    public function getPercentageMultiplier(): float
    {
        if ($this->getSaleType() !== self::SALE_TYPE_PERCENTAGE) {
            return 1;
        }

        return 1 - ($this->getSaleValue() / 100);
    }

    public function toArray(): array
    {
        return [
            'saleValue' => $this->getSaleValue(),
            'saleType' => $this->getSaleType(),
            'saleCriteria' => $this->getSaleCriteria(),
        ];
    }
}