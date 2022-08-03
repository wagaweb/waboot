<?php

namespace Waboot\addons\packages\shop_rules\rule_params;

use Waboot\addons\packages\shop_rules\ShopRuleException;

use function Waboot\addons\packages\shop_rules\strToBool;

class BuyXGetY
{
    const ADD_TO_CART_CRITERIA_CHOICE = 'choice';
    const ADD_TO_CART_CRITERIA_AUTO = 'auto';

    const PRODUCT_PAGE_MESSAGE_LAYOUT_LIST = 'list';
    const PRODUCT_PAGE_MESSAGE_LAYOUT_MESSAGE = 'message';
    const PRODUCT_PAGE_MESSAGE_LAYOUT_HIDDEN = 'hidden';

    const CHOICE_CRITERIA_PER_ORDER = 'per-order';
    const CHOICE_CRITERIA_PER_MATCHED_PRODUCT = 'per-matched-product';

    /**
     * @var array<int, BuyXGetYItem>
     */
    protected $items = [];
    /**
     * @var float
     */
    protected $minOrderTotal = 0.0;
    /**
     * @var float
     */
    protected $maxOrderTotal = 0.0;
    /**
     * @var bool
     */
    protected $calculatesTotalOnlyBetweenMatchedProducts = false;
    /**
     * @var int
     */
    protected $minMatchedProductCount = 1;
    /**
     * @var bool
     */
    protected $countMatchedProductsOnce = false;
    /**
     * @var string
     */
    protected $addToCartCriteria = self::ADD_TO_CART_CRITERIA_AUTO;
    /**
     * @var int
     */
    protected $maxNumberOfProductsToChoose = 1;
    /**
     * @var string
     */
    protected $choiceCriteria = self::CHOICE_CRITERIA_PER_ORDER;
    /**
     * @var string
     */
    protected $productPageMessageLayout = self::PRODUCT_PAGE_MESSAGE_LAYOUT_LIST;
    /**
     * @var string
     */
    protected $productPageMessage = '';
    /**
     * @var bool
     */
    protected $giftLowerPricedMatchedProduct = false;
    /**
     * @var string[]
     */
    protected $allowedRole = [];

    public function __construct()
    {
    }

    /**
     * @throws ShopRuleException
     */
    static function fromArray(array $array): self
    {
        $param = new self();

        $items = $array['products'] ?? null;
        if ($items !== null) {
            if (!is_array($items)) {
                throw new ShopRuleException('BuyXGetY products param must be array');
            }

            foreach ($items as $i) {
                if (!is_array($i)) {
                    throw new ShopRuleException('BuyXGetY products item must be array');
                }

                $param->addItem(BuyXGetYItem::fromArray($i));
            }
        }

        $minOrderTotal = $array['minOrderTotal'] ?? null;
        if ($minOrderTotal !== null) {
            if (!is_numeric($minOrderTotal)) {
                throw new ShopRuleException('Minimum order total must be numeric');
            }

            $minOrderTotal = (float)$minOrderTotal;
            if ($minOrderTotal < 0) {
                throw new ShopRuleException('Minimum order total must be positive');
            }

            $param->setMinOrderTotal($minOrderTotal);
        }

        $maxOrderTotal = $array['maxOrderTotal'] ?? null;
        if ($maxOrderTotal !== null) {
            if (!is_numeric($maxOrderTotal)) {
                throw new ShopRuleException('Maximum order total must be numeric');
            }

            $param->setMaxOrderTotal($maxOrderTotal);
        }

        $calculatesTotalOnlyBetweenMatchedProducts = $array['calculatesTotalOnlyBetweenMatchedProducts'] ?? null;
        if ($calculatesTotalOnlyBetweenMatchedProducts !== null) {
            $param->setCalculatesTotalOnlyBetweenMatchedProducts(strToBool($calculatesTotalOnlyBetweenMatchedProducts));
        }

        $minMatchedProductCount = $array['minMatchedProductCount'] ?? null;
        if ($minMatchedProductCount !== null) {
            if (!is_numeric($minMatchedProductCount)) {
                throw new ShopRuleException('Minimum matched product count must be numeric');
            }

            $minMatchedProductCount = (int)$minMatchedProductCount;
            if ($minMatchedProductCount < 1) {
                throw new ShopRuleException('Minimum matched product count must be greater than 0');
            }

            $param->setMinMatchedProductCount($minMatchedProductCount);
        }

        $countMatchedProductsOnce = $array['countMatchedProductsOnce'] ?? null;
        if ($countMatchedProductsOnce !== null) {
            $param->setCountMatchedProductsOnce(strToBool($countMatchedProductsOnce));
        }

        $addToCartCriteria = $array['addToCartCriteria'] ?? null;
        if ($addToCartCriteria !== null) {
            if (!self::isValidAddToCartCriteria($addToCartCriteria)) {
                throw new ShopRuleException('Provided add to cart criteria is not valid');
            }

            $param->setAddToCartCriteria($addToCartCriteria);
        }

        $maxNumberOfProductsToChoose = $array['maxNumberOfProductsToChoose'] ?? null;
        if ($maxNumberOfProductsToChoose !== null) {
            if (!is_numeric($maxNumberOfProductsToChoose)) {
                throw new ShopRuleException('Maximum number of products to choose must be numeric');
            }

            $maxNumberOfProductsToChoose = (int)$maxNumberOfProductsToChoose;
            if ($maxNumberOfProductsToChoose < 1) {
                throw new ShopRuleException('Maximum number of products to choose must be greater than 0');
            }

            $param->setMaxNumberOfProductsToChoose($maxNumberOfProductsToChoose);
        }

        $choiceCriteria = $ruleData['choiceCriteria'] ?? null;
        if ($choiceCriteria !== null) {
            if (!self::isValidChoiceCriteria($choiceCriteria)) {
                throw new ShopRuleException('Choice criteria is not valid');
            }

            $param->setChoiceCriteria($choiceCriteria);
        }

        $giftLowerPricedMatchedProduct = $array['giftLowerPricedMatchedProduct'] ?? null;
        if ($giftLowerPricedMatchedProduct !== null) {
            $param->setGiftLowerPricedMatchedProduct(strToBool($giftLowerPricedMatchedProduct));
        }

        $productPageMessage = $array['productPageMessage'] ?? null;
        if ($productPageMessage !== null) {
            if (!is_string($productPageMessage)) {
                throw new ShopRuleException('Product page message must be string');
            }

            $param->setProductPageMessage($productPageMessage);
        }

        $productPageMessageLayout = $array['productPageMessageLayout'] ?? null;
        if ($productPageMessageLayout !== null) {
            if (!self::isValidProductPageMessageLayout($productPageMessageLayout)) {
                throw new ShopRuleException('Product page message layout is not valid');
            }

            $param->setProductPageMessageLayout($productPageMessageLayout);
        }

        $allowedRole = $array['allowedRole'] ?? null;
        if ($allowedRole !== null) {
            if (!is_array($allowedRole)) {
                throw new ShopRuleException('Allowed role must be array');
            }

            $param->setAllowedRole($allowedRole);
        }

        return $param;
    }

    /**
     * @return BuyXGetYItem[]
     */
    public function getItems(): array
    {
        return array_values($this->items);
    }

    /**
     * @return array<int, BuyXGetYItem>
     */
    public function getItemMap(): array
    {
        return $this->items;
    }

    public function addItem(BuyXGetYItem $item): void
    {
        $this->items[$item->getProductId()] = $item;
    }

    public function getMinOrderTotal(): float
    {
        return $this->minOrderTotal;
    }

    public function setMinOrderTotal(float $minOrderTotal): void
    {
        $this->minOrderTotal = $minOrderTotal;
    }

    public function getMaxOrderTotal(): float
    {
        return $this->maxOrderTotal;
    }

    public function setMaxOrderTotal(float $maxOrderTotal): void
    {
        $this->maxOrderTotal = $maxOrderTotal;
    }

    public function getCalculatesTotalOnlyBetweenMatchedProducts(): bool
    {
        return $this->calculatesTotalOnlyBetweenMatchedProducts;
    }

    public function setCalculatesTotalOnlyBetweenMatchedProducts(bool $calculatesTotalOnlyBetweenMatchedProducts): void
    {
        $this->calculatesTotalOnlyBetweenMatchedProducts = $calculatesTotalOnlyBetweenMatchedProducts;
    }

    public function getMinMatchedProductCount(): int
    {
        return $this->minMatchedProductCount;
    }

    public function setMinMatchedProductCount(int $minMatchedProductCount): void
    {
        $this->minMatchedProductCount = $minMatchedProductCount;
    }

    public function getAddToCartCriteria(): string
    {
        return $this->addToCartCriteria;
    }

    public function setAddToCartCriteria(string $addToCartCriteria): void
    {
        $this->addToCartCriteria = $addToCartCriteria;
    }

    public function getCountMatchedProductsOnce(): bool
    {
        return $this->countMatchedProductsOnce;
    }

    public function setCountMatchedProductsOnce(bool $countMatchedProductsOnce): void
    {
        $this->countMatchedProductsOnce = $countMatchedProductsOnce;
    }

    public function getProductPageMessage(): string
    {
        return $this->productPageMessage;
    }

    public function setProductPageMessage(string $message): void
    {
        $this->productPageMessage = $message;
    }

    public function getProductPageMessageLayout(): string
    {
        return $this->productPageMessageLayout;
    }

    public function setProductPageMessageLayout(string $productPageMessageLayout): void
    {
        $this->productPageMessageLayout = $productPageMessageLayout;
    }

    public function getMaxNumberOfProductsToChoose(): int
    {
        return $this->maxNumberOfProductsToChoose;
    }

    public function setMaxNumberOfProductsToChoose(int $maxNumberOfProductsToChoose): void
    {
        $this->maxNumberOfProductsToChoose = $maxNumberOfProductsToChoose;
    }

    public function getAllowedRole(): array
    {
        return $this->allowedRole;
    }

    public function setAllowedRole(array $allowedRole): void
    {
        $this->allowedRole = $allowedRole;
    }

    public function getChoiceCriteria(): string
    {
        return $this->choiceCriteria;
    }

    public function setChoiceCriteria(string $choiceCriteria): void
    {
        $this->choiceCriteria = $choiceCriteria;
    }

    public function setGiftLowerPricedMatchedProduct(bool $giftLowerPricedMatchedProduct): void
    {
        $this->giftLowerPricedMatchedProduct = $giftLowerPricedMatchedProduct;
    }

    public function getGiftLowerPricedMatchedProduct(): bool
    {
        return $this->giftLowerPricedMatchedProduct;
    }

    public static function isValidAddToCartCriteria($value): bool
    {
        return \in_array($value, [self::ADD_TO_CART_CRITERIA_CHOICE, self::ADD_TO_CART_CRITERIA_AUTO], true);
    }

    public static function isValidProductPageMessageLayout($value): bool
    {
        return \in_array(
            $value,
            [
                self::PRODUCT_PAGE_MESSAGE_LAYOUT_LIST,
                self::PRODUCT_PAGE_MESSAGE_LAYOUT_MESSAGE,
                self::PRODUCT_PAGE_MESSAGE_LAYOUT_HIDDEN,
            ],
            true
        );
    }

    public static function isValidChoiceCriteria(string $value): bool
    {
        return in_array(
            $value,
            [
                self::CHOICE_CRITERIA_PER_ORDER,
                self::CHOICE_CRITERIA_PER_MATCHED_PRODUCT
            ],
            true
        );
    }

    public function toArray(): array
    {
        $array = [
            'products' => [],
            'minOrderTotal' => $this->getMinOrderTotal(),
            'maxOrderTotal' => $this->getMaxOrderTotal(),
            'calculatesTotalOnlyBetweenMatchedProducts' => $this->getCalculatesTotalOnlyBetweenMatchedProducts(),
            'minMatchedProductCount' => $this->getMinMatchedProductCount(),
            'countMatchedProductsOnce' => $this->getCountMatchedProductsOnce(),
            'addToCartCriteria' => $this->getAddToCartCriteria(),
            'maxNumberOfProductsToChoose' => $this->getMaxNumberOfProductsToChoose(),
            'choiceCriteria' => $this->getChoiceCriteria(),
            'giftLowerPricedMatchedProduct' => $this->getGiftLowerPricedMatchedProduct(),
            'productPageMessage' => $this->getProductPageMessage(),
            'productPageMessageLayout' => $this->getProductPageMessageLayout(),
            'allowedRole' => $this->getAllowedRole(),
        ];

        foreach ($this->getItems() as $i) {
            $array['products'][] = $i->toArray();
        }

        return $array;
    }
}
