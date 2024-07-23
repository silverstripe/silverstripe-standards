<?php

namespace SilverStripe\Standards\Tests\PHPStan\KeywordSelfRuleTest;

/**
 * PHPStan doesn't analyse traits unless there's a class that uses it
 */
class ClassUsesTrait
{
    use TestTrait;
}
