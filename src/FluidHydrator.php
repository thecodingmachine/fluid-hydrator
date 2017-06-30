<?php

namespace TheCodingMachine\FluidHydrator;

use MetaHydrator\Handler\HydratingHandlerInterface;
use MetaHydrator\Handler\SubHydratingHandler;
use MetaHydrator\MetaHydrator;
use Mouf\Hydrator\Hydrator;
use Mouf\Hydrator\TdbmHydrator;
use TheCodingMachine\FluidHydrator\Fluid\FluidField;
use TheCodingMachine\FluidHydrator\Fluid\FluidFieldOptions;

class FluidHydrator implements Hydrator
{
    /** @var MetaHydrator */
    protected $metaHydrator;
    /** @var HydratingHandlerInterface[] */
    protected $handlers = [];
    /** @var FluidHydratorFactory */
    protected $factory;

    protected $parent;
    protected $wrappingHandler;

    public function __construct(FluidHydratorFactory $factory = null)
    {
        $this->factory = $factory;
    }

    static public function new(): FluidHydrator
    {
        return new FluidHydrator();
    }

    /**
     * @return Hydrator
     */
    public function getHydrator(): Hydrator
    {
        return new MetaHydrator($this->handlers);
    }

    /**
     * @param string $key
     * @return FluidField
     */
    public function field(string $key): FluidField
    {
        return new FluidField($this, $key, $this->factory);
    }

    /**
     * @param HydratingHandlerInterface $handler
     * @return FluidHydrator
     */
    public function handler(HydratingHandlerInterface $handler, string $key = null): FluidHydrator
    {
        if ($key === null) {
            $this->handlers[] = $handler;
        } else {
            $this->handlers[$key] = $handler;
        }
        return $this;
    }

    public function __sub(FluidHydrator $child, $handler): FluidHydrator
    {
        $child->parent = $this;
        $child->wrappingHandler = $handler;
        return $child;
    }

    /**
     * Creates a new $className object, filling it with $data.
     *
     * @param array $data
     * @param string $className
     * @return object
     */
    public function hydrateNewObject(array $data, string $className)
    {
        return $this->getHydrator()->hydrateNewObject($data, $className);
    }

    /**
     * Fills $object with $data.
     *
     * @param array $data
     * @param $object
     * @return object
     */
    public function hydrateObject(array $data, $object)
    {
        return $this->getHydrator()->hydrateObject($data, $object);
    }

    public function end(): FluidFieldOptions
    {
        if ($this->parent === null) {
            throw new \Exception("Error: cannot call method 'end' here (no parent hydrator)");
        }
        return new FluidFieldOptions($this->parent, $this->wrappingHandler, $this->factory);
    }
}
