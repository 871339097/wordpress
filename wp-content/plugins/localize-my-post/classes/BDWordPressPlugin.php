<?php

/**
 * BDWordPressPlugin
 * 
 * Author: Julian Burr
 * Version: 1.0
 * Date: 2015/04/30
 *
 * Copyright (c) 2015 Julian Burr
 * License: Published under MIT license
 *
 * Description:
 * 		Basic frame class for WP Plugins including handling for plugin settings
 **/

include_once(__DIR__ . "/BDWordPressPluginOptionGroup.php");

class BDWordPressPlugin {
	
	protected $option_page_slug = "";
	protected $option_menu_title = "";
	
	protected $option_page_title = "";
	protected $option_page_introduction = "";
	
	protected $option_fields = array();
	
	public function __construct($init=true){
		if($init){
			//If automatic initialization is requested, do so
			$this->init();
		}
	}
	
	public function init(){
		//Add admin menu	
		add_action('admin_menu', array($this, 'addMenu'));
		//Init settings on admin init
		add_action('admin_init', array($this, 'initOptions'));
	}
	
	public function addMenu(){
		//Add option page
		add_options_page(
			$this->option_page_title, 
			$this->option_menu_title, 
			'administrator',
			$this->option_page_slug, 
			array($this, 'displayOptionsPage')
		);
	}
	
	public function createOption(OptionGroup $group, $option){
		//Create new Option
		if(!isset($this->option_fields[$group->getID()])){
			//Create new group array if it doesn't exist
			$this->option_fields[$group->getID()] = array(
				'title' => $group->getTitle(),
				'callback' => $group->getCallback(),
				'options' => array()
			);
		}
		$this->option_fields[$group->getID()]['options'][] = $option;
	}
	
	public function initOptions(){
		//Run through option field array to register all options in there
		foreach($this->option_fields as $optiongroup => $info){
			foreach($info['options'] as $option){
				//register_setting($optiongroup, $option['name']);
				register_setting('settings_' . $this->option_page_slug, $option['name']);
			}
		}
	}
	
	public function addOptions(){
		//Initialize conntection cache
		$connections = array();
		foreach($this->option_fields as $optiongroup => $info){
			//Set fallback callback
			if(!isset($info['callback']) || !$info['callback']){
				$info['callback'] = "doNothing";
			}
			//Add this section
			add_settings_section(
				$optiongroup, 
				$info['title'], 
				array($this, $info['callback']),
				$this->option_page_slug
			);
			foreach($info['options'] as $key => $option){
				//Set arguments for callback function together
				$args = array('field' => $option['name']);
				if(is_array($option['args'])){
					$args = array_merge($args, $option['args']);
				}
				//Check for connected options
				if(isset($option['connect']) && is_array($option['connect'])){
					foreach($option['connect'] as $connect){
						//Cache connection for later
						$connections[$connect] = array('origin' => $key);
					}
				}
				//If not a connected field that is already printed somewhere else...
				if(!is_array($connections[$key])){
					//...add field to the created section
					add_settings_field(
						$option['name'],
						$option['title'],
						array($this, $option['callback']),
						$this->option_page_slug,
						$optiongroup,
						$args
					);
				}
			}
		}
	}
	
	public function displayOptionsPage(){
		//Display the plugins options page for the backend
		//Initialize and add sections and options
		$this->addOptions();
		//Create output
		echo "<div class='wrap'>";
    	echo "<h2>{$this->option_page_title}</h2>";
		echo $this->option_page_introduction;
    	echo "<form method='post' action='options.php'>";
		//Do output
        do_settings_sections($this->option_page_slug);
		//Create output for all defined sections
        /*foreach($this->option_fields as $optiongroup => $info){
			var_dump($optiongroup);
			settings_fields($optiongroup);
		}*/
		settings_fields('settings_' . $this->option_page_slug);
		//Print submit
        submit_button();
		echo "</form>";
		echo "</div>";
	}
	
	public function callbackDisplayOptionTextfield($args){
		//Get field name from arguments
		$field = $args['field'];
		//Get options value
		$value = get_option($field);
		//And echo a proper input type="text"
		echo sprintf('<input class="regular-text" type="text" name="%s" id="%s" value="%s" />', $field, $field, $value);
	}
	
	public function callbackDisplayOptionRadios($args){
		//Get field name from arguments
		$field = $args['field'];
		$options = $args['options'];
		//Get options value
		$value = get_option($field);
		//Echo radio fieldset
		echo "<fieldset>";
		$sep = "";
		foreach($options as $option){
			$checked = "";
			if($option['value'] == $value){
				$checked = "checked='checked'";
			}
			echo sprintf('%s<label><input type="radio" name="%s" id="%s" value="%s" %s />%s</label>', 
				$sep,
				$field, 
				$field, 
				$option['value'], 
				$checked, 
				$option['label']
			);
			$sep = "<br>";
		}
		echo "</fieldset>";
	}
	
	public function callbackDisplayOptionCheckboxes($args){
		//Get field name from arguments
		$field = $args['field'];
		$options = $args['options'];
		//Get options value
		$value = get_option($field);
		var_dump($value);
		//Echo checkbox fieldset
		echo "<fieldset>";
		$sep = "";
		//Echo emptyvalue as hidden imput (in case no checkbox is checked)
		echo sprintf('<input type="hidden" name="%s" value="" />', $field);
		foreach($options as $option){
			$checked = "";
			if($option['value'] == $value){
				$checked = "checked='checked'";
			}
			echo sprintf('%s<label><input type="checkbox" name="%s" id="%s" value="%s" %s />%s</label>', 
				$sep,
				$field, 
				$field, 
				$option['value'], 
				$checked, 
				$option['label']
			);
			$sep = "<br>";
		}
		echo "</fieldset>";
	}
	
	public function doNothing(){
		//literally do nothing
	}
	
	public function addShortcode($name, $callback){
		//Add WP shortcode
		add_shortcode($name, $callback);	
	}
	
	public function flushRewriteRules(){
		//Flush WP rewrite rules (e.g. when creating new permalink structures)
		flush_rewrite_rules();
	}
	
}