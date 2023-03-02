<?php

namespace Waboot\addons\packages\checkout_multistep;

use DateTime;
use Exception;

class Subscription
{
    /** @var string */
    public $email;

    /** @var DateTime */
    public $time;

    public function __construct(string $email)
    {
        $this->email = $email;
        $this->time = new DateTime();
    }

    /**
     * @throws Exception
     */
    public static function fromArray(array $array): self
    {
        if (!is_email($array['email'] ?? '')) {
            throw new Exception('Invalid email address');
        }
        $s = new self($array['email']);

        $time = DateTime::createFromFormat('Y-m-d H:i:s', $s['time'] ?? '');
        if ($time === false) {
            throw new Exception('Invalid time format');
        }
        $s->time = $time;

        return $s;
    }
}

function createSubscriptionTable(): void
{
    global $wpdb;

    $charset = $wpdb->get_charset_collate();
    $sql = <<<SQL
create table if not exists {$wpdb->prefix}subscriptions (
    email varchar(320) not null,
    time datetime not null,
    primary key (email)
) $charset;
SQL;

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function getSubscriptions(): array
{
    global $wpdb;

    $sql = <<<SQL
select * from {$wpdb->prefix}subscriptions
SQL;

    $res = $wpdb->get_results($sql, ARRAY_A);
    $subs = [];
    foreach ($res as $s) {
        $sub = Subscription::fromArray($s);
        $subs[$sub->email] = $sub;
    }

    return $subs;
}

function insertSubscription(Subscription $s): bool
{
    global $wpdb;

    $wpdb->suppress_errors = true;
    $res = $wpdb->insert(
        "{$wpdb->prefix}subscriptions",
        [
            'email' => $s->email,
            'time' => $s->time->format('Y-m-d H:i:s'),
        ],
        ['%s', '%s']
    );
    $wpdb->suppress_errors = false;

    return $res === 1;
}
