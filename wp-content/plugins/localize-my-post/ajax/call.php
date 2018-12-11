<?php

//Include WP base to have the basic WP functions
include_once($_SERVER['DOCUMENT_ROOT'] . "/wp-blog-header.php");

//Create plain class instance (without initalization)
include_once(__DIR__ . "/../classes/LocalizeMyPost.php");
$instance = new LocalizeMyPost(false);

//Check if method exists
if(isset($_REQUEST['method']) && method_exists($instance, $_REQUEST['method'])){
	//Setup args array
	$args = array();
	if(isset($_REQUEST['args'])){
		$args = $_REQUEST['args'];
	}
	if(!is_array($args)){
		$args = array(0 => $args);
	}
	//And pass it to instances method
	$result = call_user_func_array(array($instance, $_REQUEST['method']), $args);
	
	//If response is normal value, write it into array for JSON conversion
	if(!is_array($result)){
		$result = array("result" => $result);
	}
	
	//Set status 200 header
	header('Content-Type: application/json', true, 200);
	
	//Write JSON from array
	echo json_encode($result);
}