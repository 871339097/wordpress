<?php

//Include WP base to have the basic WP functions
include_once($_SERVER['DOCUMENT_ROOT'] . "/wp-blog-header.php");

//Set status 200 header
//Include requested file if it exists
if(isset($_REQUEST['file'])){
	$file=$_REQUEST['file'];
	$file = str_replace('./','',$file);
	header('HTTP/1.1 200 OK');
	include($file);
}