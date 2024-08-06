<?php

declare(strict_types=1);

namespace SilverStripe\Standards\Tests\PHPStan;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use SilverStripe\Standards\PHPStan\TranslationFunctionRule;

/**
 * @extends RuleTestCase<TranslationFunctionRule>
 */
class TranslationFunctionRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new TranslationFunctionRule();
    }

    public function provideRule()
    {
        return [
            'no class scope' => [
                'filePaths' => [__DIR__ . '/TranslationFunctionRuleTest/raw-php.php'],
                'errorMessage' => 'First argument passed to _t() must have exactly one period.',
                'errorLines' => [8,9,10,11,12,14,15,17],
            ],
            'no class scope, no errors' => [
                'filePaths' => [__DIR__ . '/TranslationFunctionRuleTest/raw-php-correct.php'],
                'errorMessage' => '',
                'errorLines' => [],
            ],
            'in class scope' => [
                'filePaths' => [__DIR__ . '/TranslationFunctionRuleTest/InClass.php'],
                'errorMessage' => 'First argument passed to _t() must have exactly one period.',
                'errorLines' => [13,14,15,16,17,19,20,22,23],
            ],
            'in class scope, no errors' => [
                'filePaths' => [__DIR__ . '/TranslationFunctionRuleTest/InClassCorrect.php'],
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
