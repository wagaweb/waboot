<?php

namespace Waboot\inc\core;

class DB
{
    /**
     * @var \Illuminate\Database\Capsule\Manager
     */
    private $manager;

    /**
     * DB constructor.
     */
    public function __construct(){
        if(!class_exists('\Illuminate\Database\Capsule\Manager')){
            return;
        }

        global $wpdb;

        $capsule = new \Illuminate\Database\Capsule\Manager();
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

        $this->manager = $capsule;

        $this->manager->setAsGlobal();
    }

    /**
     * @throws \RuntimeException
     * @return \Illuminate\Database\Capsule\Manager
     */
    public function getManager(): \Illuminate\Database\Capsule\Manager
    {
        if(!isset($this->manager)){
            throw new \RuntimeException('DB Manager non available');
        }
        return $this->manager;
    }

    /**
     * @return wpdb
     */
    public function getWPDB(): wpdb
    {
        global $wpdb;
        return $wpdb;
    }

    /**
     * @return string
     */
    public function getDBPrefix(): string
    {
        return $this->getWPDB()->prefix;
    }

    /**
     * @return \Illuminate\Database\Schema\Builder
     */
    public function getBuilder(): \Illuminate\Database\Schema\Builder
    {
        return $this->getManager()->schema();
    }

    /**
     * @return bool
     */
    public function hasManager(): bool
    {
        return isset($this->manager);
    }

    /**
     * @param $tableName
     * @return bool
     */
    public function tableExists($tableName): bool
    {
        return $this->getBuilder()->hasTable($tableName);
    }
}