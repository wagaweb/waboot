<?php

namespace Waboot\inc\core\repositories;

use Illuminate\Database\Capsule\Manager;
use Waboot\inc\core\DBException;
use function Waboot\inc\core\helpers\Waboot;

abstract class AbstractRepository
{
    protected Manager $db;

    /**
     * @throws DBException
     */
    public function __construct(Manager $queryBuilder = null)
    {
        if(!$queryBuilder){
            $this->db = Waboot()->DB()->getQueryBuilder();
        }
    }

    abstract public function find(int|string $id);
}