<?php

/**
 * LocalizeMyPost
 * 
 * Author: Julian Burr
 * Version: 1.0
 * Date: 2016/03/05
 *
 * Copyright (c) 2016 Julian Burr
 * License: Published under MIT license
 *
 * Description:
 * 		Core class for WP Plugin "Localize My Post"
 *		Binds to all events and runs all actions
 **/

include_once(__DIR__ . "/BDWordPressPlugin.php");
include_once(__DIR__ . "/GoogleMap.php");
include_once(__DIR__ . "/LocalizeMyPostWidget.php");

class LocalizeMyPost extends BDWordPressPlugin {
	
	protected $option_page_slug = "localize-my-post-settings";
	protected $option_menu_title = "Localize My Post";
	
	protected $destination_taxonomy = 'localize-my-post-destinations';
	
	protected $option_page_title = "Localize My Post Settings";
	protected $option_page_introduction = "";
	
	private $location_fields = array(
		"bd_location_input",
		"bd_location_fmt",
		"bd_location_continent",
		"bd_location_country_code",
		"bd_location_country",
		"bd_location_city",
		"bd_location_zip",
		"bd_location_street",
		"bd_location_lat",
		"bd_location_lng"
	);
	
	public function init(){
		//Create localize options
		$this->createLocalizeOptions();
		//Add localize shortcodes
		$this->createLocalizeShortcodes();
		//Execute parents init method
		parent::init();
		//Add scripts to frondend
		add_action('wp_enqueue_scripts', array($this, 'addLocalizeFrontendScripts'));
		//Add scripts to admin pages
		add_action('admin_enqueue_scripts', array($this, 'addLocalizeAdminScripts'));
		add_action('admin_head',  array($this, 'initLocalizeAdminScripts'));
		//Save location on posts save event
		add_action('save_post', array($this, 'saveLocation'));
		//Add localize widget
		add_action('widgets_init', array($this, 'registerLocalizeWidgets'));
		//Register destination taxonomy
		add_action('init', array($this, 'initTaxonomy'));
	}
	
	public function registerLocalizeWidgets(){
		register_widget("LocalizeMyPostWidget");	
	}
	
	public function createLocalizeOptions(){
		//Create option group
		$group = new OptionGroup('localize-my-post-settings-basic', 'General Settings');
		//Radio options for the output format
		$this->createOption($group, array(
			'name' => 'localize-my-post-output',
			'title' => 'Output format',
			'callback' => 'callbackDisplayOptionRadios',
			'args' => array(
				'options' => array(
					0 => array('value' => '%fmt%', 'label' => 'Fully formatted address <code>77 McLeod St, Cairns, QLD 4870, Australia</code>'),
					1 => array('value' => '%country%, %continent%', 'label' => 'Country, Continent <code>Australia, Oceania</code>'),
					2 => array('value' => '%city%, %country%', 'label' => 'City, Country <code>Cairns, Australia</code>'),
					3 => array('value' => '%street%, %city%', 'label' => 'Street, City <code>77 Mc Leod St, Cairns</code>'),
					4 => array('value' => 'custom', 'label' => 'Custom format: ' . sprintf('<input class="regular-text code" style="width:150px;" type="text" name="%s" id="%s" value="%s" />', 'localize-my-post-output-custom', 'localize-my-post-output-custom', get_option('localize-my-post-output-custom')))
				)
			),
			//Connect to custom output format
			'connect' => array(1)
		));
		//Custom output format
		$this->createOption($group, array(
			'name' => 'localize-my-post-output-custom',
			'title' => 'Custom format',
			'callback' => 'callbackDisplayOptionTextfield'
		));
		//Default location
		$this->createOption($group, array(
			'name' => 'localize-my-post-default-location',
			'title' => 'Default location',
			'callback' => 'callbackDisplayOptionTextfield'
		));
		//Should countries and continents be displayed as archive links?
		$this->createOption($group, array(
			'name' => 'localize-my-post-link-terms',
			'title' => 'Link country and continent',
			'callback' => 'callbackDisplayOptionCheckboxes',
			'args' => array(
				'options' => array(
					0 => array('value' => 1)
				)
			)
		));
		//Add permalink settings group
		$group = new OptionGroup('localize-my-post-settings-permalink', 'Permalink Settings');
		//Permalink base
		$this->createOption($group, array(
			'name' => 'localize-my-post-permalink-base',
			'title' => 'Location base',
			'callback' => 'callbackDisplayOptionTextfield'
		));
	}
	
	public function createLocalizeShortcodes(){
		//Create shortcode for map output
		$this->addShortcode("map", array($this, "shortcodeMap"));
		//Create shortcode for location output
		$this->addShortcode("location", array($this, "shortcodeLocation"));
	}
	
	public function addLocalizeFrontendScripts($page){
		//Add google maps javascript API v3
		wp_enqueue_script('googlemaps-api-script', 'https://maps.googleapis.com/maps/api/js?v=3.exp');
	}
	
	public function addLocalizeAdminScripts($page){
		//Add google maps javascript API v3
		wp_enqueue_script('googlemaps-api-script', 'https://maps.googleapis.com/maps/api/js?v=3.exp');
		//Add icomoon iconfont for location icons
		wp_enqueue_style('location-icomoon', plugins_url('../icomoon/style.css', __FILE__));
		//Add javascript for localize button
		wp_enqueue_script('localize-button-script', plugins_url('../js/plugin.localizebutton.js', __FILE__));
		//Add localize button styles
		wp_enqueue_style('localize-button-styles', plugins_url('../css/localizebutton.css', __FILE__));
	}
	
	public function initLocalizeAdminScripts(){
		//Init admin JS
		$baseurl = '/' . str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname(__DIR__));
		echo '<script type="text/javascript">console.log("' . $baseurl . '"); (function($){ $(document).ready( function(){ $("#wp-content-media-buttons").localizeButton({ baseurl:"' . $baseurl . '" }) }) })(jQuery)</script>';
	}
	
	public function getLocalizeOption($key){
		//Return requested option
		return get_option($key);
	}
	
	public function getLocationFields(){
		//Return location field array
		return $this->location_fields;
	}
	
	public function getLocationFieldsValues($id){
		//Set up key value array
		$fieldvalue = array();
		foreach($this->location_fields as $field){
			//For every location field get value for given post id
			$value = $this->getLocationValue($id, $field);
			//And save it in array
			$fieldvalue[] = array("name" => $field, "value" => $value);
		}
		//Return key value array
		return $fieldvalue;
	}
	
	public function getLocationValue($id, $field, $single=true){
		//Return value of specific location meta field of requested id
		return get_post_meta($id, $field, $single);
	}
	
	public function getDefaultLocationFmt(){
		//Determine prefered output format for location from settings
		$fmt = $this->getLocalizeOption('localize-my-post-output');
		if($fmt == "custom"){
			$fmt = $this->getLocalizeOption('localize-my-post-output-custom');
		}
		if($fmt == ""){
			//Set last chance fallback
			$fmt = "%fmt%";
		}
		//And return it
		return $fmt;
	}
	
	public function getLocation($id, $fmt=null){
		//Get location as string
		if(!$fmt){
			$fmt = $this->getDefaultLocationFmt();
		}
		if( !$id && get_the_ID() ){
			$id = get_the_ID();
		}
		//Return formatted location
		return $this->fmtLocation($id, $fmt);
	}
	
	public function fmtLocation($id, $fmt){
		//Formatter for location output
		if(!$fmt){
			$fmt = $this->getDefaultLocationFmt();
		}
		if( !$id && get_the_ID() ){
			$id = get_the_ID();
		}
		$location = $fmt;
		$location = str_replace("%fmt%", $this->getLocationValue($id, "bd_location_fmt"), $location);
		$location = str_replace("%street%", $this->getLocationValue($id, "bd_location_street"), $location);
		$location = str_replace("%city%", $this->getLocationValue($id, "bd_location_city"), $location);
		$location = str_replace("%county%", $this->getLocationValue($id, "bd_location_county"), $location);
		$location = str_replace("%zip%", $this->getLocationValue($id, "bd_location_zip"), $location);
		
		$country_name = $this->getLocationValue($id, "bd_location_country");
		if( (int)$this->getLocalizeOption('localize-my-post-link-terms') != 1 ){
			$country = $country_name;
		} else {
			$term = $this->getPostTerm($id);
			if( is_null($term['country']) ){
				$country = $country_name;
			} else {
				$country = '<a href="' . $term['country']->permalink . '">' . $country_name . '</a>';
			}
		}
		$location = str_replace("%country%", $country, $location);
		
		$location = str_replace("%continent%", $this->getLocationValue($id, "bd_location_continent"), $location);
		
		return $location;
	}
	
	public function saveLocation($id){
		//Don't save on autosave
		if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
		//Don't save if user is not allowed to edit this post
		if(!current_user_can('edit_post', $id)) return;
		//Otherwise save location as meta data
		$this->saveLocationValues($id, $_POST);
	}
	
	public function saveLocationValues($id, $fields){
		//Save location data for post from array
		foreach($this->location_fields as $field){
			if(isset($fields[$field])){
				$this->saveLocationValue($id, $field, $fields[$field]);
			}
		}
		//Create continent, country and city in taxonomy if necessary
		$continent = $this->createTaxonomyTerm($fields['bd_location_continent']);
		$country = $this->createTaxonomyTerm($fields['bd_location_country'], array('parent'=>$continent['term_id']));
		//Set taxonomy to created country for post
		$this->setPostTerm($id, $country['term_id']);
	}
	
	public function saveLocationValue($id, $field, $value){
		//Update given location meta data
		update_post_meta($id, $field, sanitize_text_field($value));
	}
	
	public function getLocationMap($width, $height, $ids, $zoom){
		//Create new map instance
		$map = new GoogleMap();
		$map->setZoom($zoom);
		//Set width...
		if($width){
			$map->setWidth($width);
		}
		//...and height
		if($height){
			$map->setHeight($height);
		}
		//Split id list
		$ids = split(",", $ids);
		foreach($ids as $id){
			//Get lattitude and logitute of all given post ids
			$lat = $this->getLocationValue($id, "bd_location_lat");
			$lng = $this->getLocationValue($id, "bd_location_lng");
			if($lat && $lng){
				//And add marker to map
				$map->addMarker($lat, $lng);
				$map->centerMap($lat, $lng);
			}
		}
		//Return map code
		return $map->getCode();	
	}
	
	public function getAllLocations(){
		//Get all locations from published posts
		$args = array();
		$locations = get_terms($this->destination_taxonomy, $args);
		//Loop through locatons to bring them in order
		$continents = array();
		$continent_index = array();
		//Setup continents (entries with no parent)
		foreach($locations as $l){
			if( $l->parent == 0 ){
				$i = count($continents);
				$continents[$i] = $l;
				$continents[$i]->countries = array();
				$continents[$i]->count_total = $l->count;
				$continent_index[ $l->term_id ] = $i;
			}
		}
		//Then assign countries to their continents
		foreach($locations as $l){
			if( $l->parent > 0 && $continents[ $continent_index[ $l->parent ] ] ){
				$i = $continent_index[ $l->parent ];
				$l->count_total = $l->count;
				$continents[$i]->countries[] = $l;
				$continents[$i]->count_total += $l->count;
			}
		}
		//Return prepared continent->country array
		return $continents;
	}
	
	public function shortcodeMap($atts=array()){
		//Shortcode for map output
		//Initialize attributes
		$atts = shortcode_atts(array(
			'width' => null,
			'height' => null,
			'ids' => null,
			'zoom' => 8
		), $atts, 'map');
		//Fallback id if not set
		if(!$atts['ids'] && function_exists("get_the_ID") && get_the_ID() > 0){
			$atts['ids'] = get_the_ID();
		}
		//Call function to create map code
		return $this->getLocationMap($atts['width'], $atts['height'], $atts['ids'], $atts['zoom']);
	}
	
	function shortcodeLocation($atts=array()){
		//Shortcode for location output
		//Initialize attributes
		$atts = shortcode_atts(array(
			'fmt' => null,
			'id' => null
		), $atts, 'location');
		//Fallback to current ID
		if(!$atts['id'] && function_exists("get_the_ID") && get_the_ID() > 0){
			$atts['id'] = get_the_ID();
		}
		//Get location for given or current ID
		if($atts['id']){
			return $this->getLocation($atts['id'], $atts['fmt']);
		}
		return "";
	}
	
	public function initTaxonomy(){
		//Create a new taxonomy
		$labels = array(
			'name'              => __( 'Destinations' ),
			'singular_name'     => __( 'Destination' ),
			'search_items'      => __( 'Search Destinations' ),
			'all_items'         => __( 'All Destinations' ),
			'edit_item'         => __( 'Edit Destination' ),
			'update_item'       => __( 'Update Destination' ),
			'add_new_item'      => __( 'Add New Destination' ),
			'new_item_name'     => __( 'New Destination Name' ),
			'menu_name'         => __( 'Destinations' ),
		);
		//Determine slug
		$slug = $this->getLocalizeOption('localize-my-post-permalink-base');
		if( !isset($slug) || $slug == '' ){
			$slug = 'location';
		}
		//Set arguments for taxonomy registration
		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => $slug,
			'rewrite'           => array( 'slug' => $slug ),
		);
		register_taxonomy($this->destination_taxonomy, array('post'), $args);
	}
	
	public function createTaxonomyTerm($term, $args = array()) {
		//Insert new term into taxonomy
		//Check before if term already exists
		//Return term array
        if (isset($args['parent'])) {
            $parent = $args['parent'];
        } else {
            $parent = 0;
        }
        $result = term_exists($term, $this->destination_taxonomy, $parent);
        if($result == false || $result == 0){
            return wp_insert_term($term, $this->destination_taxonomy, $args);             
        } else {
            return (array) $result;
        }
	}
	
	public function setPostTerm($post_id=null, $term_id, $add=false){
		//Set custom taxonomy term for post
		if(!$post_id){
			$post_id = get_the_ID();
		}
		wp_set_post_terms($post_id, array($term_id), $this->destination_taxonomy, false);
	}
	
	public function addPostTerm($post_id=null, $term_id){
		//Add term to custom taxonomy term for post
		//Synonym for setPostTerm with add attribute set to true
		$this->setPostTerm($post_id, $term_id, true);
	}
	
	public function getPostTerm($post_id=null){
		//Get taxonomy term information for given (or current) post
		if(!$post_id){
			$post_id = get_the_ID();
		}
		$terms = get_the_terms($post_id, $this->destination_taxonomy);
		//Check if terms found
		if(!is_array($terms)){
			return array('country'=>null, 'continent'=>null);
		}
		//If so grab first result as country and determine continent by parent
		$country = $terms[0];
		$continent = get_term_by('id', $country->parent, $this->destination_taxonomy);
		//Set return array and add permalink information
		$return = array();
		$return['country'] = $country;
		$return['country']->permalink = $this->getTaxonomyTermPermalink($country->term_id);
		$return['continent'] = $continent;
		$return['continent']->permalink = $this->getTaxonomyTermPermalink($continent->term_id);
		return $return;
	}
	
	public function getTaxonomyTermPermalink($term_id){
		//Get the permalink for given taxonomy term
		return get_term_link($term_id, $this->destination_taxonomy);
	}
	
}