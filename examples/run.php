<?php
//declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once 'AwesomeMessageSerializer.php';
require_once 'AwesomeMessage.php';
require_once 'AwesomeEnum.php';
require_once 'CustomMessage.php';
require_once 'CustomMessageSerializer.php';

use AwesomePackage\AwesomeEnum;
use AwesomePackage\CustomMessage;
use awesomepackage\AwesomeMessage;
use AwesomePackage\CustomMessageSerializer;
use awesomepackage\AwesomeMessageSerializer;

$s = AwesomeMessageSerializer::create()
    ->bt('bytes')
    ->bt_empty('')
    ->str('hello world')
    ->str_empty('')
    ->boolean(true)
    ->boolean_empty(false)
    ->uint(100500)
    ->uint_empty(0)
    ->enum(AwesomeEnum::WEB_ALIAS)
    ->enum_empty(0)
    ->r_boolean(array(true, false, true))
    ->r_boolean_empty(array())
    ->r_uint(array(1, 2, 3, 4, 5, 6, 7, 8, 9))
    ->r_uint_empty(array())
    ->r_str(explode(' ', 'В лесу родилась ёлочка, В лесу она росла. Зимой и летом стройная, Зелёная была.'))
    ->r_str_empty(array())
    ->r_enum(array(AwesomeEnum::WEB_ALIAS))
    ->r_enum_empty(array())
    ->r_bt(explode(' ', 'В лесу родилась ёлочка, В лесу она росла. Зимой и летом стройная, Зелёная была.'))
    ->r_bt_empty(array())
    ->custom(new CustomMessage(CustomMessageSerializer::create()->test('привет')->dump()))
    ->custom_empty(null)
    ->r_custom(array(new CustomMessage(), new CustomMessage()))
    ->r_custom_empty(array())
    ->dump();

var_dump($s);
var_dump(new AwesomeMessage($s));
