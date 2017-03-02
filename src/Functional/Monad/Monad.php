<?php

namespace Siler\Functional\Monad;

function identity($value)
{
    return new Identity($value);
}

function maybe($value)
{
    return new Maybe($value);
}
