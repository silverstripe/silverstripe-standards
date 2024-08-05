<?php

use App\SomeClass;
use SilverStripe\i18n\i18n;

const MY_ENTITY = 'entity';

_t('abc.123');
_t(SomeClass::class . '.somekey');
_t('something.' . MY_ENTITY);
_t(SomeClass::class . '.' . MY_ENTITY);
$variable = 'nothing.wrong';
_t($variable);
i18n::_t('with.dot');
$callableString = '_t';
$callableString('with.dot');
