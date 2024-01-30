<?php

namespace SilverStripe\Standards\Tests\PHPStan\MethodAnnotationsRuleTest;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;

class ManyManyThroughModel extends DataObject implements TestOnly
{
    private static $has_one = [
        'To' => Member::class,
        'From' => DataObject::class,
    ];
}
