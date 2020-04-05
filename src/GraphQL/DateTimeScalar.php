<?php declare(strict_types=1);

namespace Siler\GraphQL;

use GraphQL\Error\Error;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;

/**
 * Class DateTimeScalar
 */
final class DateTimeScalar extends ScalarType
{
    public const FORMAT = 'Y-m-d H:i:s';

    public $name = 'DateTime';

    /**
     * @param mixed $value
     * @return string
     * @throws Error
     */
    public function serialize($value): string
    {
        if ($value instanceof \DateTime) {
            return $value->format(self::FORMAT);
        }

        throw new Error('Don\'t know how to serialize non-DateTimes');
    }

    /**
     * @param Node $valueNode
     * @param array|null $variables
     * @return mixed
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
     * @param mixed $value
     * @return mixed
     * @throws Error
     */
    public function parseValue($value)
    {
        $date_time = false;

        if (is_string($value)) {
            $date_time = \DateTime::createFromFormat(self::FORMAT, $value);
        }

        if ($date_time === false) {
            throw new Error(sprintf("Error parsing $value as DateTime (%s)", self::FORMAT));
        }

        return $date_time;
    }
}
