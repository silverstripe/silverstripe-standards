<?php

declare(strict_types=1);

namespace SilverStripe\Standards\PHPStan;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Validates that the `self` keyword is only used in situations where it's not avoidable.
 *
 * See https://phpstan.org/developing-extensions/rules
 *
 * @implements Rule<Node>
 */
class KeywordSelfRule implements Rule
{
    public const IDENTIFIER = 'keyword.self';

    public function getNodeType(): string
    {
        return Node::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        switch (get_class($node)) {
            // fetching a constant, e.g. `self::MY_CONST` or `self::class`
            case ClassConstFetch::class:
                // Traits can use `self` to get const values - but otherwise follow same
                // logic as methods or properties.
                if ($scope->isInTrait()) {
                    return [];
                }
            // static method call, e.g. `self::myMethod()`
            case StaticCall::class:
            // fetching a static property, e.g. `self::$my_property`
            case StaticPropertyFetch::class:
                if (!is_a($node->class, Name::class) || $node->class->toString() !== 'self') {
                    return [];
                }
                break;
            // `self` as a type (for a property, argument, or return type)
            case Name::class:
                // Trait can use `self` for typehinting
                if ($scope->isInTrait() || $node->toString() !== 'self') {
                    return [];
                }
                break;
            // instantiating a new object from `self`, e.g. `new self()`
            case New_::class:
                if (!is_a($node->class, Name::class) || $node->class->toString() !== 'self') {
                    return [];
                }
                break;
            default:
                return [];
        }

        $actualClass = $scope->isInTrait()
            ? 'self::class'
            : $scope->getClassReflection()->getNativeReflection()->getShortName();
        return [
            RuleErrorBuilder::message(
                "Can't use keyword 'self'. Use '$actualClass' instead."
            )->identifier(self::IDENTIFIER)->build()
        ];
    }
}
