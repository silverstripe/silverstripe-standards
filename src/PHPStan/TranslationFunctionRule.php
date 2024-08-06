<?php

declare(strict_types=1);

namespace SilverStripe\Standards\PHPStan;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\Generic\GenericClassStringType;

/**
 * Validates that the first argument to the _t() function isn't malformed.
 *
 * See https://phpstan.org/developing-extensions/rules
 *
 * @implements Rule<FuncCall|StaticCall>
 */
class TranslationFunctionRule implements Rule
{
    public const IDENTIFIER = 'translation.key';

    public function getNodeType(): string
    {
        return Node::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($node instanceof FuncCall) {
            return $this->processFuncCall($node, $scope);
        }
        if ($node instanceof StaticCall) {
            return $this->processStaticCall($node, $scope);
        }
        return [];
    }

    /**
     * Process calls to the global function _t()
     */
    private function processFuncCall(FuncCall $node, Scope $scope): array
    {
        if (!$this->callingUnderscoreT($node->name, $scope)) {
            return [];
        }
        return $this->processArgs($node->getArgs(), $scope);
    }

    /**
     * Process calls to the static method i18n::_t()
     */
    private function processStaticCall(StaticCall $node, Scope $scope): array
    {
        $class = $node->class;
        if (!($class instanceof Name)) {
            return [];
        }
        if ($class->toString() !== 'SilverStripe\i18n\i18n') {
            return [];
        }
        if (!$this->callingUnderscoreT($node->name, $scope)) {
            return [];
        }
        return $this->processArgs($node->getArgs(), $scope);
    }

    /**
     * Check if the method/function is called _t
     */
    private function callingUnderscoreT(Name|Identifier|Expr $name, Scope $scope)
    {
        if (($name instanceof Name) || ($name instanceof Identifier)) {
            $name = $name->toString();
        } else {
            $nameType = $scope->getType($name);
            // Ignore callables we can't get the names of
            if (!method_exists($nameType, 'getValue')) {
                return false;
            }
            $name = $nameType->getValue();
        }
        return $name === '_t';
    }

    /**
     * Check that the first arg value can be evaluated and has exactly one period.
     *
     * @param Arg[] $args
     */
    private function processArgs(array $args, Scope $scope): array
    {
        // If we have no args PHP itself will complain and it'll be caught by other linting, so just skip.
        if (count($args) < 1) {
            return [];
        }

        $entityArg = $args[0]->value;
        $argValue = $this->getStringValue($entityArg, $scope);
        // If phpstan can't get us a nice clear value, text collector almost certainly can't either.
        if ($argValue === null) {
            return [
                RuleErrorBuilder::message(
                    'Can\'t determine value of first argument to _t(). Use a simpler value.'
                )->identifier(self::IDENTIFIER)->build()
            ];
        }

        if (substr_count($argValue, '.') !== 1) {
            return [
                RuleErrorBuilder::message(
                    'First argument passed to _t() must have exactly one period.'
                )->identifier(self::IDENTIFIER)->build()
            ];
        }

        return [];
    }

    /**
     * Get a string value from a node, if one can be derived.
     */
    private function getStringValue(Node $node, Scope $scope): ?string
    {
        // e.g. MyClass
        if (($node instanceof Name) || ($node instanceof Identifier)) {
            return $node->toString();
        }
        $type = $scope->getType($node);
        // Variables and other types PHPStan can directly reason about
        if (method_exists($type, 'getValue')) {
            return $type->getValue();
        }
        // e.g. static::class
        if (($type instanceof GenericClassStringType) && ($type->getGenericType() instanceof TypeWithClassName)) {
            return $type->getGenericType()->getClassName();
        }
        // e.g. static::class . '.myKey'
        if ($node instanceof Concat) {
            $left = $this->getStringValue($node->left, $scope);
            $right = $this->getStringValue($node->right, $scope);
            if ($left === null || $right === null) {
                return null;
            }
            return $left . $right;
        }
        return null;
    }
}
