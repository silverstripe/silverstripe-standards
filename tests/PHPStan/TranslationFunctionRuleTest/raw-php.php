<?php

use App\SomeClass;
use SilverStripe\i18n\i18n;

const MY_ENTITY = 'entity';

_t('abc');
_t(SomeClass::class);
_t(SomeClass::class . 'somekey');
_t(MY_ENTITY);
_t(SomeClass::class . MY_ENTITY);
$variable = 'somethingwrong';
_t($variable);
i18n::_t('nodot');
$callableString = '_t';
$callableString('nodot');

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
