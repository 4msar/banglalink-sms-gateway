<?php

require_once 'vendor/autoload.php';

$config = [
    'user_id'   => 'BismillahTTC',
    'password'  => 'a1beb1d3b87555dd311e9f8528f7a09f'
];

$sms = new \Shipu\Banglalink\SMS($config);

$payload = [
  ['01869959660', ['Sumi', 'Barisal']],
  ['01700743854', ['Nahid', 'Barisal']]
];

//$payload = ['01869959660' => 'Johura', '01700743854'=>'Nahid'];

var_dump($sms->message('Hello %s, you pom %s')->send($payload));