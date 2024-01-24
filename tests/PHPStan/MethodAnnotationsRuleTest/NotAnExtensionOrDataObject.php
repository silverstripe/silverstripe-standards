<?php

namespace SilverStripe\Standards\Tests\PHPStan\MethodAnnotationsRuleTest;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\View\ViewableData;

/**
 * Since this isn't a DataObject or Extension, we don't actually validate this.
 * It can have whatever annotations it wants.
 *
 * @method string randomMethod()
 */
class NotAnExtensionOrDataObject extends ViewableData implements TestOnly
{
    private static $has_one = [
        'NotActuallyARelation' => DataObject::class,
    ];
}
