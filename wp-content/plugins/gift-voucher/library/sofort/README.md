SofortLib-PHP @ SOFORT GmbH

In diesem Dokument werden SofortLib-PHP selbst, deren Bestandteile, die Inbetriebnahme und 
die Test-Möglichkeiten kurz erläutert.

################
# Beschreibung #
################

Die SofortLib-PHP Bibliothek bietet Ihnen die Möglichkeit mit der SOFORT API mit PHP Mitteln zu kommunizieren und 
damit die SOFORT Produkte auf PHP Basis in Ihr System zu integrieren.

Nähere Informationen zur API/SDK finden Sie hier:
https://www.sofort.com/integrationCenter-ger-DE/integration/API-SDK/

Folgende SOFORT Produkte werden aktuell von der SofortLib-PHP unterstützt:

1. SOFORT Überweisung (SOFORT Banking/Payment)
2. SOFORT Überweisung Paycode
3. SOFORT Überweisung Billcode
4. Rückbuchungen (Refund)
5. iDEAL

###################
# SofortLib Paket #
###################

Die SofortLib wird als gezippte Archiv-Datei zur Verfügung gestellt (der Name der Datei 
beinhaltet zusätzlich die aktuelle Versionsnummer am Ende):

- SofortLib-PHP-.zip

Jedes Paket beinhaltet folgende Dateien/Verzeichnisse:
- den Ordner mit der Klasse/den Klassen zur Implementation des jeweiligen Paketes.
- Das Core-Verzeichnis mit den Kern-Dateien und Bibliotheken der SofortLib-PHP
- Den examples-Ordner mit Beispielen zur Nutzung der SofortLib-PHp
- Einen Ordner mit Unittests für das jeweilige Paket (und alle im Paket vorhandenen Kern-Dateien)

###############
# Integration #
###############

1. SOFORT Überweisung
---------------------

a) Funktionalität

Folgende Funktionalitäten stehen zur Verfügung:

- SOFORT Überweisung initiieren
- Die empfangene XML Status-Änderung in ein PHP-Objekt umwandeln
- Die Transaktionsdetails zu einer oder mehreren Transaktions IDs abfragen
- Die Transaktionsdetails für einen bestimmten Zeitraum oder/und Status abfragen


b) Einbinden

Um Sofort Überweisung nutzen zu können, müssen die Ordner /core und /payment in das Projekt 
kopiert werden und die Datei 

/sofort/sofortLibSofortueberweisung.php 

muss in das Projekt eingebunden werden.

Für das Auslesen von Transaktionsdetails müssen noch die beiden Dateien  

/core/sofortLibNotification.php
/core/sofortLibTransactionData.php

eingebunden werden.

c) Benutzung

Im Ordner /examples befindet sich die Datei example.sofortueberweisung.php mit einem beispielhaftem Aufruf. 
Die Datei example.transaction.php ist ein Beispiel für den Abruf von Transaktionsdetails.

2. SOFORT Überweisung Paycode
-----------------------------

a) Funktionalität

Folgende Funktionalitäten stehen zur Verfügung:

- SOFORT Überweisung Paycode initiieren/erstellen
- Die empfangene XML Status-Änderung in ein PHP-Objekt umwandeln
- Den aktuellen Paycode Status abfragen
- Die Paycode-Transaktionsdetails zu einer oder mehreren Transaktion IDs abfragen
- Die Paycode-Transaktionsdetails für einen bestimmten Zeitraum oder/und Status abfragen


b) Einbinden

Um Sofort Paycode nutzen zu können, müssen die Ordner /core und /paycode in das Projekt kopiert werden und die Datei 

/sofort/sofortLibPaycode.php 

muss in das Projekt eingebunden werden.

Für das Auslesen von Transaktionsdetails müssen noch die beiden Dateien  

/core/sofortLibNotification.php
/core/sofortLibTransactionData.php

eingebunden werden.


c) Benutzung

Im Ordner /examples befindet sich die Datei example.paycode.php mit einem beispielhaftem Aufruf. Die Dateien
example.transaction.php und example.paycode.details.php sind Beispiele für den Abruf von Transaktionsdetails.


3. SOFORT Überweisung Billcode
------------------------------

a) Funktionalität

Folgende Funktionalitäten stehen zur Verfügung:

- SOFORT Überweisung Billcode initiieren
- Die empfangene XML Status-Änderung in ein PHP-Objekt umwandeln
- Den letzten bekannten Billcode Status abfragen
- Die Transaktionsdetails zu einer oder mehreren Transaktion IDs abfragen
- Die Transaktionsdetails für einen bestimmten Zeitraum oder/und Status abfragen


b) Einbinden

Um Sofort Billcode nutzen zu können, müssen die Ordner /core und /billcode in das Projekt kopiert werden und die Datei 

/sofort/sofortLibBillcode.php 

muss in das Projekt eingebunden werden.

Für das Auslesen von Transaktionsdetails müssen noch die beiden Dateien  

/core/sofortLibNotification.inc.php
/core/sofortLibTransactionData.inc.php

eingebunden werden.

c) Benutzung

Im Ordner /examples befindet sich die Datei example.billcode.php mit einem beispielhaftem Aufruf. Die Dateien
example.transaction.php und example.billcode.details.php sind Beispiele für den Abruf von Transaktionsdetails.



4. Rückbuchungen
----------------

a) Funktionalität

Folgende Funktionalitäten stehen zur Verfügung:

- Rückbuchungen vormerken


b) Einbinden

Um Sofort Refund nutzen zu können, müssen die Ordner /core und /refund in das Projekt kopiert werden und die Datei 

/refund/sofortLibRefund.inc.php 

muss in das Projekt eingebunden werden.

Für das Auslesen von Transaktionsdetails müssen noch die beiden Dateien  

/core/sofortLibNotification.inc.php
/core/sofortLibTransactionData.inc.php

eingebunden werden.

c) Benutzung

Im Ordner /examples befindet sich die Datei example.billcode.php mit einem beispielhaftem Aufruf. Die Datei
example.transaction.php ist ein Beispiele für den Abruf von Transaktionsdetails.


5. iDEAL
--------

a) Funktionalität

Folgende Funktionalitäten stehen zur Verfügung:

- die aktuelle iDEAL Bank Liste abfragen
- eine Weiterleitung URL aus den iDEAL Zahlungsdaten generieren (für das GET Formular)
- eine Prüfsumme/Hash Wert aus den iDEAL Zahlungsdaten generieren (für das POST Formular)
- eine Prüfsumme/Hash Wert aus den iDEAL Benachrichtigunsdaten generieren


b) Einbinden

Um Sofort iDEAL nutzen zu können, müssen die Ordner /core und /ideal in das Projekt kopiert werden und die Datei 

/ideal/sofortLibIdeal.inc.php 

muss in das Projekt eingebunden werden.

Um die Bankliste zu erhalten, muss die Datei

/ideal/sofortLibIdealBanks.inc.php

eingebunden werden.

Für das Auslesen von Transaktionsdetails müssen noch die beiden Dateien  

/ideal/sofortLibIdealNotification.inc.php

eingebunden werden.

c) Benutzung

Im Ordner /examples befindet sich die Datei example.ideal.php mit einem beispielhaftem Aufruf. Die Datei
example.ideal.banks.php ist ein Beispiele für den Abruf der Bankliste.


##########
# Testen #
##########

Allgemein
---------

Alle Tests in SofortLib-PHP sind mit Hilfe vom PHPUnit Framework realisiert.

Im Verzeichnis /unittests liegen die Testklassen sowohl für die Klassen im /core als auch die 
Klassen im jeweiligen Modulordner.

1. Sofort Überweisung 
---------------------

Modulordner:
/payment 

PHPUnit Test Config:
/unittests/paymentTest.xml

Testaufruf:
phpunit --configuration paymentTest.xml


2. Sofort Überweisung Billcode
------------------------------

Modulordner:
/billcode 

PHPUnit Test Config:
/unittests/billcodeTest.xml

Testaufruf:
phpunit --configuration billcodeTest.xml


3. Sofort Überweisung Paycode
-----------------------------

Modulordner:
/paycode 

PHPUnit Test Config:
/unittests/paycodeTest.xml

Testaufruf:
phpunit --configuration paycodeTest.xml


4. Rückbuchungen
----------------

Modulordner:
/refund 

PHPUnit Test Config:
/unittests/refundTest.xml

Testaufruf:
phpunit --configuration refundTest.xml


5. iDEAL
----------------

Modulordner:
/ideal 

PHPUnit Test Config:
/unittests/idealTest.xml

Testaufruf:
phpunit --configuration idealTest.xml
