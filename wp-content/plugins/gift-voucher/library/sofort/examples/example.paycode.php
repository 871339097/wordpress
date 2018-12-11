<?php


require_once(dirname(__FILE__).'/../paycode/sofortLibPaycode.inc.php');

// enter your configuration key – you only can create a new configuration key by creating
// a new Gateway project in your account at sofort.com
$configkey = '12345:123456:edc788a4316ce7e2ac0ede037aa623d7';

$SofortLibPaycode = new SofortLibPaycode($configkey);

$SofortLibPaycode->setAmount(5.55)
	->setCurrencyCode('EUR')
	->setReason('Reason Line 1', 'Reason Line 2')
	->setSuccessUrl('http://www.google.de')
	->setSuccessLinkRedirect(0)
	->setAbortUrl('http://www.sofort.com')
	->setNotificationUrl('http://www.diesundjenes.de/notify')
	->setUserVariable('schwuppsen')
	//->setSenderBankCode('00000')
	//->setSenderIban('00000')
	//->setSenderBic('SKGIDE5FXXX')
	//->setSenderCountryCode('de')
	//->setLanguageCode('de')
	//->setVersion('4711-08/15')
	//->setEndDate('2017-10-10 23:59:59')
;

$SofortLibPaycode->createPaycode();

if($SofortLibPaycode->isError()) {
	//Sofort-API didn't accept the data
	echo $SofortLibPaycode->getError();
} else {
	//Retrieve Paycode or Paycode URL
	echo $SofortLibPaycode->getPaycode();
	echo '<br/>';
	echo $SofortLibPaycode->getPaycodeUrl();

	//echo '<pre>' . print_r($SofortLibPaycode, true) . '</pre>';
}
