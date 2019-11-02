<?php
//declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once 'AwesomeMessageSerializer';
require_once 'AwesomeMessage';

use awesomepackage\AwesomeMessage;
use awesomepackage\AwesomeMessageSerializer;

$s = AwesomeMessageSerializer::create()
    ->str('hello world')
    ->str_empty(false)
    ->boolean(true)
    ->boolean_empty(false)
    ->uint(100500)
    ->uint_empty(false)
    ->r_boolean(array(true, false, true))
    ->r_boolean_empty(false)
    ->r_uint(array(1, 2, 3, 4, 5, 6, 7, 8, 9))
    ->r_uint_empty(false)
    ->r_str(explode(' ', 'В лесу родилась ёлочка, В лесу она росла. Зимой и летом стройная, Зелёная была.'))
    ->r_str_empty(false)
    ->dump();

var_dump($s);
var_dump(new AwesomeMessage($s));
