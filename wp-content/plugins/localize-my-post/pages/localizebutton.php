<a id='button-add-location' class='button' href='#addlocation' title='Add Location'>
	<span class='icon icon-location5'></span>
    <span class='text'>Add Location</span>
</a>

<?php
	$localize = new LocalizeMyPost(false);
	foreach($localize->getLocationFields() as $field){
		echo "<input type='hidden' id='{$field}' name='{$field}' value=''>";
	}
?>

<div id='panel-add-location'>

	<div class='inner'>
    	<input id='bd_location_search_input' type='text' placeholder='Current Location' />
        <a id='bd_location_search' href='#localize' class='button'>Locate</a>
    </div>
    
    <div id='bd_location_map'></div>

	<div id='bd_location_formatted_address'>
    	<div class='inner'>
        	<span></span>
        </div>
    </div>
    
    <div id='bd_location_use_buttons'>
    	<div class='inner'>
        	<a href='#remove' id='bd_location_remove' class='button'>Remove address</a>
            <a href='#use' id='bd_location_use' class='button button-primary'>Use this address</a>
        </div>
    </div>

</div>