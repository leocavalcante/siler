<?php declare(strict_types=1);

namespace Siler\Example\GraphQL\Annotation;

use GraphQL\Language\DirectiveLocation;
use Siler\GraphQL\Annotation\Directive;

/**
 * @Directive(locations={DirectiveLocation::FIELD})
 */
class Upper
{

}
