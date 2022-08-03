<?php

namespace Waboot\addons\packages\shop_rules\rule_params;

use Waboot\addons\packages\shop_rules\ShopRuleException;

class BuyXGetYItem
{
    /**
     * @var int
     */
    protected $productId;
    /**
     * @param int
     */
    protected $quantity;

    public function __construct(int $productId, int $quantity)
    {
        $this->productId = $productId;
        $this->quantity = $quantity;
    }

    /**
     * @throws ShopRuleException
     */
    static function fromArray(array $array): self
    {
        $productId = $array['id'] ?? null;
        if (!is_numeric($productId)) {
            throw new ShopRuleException('Item product ID must be numeric');
        }
        $productId = (int)$productId;

        $quantity = $array['quantity'] ?? null;
        if (!is_numeric($quantity)) {
            throw new ShopRuleException('Item quantity must be numeric');
        }

        $quantity = (int)$quantity;
        if ($quantity < 0) {
            throw new ShopRuleException('Item quantity must be positive');
        }

        return new self($productId, $quantity);
    }

    /**
     * @return int
     */
    public function getProductId(): int
    {
        return $this->productId;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getProductId(),
            'quantity' => $this->getQuantity(),
        ];
    }
}