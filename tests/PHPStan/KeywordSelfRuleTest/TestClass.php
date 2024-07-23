<?php

namespace SilverStripe\Standards\Tests\PHPStan\KeywordSelfRuleTest;

class TestClass
{
    private const MY_CONST = 'some value';

    private static string $myProperty = self::MY_CONST;

    private static self $mySecondProperty;

    /**
     * self::class is ignored here because this is a comment.
     */
    public static function function1(self $someProperty): self
    {
        $self = new self();
        $self::class; // $self:: isn't seen as self::
        self::self();
        self::$myProperty = self::class;
        /* intentionally commented out to prove even multi-line comments are ignored.
        self::$myProperty = self::class;
        */
        return new self();
    }

    public static function self()
    {
        // doesn't need an implementation - the point of this method is just to show that "self" isn't just being
        // searched for naively.
    }
}
