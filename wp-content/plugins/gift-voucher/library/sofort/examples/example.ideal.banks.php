<?php
require_once(dirname(__FILE__).'/../ideal/sofortLibIdealBanks.inc.php');

// enter your configuration key – you only can create a new configuration key by creating
// a new Gateway project in your account at sofort.com
$SofortLibIdealBanks = new SofortLibIdealBanks('133170:291766:765036b83106e64e3ad37d26f2e511fe');

$SofortLibIdealBanks->sendRequest();

print_r($SofortLibIdealBanks->getBanks());
