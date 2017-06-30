<?php

namespace TheCodingMachine\FluidHydrator\Fluid;

use MetaHydrator\Handler\SubHydratingHandler;
use Mouf\Hydrator\Hydrator;
use Mouf\Hydrator\TdbmHydrator;
use TheCodingMachine\FluidHydrator\FluidHydrator;
use TheCodingMachine\FluidHydrator\FluidHydratorFactory;

class FluidSubObject
{
    /** @var string */
    private $key;
    /** @var string */
    private $className;
    /** @var FluidHydrator */
    private $parentHydrator;
    /** @var string */
    private $errorMessage;
    /** @var FluidHydratorFactory */
    protected $factory;

    public function __construct(string $key, string $className, FluidHydrator $parentHydrator, string $errorMessage, FluidHydratorFactory $factory = null)
    {
        $this->key = $key;
        $this->className = $className;
        $this->parentHydrator = $parentHydrator;
        $this->errorMessage = $errorMessage;
        $this->factory = $factory;
    }

    public function begin(): FluidHydrator
    {
        $hydrator = new FluidHydrator();
        $handler = new SubHydratingHandler($this->key, $this->className, $hydrator, [], $this->errorMessage);
        $this->parentHydrator->handler($handler, $this->key);
        return $this->parentHydrator->__sub($hydrator, $handler);
    }

    public function hydrator(Hydrator $hydrator = null): FluidFieldOptions
    {
        $handler = new SubHydratingHandler($this->key, $this->className, $hydrator ?? new TdbmHydrator(), [], $this->errorMessage);
        $this->parentHydrator->handler($handler, $this->key);
        return new FluidFieldOptions($this->parentHydrator, $handler, $this->factory);
    }
}