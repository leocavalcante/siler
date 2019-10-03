<?php

declare(strict_types=1);

/**
 * Borrowed from https://github.com/webonyx/graphql-php/blob/master/src/Utils/BuildSchema.php
 * Added the type resolver feature.
 */

namespace Siler\GraphQL;

use GraphQL\Error\Error;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Language\AST\SchemaDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Schema;
use GraphQL\Utils\ASTDefinitionBuilder;

use function array_map;
use function array_reduce;
use function sprintf;

/**
 * Build instance of `GraphQL\Type\Schema` out of type language definition (string or parsed AST)
 * See [section in docs](type-system/type-language.md) for details.
 */
class BuildSchema
{
    /** @var DocumentNode */
    private $ast;
/** @var Node[] */
    private $nodeMap;
/** @var callable|null */
    private $typeConfigDecorator;
/** @var bool[] */
    private $options;
/** @var array */
    private $resolvers;
    public function __construct(DocumentNode $ast, ?callable $typeConfigDecorator = null, array $options = [], array $resolvers = [])
    {
        $this->ast = $ast;
        $this->typeConfigDecorator = $typeConfigDecorator;
        $this->options = $options;
        $this->resolvers = $resolvers;
    }

    public static function build($source, ?callable $typeConfigDecorator = null, array $options = [], array $resolvers = [])
    {
        $doc = $source instanceof DocumentNode ? $source : Parser::parse($source);
        return self::buildAST($doc, $typeConfigDecorator, $options, $resolvers);
    }

    public static function buildAST(DocumentNode $ast, ?callable $typeConfigDecorator = null, array $options = [], array $resolvers = [])
    {
        $builder = new self($ast, $typeConfigDecorator, $options, $resolvers);
        return $builder->buildSchema();
    }

    public function buildSchema()
    {
        /** @var SchemaDefinitionNode $schemaDef */
        $schemaDef = null;
        $typeDefs = [];
        $this->nodeMap = [];
        $directiveDefs = [];
        foreach ($this->ast->definitions as $d) {
            switch ($d->kind) {
                case NodeKind::SCHEMA_DEFINITION:
                    if ($schemaDef) {
                        throw new Error('Must provide only one schema definition.');
                    }
                    $schemaDef = $d;

                    break;
                case NodeKind::SCALAR_TYPE_DEFINITION:
                case NodeKind::OBJECT_TYPE_DEFINITION:
                case NodeKind::INTERFACE_TYPE_DEFINITION:
                case NodeKind::ENUM_TYPE_DEFINITION:
                case NodeKind::UNION_TYPE_DEFINITION:
                case NodeKind::INPUT_OBJECT_TYPE_DEFINITION:
                    $typeName = $d->name->value;
                    if (!empty($this->nodeMap[$typeName])) {
                        throw new Error(sprintf('Type "%s" was defined more than once.', $typeName));
                    }
                    if (in_array($typeName, $this->resolvers) && $d->kind === NodeKind::SCALAR_TYPE_DEFINITION) {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       break;
                    }
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           $typeDefs[] = $d;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           $this->nodeMap[$typeName] = $d;

                    break;
                case NodeKind::DIRECTIVE_DEFINITION:
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       $directiveDefs[] = $d;

                    break;
            }
        }

        $operationTypes = $schemaDef
            ? $this->getOperationTypes($schemaDef)
            : [
                'query' => isset($this->nodeMap['Query']) ? 'Query' : null,
                'mutation' => isset($this->nodeMap['Mutation']) ? 'Mutation' : null,
                'subscription' => isset($this->nodeMap['Subscription']) ? 'Subscription' : null,
            ];
        $builder = new ASTDefinitionBuilder($this->nodeMap, $this->options, function ($typeName) {

            if (array_key_exists($typeName, $this->resolvers)) {
                return $this->resolvers[$typeName];
            }
                throw new Error('Type "' . $typeName . '" not found in document.');
        }, $this->typeConfigDecorator);
        $directives = array_map(static function ($def) use ($builder) {

                return $builder->buildDirective($def);
        }, $directiveDefs);
// If specified directives were not explicitly declared, add them.
        $skip = array_reduce($directives, static function ($hasSkip, $directive) {

                return (bool)$hasSkip || $directive->name === 'skip';
        });
        if (!$skip) {
            $directives[] = Directive::skipDirective();
        }

        $include = array_reduce($directives, static function ($hasInclude, $directive) {

                return (bool)$hasInclude || $directive->name === 'include';
        });
        if (!$include) {
            $directives[] = Directive::includeDirective();
        }

        $deprecated = array_reduce($directives, static function ($hasDeprecated, $directive) {

                return (bool)$hasDeprecated || $directive->name === 'deprecated';
        });
        if (!$deprecated) {
            $directives[] = Directive::deprecatedDirective();
        }

        // Note: While this could make early assertions to get the correctly
        // typed values below, that would throw immediately while type system
        // validation with validateSchema() will produce more actionable results.

        return new Schema([
            'query' => isset($operationTypes['query'])
                ? $builder->buildType($operationTypes['query'])
                : null,
            'mutation' => isset($operationTypes['mutation'])
                ? $builder->buildType($operationTypes['mutation'])
                : null,
            'subscription' => isset($operationTypes['subscription'])
                ? $builder->buildType($operationTypes['subscription'])
                : null,
            'typeLoader' => static function ($name) use ($builder) {

                return $builder->buildType($name);
            },
            'directives' => $directives,
            'astNode' => $schemaDef,
            'types' => function () use ($builder) {

                $types = [];
                foreach ($this->nodeMap as $name => $def) {
                    $types[] = $builder->buildType($def->name->value);
                }

                return $types;
            },
        ]);
    }

    private function getOperationTypes($schemaDef)
    {
        $opTypes = [];
        foreach ($schemaDef->operationTypes as $operationType) {
            $typeName = $operationType->type->name->value;
            $operation = $operationType->operation;
            if (isset($opTypes[$operation])) {
                throw new Error(sprintf('Must provide only one %s type in schema.', $operation));
            }

            if (!isset($this->nodeMap[$typeName])) {
                throw new Error(sprintf('Specified %s type "%s" not found in document.', $operation, $typeName));
            }

            $opTypes[$operation] = $typeName;
        }

        return $opTypes;
    }
}
