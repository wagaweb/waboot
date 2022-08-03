<?php

namespace Waboot\addons\packages\shop_rules\rule_params;

use Waboot\addons\packages\shop_rules\ShopRuleException;

class Discount
{
    public const FLAT = 'flat';
    public const PERCENTAGE = 'percentage';

    /** @var string */
    public $type = self::FLAT;

    /** @var float */
    public $amount = 0;

    /** @var string */
    public $label = '';

    public function __construct()
    {
    }

    /**
     * @throws ShopRuleException
     */
    static function fromArray(array $array): self
    {
        $discount = new self();
        $type = $array['type'] ?? null;
        if ($type !== null) {
            if (!self::isTypeValid($type)) {
                throw new ShopRuleException('Invalid discount type');
            }

            $discount->setType($type);
        }

        $amount = $array['amount'] ?? null;
        if ($amount !== null) {
            if (!is_numeric($amount)) {
                throw new ShopRuleException('Discount amount must be numeric');
            }

            $amount = (float)$amount;
            if ($amount < 0) {
                throw new ShopRuleException('Discount amount must be positive');
            }

            $discount->setAmount($amount);
        }

        $label = $array['label'] ?? null;
        if ($label !== null) {
            if (!is_string($label)) {
                throw new ShopRuleException('Discount label must be string');
            }

            $discount->setLabel($label);
        }

        return $discount;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): void
    {
        $this->amount = $amount < 0 ? 0 : $amount;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->getType(),
            'amount' => $this->getAmount(),
            'label' => $this->getLabel(),
        ];
    }

    static function isTypeValid(string $type): bool
    {
        return in_array($type, [self::FLAT, self::PERCENTAGE]);
    }
}