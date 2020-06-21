<?php declare(strict_types=1);

namespace Siler\GraphQL;

use DateTime;
use GraphQL\Error\Error;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;

class DateScalar extends ScalarType
{
    /** @var string */
    public const FORMAT = 'Y-m-d';

    /** @var string */
    public $name = 'Date';

    /**
     * @param mixed $value
     * @return string
     * @throws Error
     */
    public function serialize($value): string
    {
        if ($value instanceof DateTime) {
            return $value->format(strval(static::FORMAT));
        }

        throw new Error('Don\'t know how to serialize non-DateTime');
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

        throw new Error(sprintf('Unable to parse non string literal as %', $this->name));
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
            $date_time = DateTime::createFromFormat(strval(static::FORMAT), $value);
        }

        if ($date_time === false) {
            throw new Error(sprintf("Error parsing $value as %s. Is it in %s format?", $this->name, strval(static::FORMAT)));
        }

        return $date_time;
    }
}
