<?php
namespace FluidHydratorTest;

use MetaHydrator\Database\DBProvider;
use MetaHydratorTest\Database\StubDBProvider;
use MetaHydratorTest\FooBar;
use TheCodingMachine\FluidHydrator\FluidHydratorFactory;

class FluidDBObjectTest extends \PHPUnit_Framework_TestCase
{
    /** @var DBProvider */
    private $dbProvider;
    /** @var FluidHydratorFactory */
    private $factory;

    public function setup()
    {
        $this->dbProvider = new StubDBProvider([
            'foobar' => [
                'class' => FooBar::class,
                'pk' => ['foo'],
                'items' => [
                    new FooBar([
                        'foo' => 1,
                        'bar' => 'Joe'
                    ]),
                    new FooBar([
                        'foo' => 12,
                        'bar' => 'Jack'
                    ]),
                    new FooBar([
                        'foo' => 42,
                        'bar' => 'William'
                    ]),
                    new FooBar([
                        'foo' => 100,
                        'bar' => 'Averell'
                    ]),
                ]
            ]
        ]);
        $this->factory = new FluidHydratorFactory($this->dbProvider);
    }

    public function testRetrieve()
    {
        $hydrator = $this->factory->new();
        $hydrator
            ->field('baz')->dbobject('foobar')->readonly();

        $data = [
            'baz' => [
                'foo' => 12,
                'bar' => 'William'
            ]
        ];

        /** @var FooBar $foo */
        $foo = $hydrator->hydrateNewObject($data, FooBar::class);
        $this->assertEquals('Jack', $foo->getBaz()->getBar());
    }

    public function testParse()
    {
        $hydrator = $this->factory->new()
            ->field('baz')->dbobject('foobar')->hydrator();

        $data = [
            'baz' => [
                'foo' => 12,
                'bar' => 'William'
            ]
        ];

        /** @var FooBar $foo */
        $foo = $hydrator->hydrateNewObject($data, FooBar::class);
        $this->assertEquals('William', $foo->getBaz()->getBar());
    }
}

