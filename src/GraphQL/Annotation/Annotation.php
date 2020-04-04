<?php declare(strict_types=1);

namespace Siler\GraphQL\Annotation;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\DocParser;
use GraphQL\Type\Definition\EnumType as EnumTypeDefinition;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InterfaceType as InterfaceTypeDefinition;
use GraphQL\Type\Definition\NullableType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\UnionType as UnionTypeDefinition;
use GraphQL\Type\Schema;
use GraphQL\Type\SchemaConfig;
use Siler\GraphQL\Annotation\EnumType as EnumTypeAnnotation;
use Siler\GraphQL\Annotation\ObjectType as ObjectTypeAnnotation;
use function Siler\Klass\unqualified_name;

/**
 * Generates a schema from annotations.
 *
 * @param array<class-name> $typings
 * @param array<Type> $types
 * @return Schema
 * @throws AnnotationException
 * @throws \ReflectionException
 */
function annotated(array $typings, array $types = []): Schema
{
    AnnotationRegistry::registerLoader('class_exists');

    /** @var array<string, Type> $types */
    $types = array_reduce($types, function (array $types, Type $type): array {
        $types[$type->name] = $type;
        return $types;
    }, []);

    $reader = new AnnotationReader();
    $config = new SchemaConfig();

    foreach ($typings as $class_name) {
        $type = deannotate($types, $reader, $class_name);

        if ($type->name === 'Query' && $type instanceof ObjectType) {
            $config->setQuery($type);
        } elseif ($type->name === 'Mutation' && $type instanceof ObjectType) {
            $config->setMutation($type);
        } else {
            $types[$class_name] = $type;
        }
    }

    $config->setTypes($types);
    return new Schema($config);
}

/**
 * @param array<string, Type> $types
 * @param AnnotationReader $reader
 * @param string $class_name
 * @return Type|null
 * @throws \ReflectionException
 */
function deannotate(array $types, AnnotationReader $reader, string $class_name): ?Type
{
    $reflection = new \ReflectionClass($class_name);

    /** @var ObjectTypeAnnotation|null $annotation */
    $annotation = $reader->getClassAnnotation($reflection, ObjectTypeAnnotation::class);
    if ($annotation !== null) {
        return deannotate_object($types, $reader, $class_name, $reflection, $annotation);
    }

    /** @var InputType|null $annotation */
    $annotation = $reader->getClassAnnotation($reflection, InputType::class);
    if ($annotation !== null) {
        return deannotate_input($types, $reader, $class_name, $reflection, $annotation);
    }

    /** @var EnumTypeAnnotation|null $annotation */
    $annotation = $reader->getClassAnnotation($reflection, EnumTypeAnnotation::class);
    if ($annotation !== null) {
        return deannotate_enum($class_name, $reflection, $annotation);
    }

    /** @var InterfaceType|null $annotation */
    $annotation = $reader->getClassAnnotation($reflection, InterfaceType::class);
    if ($annotation !== null) {
        return deannotate_interface($types, $reader, $class_name, $reflection, $annotation);
    }

    /** @var UnionType|null $annotation */
    $annotation = $reader->getClassAnnotation($reflection, UnionType::class);
    if ($annotation !== null) {
        return deannotate_union($types, $class_name, $annotation);
    }
}

/**
 * @param array<string, Type> $types
 * @param string $class_name
 * @param UnionType $annotation
 * @return UnionTypeDefinition
 */
function deannotate_union(array $types, string $class_name, UnionType $annotation): UnionTypeDefinition
{
    return new UnionTypeDefinition([
        'name' => $annotation->name ?? unqualified_name($class_name),
        'description' => $annotation->description,
        'resolveType' => static function ($value) use ($types): Type {
            return type_from_string($types, get_class($value));
        },
        'types' => array_map(function (string $type_name) use ($types): Type {
            return type_from_string($types, $type_name);
        }, $annotation->types),
    ]);
}

/**
 * @param array<string, Type> $types
 * @param AnnotationReader $reader
 * @param string $class_name
 * @param \ReflectionClass $reflection
 * @param InterfaceType $annotation
 * @return InterfaceTypeDefinition
 */
function deannotate_interface(array $types, AnnotationReader $reader, string $class_name, \ReflectionClass $reflection, InterfaceType $annotation): InterfaceTypeDefinition
{
    return new InterfaceTypeDefinition([
        'name' => $annotation->name ?? unqualified_name($class_name),
        'description' => $annotation->description,
        'fields' => array_reduce(
            $reflection->getMethods(\ReflectionMethod::IS_PUBLIC),
            static function (array $fields, \ReflectionMethod $method) use ($types, $reader, $annotation): array {
                /** @var Field|null $annotation */
                $annotation = $reader->getMethodAnnotation($method, Field::class);
                $method_name = $annotation->name ?? $method->getName();

                if ($annotation === null) {
                    return $fields;
                }

                if (!isset($annotation->type)) {
                    $annotation->type = $method->getReturnType()->getName();
                }

                $fields[$method_name] = [
                    'type' => deannotate_type($types, $annotation),
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
 * @param string $class_name
 * @param \ReflectionClass $reflection
 * @param EnumType $annotation
 * @return EnumTypeDefinition
 */
function deannotate_enum(string $class_name, \ReflectionClass $reflection, EnumTypeAnnotation $annotation): EnumTypeDefinition
{
    $parser = new DocParser();
    $parser->setImports(['enumval' => EnumVal::class]);
    $parser->setIgnoreNotImportedAnnotations(true);

    return new EnumTypeDefinition([
        'name' => $annotation->name ?? unqualified_name($class_name),
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
 * @param array<string, Type> $types
 * @param AnnotationReader $reader
 * @param string $class_name
 * @param \ReflectionClass $reflection
 * @param InputType $annotation
 * @return InputObjectType
 */
function deannotate_input(array $types, AnnotationReader $reader, string $class_name, \ReflectionClass $reflection, InputType $annotation): InputObjectType
{
    return new InputObjectType([
        'name' => $annotation->name ?? unqualified_name($class_name),
        'description' => $annotation->description,
        'fields' => deannotate_properties($types, $reflection, $reader),
    ]);
}

/**
 * @param array<string, Type> $types
 * @param AnnotationReader $reader
 * @param string $class_name
 * @param \ReflectionClass $reflection
 * @param ObjectTypeAnnotation $annotation
 * @return ObjectType
 */
function deannotate_object(array $types, AnnotationReader $reader, string $class_name, \ReflectionClass $reflection, ObjectTypeAnnotation $annotation): ObjectType
{
    $annotated_method_fields = array_reduce(
        $reflection->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_STATIC),
        static function (array $fields, \ReflectionMethod $method) use ($types, $reader, $annotation): array {
            /** @var Field|null $annotation */
            $annotation = $reader->getMethodAnnotation($method, Field::class);
            $method_name = $annotation->name ?? $method->getName();

            if ($annotation === null) {
                return $fields;
            }

            if (!isset($annotation->type)) {
                $annotation->type = $method->getReturnType()->getName();
            }

            $fields[$method_name] = [
                'type' => deannotate_type($types, $annotation),
                'name' => $method_name,
                'description' => $annotation->description,
                'args' => deannotate_args($types, $method, $reader),
                'resolve' => static function ($root, array $args, $context, ResolveInfo $info) use ($method) {
                    return $method->invoke(null, $root, $args, $context, $info);
                }
            ];

            return $fields;
        },
        []
    );

    return new ObjectType([
        'name' => $annotation->name ?? unqualified_name($class_name),
        'description' => $annotation->description,
        'fields' => array_merge(deannotate_properties($types, $reflection, $reader), $annotated_method_fields),
        'interfaces' => array_map(function (string $interface_name) use ($types): Type {
            return type_from_string($types, $interface_name);
        }, $reflection->getInterfaceNames()),
    ]);
}

/**
 * @param array<string, Type> $types
 * @param \ReflectionClass $reflection
 * @param AnnotationReader $reader
 * @return array<string, array<string, mixed>
 */
function deannotate_properties(array $types, \ReflectionClass $reflection, AnnotationReader $reader): array
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
        $annotation = $reader->getPropertyAnnotation($prop, Field::class);
        $prop_name = $annotation->name ?? $prop->getName();

        $fields[$prop_name] = [
            'name' => $prop_name,
            'type' => deannotate_type($types, $annotation),
            'description' => $annotation->description,
        ];
    }

    return $fields;
}

/**
 * @param array<string, Type> $types
 * @param \ReflectionMethod $method
 * @param AnnotationReader $reader
 * @return array<string, Type>
 */
function deannotate_args(array $types, \ReflectionMethod $method, AnnotationReader $reader): array
{
    $args = [];

    /** @var Args|null $annotation */
    $annotation = $reader->getMethodAnnotation($method, Args::class);

    if ($annotation === null) {
        return $args;
    }

    foreach ($annotation->fields as $field) {
        $args[$field->name] = deannotate_type($types, $field);
    }

    return $args;
}

/**
 * @param array<string, Type> $types
 * @param Field $annotation
 * @return Type
 */
function deannotate_type(array $types, Field $annotation): Type
{
    $type = type_from_string($types, $annotation->listOf ?? $annotation->type);

    if ($type instanceof NullableType) {
        $type = Type::nonNull($type);
    }

    if (isset($annotation->listOf)) {
        $type = Type::nonNull(Type::listOf($type));
    }

    return $type;
}

/**
 * @param array<string, Type> $types
 * @param string|class-string $str
 * @return Type
 */
function type_from_string(array $types, string $str): Type
{
    if (array_key_exists($str, $types)) {
        return $types[$str];
    }

    $standards = Type::getStandardTypes();
    if (array_key_exists($str, $standards)) {
        return $standards[$str];
    }

    if ('string' === $str) {
        return Type::string();
    }

    if ('int' === $str) {
        return Type::int();
    }

    if ('bool' === $str) {
        return Type::boolean();
    }

    if ('float' === $str) {
        return Type::float();
    }

    if (class_exists($str)) {
        $type = new $str();

        if ($type instanceof Type) {
            return $type;
        }

        throw new \TypeError("Class $str does exists, but is not a Type. Does it have Annotations and it's added to annotated function array?");
    }

    throw new \TypeError("Provided class name $str is not a valid type. Perhaps your forgot to place it before another type that uses it in the `annotated` function arguments.");
}
