<?php

namespace SilverStripe\Standards\Tests\PHPStan\MethodAnnotationsRuleTest;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DataObjectSchema;
use SilverStripe\ORM\HasManyList;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\ORM\ManyManyThroughList;
use SilverStripe\Security\Group;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;

/**
 * This class has no problems. It also has some other stuff in the PHPDoc to validate
 * that those don't cause any issues either.
 *
 * @property string $ThisGetsIgnored
 * @method SiteTree NormalHasOne() This one's here to show they can be mixed in and that's fine too
 * @property bool $SomethingElse
 * @var string $randomAnnocation
 * @method DataObject PolymorphicHasOne() We have have a description too, that's fine.
 * @method DataObject MultiRelationalHasOne()
 * @method Permission WeirdButStilValidHasOne()
 * @method HasManyList<Member> NormalHasMany() Descriptions can be on any method annotation, not just has_one
 * @method HasManyList<Group> DotNotationHasMany()
 * @method ManyManyList<Permission> NormalManyMany()
 * @method ManyManyThroughList<Member> ManyManyThrough()
 * @method ManyManyList<DataObject> PolyMorphicManyMany()
 * @method ManyManyList<Group> NormalBelongsMany()
 * @method ManyManyList<Member> DotNotationManyMany()
 * @method Member NormalBelongsTo()
 * @method Group DotNotationBelongsTo()
 */
class DataObjectAllPropertiesNoErrors extends DataObject implements TestOnly
{
    private static $db = [
        'ThisGetsIgnored' => 'Varchar',
    ];

    private static $has_one = [
        'NormalHasOne' => SiteTree::class,
        'PolymorphicHasOne' => DataObject::class,
        'MultiRelationalHasOne' => [
            'class' => DataObject::class,
            DataObjectSchema::HAS_ONE_MULTI_RELATIONAL => true,
        ],
        'WeirdButStilValidHasOne' => [
            'class' => Permission::class,
        ],
    ];

    private static $has_many = [
        'NormalHasMany' => Member::class,
        'DotNotationHasMany' => Group::class . '.Parent',
    ];

    private static $many_many = [
        'NormalManyMany' => Permission::class,
        'ManyManyThrough' => [
            'through' => ManyManyThroughModel::class,
            'to' => 'To',
            'from' => 'From',
        ],
        'PolyMorphicManyMany' => DataObject::class,
        'TheresAMethodWithThisName' => Permission::class,
    ];

    private static $belongs_to = [
        'NormalBelongsTo' => Member::class,
        'DotNotationBelongsTo' => Group::class . '.Parent',
    ];

    private static $belongs_many_many = [
        'NormalBelongsMany' => Group::class,
        'DotNotationManyMany' => Member::class . '.Groups',
    ];

    public function TheresAMethodWithThisName()
    {
        //no-op
    }
}
