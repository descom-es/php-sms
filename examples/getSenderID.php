<?php

use Descom\Sms\Auth\AuthUser;
use Descom\Sms\Exceptions\RequestFail;
use Descom\Sms\Sms;

require '../vendor/autoload.php';

if ($argc < 3) {
    echo 'Usage '.$argv[0]." username password.\n";
    exit(1);
}

$sms = new Sms(new AuthUser($argv[1], $argv[2]));

try {
    $serderID = $sms->getSenderID();

    echo 'senderID Authorized:'.PHP_EOL;

    foreach ($serderID as $value) {
        echo "\t".'- '.$value.PHP_EOL;
    }
} catch (RequestFail $e) {
    echo 'Error nº: '.$e->getCode().'; message: '.$e->getMessage()."\n";
}
