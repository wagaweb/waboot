<?php

namespace Waboot\inc\core;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Builder;

class DB
{
    /**
     * @var Manager
     */
    private $queryManager;

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

        $this->queryManager = $capsule;

        $this->queryManager->setAsGlobal();
    }

    /**
     * @throws DBException
     * @return Manager
     */
    public function getQueryManager(): Manager
    {
        if(!isset($this->queryManager)){
            throw new DBException('DB Manager non available');
        }
        return $this->queryManager;
    }

    /**
     * @return bool
     */
    public function hasManager(): bool
    {
        return isset($this->queryManager);
    }

    /**
     * @return Builder
     * @throws DBException
     */
    public function getSchemaBuilder(): Builder
    {
        return $this->getQueryManager()->schema();
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
            return $this->getSchemaBuilder()->hasTable($tableName);
        } catch (DBException $e) {
            return false;
        }
    }
}