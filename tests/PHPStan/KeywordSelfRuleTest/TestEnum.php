<?php

namespace SilverStripe\Standards\Tests\PHPStan\KeywordSelfRuleTest;

enum TestEnum: string
{
    private const MY_CONST = 'some value';

    case self = self::MY_CONST;

    /**
     * self::class is ignored here because this is a comment.
     */
    public static function function1(self $someProperty): self
    {
        $selfEnumValue = self::self;
        $selfName = self::class;
        $self = new self();
        $self::class; // $self:: isn't seen as self::
        self::self();
        /* intentionally commented out to prove even multi-line comments are ignored.
        self::$myProperty = self::class;
        */
        return new self('self');
    }

    public static function self()
    {
        // doesn't need an implementation - the point of this method is just to show that "self" isn't just being
        // searched for naively.
    }
}
