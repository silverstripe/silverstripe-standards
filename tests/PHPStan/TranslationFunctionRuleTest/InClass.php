<?php

namespace SilverStripe\Standards\Tests\PHPStan\TranslationFunctionRuleTest;

use SilverStripe\i18n\i18n;

class InClass
{
    public const MY_ENTITY = 'entity';

    public function myMethod()
    {
        _t('abc');
        _t(InClass::class);
        _t(InClass::class . 'somekey');
        _t(InClass::MY_ENTITY);
        _t(__CLASS__ . InClass::MY_ENTITY);
        $variable = 'somethingwrong';
        _t($variable);
        i18n::_t('nodot');
        $callableString = '_t';
        $callableString('nodot');
        i18n::_t(static::class . 'key');

        // These should be ignored
        SomeClass::_t('abc');
        $x = new SomeClass();
        $x->_t('abc');
        $callable = [i18n::class, '_t'];
        $callable('abc');
        $callable2 = fn ($x) => $x;
        $callable2('abc');
        $callable3 = function ($x) {
            return $x;
        };
        $callable3('abc');
        i18n::set_locale('en-us');
    }
}
