<?php

/**
 * LocalizeMyPostWidget
 * 
 * Author: Julian Burr
 * Version: 1.0
 * Date: 2016/03/05
 *
 * Copyright (c) 2016 Julian Burr
 * License: Published under MIT license
 *
 * Description:
 * 		Widget to show all location groups with post count
 **/

class LocalizeMyPostWidget extends WP_Widget {
	
	private $plugin_slug = 'localize-my-post';
	
	private $widget_title = 'Locations (Localize My Post)';
	private $widget_description = 'A grouped list of locations from your posts.';

	function LocalizeMyPostWidget(){
		//Construct widget
		parent::WP_Widget(
			false, 
			$name = __($this->widget_title, $this->plugin_slug), 
			array(
				'classname' => $this->plugin_slug . '-widget', 
				'description' => __($this->widget_description, $this->plugin_slug)
			)
		);
	}

	function form($instance){	
		//Form creation
		// Check values
		if($instance){
			$title = esc_attr($instance['title']);
			$show_postcnt = esc_attr($instance['show_postcnt']);
			$groupby = esc_attr($instance['groupby']);
		} else {
			//Fallbacks
			$title = '';
			$show_postcnt = 1;
			$groupby = 'country';
		}
		//Widget title
		echo "<p>";
		echo "<label for='" . $this->get_field_id('title') . "'>" . __('Title:', $this->plugin_slug) . "</label>";
		echo "<input class='widefat' id='" . $this->get_field_id('title') ."' name='" . $this->get_field_name('title') . "' type='text' value='" . $title . "' />";
		echo "</p>";
		//Widget option to show post counts
		echo "<p>";
		echo "<input id='" . $this->get_field_id('show_postcnt') ."' name='" . $this->get_field_name('show_postcnt') . "' type='checkbox' value='1'";
		checked('1', $show_postcnt);
		echo " />";
		echo "<label for='" . $this->get_field_id('show_postcnt') . "'>" . __('Show post counts', $this->plugin_slug) . "</label>";
		echo "</p>";
		//Widget option to determine what should be grouped by
		echo "<p>";
		echo "<label for='" . $this->get_field_id('groupby') . "'>" . __('Group by:', $this->plugin_slug) . "</label> ";
		echo "<select id='" . $this->get_field_id('groupby') ."' name='" . $this->get_field_name('groupby') . "'>";
		echo "<option value='country' ";
		echo ($groupby == 'country') ? 'selected' : '';
		echo ">" . __('Country', $this->plugin_slug) . "</option>";
		echo "<option value='continent' ";
		echo ($groupby == 'continent') ? 'selected' : '';
		echo ">" . __('Continent', $this->plugin_slug) . "</option>";
		echo "</select>";
		echo "</p>";
	}

	function update($new_instance, $old_instance){
		//Widget update method
		$instance = $old_instance;
		// Fields
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['show_postcnt'] = strip_tags($new_instance['show_postcnt']);
		$instance['groupby'] = strip_tags($new_instance['groupby']);
		return $instance;
	}

	function widget($args, $instance){
		//Display widget
		//Extract widget options
		extract($args);
		if(!$instance['title']){
			$instance['title'] = "Locations";
		}
		$title = apply_filters('widget_title', $instance['title']);
		echo $before_widget;
		//Display the widget
		echo '<div class="widget-text widget_links">';
		//Check if title is set
		if($title){
			echo $before_title . $title . $after_title;
		}
		//Create plugin class instance and get all locations as array
		$localize = new LocalizeMyPost(false);
		$continents = $localize->getAllLocations();
		if( count($continents) > 0 ){
			echo "<ul>";
			foreach( $continents as $con ){
				echo "<li class='continent continent-{$con->slug} continent-id-{$con->term_id}'>";
				echo "<a href='" . get_term_link($con->term_id) . "'>{$con->name}</a>";
				echo ($instance['show_postcnt'] == 1) ? " ({$con->count_total})" : "";
				if( $instance['groupby'] != 'continent' && count($con->countries) > 0 ){
					echo "<ul>";
					foreach( $con->countries as $cou ){
						echo "<li class='country country-{$cou->slug} country-id-{$cou->term_id}'>";
						echo "<a href='" . get_term_link($cou->term_id) . "'>{$cou->name}</a>";
						echo ($instance['show_postcnt'] == 1) ? " ({$cou->count_total})" : "";
					}
				}
				echo "</li>";
			}
			echo "</ul>";
		} else {
			echo __("No locations found!", $this->plugin_slug);
		}
		echo '</div>';
		echo $after_widget;
	}
	
}