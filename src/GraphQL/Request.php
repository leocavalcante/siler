<?php declare(strict_types=1);

namespace Siler\GraphQL;

/**
 * @internal ValueBag for GraphQL requests.
 */
final class Request
{
    /** @var string */
    private $query;
    /** @var array */
    private $variables;
    /** @var string|null */
    private $operationName;

    /**
     * @param string $query
     * @param array $variables
     * @param string|null $operationName
     */
    public function __construct(string $query, array $variables, ?string $operationName = null)
    {
        $this->query = $query;
        $this->variables = $variables;
        $this->operationName = $operationName;
    }

    /**
     * @return array<string, mixed>
     * @internal For legacy purposes, may be removed.
     */
    public function toArray(): array
    {
        return [
            'query' => $this->query,
            'variables' => $this->variables,
            'operationName' => $this->operationName,
        ];
    }
}
