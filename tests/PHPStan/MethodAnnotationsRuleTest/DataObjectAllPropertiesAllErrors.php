<?php

namespace SilverStripe\Standards\Tests\PHPStan\MethodAnnotationsRuleTest;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\ORM\ManyManyThroughList;
use SilverStripe\ORM\SS_List;
use SilverStripe\Security\Group;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;

/**
 * This class has all of the problems. It also has some other stuff in the PHPDoc to validate
 * that those don't cause any unexpected issues.
 *
 * @property string $ThisGetsIgnored
 * @method SiteTree NormalHasOne() This one's here to show they can be mixed in and that's fine too
 * @property bool $SomethingElse
 * @var string $randomAnnocation
 * @method ManyManyList<Group> NormalManyMany()
 * @method ManyManyList<Permission> TheresAMethodWithThisName()
 * @method ManyManyList<Permission> NoManyManyForThis()
 * @method Member NoHasOneForThis()
 * @method string completely_left_field()
 * @method Group DotNotationBelongsTo(string $paramShouldntBeHere)
 * @method ManyManyThroughList<Member> ManyManyThrough(int $idShouldntBeHere)
 * @method Member NormalBelongsTo
 * @method DotNotationManyMany: ManyManyList<Member>
 * @method SS_List<DataObject> PolyMorphicManyMany()
 * @method HasManyList<Member> NormalHasMany() Descriptions can be on any method annotation, not just has_one
 * @method HasManyList<Group> DotNotationHasMany()
 * @method SiteTree NormalHasOne() duplicated
 */
class DataObjectAllPropertiesAllErrors extends DataObject implements TestOnly
{
    private static $db = [
        'ThisGetsIgnored' => 'Varchar',
    ];

    private static $has_one = [
        'NormalHasOne' => SiteTree::class,
        'PolymorphicHasOne' => DataObject::class,
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
        'DotNotationManyMany' => Member::class . '.Groups',
    ];

    public function TheresAMethodWithThisName()
    {
        //no-op
    }
}
