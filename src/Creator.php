<?php

namespace Mindy\Creator;

use InvalidArgumentException;

/**
 * Class Creator
 * @package Mindy\Helper
 */
class Creator
{
    /**
     * @var string
     */
    const SINGLETON_METHOD = 'getInstance';
    /**
     * @var array initial property values that will be applied to objects newly created via [[createObject]].
     * The array keys are class names without leading backslashes "\", and the array values are the corresponding
     * name-value pairs for initializing the created class instances. For example,
     *
     * ~~~
     * [
     *     'Bar' => [
     *         'prop1' => 'value1',
     *         'prop2' => 'value2',
     *     ],
     *     'mycompany\foo\Car' => [
     *         'prop1' => 'value1',
     *         'prop2' => 'value2',
     *     ],
     * ]
     * ~~~
     *
     * @see createObject()
     */
    public static $objectConfig = [];

    /**
     * @param $config
     * @return null|object
     */
    public static function createObject($config)
    {
        if ($config instanceof \Closure) {
            return $config->__invoke();
        }

        static $reflections = [];

        if (is_string($config)) {
            $class = $config;
            $config = [];
        } elseif (isset($config['class'])) {
            $class = $config['class'];
            unset($config['class']);
        } else {
            throw new InvalidArgumentException('Object configuration must be an array containing a "class" element.');
        }

        $class = ltrim($class, '\\');
        if (isset(static::$objectConfig[$class])) {
            $config = array_merge(static::$objectConfig[$class], $config);
        }

        if (($n = func_num_args()) > 1) {
            /** @var \ReflectionClass $reflection */
            if (isset($reflections[$class])) {
                $reflection = $reflections[$class];
            } else {
                $reflection = $reflections[$class] = new \ReflectionClass($class);
            }

            $args = func_get_args();
            // remove $config
            array_shift($args);
            if (!empty($config)) {
                $args[] = $config;
            }

            if (method_exists($class, self::SINGLETON_METHOD)) {
                $obj = call_user_func_array([$class, self::SINGLETON_METHOD], $args);
            } else {
                $obj = $reflection->newInstanceArgs($args);
            }
        } else {
            $obj = empty($config) ? new $class : new $class($config);
        }

        return $obj;
    }

    /**
     * @param $class
     * @param bool $autoload
     * @return array
     */
    public static function classUseTrait($class, $trait, $autoload = true)
    {
        $traits = [];

        do {
            $traits = array_merge(class_uses($class, $autoload), $traits);
        } while ($class = get_parent_class($class));

        foreach ($traits as $trait => $same) {
            $traits = array_merge(class_uses($trait, $autoload), $traits);
        }

        return array_key_exists($trait, array_unique($traits));
    }

    /**
     * Configures an object with the initial property values.
     * @param object $object the object to be configured
     * @param array $properties the property initial values given in terms of name-value pairs.
     * @return object the object itself
     */
    public static function configure($object, $properties)
    {
        foreach ($properties as $name => $value) {
            $object->{$name} = $value;
        }
        return $object;
    }
}
