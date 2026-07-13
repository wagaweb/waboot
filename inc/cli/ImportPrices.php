<?php

namespace Waboot\inc\cli;

use Waboot\inc\core\cli\AbstractCommand;

class ImportPrices extends AbstractCommand
{
    const KEY_ID = 'id';
    const KEY_SKU = 'sku';

    const PRICE_REGULAR = 'regular';
    const PRICE_SALE = 'sale';
    const PRICE_ROLE = 'role';
    const PRICE_RESET = 'reset';

    protected $logDirName = 'import-prices';
    protected $logFileName = 'import-prices';

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
                    'description' => 'The type of price to import (`regular`/`sale`/`role`/`reset`)',
                    'optional' => false,
                    'repeating' => false,
                ],
                [
                    'type' => 'flag',
                    'name' => 'role',
                    'description' => 'The role which the price will be assigned to',
                    'optional' => true,
                    'repeating' => false,
                ],
                [
                    'type' => 'flag',
                    'name' => 'skip-header',
                    'description' => 'Skip the first row',
                    'optional' => true,
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
    }

    public function run(array $args, array $assoc_args): int
    {
        global $wpdb;

        $timezone = wp_timezone();

        $dry = boolval($assoc_args['dry'] ?? false);

        $filename = $args[0] ?? null;
        if (empty($filename)) {
            $this->error('You need to pride the target file name');
            return -1;
        }

        $keyType = $args[1] ?? null;
        if (!in_array($keyType, [self::KEY_ID, self::KEY_SKU])) {
            $this->error(sprintf('Invalid key: %s', $keyType));
            return -1;
        }

        $priceType = $args[2] ?? null;
        if (!in_array($priceType, [
            self::PRICE_REGULAR,
            self::PRICE_SALE,
            self::PRICE_ROLE,
            self::PRICE_RESET
        ])) {
            $this->error(sprintf('Invalid price type: %s', $priceType));
            return -1;
        }

        $role = $assoc_args['role'] ?? null;
        if ($priceType === self::PRICE_ROLE) {
            global $wp_roles;
            $validRoles = array_map(fn($r) => $r->name, $wp_roles->role_objects);
            if (!in_array($role, $validRoles)) {
                $this->error(sprintf('Invalid role: %s. Valid roles: %s', $role, implode(', ', $validRoles)));
                return -1;
            }
        }

        if (!is_file($filename)) {
            $this->error(sprintf('Invalid file name: %s', $filename));
            return -1;
        }

        $stream = fopen($filename, 'r');
        if ($stream === false) {
            $this->error(sprintf('Cannot open file: %s', $filename));
            return -1;
        }

        $this->log('Collecting data');

        if (wc_string_to_bool($assoc_args['skip-header'] ?? false)) {
            fgetcsv($stream, null, ';');
        }

        $map = [];
        while (($row = fgetcsv($stream, null, ';')) !== false) {
            $key = trim($row[0] ?? '');
            if (empty($key)) {
                $this->log(sprintf('Invalid key: %s', $key));
                continue;
            }
            if ($keyType === self::KEY_SKU) {
                $key = strtoupper($key);
            }

            $rawPrice = trim($row[1] ?? '');
            $price = floatval($rawPrice);
            if ($price <= 0) {
                $this->log(sprintf('Invalid price: %s', $rawPrice));
                continue;
            }

            $from = null;
            $rawDate = $row[2] ?? '';
            if (strlen($rawDate) > 0) {
                $date = \DateTime::createFromFormat('Y-m-d', $rawDate, $timezone);
                if ($date === false) {
                    $this->error('Invalid date format');
                    return -1;
                }
                $date->setTime(0, 0);
                $from = $date->getTimestamp();
            }

            $to = null;
            $rawDate = $row[3] ?? '';
            if (strlen($rawDate) > 0) {
                $date = \DateTime::createFromFormat('Y-m-d', $rawDate, $timezone);
                if ($date === false) {
                    $this->error('Invalid date format');
                    return -1;
                }
                $date->setTime(23, 59, 59);
                $to = $date->getTimestamp();
            }

            $map[$key] = [$price, $from, $to];
        }

        if ($keyType === self::KEY_ID) {
            $newMap = [];
            foreach ($map as $id => $entry) {
                $newMap[(int)$id] = $entry;
            }
            $map = $newMap;
        } elseif ($keyType === self::KEY_SKU) {
            $in = implode(',', array_fill(0, count($map), '%s'));
            $sql = <<<SQL
select distinct post_id as id, upper(meta_value) as sku
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
        /** @var \WP_Post[] $prodPosts */
        $prodPosts = [];
        if (!empty($ids)) {
            $prodPosts = get_posts([
                'posts_per_page' => -1,
                'post__in' => $ids,
                'post_type' => ['product', 'product_variation'],
                'ignore_sticky_posts' => 1,
            ]);
        }

        $now = time();
        $count = count($prodPosts);
        $this->log(sprintf('Updating %d products', $count));
        /** @var int[] $prodsToSync */
        $prodsToSync = [];
        foreach ($prodPosts as $i => $post) {
            $p = wc_get_product($post);
            if (empty($p)) {
                $this->log(sprintf('%d/%d - Product %d not found. Skipping', $i + 1, $count, $post->ID));
                continue;
            }

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

            if ($p->get_type() === 'variation') {
                $prodsToSync[$p->get_parent_id()] = $p->get_parent_id();
            }

            foreach ($prodsToParse as $pp) {
                /** @var float $price */
                $price = $priceEntry[0] ?? null;
                if (empty($price)) {
                    return -1;
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
                } elseif ($priceType === self::PRICE_ROLE) {
                    $pp->update_meta_data(
                        '_role_base_price_' . $role,
                        serialize([
                            'discount_type' => 'fixed_price',
                            'discount_value' => $price,
                            'min_qty' => '',
                            'max_qty' => '',
                        ])
                    );
                } elseif ($priceType === self::PRICE_RESET) {
                    global $wpdb;

                    $sql = <<<SQL
delete from $wpdb->postmeta where post_id = %d and meta_key like '_role_base_price_%%';
SQL;
                    if (!$dry) {
                        $wpdb->query($wpdb->prepare($sql, $pp->get_id()));
                    }

                    $pp->set_date_on_sale_from();
                    $pp->set_date_on_sale_to();
                    $pp->set_sale_price('');
                    $pp->set_price($pp->get_regular_price('edit'));
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
        return 0;
    }
}
