=== Meks Easy Photo Feed Widget ===
Contributors: mekshq
Donate link: https://mekshq.com/
Tags: instagram, instagram widget, instagram feed, instagram gallery, instagram images, instagram hashtag, sidebar, images, photos, widget, 
Requires at least: 3.7
Tested up to: 5.8
Stable tag: 1.2.4
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html

Easily display Instagram photos as a widget that looks good in (almost) any WordPress theme.

== Description ==

Meks Easy Photo Feed (formerly Instagram) Widget WordPress plugin is made to help you display good looking Instagram photos with a few clicks of the button. Several smart options are provided to fine-tune the widget appearance in order to match your personal taste as well as match any WordPress theme style out of the box.

== Features ==

* Instagram authorization required since Instagram "Basic Permission" API is now Legacy API
* Pull Instagram images by username
* Multiple usernames
* Choose number of Instagram photos to pull
* Choose in how many columns you would like to display your Instagram photos
* Specify spacing between Instagram images
* Fine-tune widget container size to pull the most optimized Instagram image size and match the current theme layout
* Automatically display the "Follow me" link
* Built-in caching (via transients) for optimized performance
* Shortcode [meks_easy_photo_feed title="Your Feed Title" username="your_username" container_size=2000 columns=6 photo_space=5 photos_number=12 link_text="your_username"]

Meks Easy Photo Feed Widget plugin is created by [Meks](https://mekshq.com)


== Installation ==

1. Upload plugin .zip file to plugins via WordPress admin panel or upload unzipped folder to your wp-content/plugins/ folder
2. Activate the plugin through the "Plugins" menu in WordPress
3. Go to Appearance -> Widgets to add widget and manage its options

== Frequently Asked Questions ==

For any questions, error reports and suggestions please visit https://mekshq.com/contact


== Screenshots ==

1. 3 columns
2. 4 columns
3. 2 columns
4. 1 column
5. Widget options

== Changelog ==

= 1.2.4 = 
* Improved: Prevent XSS (possible security issue in rare cases and only if users are logged in)

= 1.2.2 = 
* Added: Admin notification for meks plugins

= 1.2.1 = 
* Added: Shortcode - Display Instagram Photo Feed anywhere with shortcode
* Improved: Improved Business API authorization flow and error handling

= 1.2 = 
* Added: Full migration to Instagram Basic Display API since "Basic Permission" API is now Legacy API 

= 1.1.2 = 
* Fixed: Settings page link not properly displayed 

= 1.1.1 = 
* Fixed: Authorization flow not working properly for footer section in Meks themes

= 1.1 = 
* Added: Authorization settings page (for websites/IPs which are not allowed to request images from Instagram directly)

= 1.0.7 =
* Improved: Workaround for websites on shared hosting/IP blocked by Instagram server which are unable to retrieve the images
* Modified: Removed options for lower refresh intervals to avoid possible IP blockings( now minimum refresh interval is 12 hours )

= 1.0.6 =
* Added: Option to select up to 12 columns (useful for horizontal widgetized areas)
* Modified: Plugin name changed to Meks Easy Photo Feed due to Instagram trademark policy rules

= 1.0.5 =
* Added: Option to change widget refresh time

= 1.0.4 =
* Fixed: Pulling images by username stopped working due to Instagram API changes
* Improved: A slight code refactoring

= 1.0.3 =
* Fixed: Pulling images by username recently stopped working due to Instagram API changes

= 1.0.2 =
* Minor styling issues fixed

= 1.0.1 =
* Pulling images by hashtag is slightly modified under the hood due to Instagram API changes

= 1.0 =
* Initial release