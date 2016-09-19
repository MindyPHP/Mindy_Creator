<?php

namespace Mindy\Tests\Creator;

use function Mindy\Creator\createObject;
use Mindy\Creator\Creator;
use PHPUnit_Framework_TestCase;

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 07/01/14.01.2014 13:50
 */
abstract class Test
{
    public function __construct(array $options = [])
    {
        foreach ($options as $name => $param) {
            $this->$name = $param;
        }
    }
}

trait SimpleTrait
{

}

class TestSingleton
{
    private static $_instance;

    public $id = 0;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public static function getInstance($id)
    {
        if (self::$_instance === null) {
            self::$_instance = new self($id);
        }
        return self::$_instance;
    }
}

class TestCreate extends Test
{
    use SimpleTrait;

    public $test;
}

class TestTrait extends TestCreate
{

}

class CreatorTest extends PHPUnit_Framework_TestCase
{
    public function testArray()
    {
        $obj = Creator::createObject([
            'class' => TestCreate::class,
            'test' => 1
        ]);
        $this->assertInstanceOf(TestCreate::class, $obj);
        $this->assertEquals(1, $obj->test);

        $obj = createObject([
            'class' => TestCreate::class,
            'test' => 1
        ]);
        $this->assertInstanceOf(TestCreate::class, $obj);
        $this->assertEquals(1, $obj->test);
    }

    public function testString()
    {
        $obj = Creator::createObject(TestCreate::class, [
            'test' => 1
        ]);
        $this->assertInstanceOf(TestCreate::class, $obj);
        $this->assertEquals(1, $obj->test);

        $obj = createObject(TestCreate::class, [
            'test' => 1
        ]);
        $this->assertInstanceOf(TestCreate::class, $obj);
        $this->assertEquals(1, $obj->test);
    }

    public function testClosure()
    {
        $obj = Creator::createObject(function () {
            return new TestCreate();
        });
        $this->assertInstanceOf(TestCreate::class, $obj);
        $this->assertNull($obj->test);

        $obj = createObject(function () {
            return new TestCreate();
        });
        $this->assertInstanceOf(TestCreate::class, $obj);
        $this->assertNull($obj->test);
    }

    public function testDefaults()
    {
        Creator::$objectConfig = [
            TestCreate::class => [
                'test' => 1
            ]
        ];

        $obj = Creator::createObject([
            'class' => TestCreate::class,
        ]);
        $this->assertInstanceOf(TestCreate::class, $obj);
        $this->assertEquals(1, $obj->test);

        $obj = Creator::createObject(TestCreate::class);
        $this->assertInstanceOf(TestCreate::class, $obj);
        $this->assertEquals(1, $obj->test);
    }

    public function testConfigure()
    {
        $obj = Creator::createObject([
            'class' => TestCreate::class,
            'test' => 1
        ]);

        $this->assertInstanceOf(TestCreate::class, $obj);
        $this->assertEquals(1, $obj->test);

        Creator::configure($obj, ['test' => 2]);
        $this->assertEquals(2, $obj->test);
    }

    /**
     * @expectedException \Exception
     */
    public function testException()
    {
        Creator::createObject([]);
    }

    public function testParams()
    {
        $obj = Creator::createObject([
            'class' => TestCreate::class
        ], ['test' => 1]);
        $this->assertInstanceOf(TestCreate::class, $obj);
        $this->assertEquals(1, $obj->test);

        $obj = Creator::createObject([
            'class' => TestCreate::class
        ], ['test' => 1]);
        $this->assertInstanceOf(TestCreate::class, $obj);
        $this->assertEquals(1, $obj->test);
    }

    public function testSingleton()
    {
        $obj = Creator::createObject(['class' => TestSingleton::class], 1);
        $this->assertEquals(1, $obj->id);
        $obj = Creator::createObject(['class' => TestSingleton::class], 2);
        $this->assertEquals(1, $obj->id);
    }

    public function testUseTrait()
    {
        $obj = createObject(TestTrait::class);
        Creator::classUseTrait($obj, TestTrait::class);
    }
}
