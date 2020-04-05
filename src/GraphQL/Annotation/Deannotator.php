<?php declare(strict_types=1);

namespace Siler\GraphQL\Annotation;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\DocParser;
use GraphQL\Type\Definition\Directive as DirectiveDefinition;
use GraphQL\Type\Definition\EnumType as EnumTypeDefinition;
use GraphQL\Type\Definition\InputObjectType as InputTypeDefinition;
use GraphQL\Type\Definition\InterfaceType as InterfaceTypeDefinition;
use GraphQL\Type\Definition\NullableType;
use GraphQL\Type\Definition\ObjectType as ObjectTypeDefinition;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\UnionType as UnionTypeDefinition;
use GraphQL\Type\Schema;
use GraphQL\Type\SchemaConfig;

/**
 * @internal
 */
final class Deannotator
{
    /** @var AnnotationReader */
    private $reader;
    /** @var array<string, Type> */
    private $types;
    /** @var DirectiveDefinition[] */
    private $directives;
    /** @var array<string> */
    private $annotations;

    /**
     * @param AnnotationReader $reader
     * @param array $types
     * @param array $directives
     */
    public function __construct(AnnotationReader $reader, array $types = [], array $directives = [])
    {
        $this->reader = $reader;
        $this->types = $types;
        $this->directives = $directives;

        $this->annotations = [
            ObjectType::class,
            EnumType::class,
            InterfaceType::class,
            UnionType::class,
            InputType::class,
            Directive::class,
        ];
    }

    /**
     * @param array $classNames
     * @return Schema
     * @throws \ReflectionException
     */
    public function deannotate(array $classNames): Schema
    {
        $config = new SchemaConfig();

        foreach ($classNames as $class_name) {
            /** @var Type|DirectiveDefinition|null $deannotated */
            $deannotated = $this->deannotateClass($class_name);

            if ($deannotated instanceof DirectiveDefinition) {
                $this->directives[] = $deannotated;
            }

            if ($deannotated instanceof Type) {
                if ($deannotated->name === 'Query' && $deannotated instanceof ObjectTypeDefinition) {
                    $config->setQuery($deannotated);
                } elseif ($deannotated->name === 'Mutation' && $deannotated instanceof ObjectTypeDefinition) {
                    $config->setMutation($deannotated);
                } else {
                    $this->types[$class_name] = $deannotated;
                }
            }
        }

        $config->setDirectives($this->directives);
        $config->setTypes($this->types);
        return new Schema($config);
    }

    /**
     * @param string $className
     * @return mixed
     * @throws \ReflectionException
     */
    private function deannotateClass(string $className)
    {
        $reflection = new \ReflectionClass($className);

        foreach ($this->annotations as $annotation_class) {
            $annotation = $this->reader->getClassAnnotation($reflection, $annotation_class);

            if ($annotation instanceof ObjectType) {
                return $this->objectType($reflection, $annotation);
            }

            if ($annotation instanceof EnumType) {
                return $this->enumType($reflection, $annotation);
            }

            if ($annotation instanceof InterfaceType) {
                return $this->interfaceType($reflection, $annotation);
            }

            if ($annotation instanceof UnionType) {
                return $this->unionType($reflection, $annotation);
            }

            if ($annotation instanceof InputType) {
                return $this->inputType($reflection, $annotation);
            }

            if ($annotation instanceof Directive) {
                return $this->directive($reflection, $annotation);
            }
        }

        return null;
    }

    /**
     * @param \ReflectionClass $reflection
     * @param ObjectType $annotation
     * @return ObjectTypeDefinition
     */
    private function objectType(\ReflectionClass $reflection, ObjectType $annotation): ObjectTypeDefinition
    {
        $fields = array_reduce(
            $reflection->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_STATIC),
            function (array $fields, \ReflectionMethod $method) use ($annotation): array {
                /** @var Field|null $annotation */
                $annotation = $this->reader->getMethodAnnotation($method, Field::class);
                $method_name = $annotation->name ?? $method->getName();

                if ($annotation === null) {
                    return $fields;
                }

                if (!isset($annotation->type)) {
                    $annotation->type = $method->getReturnType()->getName();
                }

                $fields[$method_name] = [
                    'type' => $this->type($annotation),
                    'name' => $method_name,
                    'description' => $annotation->description,
                    'args' => $this->args($method),
                    'resolve' => static function ($root, array $args, $context, ResolveInfo $info) use ($method) {
                        return $method->invoke(null, $root, $args, $context, $info);
                    }
                ];

                return $fields;
            },
            []
        );

        return new ObjectTypeDefinition([
            'name' => $annotation->name ?? $reflection->getShortName(),
            'description' => $annotation->description,
            'fields' => array_merge($this->fields($reflection), $fields),
            'interfaces' => array_map(function (string $interface_name): Type {
                return $this->typeFromString($interface_name);
            }, $reflection->getInterfaceNames()),
        ]);
    }

    /**
     * @param Field $annotation
     * @return Type
     */
    private function type(Field $annotation): Type
    {
        $type = $this->typeFromString($annotation->listOf ?? $annotation->type);

        if ($type instanceof NullableType) {
            $type = Type::nonNull($type);
        }

        if (isset($annotation->listOf)) {
            $type = Type::nonNull(Type::listOf($type));
        }

        return $type;
    }

    /**
     * @param string|class-string $str
     * @return Type
     */
    private function typeFromString(string $value): Type
    {
        if (array_key_exists($value, $this->types)) {
            return $this->types[$value];
        }

        $standards = Type::getStandardTypes();
        if (array_key_exists($value, $standards)) {
            return $standards[$value];
        }

        if ('string' === $value) {
            return Type::string();
        }

        if ('int' === $value) {
            return Type::int();
        }

        if ('bool' === $value) {
            return Type::boolean();
        }

        if ('float' === $value) {
            return Type::float();
        }

        if (class_exists($value)) {
            $type = new $value();

            if ($type instanceof Type) {
                return $type;
            }

            throw new \TypeError("Class $value does exists, but is not a Type. Does it have Annotations and it's added to annotated function array?");
        }

        throw new \TypeError("Provided class name $value is not a valid type. Perhaps your forgot to place it before another type that uses it in the `annotated` function arguments.");
    }

    /**
     * @param \ReflectionMethod $method
     * @return array<string, Type>
     */
    private function args(\ReflectionMethod $method): array
    {
        $args = [];

        /** @var Args|null $annotation */
        $annotation = $this->reader->getMethodAnnotation($method, Args::class);

        if ($annotation === null) {
            return $args;
        }

        foreach ($annotation->fields as $field) {
            $args[$field->name] = $this->type($field);
        }

        return $args;
    }

    /**
     * @param \ReflectionClass $reflection
     * @return array<string, array<string, mixed>
     */
    private function fields(\ReflectionClass $reflection): array
    {
        /** @var array<string, array<string, mixed> $fields */
        $fields = [];

        $props = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);

        // TODO: We are going only one level deep, this should be recursive.
        if ($reflection->getParentClass()) {
            $props = array_merge($props, $reflection->getParentClass()->getProperties(\ReflectionProperty::IS_PUBLIC));
        }

        foreach ($props as $prop) {
            /** @var Field $annotation */
            $annotation = $this->reader->getPropertyAnnotation($prop, Field::class);
            $prop_name = $annotation->name ?? $prop->getName();

            $fields[$prop_name] = [
                'name' => $prop_name,
                'type' => $this->type($annotation),
                'description' => $annotation->description,
            ];
        }

        return $fields;
    }

    /**
     * @param \ReflectionClass $reflection
     * @param EnumType $annotation
     * @return EnumTypeDefinition
     */
    private function enumType(\ReflectionClass $reflection, EnumType $annotation): EnumTypeDefinition
    {
        $parser = new DocParser();
        $parser->setImports(['enumval' => EnumVal::class]);
        $parser->setIgnoreNotImportedAnnotations(true);

        return new EnumTypeDefinition([
            'name' => $annotation->name ?? $reflection->getShortName(),
            'description' => $annotation->description,
            'values' => array_reduce(
                $reflection->getReflectionConstants(),
                static function (array $values, \ReflectionClassConstant $const) use ($parser): array {
                    $parsed = $parser->parse($const->getDocComment());
                    /** @var EnumVal $annotation */
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
     * @param InterfaceType $annotation
     * @return InterfaceTypeDefinition
     */
    private function interfaceType(\ReflectionClass $reflection, InterfaceType $annotation): InterfaceTypeDefinition
    {
        return new InterfaceTypeDefinition([
            'name' => $annotation->name ?? $reflection->getShortName(),
            'description' => $annotation->description,
            'fields' => array_reduce(
                $reflection->getMethods(\ReflectionMethod::IS_PUBLIC),
                function (array $fields, \ReflectionMethod $method) use ($annotation): array {
                    /** @var Field|null $annotation */
                    $annotation = $this->reader->getMethodAnnotation($method, Field::class);
                    $method_name = $annotation->name ?? $method->getName();

                    if ($annotation === null) {
                        return $fields;
                    }

                    if (!isset($annotation->type)) {
                        $annotation->type = $method->getReturnType()->getName();
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
     * @param UnionType $annotation
     * @return UnionTypeDefinition
     */
    private function unionType(\ReflectionClass $reflection, UnionType $annotation): UnionTypeDefinition
    {
        return new UnionTypeDefinition([
            'name' => $annotation->name ?? $reflection->getShortName(),
            'description' => $annotation->description,
            'resolveType' => function ($value): Type {
                return $this->typeFromString(get_class($value));
            },
            'types' => array_map(function (string $type): Type {
                return $this->typeFromString($type);
            }, $annotation->types),
        ]);
    }

    /**
     * @param \ReflectionClass $reflection
     * @param InputType $annotation
     * @return InputTypeDefinition
     */
    private function inputType(\ReflectionClass $reflection, InputType $annotation): InputTypeDefinition
    {
        return new InputTypeDefinition([
            'name' => $annotation->name ?? $reflection->getShortName(),
            'description' => $annotation->description,
            'fields' => $this->fields($reflection),
        ]);
    }

    /**
     * @param \ReflectionClass $reflection
     * @param Directive $annotation
     * @return DirectiveDefinition
     */
    private function directive(\ReflectionClass $reflection, Directive $annotation): DirectiveDefinition
    {
        return new DirectiveDefinition([
            'name' => $annotation->name ?? $reflection->getShortName(),
            'description' => $annotation->description,
            'locations' => $annotation->locations,
        ]);
    }
}
