<?php declare(strict_types=1);

namespace Siler\Prelude;

/**
 * @template T
 */
interface PatchInterface
{
    /**
     * @param array $data
     * @return mixed
     * @psalm-return T
     */
    public function patch(array $data);
}
