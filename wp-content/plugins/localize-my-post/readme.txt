=== Localize My Post ===
Contributors: julianburr
Tags: location, google maps, localize, maps, map
Requires at least: 3.0.1
Tested up to: 4.4.2
Stable tag: 1.0
License: MIT

Easily add location information to your contents, as well as even esier use them in your theme or widgets.

== Description ==

This plugin makes it super easy to add and manage locations in your posts and pages. This way you can show everyone where you were when you made the post, what part of the world this post is about, etc.

To do so is easy. After installation of the plugin, a new button appears in your page and post editor. Click it, to open a panel where you can search for the location you want to assign to the content. A google map helps you to find the right place. Once your happy, simply click 'Use this location' and the plugin handles the rest.

== Custom Taxonomy ==

This plugin works by using a custom taxonomy for your destionation as well as custom meta information. All location information (received via the Google Maps API) are stored in the meta data of the content. Additionally country and continent are saved in the mentioned (hierarchical) custom taxonomy, allowing easy access as archives or as additional meta information in your theme and widgets.

The following custom meta fields are used:

* `bd_location_input` - The original input in the search field of the plugin panel
* `bd_location_fmt` - The complete (formatted) version of the location as received by the Google Maps API
* `bd_location_continent` - The continent
* `bd_location_country_code` - The two character country code
* `bd_location_country` - The country name
* `bd_location_city` - The city name
* `bd_location_zip` - The areas ZIP code
* `bd_location_street` - The street name
* `bd_location_lat` - Latitude of the location as given by Google Maps API
* `bd_location_lng` - Longitude

The custom taxonomy is registered under the following name:

* `localize-my-post-destinations`

The first level of this taxonomy regresents continents, where the second level represents the counrties. New entries for this taxonomy are automatically added on the fly as well as connected to your posts according to your selected location.

== Widget, Short Codes & Functions ==

= Widget =

This plugin comes with a widget similar to Wordpress' categories widget. It shows all locations of your post, optionally followed by the post count. Use this simple widget to show people where you've been...

= Short Codes =

The two short codes offered by this plugin are the following:

`[map]` - Prints a google map centered to the location of the given ids or the current post if no id is defined.

Attributes (optional):

* width
* height
* ids
* zoom (default 8)

`[location]` - Prints the location using the given format string. If no format is defined, it uses the default format from the plugin settings.

Attributes (optional):

* fmt
* id