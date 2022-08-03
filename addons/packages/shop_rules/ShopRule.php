<?php

namespace Waboot\addons\packages\shop_rules;

use Waboot\addons\packages\shop_rules\rule_params\BuyXGetY;
use Waboot\addons\packages\shop_rules\rule_params\Discount;
use Waboot\addons\packages\shop_rules\rule_params\JoinTaxonomy;
use Waboot\addons\packages\shop_rules\rule_params\Sale;

class ShopRule
{
    public const TYPE_BUY_X_GET_Y = 'buy-x-get-y';
    public const TYPE_CART_ADJUSTMENT = 'cart-adjustment';
    public const TYPE_JOIN_TAXONOMY = 'join-taxonomy';
    public const TYPE_SALE = 'sale';
    /**
     * @var int
     */
    protected $id;
    /**
     * @var string
     */
    protected $name;
    /**
     * @var int
     */
    protected $order = 1;
    /**
     * @var bool
     */
    protected $enabled = false;
    /**
     * @var \DateTime
     */
    protected $dateFrom;
    /**
     * @var \DateTimeZone
     */
    protected $dateFromTimezone;
    /**
     * @var \DateTime
     */
    protected $dateTo;
    /**
     * @var \DateTimeZone
     */
    protected $dateToTimezone;
    /**
     * @var string
     */
    protected $type;
    /**
     * @var ShopRuleTaxFilter[]
     */
    protected $taxFilters = [];
    /**
     * @var JoinTaxonomy|null
     */
    protected $joinTaxParam;
    /**
     * @var BuyXGetY|null
     */
    protected $buyXgetYParam;
    /**
     * @var Sale|null
     */
    protected $saleParam;
    /**
     * @var Discount|null
     */
    protected $discountParam;

    public function __construct(string $name, string $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * @return bool
     */
    public function hasId(): bool
    {
        return $this->id !== null;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return $this->order ?? 1;
    }

    /**
     * @param int $order
     */
    public function setOrder(int $order): void
    {
        $this->order = $order;
    }

    public function getKey(): string
    {
        return sanitize_title($this->getName());
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * @return \DateTime
     */
    public function getDateFrom(): \DateTime
    {
        return $this->dateFrom;
    }

    /**
     * @param \DateTime $dateFrom
     */
    public function setDateFrom(\DateTime $dateFrom): void
    {
        $this->dateFrom = $dateFrom;
    }

    /**
     * @return \DateTimeZone
     */
    public function getDateFromTimezone(): \DateTimeZone
    {
        return $this->dateFromTimezone;
    }

    /**
     * @param \DateTimeZone $dateFromTimezone
     */
    public function setDateFromTimezone(\DateTimeZone $dateFromTimezone): void
    {
        $this->dateFromTimezone = $dateFromTimezone;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateTo(): ?\DateTime
    {
        return $this->dateTo;
    }

    /**
     * @return string
     */
    public function getTimeZoneName(): string
    {
        return $this->getDateFromTimezone()->getName();
    }

    /**
     * @param \DateTime $dateTo
     */
    public function setDateTo(\DateTime $dateTo): void
    {
        $this->dateTo = $dateTo;
    }

    /**
     * @return \DateTimeZone
     */
    public function getDateToTimezone(): \DateTimeZone
    {
        return $this->dateToTimezone;
    }

    /**
     * @param \DateTimeZone $dateToTimezone
     */
    public function setDateToTimezone(\DateTimeZone $dateToTimezone): void
    {
        $this->dateToTimezone = $dateToTimezone;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return ShopRuleTaxFilter[]
     */
    public function getTaxFilters(): array
    {
        return $this->taxFilters;
    }

    /**
     * @param ShopRuleTaxFilter[] $taxFilters
     */
    public function setTaxFilters(array $taxFilters): void
    {
        $this->taxFilters = $taxFilters;
    }

    /**
     * @return JoinTaxonomy|null
     */
    public function getJoinTaxParam(): ?JoinTaxonomy
    {
        return $this->joinTaxParam;
    }

    /**
     * @param JoinTaxonomy|null $joinTaxParam
     */
    public function setJoinTaxParam(?JoinTaxonomy $joinTaxParam): void
    {
        $this->joinTaxParam = $joinTaxParam;
    }

    /**
     * @return BuyXGetY|null
     */
    public function getBuyXgetYParam(): ?BuyXGetY
    {
        return $this->buyXgetYParam;
    }

    /**
     * @param BuyXGetY|null $buyXgetYParam
     */
    public function setBuyXgetYParam(?BuyXGetY $buyXgetYParam): void
    {
        $this->buyXgetYParam = $buyXgetYParam;
    }

    /**
     * @return Sale|null
     */
    public function getSaleParam(): ?Sale
    {
        return $this->saleParam;
    }

    /**
     * @param Sale|null $saleParam
     */
    public function setSaleParam(?Sale $saleParam): void
    {
        $this->saleParam = $saleParam;
    }

    public function isActive(): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $today = new \DateTime('now', $this->getDateFromTimezone());
        if ($today < $this->getDateFrom() || $today > $this->getDateTo()) {
            return false;
        }

        return true;
    }

    public function getDiscountParam(): ?Discount
    {
        return $this->discountParam;
    }

    public function setDiscountParam(?Discount $discount): void
    {
        $this->discountParam = $discount;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $result = [];
        $result['id'] = $this->getId();
        $result['name'] = $this->getName();
        $result['order'] = $this->getOrder();
        $result['enabled'] = $this->isEnabled();
        $result['from'] = $this->getDateFrom()->format('c');
        if ($this->getDateTo() !== null) {
            $result['to'] = $this->getDateTo()->format('c');
        }
        $result['timezone'] = $this->getTimeZoneName();
        $result['type'] = $this->getType();
        if ($this->order === null) {
            $this->setOrder(1);
        }
        $result['taxFilters'] = [];
        foreach ($this->getTaxFilters() as $filter) {
            $result['taxFilters'][] = $filter->toArray();
        }
        if ($this->getType() === self::TYPE_JOIN_TAXONOMY && $this->getJoinTaxParam() !== null) {
            $result['joinTaxonomy'] = $this->getJoinTaxParam()->toArray();
        }
        if ($this->getType() === self::TYPE_BUY_X_GET_Y && $this->getBuyXgetYParam() !== null) {
            $result = array_merge($result, $this->getBuyXgetYParam()->toArray());
        }
        if ($this->getType() === self::TYPE_SALE && $this->getSaleParam() !== null) {
            $result = array_merge($result, $this->getSaleParam()->toArray());
        }
        if ($this->getType() === self::TYPE_CART_ADJUSTMENT && $this->getDiscountParam() !== null) {
            $result = array_merge($result, $this->getBuyXgetYParam()->toArray());
            $result['discount'] = $this->getDiscountParam()->toArray();
        }
        return $result;
    }

    /**
     * Takes an array and tries to generate a ShopRule.
     * Assumes that terms may be referred by id or by name.
     * Assumes that products may be referred by id or by SKU.
     *
     * @param array $ruleData
     * @param string $defaultTimeZoneName
     * @return ShopRule
     * @throws ShopRuleException
     */
    public static function fromArray(array $ruleData, string $defaultTimeZoneName = 'Europe/Rome'): ShopRule
    {
        $name = $ruleData['name'] ?? $ruleData['title'] ?? null;
        if ($name === null) {
            throw new ShopRuleException('Invalid name');
        }

        $type = $ruleData['type'] ?? null;
        if (!self::isValidType($type)) {
            throw new ShopRuleException('Invalid type');
        }

        $rule = new ShopRule($name, $type);

        $id = $ruleData['id'] ?? null;
        if ($id !== null) {
            $rule->setId((int)$id);
        }

        $enabled = $ruleData['enabled'] ?? null;
        if ($enabled !== null) {
            $rule->setEnabled(strToBool($enabled));
        }

        $timeZone = new \DateTimeZone($ruleData['timezone'] ?? $defaultTimeZoneName);
        $rule->setDateFromTimezone($timeZone);
        $rule->setDateToTimezone($timeZone);

        $from = $ruleData['from'] ?? null;
        if ($from === null) {
            throw new ShopRuleException('Starting date not set');
        }
        if (strpos($from, 'T')) {
            //Assumes it is in ISO 8601
            $from = date_create($from, $rule->getDateFromTimezone());
            if ($from === false) {
                throw new ShopRuleException('Starting date is invalid');
            }
        } else {
            $from = date_create_from_format('Y-m-d', $from, $rule->getDateFromTimezone());
            if ($from === false) {
                throw new ShopRuleException('Starting date is invalid');
            }
            $from->setTime(0, 0);
        }
        $rule->setDateFrom($from);

        $to = $ruleData['to'] ?? null;
        if ($to === null) {
            throw new ShopRuleException('Starting date not set');
        }
        if (strpos($to, 'T')) {
            //Assumes it is in ISO 8601
            $to = date_create($to, $rule->getDateToTimezone());
            if ($to === false) {
                throw new ShopRuleException('Starting date is invalid');
            }
        } else {
            $to = date_create_from_format('Y-m-d', $to, $rule->getDateToTimezone());
            if ($to === false) {
                throw new ShopRuleException('Starting date is invalid');
            }
            $to->setTime(0, 0);
        }
        $rule->setDateTo($to);

        $order = $ruleData['order'] ?? null;
        if (is_numeric($order)) {
            $order = (int)$order;
            if ($order < 1) {
                $order = 1;
            }

            $rule->setOrder($order);
        }

        $taxFilters = [];
        $taxFiltersData = $ruleData['taxFilters'] ?? null;
        if ($taxFiltersData !== null) {
            if (!is_array($taxFiltersData)) {
                throw new ShopRuleException('Tax filter data must be array');
            }

            foreach ($taxFiltersData as $tf) {
                $taxFilters[] = ShopRuleTaxFilter::fromArray($tf);
            }

            $rule->setTaxFilters($taxFilters);
        }

        if ($rule->getType() === self::TYPE_JOIN_TAXONOMY) {
            $joinTaxonomyData = $ruleData['joinTaxonomy'] ?? null;
            if (!is_array($joinTaxonomyData)) {
                throw new ShopRuleException('Join taxonomy data must be array');
            }

            $rule->setJoinTaxParam(JoinTaxonomy::fromArray($joinTaxonomyData));
        }

        if ($rule->getType() === self::TYPE_BUY_X_GET_Y) {
            $rule->setBuyXgetYParam(BuyXGetY::fromArray($ruleData));
        }

        if ($rule->getType() === self::TYPE_SALE) {
            $rule->setSaleParam(Sale::fromArray($ruleData));
        }

        if ($rule->getType() === self::TYPE_CART_ADJUSTMENT) {
            $rule->setBuyXgetYParam(BuyXGetY::fromArray($ruleData));
            $discountData = $ruleData['discount'] ?? null;
            if (!is_array($discountData)) {
                throw new ShopRuleException('Discount data must be array');
            }

            $rule->setDiscountParam(Discount::fromArray($discountData));
        }

        return $rule;
    }

    public static function isValidType($type): bool
    {
        return \in_array(
            $type,
            [
                self::TYPE_BUY_X_GET_Y,
                self::TYPE_JOIN_TAXONOMY,
                self::TYPE_SALE,
                self::TYPE_CART_ADJUSTMENT
            ],
            true
        );
    }
}
