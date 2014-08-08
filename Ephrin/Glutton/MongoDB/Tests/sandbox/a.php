<?php


$a = ['$ref' => 'cccc', '$db' => 'qqe'];

$b = ['$ref' => 'cccc', '$db' => 'qqe', 'tt' => 'qwddq'];


$r = array_diff_assoc($b, $a);

var_dump($r);
 