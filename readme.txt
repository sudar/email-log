=== Email Log ===
Contributors: sudar 
Tags: email, wpmu, wordpress-mu, log
Requires at least: 2.8
Tested up to: 2.8.4
Stable tag: 0.2

Logs every email sent through WordPress. Compatiable with WPMU too.

== Description ==

Logs every email sent through WordPress. Compatiable with WPMU too.

#### Viewing logged emails

The logged emails will be stored in a separate table and can be viewed from the admin interface. While viewing the logs, the emails can be filtered or sorted based on the date, to address, subject etc.

**Deleting logged emails**

In the admin interface, all the logged emails can be delete in bulk or can also be selectively deleted based on date, to address, subject.

**Cleaning up db on uninstall**

As [recommended by Ozh][1], the Plugin has uninstall hook which will clean up the database when the Plugin is uninstalled.

 [1]: http://sudarmuthu.com/blog/2009/10/07/lessons-from-wordpress-plugin-competition.html

== Installation ==

#### Normal WordPress installations

Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation and then activate the Plugin from Plugins page.

#### WordPress MU installations

Extract the zip file and drop the contents in the wp-content/plugins/ directory or mu-plugins directory of your WordPress MU installation and then activate the Plugin from Plugins page. You can activate it site wide or just for a single blog.
== Screenshots ==
1. The following screenshot shows how the logged emails will be displayed

2. This screenshot shows how the email logs could be filtered or sorted.

3. This one shows how the email logs could be deleted

== Changelog ==

###v0.1 (2009-10-08)

*   Initial Release

###v0.2 (2009-10-15)

*   Added compatability for MySQL 4

==Readme Generator== 

This Readme file was generated using <a href = 'http://sudarmuthu.com/wordpress/wp-readme'>wp-readme</a>, which generates readme files for WordPress Plugins.