<?php
/**
 * Plugin Name: Localize My Post
 * Plugin URI: https://github.com/julianburr/
 * Description: Easily add location information to your contents, as well as even esier use them in your theme or widgets
 * Version: 1.0
 * Author: julianburr
 * License: MIT
 */
 
//Include plugin class

include_once(__DIR__ . "/classes/LocalizeMyPost.php");
$localize = new LocalizeMyPost();

//General global functions

function the_location($before="", $after=""){
	$location = get_the_location(get_the_ID());	
	echo $before . $location . $after;
}

function get_the_location($id=null, $fmt=null){
	$localize = new LocalizeMyPost(false);
	return $localize->getLocation($id, $fmt);
}

function the_map($width, $height, $ids=null, $zoom=null){
	$localize = new LocalizeMyPost(false);
	if(!$id){
		$id = get_the_ID(); 
	}
	echo $localize->getLocationMap($width, $height, $ids, $zoom);
};