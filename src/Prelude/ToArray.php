<?php declare(strict_types=1);

namespace Siler\Prelude;

use function Siler\Str\snake_case;

/**
 * Trait ToArray
 * @package Siler\Prelude
 */
trait ToArray
{
    /**
     * @param bool $convertCase
     * @return array
     */
    public function toArray(bool $convertCase = true): array
    {
        $arr = [];
        $vars = get_object_vars($this);

        foreach ($vars as $key => $value) {
            if ($convertCase) {
                $key = snake_case($key);
            }

            $arr[$key] = $value;
        }

        return $arr;
    }
}
