<?php declare(strict_types=1);

namespace App;

use Siler\GraphQL\Annotation\InputType;

/**
 * @InputType
 */
class TodoInput
{
    /** @var string */
    public $title;
}
