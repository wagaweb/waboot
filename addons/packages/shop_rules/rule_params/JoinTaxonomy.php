<?php

namespace Waboot\addons\packages\shop_rules\rule_params;

use Waboot\addons\packages\shop_rules\ShopRuleException;

class JoinTaxonomy
{
    /**
     * @var string
     */
    protected $taxonomy;
    /**
     * @param int
     */
    protected $termId;

    public function __construct(string $taxonomy, int $termId)
    {
        $this->taxonomy = $taxonomy;
        $this->termId = $termId;
    }

    /**
     * @throws ShopRuleException
     */
    static function fromArray(array $array): self {
        $taxonomy = $array['taxonomy'] ?? null;
        if (!is_string($taxonomy)) {
            throw new ShopRuleException('Taxonomy parameter must be string');
        }

        $termData = $array['term'] ?? null;
        if (is_numeric($termData)) {
            $termId = (int)$termData;
        } elseif (is_string($termData)) {
            $term = get_term_by('name', $termData, $taxonomy);
            if (empty($term)) {
                $term = get_term_by('slug', $termData, $taxonomy);
            }
            if (empty($term)) {
                throw new ShopRuleException('Join taxonomy: provided term does not exists');
            }
            $termId = $term->term_id;
        } else {
            throw new ShopRuleException('Term parameter must be string or numeric');
        }

        return new self($taxonomy, $termId);
    }

    public function getTaxonomy(): string
    {
        return $this->taxonomy;
    }

    public function getTermId(): int
    {
        return $this->termId;
    }

    public function toArray(): array {
       return [
           'taxonomy' => $this->getTaxonomy(),
           'term' => $this->getTermId(),
       ];
    }
}