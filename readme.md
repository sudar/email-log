# Email Log #
**Contributors:** sudar  
**Tags:** email, wpmu, wordpress-mu, log  
**Requires at least:** 3.3  
**Tested up to:** 3.6  
**Stable tag:** 1.5  

Logs every email sent through WordPress. Compatible with WPMU too.

## Description ##

Logs every email sent through WordPress. Compatible with WPMU too.

### Viewing logged emails

The logged emails will be stored in a separate table and can be viewed from the admin interface. While viewing the logs, the emails can be filtered or sorted based on the date, email, subject etc.

### Deleting logged emails

In the admin interface, all the logged emails can be delete in bulk or can also be selectively deleted based on date, email and subject.

### Forward email (Pro addon)

You can [buy the Forward email pro addon](http://sudarmuthu.com/out/buy-email-log-forward-email-addon), which allows you to send a copy of all the emails send from WordPress, to another email address. The addon allows you to choose whether you want to forward through to, cc or bcc fields. This can be extremely useful when you want to debug by analyzing the emails that are sent from WordPress. The cost of the addon is $15.

### Cleaning up db on uninstall

As [recommended by Ozh][1], the Plugin has an uninstall hook which will clean up the database when the Plugin is uninstalled.

 [1]: http://sudarmuthu.com/blog/2009/10/07/lessons-from-wordpress-plugin-competition.html

### Development

The development of the Plugin happens over at [github](http://github.com/sudar/email-log). If you want to contribute to the Plugin, [fork the project at github](http://github.com/sudar/email-log) and send me a pull request.

If you are not familiar with either git or Github then refer to this [guide to see how fork and send pull request](http://sudarmuthu.com/blog/contributing-to-project-hosted-in-github).

If you are looking for ideas, then you can start with one of the following TODO items :)

### TODO for Future releases

The following are the features that I am thinking of adding to the Plugin, when I get some free time. If you have any feature request or want to increase the priority of a particular feature, then let me know.

- Add option to automatically delete the logs periodically
- Make it MU compatible
- Add the ability to view the entire email
- Add the ability to resend the emails

### Support

- If you have found a bug/issue or have a feature request, then post them in [github issues](https://github.com/sudar/email-log/issues)
- If you have a question about usage or need help to troubleshoot, then post in WordPress forums or leave a comment in [Plugins's home page][1]
- If you like the Plugin, then kindly leave a review/feedback at [WordPress repo page][7].
- If you find this Plugin useful or and wanted to say thank you, then there are ways to [make me happy](http://sudarmuthu.com/if-you-wanna-thank-me) :) and I would really appreciate if you can do one of those.
- If anything else, then contact me in [twitter][2].

### Stay updated

I would be posting updates about this Plugin in my [blog][3] and in [Twitter][2]. If you want to be informed when new version of this Plugin is released, then you can either subscribe to this [blog's RSS feed][4] or [follow me in Twitter][2].

You can also checkout some of the [other Plugins that I have released][5].

 [1]: http://sudarmuthu.com/wordpress/email-log
 [2]: http://twitter.com/sudarmuthu
 [3]: http://sudarmuthu.com/blog
 [4]: http://sudarmuthu.com/feed
 [5]: http://sudarmuthu.com/wordpress
 [7]: http://wordpress.org/extend/plugins/email-log

## Translation ##

The Plugin currently has translations for the following languages.

*   German (Thanks Frank)
*   Lithuanian (Thanks  Vincent G)
*   Dutch (Thanks Zjan Preijde)

The pot file is available with the Plugin. If you are willing to do translation for the Plugin, use the pot file to create the .po files for your language and let me know. I will add it to the Plugin after giving credit to you.

## Installation ##

### Normal WordPress installations

Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation and then activate the Plugin from Plugins page.

### WordPress MU installations

Extract the zip file and drop the contents in the wp-content/plugins/ directory or mu-plugins directory of your WordPress MU installation and then activate the Plugin from the main blog's Plugins page.

## Screenshots ##

1. The above screenshot shows how the logged emails will be displayed by the Plugin

2. This screenshot shows how you can configure the email display screen. You can choose the fields and the number of emails per page.

## Changelog ##

### v0.1 (2009-10-08) ###
*   Initial Release

### v0.2 (2009-10-15) ###
*   Added compatibility for MySQL 4

### v0.3 (2009-10-19) ###
*   Added compatibility for MySQL 4 (Thanks Frank)

### v0.4 (2010-01-02) ###
*   Added German translation (Thanks Frank)

### v0.5 (2012-01-01) ###
*   Fixed a deprecation notice

### v0.6 (2012-04-29) (Dev time: 2 hours) ###
* Added option to delete individual email logs
* Moved pages per screen option to Screen options panel
* Added information to the screen help tab                   
* Added Lithuanian translations

### v0.7 (2012-06-23) (Dev time: 1 hour) ###
* Changed Timestamp(n) MySQL datatype to Timestamp (now compatible with MySQL 5.5+)
* Added the ability to bulk delete checkboxes

### v0.8 (2012-07-12) (Dev time: 1 hour) ###
* Fixed undefined notices - http://wordpress.org/support/topic/plugin-email-log-notices-undefined-indices
* Added Dutch translations

### v0.8.1 (2012-07-23) (Dev time: 0.5 hour) ###
* Reworded most error messages and fixed lot of typos

### v0.9(2013-01-08) - (Dev time: 1 hour)  ###
* Use blog date/time for send date instead of server time
* Handle cases where the headers send is an array

### v0.9.1 (2013-01-08) - (Dev time: 0.5 hour)  ###
* Moved the menu under tools (Thanks samuelaguilera)

### v0.9.2 (2013-03-14) - (Dev time: 0.5 hour)  ###
* Added support for filters which can be used while logging emails

### v0.9.3 (2013-04-01) - (Dev time: 0.5 hour)  ###
* Moved table name into a separate constants file

### v1.0 (2013-04-17) - (Dev time: 0.5 hour)  ###
* Added support for buying pro addons

### v1.1 (2013-04-27) - (Dev time: 0.5 hour)  ###
* Added more documentation

### v1.5 (2013-09-09) - (Dev time: 10 hours) ###
* Rewrote Admin interface using native tables

## Upgrade Notice ##

### 0.9.2 ###
Added filters for more customizing

### 1.0 ###
Added support for buying pro addons

### 1.5 ###
Rewrote Admin interface using native tables

## Readme Generator ##

This Readme file was generated using <a href = 'http://sudarmuthu.com/wordpress/wp-readme'>wp-readme</a>, which generates readme files for WordPress Plugins.
