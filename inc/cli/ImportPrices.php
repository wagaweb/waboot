<?php

namespace Waboot\inc\cli;

use Waboot\inc\core\cli\AbstractCommand;

class ImportPrices extends AbstractCommand
{
    const KEY_ID = 'id';
    const KEY_SKU = 'sku';

    const PRICE_REGULAR = 'regular';
    const PRICE_SALE = 'sale';

    private const ECODE_INVALID_DATE = 3000;

    protected $logDirName = 'import-prices';
    protected $logFileName = 'import-prices';

    /**
     * @return array
     */
    public static function getCommandDescription(): array
    {
        //@see: https://make.wordpress.org/cli/handbook/guides/commands-cookbook/#wp_cliadd_commands-third-args-parameter
        return [
            'shortdesc' => 'Import prices',
            'synopsis' => [
                [
                    'type' => 'positional',
                    'name' => 'filename',
                    'description' => 'The path to the file to import',
                    'optional' => false,
                    'repeating' => false,
                ],
                [
                    'type' => 'positional',
                    'name' => 'key',
                    'description' => 'The type of key that identify the product (`id`/`sku`)',
                    'optional' => false,
                    'repeating' => false,
                ],
                [
                    'type' => 'positional',
                    'name' => 'price',
                    'description' => 'The type of price to import (`regular`/`sale`)',
                    'optional' => false,
                    'repeating' => false,
                ],
                [
                    'type' => 'flag',
                    'name' => 'dry',
                    'description' => 'Dry run',
                    'optional' => true,
                    'repeating' => false,
                ],
            ],
        ];
    }

    /**
     * @throws \Exception
     */
    public function __invoke(array $args, array $assoc_args)
    {
        parent::__invoke($args, $assoc_args);

        global $wpdb;

        $dry = boolval($assoc_args['dry'] ?? false);

        $filename = $args[0] ?? null;
        if (empty($filename)) {
            $this->error('You need to pride the target file name');
            return;
        }

        $key = $args[1] ?? null;
        if (!in_array($key, [self::KEY_ID, self::KEY_SKU])) {
            $this->error(sprintf('Invalid key: %s', $key));
            return;
        }

        $priceType = $args[2] ?? null;
        if (!in_array($priceType, [self::PRICE_REGULAR, self::PRICE_SALE])) {
            $this->error(sprintf('Invalid price type: %s', $priceType));
            return;
        }

        if (!is_file($filename)) {
            $this->error(sprintf('Invalid file name: %s', $filename));
            return;
        }

        $stream = fopen($filename, 'r');
        if ($stream === false) {
            $this->error(sprintf('Cannot open file: %s', $filename));
            return;
        }

        $this->log('Collecting data');
        $map = [];
        while (($row = fgetcsv($stream, null, ';')) !== false) {
            $key = trim($row[0] ?? '');
            if (empty($key)) {
                $this->log(sprintf('Invalid key: %s', $key));
                continue;
            }

            $rawPrice = trim($row[1] ?? '');
            $price = floatval($rawPrice);
            if ($price <= 0) {
                $this->log(sprintf('Invalid price: %s', $rawPrice));
                continue;
            }

            try {
                $from = $this->getDateFromRow($row, 2);
                $to = $this->getDateFromRow($row, 3);
            } catch (\Exception $e) {
                if ($e->getCode() === self::ECODE_INVALID_DATE) {
                    $this->log('Invalid date format');
                    continue;
                }

                throw $e;
            }

            $map[$key] = [$price, $from, $to];
        }

        if ($key === self::KEY_ID) {
            $newMap = [];
            foreach ($map as $id => $entry) {
                $newMap[(int)$id] = $entry;
            }
            $map = $newMap;
        } elseif ($key === self::KEY_SKU) {
            $in = implode(',', array_fill(0, count($map), '%s'));
            $sql = <<<SQL
select distinct post_id as id, meta_value as sku
from $wpdb->postmeta
where meta_key = '_sku' and meta_value IN ($in)
SQL;
            $res = $wpdb->get_results($wpdb->prepare($sql, array_keys($map)));

            $newMap = [];
            foreach ($res as $p) {
                $newMap[$p->id] = $map[$p->sku];
            }
            $map = $newMap;
        }

        $ids = array_keys($map);
        /** @var \WC_Product[] $prods */
        $prods = [];
        if (!empty($ids)) {
            $prods = wc_get_products(['limit' => -1, 'include' => $ids]);
        }

        $now = time();
        $count = count($prods);
        $this->log(sprintf('Updating %d products', $count));
        /** @var int[] $prodsToSync */
        $prodsToSync = [];
        foreach ($prods as $i => $p) {
            $this->log(sprintf('%d/%d - Updating Product #%d (%s)', $i + 1, $count, $p->get_id(), $p->get_type()));
            $priceEntry = $map[$p->get_id()] ?? null;
            if (empty($priceEntry)) {
                continue;
            }

            $prodsToParse = [$p];
            if ($p->get_type() === 'variable') {
                $prodsToParse = [];
                foreach ($p->get_children() as $id) {
                    $c = wc_get_product($id);
                    if (!empty($c)) {
                        $prodsToParse[] = $c;
                    }
                }
                $prodsToSync[] = $p->get_id();
            }

            foreach ($prodsToParse as $pp) {
                /** @var float $price */
                $price = $priceEntry[0] ?? null;
                if (empty($price)) {
                    return;
                }

                if ($priceType === self::PRICE_REGULAR) {
                    $pp->set_regular_price($price);
                    if (!$pp->is_on_sale()) {
                        $pp->set_price($price);
                    }
                } elseif ($priceType === self::PRICE_SALE) {
                    /** @var int|null $from */
                    $from = $priceEntry[1] ?? null;
                    /** @var int|null $to */
                    $to = $priceEntry[2] ?? null;
                    $set = true;
                    if ($from !== null) {
                        $pp->set_date_on_sale_from($from);
                        $set = $now >= $from;
                    }
                    if ($to !== null) {
                        $pp->set_date_on_sale_to($to);
                        $set = $set && ($now < $to);
                    }

                    $pp->set_sale_price($price);
                    if ($set) {
                        $pp->set_price($price);
                    }
                }

                if (!$dry) {
                    $pp->save();
                }
            }
        }

        $count = count($prodsToSync);
        $this->log(sprintf('Syncing %d products', $count));
        foreach ($prodsToSync as $i => $p) {
            $this->log(sprintf('%d/%d - Syncing product #%d', $i + 1, $count, $p));
            if (!$dry) {
                \WC_Product_Variable::sync($p);
            }
        }

        $this->success('Done');
    }

    /**
     * @throws \Exception
     */
    private function getDateFromRow(array $row, $index): ?int
    {
        $rawDate = $row[$index] ?? '';
        if (!is_string($rawDate) || strlen($rawDate) < 1) {
            return null;
        }

        $date = \DateTime::createFromFormat('Y-m-d', $rawDate);
        if ($date === false) {
            throw new \Exception('Invalid date format', self::ECODE_INVALID_DATE);
        }
        $date->setTime(23, 59);

        return $date->getTimestamp();
    }
}
