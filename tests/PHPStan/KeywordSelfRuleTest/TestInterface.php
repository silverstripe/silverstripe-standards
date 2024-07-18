<?php

namespace SilverStripe\Standards\Tests\PHPStan\KeywordSelfRuleTest;

/**
 * Usage of `self` in an interface refers to the interface itself, unlike with traits.
 * This means we should avoid using self, since it's not actually needed here.
 */
interface TestInterface
{
    public const MY_CONST = 'some value';

    public const MY_SECOND_CONST = self::MY_CONST;

    /**
     * self::class is ignored here because this is a comment.
     */
    public static function function1(self $someProperty): self;

    public static function self();
}
