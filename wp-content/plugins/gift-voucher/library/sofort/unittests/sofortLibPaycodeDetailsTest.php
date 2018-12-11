<?php
require_once(dirname(__FILE__).'/../paycode/sofortLibPaycodeDetails.inc.php');
require_once('sofortLibTest.php');

class Unit_SofortLibPaycodeDetailsTest extends SofortLibTest {

	protected $_classToTest = 'SofortLibPaycodeDetails';
	
	public function testGetPaycode() {
		$SofortPaycodeDetails = new SofortLibPaycodeDetails(self::$configkey);
		$this->assertFalse($SofortPaycodeDetails->getPaycode());
		
		$response = self::_getProperty('_response', $this->_classToTest);
		$data_2_b_tested = '456789';
		$data_structure = array('paycode' => array('@data' => $data_2_b_tested));
		$response->setValue($SofortPaycodeDetails, $data_structure);
		$this->assertEquals($data_2_b_tested, $SofortPaycodeDetails->getPaycode());
	}
	
	
	public function testSetPaycode () {
		$SofortPaycodeDetails = new SofortLibPaycodeDetails(self::$configkey);
		$paycode = '456789';
		$SofortPaycodeDetails->setPaycode($paycode);
		$parameters = $SofortPaycodeDetails->getParameters();
		$this->assertEquals($paycode, $parameters['paycode']);
	}
}