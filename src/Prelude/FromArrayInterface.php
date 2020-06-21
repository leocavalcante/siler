<?php declare(strict_types=1);

namespace Siler\Prelude;

/**
 * @template T
 */
interface FromArrayInterface
{
    /**
     * @param array $data
     * @return mixed
     * @psalm-return T
     */
    public static function fromArray(array $data);
}
