<?php

namespace TheCodingMachine\FluidHydrator;

use MetaHydrator\Database\DBProvider;

class FluidHydratorFactory
{
    /** @var DBProvider */
    private $dbProvider;
    public function getDbProvider() { return $this->dbProvider; }
    public function setDbProvider(DBProvider $dbProvider) { $this->dbProvider = $dbProvider; }

    /**
     * FluidHydratorFactory constructor.
     * @param DBProvider $dbProvider
     */
    public function __construct(DBProvider $dbProvider = null)
    {
        $this->dbProvider = $dbProvider;
    }

    public function new(): FluidHydrator
    {
        return new FluidHydrator($this);
    }
}