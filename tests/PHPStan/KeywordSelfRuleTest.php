<?php

declare(strict_types=1);

namespace SilverStripe\Standards\Tests\PHPStan;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use SilverStripe\Standards\PHPStan\KeywordSelfRule;

/**
 * @extends RuleTestCase<KeywordSelfRule>
 */
class KeywordSelfRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new KeywordSelfRule();
    }

    public function provideRule()
    {
        return [
            'interface' => [
                'filePaths' => [__DIR__ . '/KeywordSelfRuleTest/TestInterface.php'],
                'errorMessage' => "Can't use keyword 'self'. Use 'TestInterface' instead.",
                // Note that 13 only actually has one violation, but does no harm to be counted twice.
                'errorLines' => [13, 13, 18, 18],
            ],
            'class' => [
                'filePaths' => [__DIR__ . '/KeywordSelfRuleTest/TestClass.php'],
                'errorMessage' => "Can't use keyword 'self'. Use 'TestClass' instead.",
                // Note that 9 and 21 only actually have one violation, but does no harm to be counted twice.
                'errorLines' => [9, 9, 11, 16, 16, 18, 20, 21, 21, 21, 25],
            ],
            'enum' => [
                'filePaths' => [__DIR__ . '/KeywordSelfRuleTest/TestEnum.php'],
                'errorMessage' => "Can't use keyword 'self'. Use 'TestEnum' instead.",
                // Note that 9, 16, and 17 only actually have one violation, but does no harm to be counted twice.
                'errorLines' => [9, 9, 14, 14, 16, 16, 17, 17, 18, 20, 24],
            ],
            'trait' => [
                'filePaths' => [
                    __DIR__ . '/KeywordSelfRuleTest/TestTrait.php',
                    __DIR__ . '/KeywordSelfRuleTest/ClassUsesTrait.php',
                ],
                'errorMessage' => "Can't use keyword 'self'. Use 'self::class' instead.",
                'errorLines' => [17, 19, 20, 24],
            ],
            'trait no errors' => [
                'filePaths' => [
                    __DIR__ . '/KeywordSelfRuleTest/TestTraitCorrect.php',
                    __DIR__ . '/KeywordSelfRuleTest/ClassUsesTraitCorrect.php',
                ],
                'errorMessage' => '',
                'errorLines' => [],
            ],
        ];
    }

    /**
     * @dataProvider provideRule
     */
    public function testRule(array $filePaths, string $errorMessage, array $errorLines): void
    {
        $errors = [];
        foreach ($errorLines as $line) {
            $errors[] = [$errorMessage, $line];
        }
        $this->analyse($filePaths, $errors);
    }
}
