<?php

namespace SilverStripe\Standards\Tests\PHPStan\KeywordSelfRuleTest;

trait TestTraitCorrect
{
    // Can't define a const on a trait, but the trait can access consts on the class that uses it
    private static string $myProperty = self::MY_CONST;

    private static self $mySecondProperty;

    /**
     * self::class is ignored here because this is a comment.
     */
    public static function function1(self $someProperty): self
    {
        $self = new (self::class)();
        $self::class; // $self:: isn't seen as self::
        self::class::self();
        self::class::$myProperty = self::class;
        /* intentionally commented out to prove even multi-line comments are ignored.
        self::$myProperty = self::class;
        */
        // Alternative acceptable syntax for instantiating
        $selfClass = self::class;
        return new $selfClass();
    }

    public static function self()
    {
        // doesn't need an implementation - the point of this method is just to show that "self" isn't just being
        // searched for naively.
    }
}
