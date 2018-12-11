<?php

class GoogleMap {
	
	private $id = null;
	
	private $code = null;
	private $options = array("zoom" => 8);
	
	private $width = 400;
	private $height = 400;
	
	private $center = array("lat" => 0, "lng" => 0);
	private $markers = array();
	
	public function __construct(){
		$this->id = "map-" . mt_rand();
	}
	
	public function setWidth($width){
		$this->width = $width;
	}
	
	public function setHeight($height){
		$this->height = $height;
	}
	
	public function setZoom($zoom){
		$this->addOption("zoom", $zoom);
	}
	
	public function addOption($name, $value){
		$this->options[$name] = $value;
	}
	
	public function addMarker($lat, $lng){
		$this->markers[] = array("lat" => $lat, "lng" => $lng);	
	}
	
	public function centerMap($lat, $lng){
		$this->center = array("lat" => $lat, "lng" => $lng);
	}
	
	public function getCode(){
		//Add map container
		$this->code = "<div class='localize-map' id='{$this->id}' style='width:{$this->width}px; height:{$this->height}px;'></div>\n\n";
		//Add javascript for map initialization
		$this->code .= "<script type='text/javascript' language='javascript'>\n";
		$this->code .= "\t//Init map\n";
		$this->code .= "\tvar mapOptions = " . json_encode($this->options) . "\n";
		$this->code .= "\tvar map = new google.maps.Map(document.getElementById('{$this->id}'), mapOptions);\n";
		//Add markers
		foreach($this->markers as $key => $marker){
			$this->code .= "\nvar marker{$key} = new google.maps.Marker({\n";
			$this->code .= "\tmap: map,\n";
			$this->code .= "\tposition: new google.maps.LatLng({$marker['lat']}, {$marker['lng']})\n";
			$this->code .= "});\n";
		}
		//Center Map
		if($this->center['lat'] && $this->center['lng']){
			$this->code .= "\nmap.setCenter(new google.maps.LatLng({$this->center['lat']}, {$this->center['lng']}));\n";
		}
		//Done
		$this->code .= "</script>";
		return $this->code;
	}
	
}