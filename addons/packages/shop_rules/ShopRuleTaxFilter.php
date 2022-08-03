<?php

namespace Waboot\addons\packages\shop_rules;

class ShopRuleTaxFilter
{
    const CRITERIA_IN = 'in';
    const CRITERIA_NOT_IN = 'not-in';

    /**
     * @var string
     */
    protected $taxonomy;
    /**
     * @param int[]
     */
    protected $terms;
    /**
     * @var string
     */
    protected $criteria = self::CRITERIA_IN;
    /**
     * @var bool
     */
    protected $atLeastOne = false;

    public function __construct(string $taxonomy, array $terms)
    {
        $this->taxonomy = $taxonomy;
        $this->terms = $terms;
    }

    /**
     * @throws ShopRuleException
     */
    public static function fromArray(array $array): ShopRuleTaxFilter
    {
        $taxonomy = $array['taxonomy'] ?? null;
        if (!is_string($taxonomy)) {
            throw new ShopRuleException('Tax filter taxonomy must be string');
        }

        $terms = [];
        $termsData = $array['terms'] ?? null;
        if (!is_array($termsData)) {
            throw new ShopRuleException('Tax filter terms must be array');
        }
        foreach ($termsData as $t) {
            if (is_numeric($t)) {
                $terms[] = (int)$t;
            } elseif (is_string($t)) {
                $term = get_term_by('name', $t, $taxonomy);
                if (empty($term)) {
                    $term = get_term_by('slug', $t, $taxonomy);
                }
                if (empty($term)) {
                    throw new ShopRuleException('Tax filter: provided terms does not exists');
                }
                $terms[] = $term->term_id;
            } else {
                throw new ShopRuleException('Tax filter term must be string or numeric');
            }
        }

        $filter = new self($taxonomy, $terms);

        $criteria = $array['criteria'] ?? null;
        if ($criteria !== null) {
            if (!self::isValidCriteria($criteria)) {
                throw new ShopRuleException('Tax filter criteria is not valid');
            }

            $filter->setCriteria($criteria);
        }

        $atLeastOne = $array['atLeastOne'] ?? null;
        if ($atLeastOne !== null) {
            $filter->setAtLeastOne(strToBool($atLeastOne));
        }

        return $filter;
    }

    public function getTaxonomy(): string
    {
        return $this->taxonomy;
    }

    public function setTaxonomy(string $taxonomy): void
    {
        $this->taxonomy = $taxonomy;
    }

    public function getCriteria(): string
    {
        return $this->criteria;
    }

    public function setCriteria(string $criteria): void
    {
        $this->criteria = $criteria;
    }

    /**
     * @return int[]
     */
    public function getTerms(): array
    {
        return $this->terms;
    }

    /**
     * @param int[] $terms
     * @return void
     */
    public function setTerms(array $terms): void
    {
        $this->terms = $terms;
    }

    public function setAtLeastOne(bool $atLeastOne): void
    {
        $this->atLeastOne = $atLeastOne;
    }

    public function getAtLeastOne(): bool
    {
        return $this->atLeastOne;
    }

    static function isValidCriteria(string $criteria): bool
    {
        return in_array(
            $criteria,
            [
                self::CRITERIA_IN,
                self::CRITERIA_NOT_IN,
            ]
        );
    }

    public function toArray(): array
    {
        return [
            'taxonomy' => $this->getTaxonomy(),
            'terms' => $this->getTerms(),
            'criteria' => $this->getCriteria(),
            'atLeastOne' => $this->getAtLeastOne(),
        ];
    }
}