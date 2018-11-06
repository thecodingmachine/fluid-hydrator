<?php

namespace TheCodingMachine\FluidHydrator\Fluid;

use MetaHydrator\Handler\SimpleHydratingHandler;
use MetaHydrator\Parser\BoolParser;
use MetaHydrator\Parser\DateTimeParser;
use MetaHydrator\Parser\FloatParser;
use MetaHydrator\Parser\IntParser;
use MetaHydrator\Parser\ParserInterface;
use MetaHydrator\Parser\SimpleArrayParser;
use MetaHydrator\Parser\StringParser;
use Mouf\Hydrator\Hydrator;
use TheCodingMachine\FluidHydrator\FluidHydrator;

class FluidField
{
    /**
     * @var FluidHydrator
     */
    private $hydrator;
    /**
     * @var string
     */
    protected $key;

    /**
     * FluidHydratingHandler constructor.
     * @param FluidHydrator $hydrator
     * @param string $key
     */
    public function __construct(FluidHydrator $hydrator, string $key)
    {
        $this->hydrator = $hydrator;
        $this->key = $key;
    }

    /**
     * @param string $errorMessage
     * @return FluidFieldOptions
     */
    public function string(string $errorMessage = 'Invalid value'): FluidFieldOptions
    {
        return $this->parser(new StringParser($errorMessage));
    }
    /**
     * @param string $errorMessage
     * @return FluidFieldOptions
     */
    public function int(string $errorMessage = 'Invalid value'): FluidFieldOptions
    {
        return $this->parser(new IntParser($errorMessage));
    }
    /**
     * @param string $errorMessage
     * @return FluidFieldOptions
     */
    public function float(string $errorMessage = 'Invalid value'): FluidFieldOptions
    {
        return $this->parser(new FloatParser($errorMessage));
    }
    /**
     * @param string $errorMessage
     * @return FluidFieldOptions
     */
    public function bool(string $errorMessage = 'Invalid value'): FluidFieldOptions
    {
        return $this->parser(new BoolParser($errorMessage));
    }
    /**
     * @param string $format
     * @param bool $immutable
     * @param string $errorMessage
     * @return FluidFieldOptions
     */
    public function date(string $format = 'Y-m-d', bool $immutable = true, string $errorMessage = 'Invalid value'): FluidFieldOptions
    {
        return $this->parser(new DateTimeParser($format, true, $errorMessage, $immutable));
    }
    /**
     * @param string $errorMessage
     * @return FluidFieldOptions
     */
    public function simpleArray(string $errorMessage = 'Invalid value'): FluidFieldOptions
    {
        return $this->parser(new SimpleArrayParser($errorMessage));
    }
    /**
     * @param string $className
     * @param Hydrator|null $hydrator
     * @param string $errorMessage
     * @return FluidObject
     */
    public function object(string $className, string $errorMessage = 'Invalid value'): FluidObject
    {
        return new FluidObject($this->key, $className, $this->hydrator, $errorMessage);
    }
    /**
     * @param string $className
     * @param string $errorMessage
     * @return FluidSubObject
     */
    public function subobject(string $className, string $errorMessage = 'Invalid value'): FluidSubObject
    {
        return new FluidSubObject($this->key, $className, $this->hydrator, $errorMessage);
    }
    /**
     * @param ParserInterface $parser
     * @return FluidFieldOptions
     */
    public function parser(ParserInterface $parser): FluidFieldOptions
    {
        $handler = new SimpleHydratingHandler($this->key, $parser);
        $this->hydrator->handler($handler, $this->key);
        return new FluidFieldOptions($this->hydrator, $handler);
    }
}
