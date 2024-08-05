<?php

namespace SilverStripe\Standards\Tests\PHPStan\TranslationFunctionRuleTest;

use SilverStripe\i18n\i18n;

class InClassCorrect
{
    public const MY_ENTITY = 'entity';

    public function myMethod()
    {
        _t('abc.123');
        _t(InClass::class . '.somekey');
        _t('something.' . InClass::MY_ENTITY);
        _t(__CLASS__ . '.' . InClass::MY_ENTITY);
        $variable = 'nothing.wrong';
        _t($variable);
        i18n::_t('with.dot');
        $callableString = '_t';
        $callableString('with.dot');
        i18n::_t(static::class . '.key');
    }
}
