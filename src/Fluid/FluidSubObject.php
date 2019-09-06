<?php

namespace TheCodingMachine\FluidHydrator\Fluid;

use MetaHydrator\Handler\SubHydratingHandler;
use Mouf\Hydrator\Hydrator;
use Mouf\Hydrator\TdbmHydrator;
use TheCodingMachine\FluidHydrator\FluidHydrator;

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

    public function __construct(string $key, string $className, FluidHydrator $parentHydrator, string $errorMessage)
    {
        $this->key = $key;
        $this->className = $className;
        $this->parentHydrator = $parentHydrator;
        $this->errorMessage = $errorMessage;
    }

    public function begin(): FluidHydrator
    {
        $hydrator = new FluidHydrator();
        $handler = new SubHydratingHandler($this->key, $this->className, $hydrator, [], null, $this->errorMessage);
        $this->parentHydrator->handler($handler, $this->key);
        return $this->parentHydrator->__sub($hydrator, $handler);
    }

    public function hydrator(Hydrator $hydrator = null): FluidFieldOptions
    {
        $handler = new SubHydratingHandler($this->key, $this->className, $hydrator ?? new TdbmHydrator(), [], null, $this->errorMessage);
        $this->parentHydrator->handler($handler, $this->key);
        return new FluidFieldOptions($this->parentHydrator, $handler);
    }
}