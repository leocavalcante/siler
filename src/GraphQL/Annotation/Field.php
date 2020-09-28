<?php declare(strict_types=1);

namespace Siler\GraphQL\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"METHOD", "PROPERTY", "ANNOTATION"})
 * @psalm-suppress MissingConstructor
 */
final class Field
{
    /**
     * @var string
     * @psalm-var string|null
     */
    public $type;
    /**
     * @var string
     * @psalm-var string|null
     */
    public $name;
    /**
     * @var string
     * @psalm-var string|null
     */
    public $description;
    /**
     * @var string
     * @psalm-var string|null
     */
    public $listOf;
    /**
     * @var boolean
     * @psalm-var boolean|null
     */
    public $nullable = false;
    /**
     * @var boolean
     * @psalm-var boolean|null
     */
    public $nullableList = false;
    /**
     * @var callable|null
     */
    public $resolve;

    public function type(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function description(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function listOf(string $listOf): self
    {
        $this->listOf = $listOf;
        return $this;
    }

    public function nullable(bool $nullable): self
    {
        $this->nullable = $nullable;
        return $this;
    }

    public function nullableList(bool $nullableList): self
    {
        $this->nullableList = $nullableList;
        return $this;
    }

    public function resolve(?callable $resolve): self
    {
        $this->resolve = $resolve;
        return $this;
    }
}
