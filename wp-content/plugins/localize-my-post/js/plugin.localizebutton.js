(function( $ ){
	
	$.fn.localizeButton = function(options){
		
		var settings = $.extend({
			animation_duration: 300,
			baseurl:''
        }, options );
		
		var plugin_slug = 'bd-localize-my-post',
			location_fields_values = null,
			localize_addhtml = null,
			post_id = $('#post_ID').val(),
			add_button,
			add_button_text,
			add_button_default_text,
			add_panel,
			search_button,
			search_field,
			panel_fmt,
			panel_fmt_text,
			use_button,
			remove_button,
			map,
			geocoder,
			geolocation,
			baseurl = settings.baseurl,
			continent_file = baseurl + '/json/continents.json';
			
		function setPanelLocation(){
			//Set all labels and fields in panel
			search_field.val($('#bd_location_input').val());
			add_button.attr('title', $('#bd_location_fmt').val()).addClass('button-primary');
			var cut_text = $('#bd_location_fmt').val();
			if(cut_text.length > 30){
				cut_text = cut_text.substring(0, 30) + '...';
			}
			add_button_text.text(cut_text);
			panel_fmt.show();
			panel_fmt_text.text($('#bd_location_fmt').val());
			googleMapsGeocoder(search_field.val());
		}
		
		function clearPanelLocation(){
			//Clear location fields and labels
			for(var i=0; i<location_fields_values.length; i++){
				$('#' + location_fields_values[i].name).val('');
			}
			search_field.val('');
			add_button.attr('title', add_button_default_text).removeClass('button-primary');
			add_button_text.text(add_button_default_text);
			panel_fmt.hide();
			panel_fmt_text.text('');
		}
		
		function usePanelLocation(){
			//Use currently gathered geodata from panel
			$('#bd_location_input').val(search_field.val());
			$('#bd_location_fmt').val(geolocation.formatted_address);
			for(var i=0; i<geolocation.address_components.length; i++){
				switch(geolocation.address_components[i].types[0]){
					case 'locality': 
						$('#bd_location_city').val(geolocation.address_components[i].long_name); 
					break;
					case 'administrative_area_level_1':
						$('#bd_location_county').val(geolocation.address_components[i].long_name);
					break;
					case 'country':
						$('#bd_location_country').val(geolocation.address_components[i].long_name);
						var country_code = geolocation.address_components[i].short_name;
						$('#bd_location_country_code').val(country_code);
						console.log('country', $('#bd_location_continent'), country_code, countryCodeToContinent( country_code ))
						countryCodeToContinent( country_code , function( continent ){
							$('#bd_location_continent').val( continent );
						})
					break;
					case 'postal_code': 
						$('#bd_location_zip').val(geolocation.address_components[i].long_name);
					break;
				}
			}
			$('#bd_location_lat').val(geolocation.geometry.location.lat());
			$('#bd_location_lng').val(geolocation.geometry.location.lng());
			setPanelLocation();
		}
		
		function countryCodeToContinent( code, callback ){
			//Get continent name from given country code (XX)
			//Work with callback function since getJSON is async
			var continent;
			$.getJSON( continent_file, function( data ){
				console.log('getJSON', data, code, data[code])
				if( typeof callback == 'function' ) callback( data[code] || 'Unknown' );
			})
		}
		
		function positionPanel(){
			//Position panel centered under button
			add_panel.css({
				'width' : add_panel.width(),
				'top' : add_button.position().top + add_button.height(),
				'left' : add_button.position().left + (add_button.width() / 2),
				'margin-left' : (add_panel.width() / 2) * (-1)
			});
		}
		
		function googleMapsInitMap(){
			//Initialize geocoder API
			geocoder = new google.maps.Geocoder();
			//Init map
			var latlng = new google.maps.LatLng(-34.397, 150.644);
			var mapOptions = {
				zoom: 8,
				center: latlng
			}
			//Finish initialization
			map = new google.maps.Map(document.getElementById('bd_location_map'), mapOptions);
		}
		
		function googleMapsGeocoder(address){
			//Get location information from entered address
			geocoder.geocode({ 'address': address }, function(results, status){
				if(status == google.maps.GeocoderStatus.OK){
					//Save result in global variable
					geolocation = results[0];
					googleMapsSetMap();
				} else {
					console.log('Geocode was not successful for the following reason: ' + status);
				}
			});
		}
		
		function googleMapsReverseGeocoder(){
			if(navigator.geolocation){
				navigator.geolocation.getCurrentPosition(function(pos){
					var latlng = new google.maps.LatLng(pos.coords.latitude, pos.coords.longitude);
					geocoder.geocode({ 'latLng': latlng }, function(results, status){
						if(status == google.maps.GeocoderStatus.OK){
							//Save result in global variable
							geolocation = results[0];
							googleMapsSetMap();
							search_field.val(geolocation.formatted_address);
						} else {
							console.log('Geocode was not successful for the following reason: ' + status);
						}
					});
				});
			} else {
				console.log('Geolocator not available');
			}
		}
		
		function googleMapsSetMap(){
			//And marker to this position
			var marker = new google.maps.Marker({
				map: map,
				position: geolocation.geometry.location
			});
			//Center map to found geolocation
			map.setCenter(geolocation.geometry.location);
			//Show formated address in panel
			panel_fmt.show();
			panel_fmt_text.text(geolocation.formatted_address);	
		}
		
		return this.each(function(){
			
			//Save selected element in variable
			var selection = $(this);
			
			//Get location fields
			$.ajax({
				url: baseurl + '/ajax/call.php',
				data: {
					'method' : 'getLocationFieldsValues',
					'args[]' : post_id
				},
				dataType: 'json'
			}).done(function(data){
				//Save location fields
				location_fields_values = data;
				
				//Get html of localize button and panel
				$.ajax({
					url: baseurl + '/ajax/include.php',
					data: 'file=../pages/localizebutton.php',
					dataType: 'html'
				}).done(function(data){
					//Save html to be added
					localize_addhtml = data;
					
					//...and append it
					selection.append(localize_addhtml);
					
					//Assign new (often used) elements to variables
					add_button = $('#button-add-location');
					add_button_text = add_button.find('span.text:first');
					add_button_default_text = add_button_text.text();
					add_panel = $('#panel-add-location');
					search_button = $('#bd_location_search');
					search_field = $('#bd_location_search_input');
					use_button = $('#bd_location_use');
					remove_button = $('#bd_location_remove');
					panel_fmt = $('#bd_location_formatted_address');
					panel_fmt_text = panel_fmt.find('span:first');
					
					//Set hidden field values
					for(var i=0; i<location_fields_values.length; i++){
						$('#' + location_fields_values[i].name).val(location_fields_values[i].value);
					}
					
					googleMapsInitMap();
					
					//If set, init localize button and panel
					if($('#bd_location_input').val() != ''){
						setPanelLocation();
					}
					
					//Add event handler
					
					//On add button click, toggle visibility of panel
					add_button.on('click', function(){
						if(add_panel.attr('data-visible') != 1){
							positionPanel();
							add_panel.fadeIn(settings.animation_duration).attr('data-visible', 1)
						} else {
							add_panel.fadeOut(settings.animation_duration).attr('data-visible', 0)
						}
						return false;
					});
					
					//On locate button click run google maps geocoder api
					search_button.on('click', function(){
						if(search_field.val() == ''){
							//Try using users current location
							googleMapsReverseGeocoder();
						} else {
							googleMapsGeocoder(search_field.val());
						}
						return false;
					});
					
					//If the user presses enter while in the search field of the panel
					//Catch event and trigger search
					search_field.on('keydown', function(e){
						if(e.key == 'Enter'){
							search_button.click();
							return false;
						}
					});
					
					//On use this location click fill inputs with data
					use_button.on('click', function(){
						usePanelLocation();
						add_button.click();	
						return false;
					});
					
					//On remove button click clear all data
					remove_button.on('click', function(){
						clearPanelLocation();
						add_button.click();	
						return false;
					});
				});
			});
		});
		
	}
	
	//$(document).ready(function(){
	//	$('#wp-content-media-buttons').localizeButton();
	//});
	
})( jQuery );