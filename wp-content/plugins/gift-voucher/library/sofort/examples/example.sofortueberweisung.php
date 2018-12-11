<?php

require_once(dirname(__FILE__).'/../payment/sofortLibSofortueberweisung.inc.php');

// enter your configuration key – you only can create a new configuration key by creating
// a new Gateway project in your account at sofort.com
$configkey = '133170:291766:765036b83106e64e3ad37d26f2e511fe';
$Sofortueberweisung = new Sofortueberweisung($configkey);

$Sofortueberweisung->setAmount(10.21);
$Sofortueberweisung->setCurrencyCode('EUR');

$Sofortueberweisung->setReason('Testueberweisung', 'Verwendungszweck');
$Sofortueberweisung->setSuccessUrl('http://www.google.de', true);
$Sofortueberweisung->setAbortUrl('http://www.google.de');
$Sofortueberweisung->setNotificationUrl('http://www.google.de');

// $Sofortueberweisung->setSenderSepaAccount('SFRTDE20XXX', 'DE06000000000023456789', 'Max Mustermann');
// $Sofortueberweisung->setSenderCountryCode('DE');
// $Sofortueberweisung->setNotificationUrl('http://www.google.de', 'loss,pending');
// $Sofortueberweisung->setNotificationUrl('http://www.yahoo.com', 'loss');
// $Sofortueberweisung->setNotificationUrl('http://www.bing.com', 'pending');
// $Sofortueberweisung->setNotificationUrl('http://www.sofort.com', 'received');
// $Sofortueberweisung->setNotificationUrl('http://www.youtube.com', 'refunded');
// $Sofortueberweisung->setNotificationUrl('http://www.youtube.com', 'untraceable');
//$Sofortueberweisung->setCustomerprotection(true);


$Sofortueberweisung->sendRequest();

if($Sofortueberweisung->isError()) {
	//SOFORT-API didn't accept the data
	echo $Sofortueberweisung->getError();
} else {
	//buyer must be redirected to $paymentUrl else payment cannot be successfully completed!
	$paymentUrl = $Sofortueberweisung->getPaymentUrl();
	header('Location: '.$paymentUrl);
}

