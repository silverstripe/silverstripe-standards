<?php

declare(strict_types=1);

namespace SilverStripe\Standards\Tests\PHPStan;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use SilverStripe\Standards\PHPStan\MethodAnnotationsRule;

/**
 * @extends RuleTestCase<MethodAnnotationsRule>
 */
class MethodAnnotationsRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        $reflectionProvider = $this->createReflectionProvider();
        return new MethodAnnotationsRule($reflectionProvider);
    }

    public function provideRule()
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        $lineForErrors = 37;
        $errors = [
            'duplicated annotation' => [
                '@method annotation \'SiteTree NormalHasOne()\' appears 2 times. Remove the duplicates.',
                $lineForErrors,
            ],
            'wrong type 2' => [
                '@method annotation \'ManyManyList<Group> NormalManyMany()\' should be \'ManyManyList<Permission> NormalManyMany()\'.',
                $lineForErrors,
            ],
            'method already exists' => [
                '@method annotation \'ManyManyList<Permission> TheresAMethodWithThisName()\' should be removed, because a method named \'TheresAMethodWithThisName\' already exists.',
                $lineForErrors,
            ],
            'unnecessary annotation 1' => [
                '@method annotation \'ManyManyList<Permission> NoManyManyForThis()\' isn\'t expected and should be removed.',
                $lineForErrors,
            ],
            'unnecessary annotation 2' => [
                '@method annotation \'Member NoHasOneForThis()\' isn\'t expected and should be removed.',
                $lineForErrors,
            ],
            'unnecessary annotation 3' => [
                '@method annotation \'string completely_left_field()\' isn\'t expected and should be removed.',
                $lineForErrors,
            ],
            'shouldnt have params 1' => [
                '@method annotation \'Group DotNotationBelongsTo(string $paramShouldntBeHere)\' should be \'Group DotNotationBelongsTo()\'.',
                $lineForErrors,
            ],
            'shouldnt have params 2' => [
                '@method annotation \'ManyManyThroughList<Member> ManyManyThrough(int $idShouldntBeHere)\' should be \'ManyManyThroughList<Member> ManyManyThrough()\'.',
                $lineForErrors,
            ],
            'wrong type 1' => [
                '@method annotation \'SS_List<DataObject> PolyMorphicManyMany()\' should be \'ManyManyList<DataObject> PolyMorphicManyMany()\'.',
                $lineForErrors,
            ],
            'missing use statement 1' => [
                '@method annotation \'HasManyList<Member> NormalHasMany()\' needs a use statement for class \'HasManyList\'.',
                $lineForErrors,
            ],
            'missing use statement 2' => [
                '@method annotation \'HasManyList<Group> DotNotationHasMany()\' needs a use statement for class \'HasManyList\'.',
                $lineForErrors,
            ],
            'outright missing annotation' => [
                '@method annotation \'DataObject PolymorphicHasOne()\' is missing or has an invalid syntax.',
                $lineForErrors,
            ],
            'poorly formed annotation 1' => [
                '@method annotation \'Member NormalBelongsTo()\' is missing or has an invalid syntax.',
                $lineForErrors,
            ],
            'poorly formed annotation 2' => [
                '@method annotation \'ManyManyList<Member> DotNotationManyMany()\' is missing or has an invalid syntax.',
                $lineForErrors,
            ],
        ];
        return [
            'DataObjectAllPropertiesNoErrors.php' => [
                'filePath' => __DIR__ . '/MethodAnnotationsRuleTest/DataObjectAllPropertiesNoErrors.php',
                'expectedErrors' => [],
            ],
            'ExtensionMixedNoErrors.php' => [
                'filePath' => __DIR__ . '/MethodAnnotationsRuleTest/ExtensionMixedNoErrors.php',
                'expectedErrors' => [],
            ],
            'DataObjectNoAnnotations.php' => [
                'filePath' => __DIR__ . '/MethodAnnotationsRuleTest/DataObjectNoAnnotations.php',
                'expectedErrors' => [],
            ],
            'NotAnExtensionOrDataObject.php' => [
                'filePath' => __DIR__ . '/MethodAnnotationsRuleTest/NotAnExtensionOrDataObject.php',
                'expectedErrors' => [],
            ],
            'DataObjectAllPropertiesAllErrors.php' => [
                'filePath' => __DIR__ . '/MethodAnnotationsRuleTest/DataObjectAllPropertiesAllErrors.php',
                'expectedErrors' => $errors,
            ],
            'DataObjectAllConfigFromMethodIgnored.php' => [
                'filePath' => __DIR__ . '/MethodAnnotationsRuleTest/DataObjectAllConfigFromMethodIgnored.php',
                'expectedErrors' => [
                    [
                        '@method annotation \'DataObject PolymorphicHasOne()\' is missing or has an invalid syntax.',
                        16
                    ],
                    [
                        '@method annotation \'HasManyList<Group> DotNotationHasMany()\' is missing or has an invalid syntax.',
                        16
                    ],
                ],
            ],
            'ExtensionMixedAllErrors.php' => [
                'filePath' => __DIR__ . '/MethodAnnotationsRuleTest/ExtensionMixedAllErrors.php',
                'expectedErrors' => $errors,
            ],
        ];
        // phpcs:enable Generic.Files.LineLength.TooLong
    }

    /**
     * @dataProvider provideRule
     */
    public function testRule(string $filePath, array $expectedErrors): void
    {
        $this->analyse([$filePath], $expectedErrors);
    }
}
