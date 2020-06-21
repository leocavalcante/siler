<?php declare(strict_types=1);

namespace Siler\Prelude;

use ReflectionObject;
use function Siler\Str\snake_case;

/**
 * Trait FromArray
 * @package Siler\Prelude
 */
trait FromArray
{
    /**
     * @param array $arr
     * @return static
     */
    public static function fromArray(array $arr): self
    {
        $obj = new self();
        $reflection = new ReflectionObject($obj);

        foreach ($reflection->getProperties() as $prop) {
            $prop_name = $prop->getName();
            $key = snake_case($prop_name);

            if (array_key_exists($prop_name, $arr)) {
                $obj->{$prop_name} = $arr[$prop_name];
            } elseif (array_key_exists($key, $arr)) {
                $obj->{$prop_name} = $arr[$key];
            }
        }

        return $obj;
    }
}
