<?php

namespace SilverStripe\Standards\Tests\PHPStan\MethodAnnotationsRuleTest;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

class DataObjectNoAnnotations extends DataObject implements TestOnly
{
    private static $db = [
        'ThisGetsIgnored' => 'Varchar',
    ];

    private static $has_one = [];

    private static $has_many = [];

    private static $many_many = [];

    private static $belongs_to = [];

    private static $belongs_many_many = [];
}
