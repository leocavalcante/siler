<?php declare(strict_types=1);

namespace Siler\GraphQL;

use GraphQL\Error\Error;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;

/**
 * Class DateTimeScalar
 * @package App
 */
final class DateTimeScalar extends ScalarType
{
    public const FORMAT = 'Y-m-d H:i:s';

    public $name = 'DateTime';

    /**
     * @param \DateTime $value
     * @return string
     */
    public function serialize($value): string
    {
        return $value->format(self::FORMAT);
    }

    /**
     * @param Node $valueNode
     * @param array|null $variables
     * @return \DateTime
     * @throws Error
     */
    public function parseLiteral($valueNode, ?array $variables = null)
    {
        if ($valueNode instanceof StringValueNode) {
            return $this->parseValue($valueNode->value);
        }

        throw new Error('Unable to parse non string literal as DateTime');
    }

    /**
     * @param string $value
     * @return \DateTime
     */
    public function parseValue($value): \DateTime
    {
        return \DateTime::createFromFormat(self::FORMAT, $value);
    }
}
