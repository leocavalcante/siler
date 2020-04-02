<?php declare(strict_types=1);

namespace Siler\Prelude;

use function Siler\Obj\patch;

/**
 * OO interface for `Obj/patch`.
 * @package Siler\Prelude
 */
trait Patch
{
    /**
     * @param array $arr
     * @return $this
     */
    public function patch(array $arr): self
    {
        return patch($this, $arr);
    }
}
