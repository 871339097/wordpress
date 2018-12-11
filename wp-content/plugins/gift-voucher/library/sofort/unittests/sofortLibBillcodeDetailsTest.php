<?php
require_once(dirname(__FILE__).'/../billcode/sofortLibBillcodeDetails.inc.php');
require_once('sofortLibTest.php');

class Unit_SofortLibBillcodeDetailsTest extends SofortLibTest {
	
	protected $_classToTest = 'SofortLibBillcodeDetails';
	
	public function testGetBillcode() {
		$SofortLibBillcodeDetails = new SofortLibBillcodeDetails(self::$configkey);
		$this->assertFalse($SofortLibBillcodeDetails->getBillcode());
		
		$response = self::_getProperty('_response', $this->_classToTest);
		$data_2_b_tested = '456789';
		$data_structure = array('billcode' => array('@data' => $data_2_b_tested));
		$response->setValue($SofortLibBillcodeDetails, $data_structure);
		$this->assertEquals($data_2_b_tested, $SofortLibBillcodeDetails->getBillcode());
	
	}
	
	
	public function testSetBillcode () {
		$SofortLibBillcodeDetails = new SofortLibBillcodeDetails(self::$configkey);
		$billcode = '456789';
		$SofortLibBillcodeDetails->setBillcode($billcode);
		$parameters = $SofortLibBillcodeDetails->getParameters();
		$this->assertEquals($billcode, $parameters['billcode']);
	}
}