<?php

namespace SilverStripe\Standards\Tests\PHPStan\MethodAnnotationsRuleTest;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Group;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;

/**
 * This class exists to show that config in get_extra_config() gets ignored for DataObject classes.
 * It also shows that normal config is not ignored even when there's a get_extra_config() method.
 */
class DataObjectAllConfigFromMethodIgnored extends DataObject implements TestOnly
{
    private static $db = [
        'ThisGetsIgnored' => 'Varchar',
    ];

    private static $has_one = [
        'PolymorphicHasOne' => DataObject::class,
    ];

    private static $has_many = [
        'DotNotationHasMany' => Group::class . '.Parent',
    ];

    public static function get_extra_config($class, $extension, $args): array
    {
        return [
            'has_one' => [
                'NormalHasOne' => SiteTree::class,
            ],
            'has_many' => [
                'DotNotationHasMany' => Group::class . '.Parent',
            ],
            'many_many' => [
                'ManyManyThrough' => [
                    'through' => ManyManyThroughModel::class,
                    'to' => 'To',
                    'from' => 'From',
                ],
                'PolyMorphicManyMany' => DataObject::class,
            ],
            'belongs_to' => [
                'NormalBelongsTo' => Member::class,
                'DotNotationBelongsTo' => Group::class . '.Parent',
            ],
            'belongs_many_many' => [
                'DotNotationManyMany' => Member::class . '.Groups',
            ],
        ];
    }
}
