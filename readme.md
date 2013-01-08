=== Email Log ===
Contributors: sudar 
Tags: email, wpmu, wordpress-mu, log
Requires at least: 2.8
Tested up to: 3.5
Stable tag: 0.9.1

Logs every email sent through WordPress. Compatible with WPMU too.

== Description ==

Logs every email sent through WordPress. Compatible with WPMU too.

#### Viewing logged emails

The logged emails will be stored in a separate table and can be viewed from the admin interface. While viewing the logs, the emails can be filtered or sorted based on the date, to address, subject etc.

**Deleting logged emails**

In the admin interface, all the logged emails can be delete in bulk or can also be selectively deleted based on date, to address, subject.

**Cleaning up db on uninstall**

As [recommended by Ozh][1], the Plugin has uninstall hook which will clean up the database when the Plugin is uninstalled.

 [1]: http://sudarmuthu.com/blog/2009/10/07/lessons-from-wordpress-plugin-competition.html

### Translation

*   German (Thanks Frank)
*   Lithuanian (Thanks  Vincent G , from [http://www.host1free.com][6])
*   Dutch (Thanks Zjan Preijde)

The pot file is available with the Plugin. If you are willing to do translation for the Plugin, use the pot file to create the .po files for your language and let me know. I will add it to the Plugin after giving credit to you.

### Development and Support
The development of the Plugin happens over at [github](http://github.com/sudar/email-log). If you want to contribute to the Plugin, [fork the project at github](http://github.com/sudar/email-log) and send me a pull request.

If you are not familiar with either git or Github then refer to this [guide to see how fork and send pull request](http://sudarmuthu.com/blog/contributing-to-project-hosted-in-github).

Support for the Plugin is available from the [Plugin's home page][1]. If you have any questions or suggestions, do leave a comment there or contact me in [twitter][2].

### Stay updated

I would be posting updates about this Plugin in my [blog][3] and in [Twitter][2]. If you want to be informed when new version of this Plugin is released, then you can either subscribe to this [blog's RSS feed][4] or [follow me in Twitter][2].

### Links

*   [Plugin home page][1]
*   [Author's Blog][3]
*   [Other Plugins by the author][5]

 [1]: http://sudarmuthu.com/wordpress/email-log
 [2]: http://twitter.com/sudarmuthu
 [3]: http://sudarmuthu.com/blog
 [4]: http://sudarmuthu.com/feed
 [5]: http://sudarmuthu.com/wordpress
 [6]: http://www.host1free.com

== Installation ==

#### Normal WordPress installations

Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation and then activate the Plugin from Plugins page.

#### WordPress MU installations

Extract the zip file and drop the contents in the wp-content/plugins/ directory or mu-plugins directory of your WordPress MU installation and then activate the Plugin from the main blog's Plugins page.

== Screenshots ==
1. The following screenshot shows how the logged emails will be displayed

2. This screenshot shows how the email logs could be filtered or sorted.

3. This one shows how the email logs could be deleted

== Changelog ==

###v0.1 (2009-10-08)

*   Initial Release

###v0.2 (2009-10-15)

*   Added compatibility for MySQL 4

###v0.3 (2009-10-19)

*   Added compatibility for MySQL 4 (Thanks Frank)

###v0.4 (2010-01-02)

*   Added German translation (Thanks Frank)

###v0.5 (2012-01-01)

*   Fixed a deprecation notice

###v0.6 (2012-04-29) (Dev time: 2 hours)
* Added option to delete individual email logs
* Moved pages per screen option to Screen options panel
* Added information to the screen help tab                   
* Added Lithuanian translations

###v0.7 (2012-06-23) (Dev time: 1 hour)
* Changed Timestamp(n) MySQL datatype to Timestamp (now compatible with MySQL 5.5+)
* Added the ability to bulk delete checkboxes

###v0.8 (2012-07-12) (Dev time: 1 hour)
* Fixed undefined notices - http://wordpress.org/support/topic/plugin-email-log-notices-undefined-indices
* Added Dutch translations

###v0.8.1 (2012-07-23) (Dev time: 0.5 hour)
* Reworded most error messages and fixed lot of typos

### v0.9(2013-01-08) - (Dev time: 1 hour) 
* Use blog date/time for send date instead of server time
* Handle cases where the headers send is an array

### v0.9.1 (2013-01-08) - (Dev time: 0.5 hour) 
* Moved the menu under tools (Thanks samuelaguilera)

==Readme Generator== 

This Readme file was generated using <a href = 'http://sudarmuthu.com/wordpress/wp-readme'>wp-readme</a>, which generates readme files for WordPress Plugins.
