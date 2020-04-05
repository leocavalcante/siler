<?php declare(strict_types=1);

namespace Siler\Test\Unit\GraphQL\Annotated;

use GraphQL\Language\DirectiveLocation;
use Siler\GraphQL\Annotation\Directive;

/** @Directive(locations={DirectiveLocation::FIELD}) */
class MyDirective
{

}
