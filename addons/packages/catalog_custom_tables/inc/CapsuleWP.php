<?php

namespace Waboot\addons\packages\catalog_custom_tables\inc;

use Illuminate\Database\Capsule\Manager;

class CapsuleWP
{
    /**
     * @var Manager
     */
    private $capsule;

    public function __construct(){
        global $wpdb;

        $capsule = new Manager();
        $capsule->addConnection([
            'driver' => 'mysql',
            'host' => DB_HOST,
            'database' => DB_NAME,
            'username'  => DB_USER,
            'password'  => DB_PASSWORD,
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => $wpdb->prefix,
        ]);

        $this->capsule = $capsule;

        $this->capsule->setAsGlobal();
    }

    public function getManager(): Manager{
        return $this->capsule;
    }

    public function getWPDB(): \wpdb{
        global $wpdb;
        return $wpdb;
    }

    public function getDBPrefix(): string{
        return $this->getWPDB()->prefix;
    }

    public function getBuilder(): \Illuminate\Database\Schema\Builder{
        return $this->getManager()->schema();
    }

    public function tableExists($tableName): bool {
        return $this->getBuilder()->hasTable($tableName);
    }
}