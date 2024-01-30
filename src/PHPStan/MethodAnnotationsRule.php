<?php

declare(strict_types=1);

namespace SilverStripe\Standards\PHPStan;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;
use PHPStan\Type\VerbosityLevel;
use ReflectionProperty;
use SilverStripe\Core\Extension;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\HasManyList;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\ORM\ManyManyThroughList;

/**
 * Validates that `@method` annotations in DataObject and Extension classes are correct,
 * according to the relations defined within those classes.
 *
 * @implements Rule<Class_>
 */
class MethodAnnotationsRule implements Rule
{
    private ReflectionProvider $reflectionProvider;

    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }

    public function getNodeType(): string
    {
        return Class_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        /** @var Class_ $node */
        $className = $node->namespacedName->toString();
        if (!$this->reflectionProvider->hasClass($className)) {
            return [];
        }

        $classReflection = $this->reflectionProvider->getClass($className);
        if (!$this->isDataObjectOrExtension($classReflection)) {
            return [];
        }

        $existingAnnotations = $this->getMethodAnnotations($classReflection);
        $expectedAnnotations = $this->getExpectedAnnotations($classReflection);

        $errors = [];

        // Check that existing annotations are all valid
        foreach ($existingAnnotations as $methodName => $annotationData) {
            $annotationString = $annotationData['annotationString'];
            $returnString = $annotationData['returnString'];

            // Complain about any @method annotations we don't expect
            if (!array_key_exists($methodName, $expectedAnnotations)) {
                $errors[] = RuleErrorBuilder::message(
                    "@method annotation '$annotationString' isn't expected and should be removed."
                )->build();
                continue;
            }

            // Complain about @method annotations if a real method exists with that name
            if ($classReflection->hasNativeMethod($methodName)) {
                $errors[] = RuleErrorBuilder::message(
                    "@method annotation '$annotationString' should be removed,"
                    . " because a method named '$methodName' already exists."
                )->build();
                continue;
            }

            // Complain about duplicated @method annotations
            if ($annotationData['count'] > 1) {
                $count = $annotationData['count'];
                $errors[] = RuleErrorBuilder::message(
                    "@method annotation '$annotationString' appears $count times. Remove the duplicates."
                )->build();
                continue;
            }

            $expectedAnnotationString = $expectedAnnotations[$methodName]['annotationString'];
            $expectedReturnString = $expectedAnnotations[$methodName]['returnString'];

            if ($returnString !== $expectedReturnString) {
                // Complain about missing use statements
                $fullReturnType = $annotationData['returnType'];
                if ($fullReturnType instanceof ObjectType) {
                    $returnClassName = $fullReturnType->getClassName();
                    if (!$this->reflectionProvider->hasClass($returnClassName)) {
                        $shortClassName = $this->getShortClassName($returnClassName);
                        $errors[] = RuleErrorBuilder::message(
                            "@method annotation '$annotationString' needs a use statement for class '$shortClassName'."
                        )->build();
                        continue;
                    }
                }

                // Complain about type mismatches
                $errors[] = RuleErrorBuilder::message(
                    "@method annotation '$annotationString' should be '$expectedAnnotationString'."
                )->build();
                continue;
            }

            // Complain about annotation strings looking different than what we expect.
            // Can be caused e.g. by adding parameters to the method annotation, which should not be included.
            if ($annotationString !== $expectedAnnotationString) {
                $errors[] = RuleErrorBuilder::message(
                    "@method annotation '$annotationString' should be '$expectedAnnotationString'."
                )->build();
                continue;
            }
        }

        // Complain about any missing annotations.
        // Note that if an @method annotation is malformed, it will also be reported as missing.
        $missingAnnotations = array_diff_key($expectedAnnotations, $existingAnnotations);
        foreach ($missingAnnotations as $methodName => $missingData) {
            // Skip if theres a method with that name
            if ($classReflection->hasNativeMethod($methodName)) {
                continue;
            }
            $expectedAnnotationString = $missingData['annotationString'];
            $errors[] = RuleErrorBuilder::message(
                "@method annotation '$expectedAnnotationString' is missing or has an invalid syntax."
            )->build();
        }

        return $errors;
    }

    /**
     * Check if the class represented by the ClassReflection object is DataObject,
     * Extension, or subclass of either of those.
     */
    private function isDataObjectOrExtension(ClassReflection $reflection): bool
    {
        return $this->isDataObject($reflection) || $this->isExtension($reflection);
    }

    /**
     * Check if the class represented by the ClassReflection object is DataObject,
     * Extension, or subclass of either of those.
     */
    private function isDataObject(ClassReflection $reflection): bool
    {
        return $reflection->getName() === DataObject::class
            || $reflection->isSubclassOf(DataObject::class);
    }

    /**
     * Check if the class represented by the ClassReflection object is Extension (or subclass).
     */
    private function isExtension(ClassReflection $reflection): bool
    {
        return $reflection->getName() === Extension::class
            || $reflection->isSubclassOf(Extension::class);
    }

    /**
     * Get all `@method` annotations for the class represented by the ClassReflection object.
     */
    private function getMethodAnnotations(ClassReflection $reflection): array
    {
        $phpDoc = $reflection->getResolvedPhpDoc();
        if (!$phpDoc) {
            return [];
        }

        $annotations = [];
        foreach ($phpDoc->getPhpDocNodes() as $phpDocNode) {
            $methodNodes = $phpDocNode->getMethodTagValues();
            $resolvedMethodNodes = $phpDoc->getMethodTags();

            foreach ($methodNodes as $node) {
                $methodName = $node->methodName;
                if (isset($annotations[$methodName])) {
                    $annotations[$methodName]['count']++;
                    continue;
                }
                $returnType = $resolvedMethodNodes[$methodName]->getReturnType();
                $annotations[$methodName] = [
                    'returnString' => $returnType->describe(VerbosityLevel::typeOnly()),
                    'returnType' => $returnType,
                    // Ignore the description - it's fine if the developer adds a description for the method,
                    // but we won't validate it
                    'annotationString' => trim(str_replace($node->description, '', $node->__toString())),
                    'count' => 1,
                ];
            }
        }

        return $annotations;
    }

    /**
     * Get all expected `@method` annotations for the class represented by the ClassReflection object.
     *
     * This is based on the relations defined in config on that class directly.
     * Config defined on extensions will only count towards the expected annotations for that extension class.
     * Config defined in yaml doesn't count towards any expected annotations.
     * If the class has an actual defined method with the same name, it WILL be returned from this method, and needs
     * to be checked against separately.
     */
    private function getExpectedAnnotations(ClassReflection $reflection): array
    {
        $expected = [];

        // Get any has_one and belongs_to relation methods
        $hasOne = array_merge(
            $this->getDefaultConfigValue($reflection, 'has_one'),
            $this->getDefaultConfigValue($reflection, 'belongs_to')
        );
        foreach ($hasOne as $name => $spec) {
            // has_one can be defined with a special associative array
            if (is_array($spec)) {
                // If there's no 'class' key, the has_one is malformed and we can't infer what is expected.
                // This rule isn't for validating config so we won't give an error.
                // In that case just assume it's polymorphic and move on.
                $className = $spec['class'] ?? DataObject::class;
            } else {
                $className = $spec;
            }
            // Remove any dot notation (for belongs_to) to get the true class name
            $className = strtok($className, '.');
            $shortClassName = $this->getShortClassName($className);
            $expected[$name] = [
                'returnString' => $className,
                'annotationString' => "{$shortClassName} {$name}()",
            ];
        }

        // Get any has_many relation methods
        $hasMany = $this->getDefaultConfigValue($reflection, 'has_many');
        foreach ($hasMany as $name => $spec) {
            // Remove any dot notation to get the class name
            $className = strtok($spec, '.');
            $shortClassName = $this->getShortClassName($className);
            // We don't need to be as specific as PolyMorphicHasManyList - there's too many edge cases
            // and that's a subclass of HasManyList anyway.
            $expected[$name] = [
                'returnString' => HasManyList::class . "<$className>",
                'annotationString' => "HasManyList<{$shortClassName}> {$name}()",
            ];
        }

        // Get any many_many relation methods
        $manyMany = array_merge(
            $this->getDefaultConfigValue($reflection, 'many_many'),
            $this->getDefaultConfigValue($reflection, 'belongs_many_many')
        );
        foreach ($manyMany as $name => $spec) {
            if (is_array($spec)) {
                $className = $this->getClassFromManyManyThrough($spec);
                $listClass = ManyManyThroughList::class;
            } else {
                // Remove any dot notation to get the class name
                $className = strtok($spec, '.');
                $listClass = ManyManyList::class;
            }
            $shortClassName = $this->getShortClassName($className);
            $shortListClass = $this->getShortClassName($listClass);
            $expected[$name] = [
                'returnString' => "{$listClass}<{$className}>",
                'annotationString' => "{$shortListClass}<{$shortClassName}> {$name}()",
            ];
        }

        return $expected;
    }

    /**
     * Get the default value for the given configuration property.
     * Ignores YAML, config inherited from extensions, and config inherited from superclasses.
     */
    private function getDefaultConfigValue(ClassReflection $class, string $propertyName): array
    {
        return array_merge(
            $this->getDefaultConfigFromProperty($class, $propertyName),
            $this->getDefaultConfigFromMethod($class, $propertyName)
        );
    }

    /**
     * Get default configuration defined using a private static property.
     */
    private function getDefaultConfigFromProperty(ClassReflection $class, string $propertyName): array
    {
        if (!$class->hasNativeProperty($propertyName)) {
            return [];
        }

        $property = $class->getNativeProperty($propertyName);

        if (!$property->isStatic() || !$property->isPrivate()) {
            return [];
        }

        /** @var ReflectionProperty $nativeProperty */
        $nativeProperty = $property->getNativeReflection();
        $value = $nativeProperty->getDefaultValue();

        return is_array($value) ? $value : [];
    }

    /**
     * Get configuration defined using the public static get_extra_config() method.
     */
    private function getDefaultConfigFromMethod(ClassReflection $class, string $propertyName): array
    {
        if (!$this->isExtension($class) || !$class->hasNativeMethod('get_extra_config')) {
            return [];
        }

        $method = $class->getNativeMethod('get_extra_config');
        $className = $class->getName();

        if (
            !$method->isStatic()
            || !$method->isPublic()
            || $method->getDeclaringClass()->getName() !== $className
        ) {
            return [];
        }

        $allExtraConfig = $className::get_extra_config(DataObject::class, $className, []);
        $value = $allExtraConfig[$propertyName] ?? [];
        return is_array($value) ? $value : [];
    }

    /**
     * Get the name of the class that will be contained in a ManyManyThroughList
     */
    private function getClassFromManyManyThrough(array $spec): string
    {
        if (!isset($spec['through']) || !isset($spec['to'])) {
            // Malformed many_many - we can't infer what is expected here.
            // This rule isn't for validating config so we won't give an error.
            // Just assume it's polymorphic and move on.
            return DataObject::class;
        }

        $throughClass = $spec['through'];
        if (!$this->reflectionProvider->hasClass($throughClass)) {
            // Probably malformed many_many. Again, just assume it's polymorphic.
            return DataObject::class;
        }

        $classReflection = $this->reflectionProvider->getClass($throughClass);
        $hasOne = $this->getDefaultConfigValue($classReflection, 'has_one');

        // If that relation isn't there, it's a malformed through class.
        // Again, just assume it's polymorphic in that scenario.
        return $hasOne[$spec['to']] ?? DataObject::class;
    }

    /**
     * Get the class name portion of a FQCN
     */
    private function getShortClassName(string $className): string
    {
        $parts = explode('\\', $className);
        return end($parts);
    }
}
