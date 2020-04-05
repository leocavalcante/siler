<?php declare(strict_types=1);

namespace Siler\GraphQL;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\DocParser;
use GraphQL\Type\Definition;
use GraphQL\Type\Schema;
use GraphQL\Type\SchemaConfig;
use Siler\GraphQL\Annotation;

/**
 * @internal
 */
final class Deannotator
{
    /** @var AnnotationReader */
    private $reader;
    /** @var array<string, Definition\Type> */
    private $types;
    /** @var Definition\Directive[] */
    private $directives;
    /** @var array<string> */
    private $annotations;

    /**
     * @param AnnotationReader $reader
     * @param array<string, Definition\Type> $types
     * @param Definition\Directive[] $directives
     */
    public function __construct(AnnotationReader $reader, array $types = [], array $directives = [])
    {
        $this->reader = $reader;
        $this->types = $types;
        $this->directives = $directives;

        $this->annotations = [
            Annotation\ObjectType::class,
            Annotation\EnumType::class,
            Annotation\InterfaceType::class,
            Annotation\UnionType::class,
            Annotation\InputType::class,
            Annotation\Directive::class,
        ];
    }

    /**
     * @param array<class-string> $classNames
     * @return Schema
     * @throws \ReflectionException
     */
    public function deannotate(array $classNames): Schema
    {
        $config = new SchemaConfig();

        foreach ($classNames as $class_name) {
            /** @var Definition\Type|Definition\Directive|null $deannotated */
            $deannotated = $this->deannotateClass($class_name);

            if ($deannotated instanceof Definition\Directive) {
                $this->directives[] = $deannotated;
            }

            if ($deannotated instanceof Definition\Type) {
                if ($deannotated instanceof Definition\ObjectType) {
                    if ($deannotated->name === 'Query') {
                        $config->setQuery($deannotated);
                    } elseif ($deannotated->name === 'Mutation') {
                        $config->setMutation($deannotated);
                    }
                }

                $this->types[$class_name] = $deannotated;
            }
        }

        $config->setDirectives($this->directives);
        $config->setTypes($this->types);
        return new Schema($config);
    }

    /**
     * @param string $className
     * @psalm-param class-string $className
     * @return mixed
     * @psalm-return Definition\Type|Definition\Directive|null
     * @throws \ReflectionException
     */
    private function deannotateClass(string $className)
    {
        $reflection = new \ReflectionClass($className);

        foreach ($this->annotations as $annotation_class) {
            $annotation = $this->reader->getClassAnnotation($reflection, $annotation_class);

            if ($annotation instanceof Annotation\ObjectType) {
                return $this->objectType($reflection, $annotation);
            }

            if ($annotation instanceof Annotation\EnumType) {
                return $this->enumType($reflection, $annotation);
            }

            if ($annotation instanceof Annotation\InterfaceType) {
                return $this->interfaceType($reflection, $annotation);
            }

            if ($annotation instanceof Annotation\UnionType) {
                return $this->unionType($reflection, $annotation);
            }

            if ($annotation instanceof Annotation\InputType) {
                return $this->inputType($reflection, $annotation);
            }

            if ($annotation instanceof Annotation\Directive) {
                return $this->directive($reflection, $annotation);
            }
        }

        return null;
    }

    /**
     * @param \ReflectionClass $reflection
     * @param Annotation\ObjectType $annotation
     * @return Definition\ObjectType
     * @throws \ReflectionException
     */
    private function objectType(\ReflectionClass $reflection, Annotation\ObjectType $annotation): Definition\ObjectType
    {
        $fields = array_reduce(
            $reflection->getMethods(\ReflectionMethod::IS_PUBLIC),
            function (array $fields, \ReflectionMethod $method) use ($annotation): array {
                /** @var Annotation\Field|null $annotation */
                $annotation = $this->reader->getMethodAnnotation($method, Annotation\Field::class);

                if ($annotation === null) {
                    return $fields;
                }

                $method_name = $annotation->name ?? $method->getName();

                if ($annotation->type === null) {
                    /** @var \ReflectionNamedType|null $return_type */
                    $return_type = $method->getReturnType();
                    if ($return_type !== null) {
                        $annotation->type = $return_type->getName();
                    }
                }

                $fields[$method_name] = [
                    'type' => $this->type($annotation),
                    'name' => $method_name,
                    'description' => $annotation->description,
                    'args' => $this->args($method),
                    'resolve' =>
                    /**
                     * @param mixed $root
                     * @param array $args
                     * @param mixed $context
                     * @param Definition\ResolveInfo $info
                     * @return mixed
                     */
                        static function ($root, array $args, $context, Definition\ResolveInfo $info) use ($method) {
                            if (!$method->isStatic() && is_object($root)) {
                                return $method->invoke($root, $root, $args, $context, $info);
                            }

                            return $method->invoke(null, $root, $args, $context, $info);
                        }
                ];

                return $fields;
            },
            []
        );

        return new Definition\ObjectType([
            'name' => $annotation->name ?? $reflection->getShortName(),
            'description' => $annotation->description,
            'fields' => array_merge($this->fields($reflection), $fields),
            'interfaces' => array_map(function (string $interface_name): Definition\Type {
                return $this->typeFromString($interface_name);
            }, $reflection->getInterfaceNames()),
        ]);
    }

    /**
     * @param Annotation\Field $annotation
     * @return Definition\Type
     * @throws \ReflectionException
     */
    private function type(Annotation\Field $annotation): Definition\Type
    {
        $type = $annotation->listOf ?? $annotation->type;

        if ($type === null) {
            throw new \UnexpectedValueException("Can't figure out a type from field {$annotation->name}. You should set type or listOf (in case of a list).");
        }

        $type = $this->typeFromString($type);

        if ($type instanceof Definition\NullableType) {
            $type = Definition\Type::nonNull($type);
        }

        if (isset($annotation->listOf)) {
            $type = Definition\Type::nonNull(Definition\Type::listOf($type));
        }

        return $type;
    }

    /**
     * @param string|class-string $value
     * @return Definition\Type
     * @throws \ReflectionException
     */
    private function typeFromString(string $value): Definition\Type
    {
        if (array_key_exists($value, $this->types)) {
            return $this->types[$value];
        }

        $standards = Definition\Type::getStandardTypes();
        if (array_key_exists($value, $standards)) {
            return $standards[$value];
        }

        if ('string' === $value) {
            return Definition\Type::string();
        }

        if ('int' === $value) {
            return Definition\Type::int();
        }

        if ('bool' === $value) {
            return Definition\Type::boolean();
        }

        if ('float' === $value) {
            return Definition\Type::float();
        }

        if (class_exists($value)) {
            $reflection = new \ReflectionClass($value);
            $type = $reflection->newInstanceWithoutConstructor();

            if ($type instanceof Definition\Type) {
                return $type;
            }

            throw new \TypeError("Class $value does exists, but is not a Type. Does it have Annotations and it's added to annotated function array?");
        }

        throw new \TypeError("Provided class name $value is not a valid type. Perhaps your forgot to place it before another type that uses it in the `annotated` function arguments.");
    }

    /**
     * @param \ReflectionMethod $method
     * @return array<string, Definition\Type>
     * @throws \ReflectionException
     */
    private function args(\ReflectionMethod $method): array
    {
        $args = [];

        /** @var Annotation\Args|null $annotation */
        $annotation = $this->reader->getMethodAnnotation($method, Annotation\Args::class);

        if ($annotation === null) {
            return $args;
        }

        foreach ($annotation->fields as $field) {
            if ($field->name === null) {
                throw new \UnexpectedValueException("Fields on Args must have a name.");
            }

            $args[$field->name] = $this->type($field);
        }

        return $args;
    }

    /**
     * @param \ReflectionClass $reflection
     * @return array<string, array<string, mixed>>
     * @throws \ReflectionException
     */
    private function fields(\ReflectionClass $reflection): array
    {
        /** @var array<string, array<string, mixed>> $fields */
        $fields = [];

        $props = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);

        // TODO: We are going only one level deep, this should be recursive.
        if ($reflection->getParentClass()) {
            $props = array_merge($props, $reflection->getParentClass()->getProperties(\ReflectionProperty::IS_PUBLIC));
        }

        foreach ($props as $prop) {
            /** @var Annotation\Field|null $annotation */
            $annotation = $this->reader->getPropertyAnnotation($prop, Annotation\Field::class);

            if ($annotation === null) {
                continue;
            }

            $name = $annotation->name ?? $prop->getName();

            $fields[$name] = [
                'name' => $name,
                'type' => $this->type($annotation),
                'description' => $annotation->description,
            ];
        }

        return $fields;
    }

    /**
     * @param \ReflectionClass $reflection
     * @param Annotation\EnumType $annotation
     * @return Definition\EnumType
     */
    private function enumType(\ReflectionClass $reflection, Annotation\EnumType $annotation): Definition\EnumType
    {
        $parser = new DocParser();
        $parser->setImports(['enumval' => Annotation\EnumVal::class]);
        $parser->setIgnoreNotImportedAnnotations(true);

        return new Definition\EnumType([
            'name' => $annotation->name ?? $reflection->getShortName(),
            'description' => $annotation->description,
            'values' => array_reduce(
                $reflection->getReflectionConstants(),
                static function (array $values, \ReflectionClassConstant $const) use ($parser): array {
                    $parsed = $parser->parse($const->getDocComment());
                    /** @var Annotation\EnumVal $annotation */
                    $annotation = $parsed[0];

                    $values[$annotation->name ?? $const->getName()] = [
                        'name' => $annotation->name ?? $const->getName(),
                        'description' => $annotation->description,
                        'value' => $annotation->value ?? $const->getValue(),
                    ];

                    return $values;
                },
                [],
            ),
        ]);
    }

    /**
     * @param \ReflectionClass $reflection
     * @param Annotation\InterfaceType $annotation
     * @return Definition\InterfaceType
     */
    private function interfaceType(\ReflectionClass $reflection, Annotation\InterfaceType $annotation): Definition\InterfaceType
    {
        return new Definition\InterfaceType([
            'name' => $annotation->name ?? $reflection->getShortName(),
            'description' => $annotation->description,
            'fields' => array_reduce(
                $reflection->getMethods(\ReflectionMethod::IS_PUBLIC),
                function (array $fields, \ReflectionMethod $method) use ($annotation): array {
                    /** @var Annotation\Field|null $annotation */
                    $annotation = $this->reader->getMethodAnnotation($method, Annotation\Field::class);

                    if ($annotation === null) {
                        return $fields;
                    }

                    $method_name = $annotation->name ?? $method->getName();

                    if ($annotation->type === null) {
                        /** @var \ReflectionNamedType|null $return_type */
                        $return_type = $method->getReturnType();
                        if ($return_type !== null) {
                            $annotation->type = $return_type->getName();
                        }
                    }

                    $fields[$method_name] = [
                        'type' => $this->type($annotation),
                        'name' => $method_name,
                        'description' => $annotation->description,
                    ];

                    return $fields;
                },
                []
            ),
        ]);
    }

    /**
     * @param \ReflectionClass $reflection
     * @param Annotation\UnionType $annotation
     * @return Definition\UnionType
     */
    private function unionType(\ReflectionClass $reflection, Annotation\UnionType $annotation): Definition\UnionType
    {
        return new Definition\UnionType([
            'name' => $annotation->name ?? $reflection->getShortName(),
            'description' => $annotation->description,
            'resolveType' => function (object $value): Definition\Type {
                return $this->typeFromString(get_class($value));
            },
            'types' => array_map(function (string $type): Definition\Type {
                return $this->typeFromString($type);
            }, $annotation->types ?? []),
        ]);
    }

    /**
     * @param \ReflectionClass $reflection
     * @param Annotation\InputType $annotation
     * @return Definition\InputObjectType
     * @throws \ReflectionException
     */
    private function inputType(\ReflectionClass $reflection, Annotation\InputType $annotation): Definition\InputObjectType
    {
        return new Definition\InputObjectType([
            'name' => $annotation->name ?? $reflection->getShortName(),
            'description' => $annotation->description,
            'fields' => $this->fields($reflection),
        ]);
    }

    /**
     * @param \ReflectionClass $reflection
     * @param Annotation\Directive $annotation
     * @return Definition\Directive
     */
    private function directive(\ReflectionClass $reflection, Annotation\Directive $annotation): Definition\Directive
    {
        return new Definition\Directive([
            'name' => $annotation->name ?? lcfirst($reflection->getShortName()),
            'description' => $annotation->description,
            'locations' => $annotation->locations,
        ]);
    }
}
