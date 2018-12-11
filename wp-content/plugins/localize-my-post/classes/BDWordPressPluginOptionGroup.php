<?php

/**
 * BDWordPressPluginOptionGroup
 * 
 * Author: Julian Burr
 * Version: 1.0
 * Date: 2015/04/30
 *
 * Copyright (c) 2015 Julian Burr
 * License: Published under MIT license
 *
 * Description:
 * 		Basic frame class for WP Plugin Optiongroups to handle
 *		Plugin settings
 **/

class OptionGroup {
	
	private $id = null;
	private $title = null;
	private $callback = null;
	
	public function __construct($id, $title=null, $callback=null){
		$this->id = $id;
		$this->title = $title;
		$this->callback = $callback;
	}
	
	public function getID(){
		return $this->id;
	}
	
	public function getTitle(){
		return $this->title;
	}
	
	public function getCallback(){
		return $this->callback;
	}
	
}