<?php

namespace TheCodingMachine\FluidHydrator\Fluid;

use MetaHydrator\Handler\SimpleHydratingHandler;
use MetaHydrator\Parser\DBParser;
use MetaHydrator\Parser\DBRetriever;
use MetaHydrator\Parser\ObjectParser;
use Mouf\Hydrator\Hydrator;
use Mouf\Hydrator\TdbmHydrator;
use TheCodingMachine\FluidHydrator\FluidHydrator;
use TheCodingMachine\FluidHydrator\FluidHydratorFactory;

class FluidDBObject
{
    /** @var string */
    private $key;
    /** @var string */
    private $table;
    /** @var FluidHydrator */
    private $parentHydrator;
    /** @var string */
    private $errorMessage;
    /** @var FluidHydratorFactory */
    protected $factory;

    public function __construct(string $key, string $table, FluidHydrator $parentHydrator, string $errorMessage, FluidHydratorFactory $factory = null)
    {
        $this->key = $key;
        $this->table = $table;
        $this->parentHydrator = $parentHydrator;
        $this->errorMessage = $errorMessage;
        $this->factory = $factory;
    }

    public function readonly(): FluidFieldOptions
    {
        $parser = new DBRetriever($this->factory->getDbProvider(), $this->table, $this->errorMessage);
        $handler = new SimpleHydratingHandler($this->key, $parser);
        $this->parentHydrator->handler($handler, $this->key);
        return new FluidFieldOptions($this->parentHydrator, $handler, $this->factory);
    }

    public function begin(): FluidHydrator
    {
        $hydrator = new FluidHydrator();
        $parser = new DBParser($this->factory->getDbProvider(), $this->table, $hydrator, $this->errorMessage);
        $handler = new SimpleHydratingHandler($this->key, $parser);
        $this->parentHydrator->handler($handler, $this->key);
        return $this->parentHydrator->__sub($hydrator, $handler);
    }

    public function hydrator(Hydrator $hydrator = null): FluidFieldOptions
    {
        $parser = new DBParser($this->factory->getDbProvider(), $this->table, $hydrator ?? new TdbmHydrator(), $this->errorMessage);
        $handler = new SimpleHydratingHandler($this->key, $parser);
        $this->parentHydrator->handler($handler, $this->key);
        return new FluidFieldOptions($this->parentHydrator, $handler, $this->factory);
    }
}