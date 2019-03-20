<?php

namespace Siler\Functional\Monad;

/**
 * @param mixed $value
 *
 * @return Identity
 */
function identity($value): Identity
{
    return new Identity($value);
}

/**
 * @param mixed $value
 *
 * @return Maybe
 */
function maybe($value): Maybe
{
    return new Maybe($value);
}
