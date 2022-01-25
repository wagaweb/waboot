<?php

namespace Waboot\inc\core;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Builder;

class DB
{
    /**
     * @var Manager
     */
    private $manager;

    /**
     * @return DB|null
     */
    public static function getInstance(): ?DB
    {
        static $instance = null;
        if (null === $instance) {
            $instance = new static();
        }

        return $instance;
    }

    /**
     * DB constructor.
     * @throws DBUnavailableDependencyException
     */
    protected function __construct()
    {
        if(!class_exists('\Illuminate\Database\Capsule\Manager')){
            throw new DBUnavailableDependencyException('Class \Illuminate\Database\Capsule\Manager non found');
        }

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

        $this->manager = $capsule;

        $this->manager->setAsGlobal();
    }

    /**
     * @throws DBException
     * @return Manager
     */
    public function getManager(): Manager
    {
        if(!isset($this->manager)){
            throw new DBException('DB Manager non available');
        }
        return $this->manager;
    }

    /**
     * @return bool
     */
    public function hasManager(): bool
    {
        return isset($this->manager);
    }

    /**
     * @return Builder
     * @throws DBException
     */
    public function getBuilder(): Builder
    {
        return $this->getManager()->schema();
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
     * @param $tableName
     * @return bool
     */
    public function tableExists($tableName): bool
    {
        try {
            return $this->getBuilder()->hasTable($tableName);
        } catch (DBException $e) {
            return false;
        }
    }
}