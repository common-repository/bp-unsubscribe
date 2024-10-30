=== BP Unsubscribe ===
Contributors: aheadzen
Tags: buddypress,notifications,future prediction,future notifications,dasha notifications,calender,astrology
Requires at least : 4.0.0
Tested up to: 4.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Unsubscribe plugin to stop emails coming from buddypress. It will allow user to one click disable all notification emails.

By using of shortcode user can send the unsubscribe link via email.

The plugin included unsubscribe link directly with email content for activity notification emails, message notification emails, group notification emails.

The plugin will redirected to profile notification settigs and will unsert all settings.

== Description ==

The plugin already added unsubscribe link added to the email send by activity notification emails, message notification emails, group notification emails.

Any user can unsubscribe email notification by one click only.

Shotcode :: 
---------------------
[az_unsubscribe_emails]
[az_unsubscribe_emails user_id=100]

-- You may use the shortcode with user id for specific user to give unsubscribe link.
-- If you not passing user id, it will return the current login user's unsubscribe link.
-- The shortcode just return the full url of unsubscribe link and you may use your way in your html.
-- If no user login then it will process automaticaly, redirected to login page and unsubscribe page....


User ID wordpress filter :: 
---------------------
azbp_unsubscribe_user_id

-- You can user the filder for change user id for unsubscribe.


User Subscribe/Unsubscribe  Links:: 
---------------------
http://YOUR-SITE-URL.COM/members/USERNAME/settings/notifications/?az_unsubscribe=all
http://YOUR-SITE-URL.COM/members/USERNAME/settings/notifications/?az_subscribe=all

OR 

Default Links
-------------
http://YOUR-SITE-URL.COM/?az_unsubscribe=all



== Installation ==
1. Unzip and upload plugin folder to your /wp-content/plugins/ directory  OR Go to wp-admin > plugins > Add new Plugin & Upload plugin zip.
2. Go to wp-admin > Plugins(left menu) > Activate the plugin



== Changelog ==

= 1.0.0 =
* Fresh Public Release.


= 1.0.1 =
* For new user unsubscribe email link now working - problem solved.


= 1.0.2 =
* Removed unwanted text.


= 1.0.3 =
* Voter plugin unsubscribe related default settings added.
* New filter added for default key settings you may use like below

add_filter('az_bp_unsubscribe_notification_keys','az_bp_unsubscribe_notification_keys_fun');
function az_bp_unsubscribe_notification_keys_fun($keys){	
	/**************************
	YOUR ADDITIONAL CODE HERE
	**************************/	
	return print_r($keys);exit;;
}


= 1.0.4 =
* buddypres 2.5+ unsubscribe notification -- correction done

= 1.0.5 =
* user profile changed "All Emails" title --> "Receive All Email Notifications"
* Administrator can edit other profile's email notification.

= 1.0.6 =
* "BuddyPress Follow" (Plugin URI: http://wordpress.org/extend/plugins/buddypress-followers)
	-- email notificaiton edit to add unsubscribe link.




