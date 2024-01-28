## Changelog ##

### v2.4.9 – (In Dev) ###
- Tweak: Explicitly check the action name and only allow actions based on an allow list.

### v2.4.8 – (2021-11-06) ###
- Fix: Escape the search parameters before rendering them

### v2.4.7 – (2021-10-13) ###
- Fix: Fix the typo that was introduced in v2.4.6

### v2.4.6 – (2021-10-13) ###
- Fix: Enhance how order and order by works.
- Tweak: Tested upto WordPress 5.8

### v2.4.5 – (2021-04-13) ###
- Fix: Don't override WordPress plugin update in base plugin.

### v2.4.4 – (2021-03-28) ###
- Tweak: Use the jQuery UI that is bundled with WordPress.
- Tweak: Don't load add-on information from a local json file.

### v2.4.3 – (2020-10-09) ###
- New: Added `el_log_time_display_format` filter to change the email log display time.
- New: Provide an option for translators to override the email log display time.
- Tweak: Enhanced the nonce check.

### v2.4.2 – (2020-07-07) ###
- Fix: Fixed issues with email preview tab switcher that occurred in some installations.
- Tweak: Enhanced the UI for lifetime license activation.

### v2.4.1 – (2020-07-06) ###
- Fix: Added a missing file that broke v2.4.0 release for some installations.

### v2.4.0 – (2020-07-05) ###
- New: Capture the error message when an email fails to be delivered.
- New: Show seconds in send time.
- Tweak: Enhance the email preview screen.
- Tweak: Code enhancements for better maintenance.
- Fix: Minor CSS alignment issues in addon list page.

### v2.3.2 – (2020-03-03) ###
- New: Added the ability to log BuddyPress emails which don't use wp_mail function.
- New: Supports PHP 5.6 to PHP 7.4
- Tweak: Design tweaks for WordPress 5.3
- Fix: Don't throw a warning if wrong data is passed to wp_mail function.
- Fix: Bulk Actions that were performed from the bottom dropdown in the Log List page was not working.

### v2.3.1 – (2019-04-11) ###
- Fix: Added a missing file that broke v2.3.1 release for some installations.

### v2.3.0 - (2019-04-11) ###

- New: The 'To' and 'Subject' fields now supports text upto 500 characters.
- New: Store the name of the attachment file if present.
- New: Store ip address of the request that triggered the email.
- New: Setting to enable notification when the number of email logs reach a threshold.
- New: Dashboard Widget - Provide an option to hide Dashboard Widget from all users.
- Tweak: Dashboard Widget - Add a thousand separator for logs count.
- Tweak: Improve the preview of HTML emails.
- Tweak: Enhanced the output of System Info page.
- Fix: Deleting logs in bulk was not working.
- Fix: Added compatibility with wp-ses plugin.
- Fix: Added the ability to deactivate expired license.

### v2.2.5 - (2018-02-22) ###
- Tweak: Show Dashboard Widget only if the user can view email logs.

### v2.2.4 - (2017-11-23) ###
- Tweak: Tweaks to how the license is handled.

### v2.2.3 - (2017-11-09) ###
- Fix: Escaped all the fields while displaying email details in popup.

### v2.2.2 - (2017-11-07) ###
- Tweak: Improved the way nonce checks are performed.
- Fix: Escaped the email content before showing it in preview.

### v2.2.1 - (2017-11-04) ###
- Tweak: Improved the UX of the view email content screen.

### v2.2.0 - (2017-10-09) ###
- New: Dashboard Widget that display summary about email logs.
- Tweak: Performance improvements in add-on license code.

### v2.1.0 - (2017-09-21) ###
- New: GUI option to choose the user roles that can access email logs.
- New: GUI option to delete email log table when the plugin is uninstalled.
- Tweak: Performance improvements.
- Tweak: Delete all traces of the plugin from DB if the user chooses to destroy data during uninstall.
- Fix: Handle cases where there is a quote in front of email address.
- Fix: Handle cases where array passed to `wp_mail` may not contain all the required fields.

### v2.0.2 - (2017-08-07) ###
- Fix: Renamed include/util directory to correct case. This caused issues in some install.

### v2.0.1 - (2017-08-04) ###
- Fix: Fixed a JavaScript issue in view logs page.
- Fix: Fixed a CSS issue in view logs page.
- Fix: Fixed a race condition between plugin and add-ons.

### v2.0.0 - (2017-08-04) ###
- New: Ability to filter logs by date.
- New: Ability to filter logs by name.
- New: Complete rewrite for better performance.
- Docs: Dropped support for PHP 5.2

### v1.9.1 - (2016-07-02) - (Dev time: 0.5 hour) ###
- Fix: Only allow users with `manage_option` capability to view email content.

### v1.9 - (2016-06-19) - (Dev time: 6 hours) ###
- Fix: Improve the performance of count query (issue #33)
- Docs: Added access modifiers to class methods
- Docs: Removed unused array_get() method
- Docs: Inline documentation added
- Tests: Added Unit tests

### v1.8.2 (2016-04-20) - (Dev time: 1 hour) ###
- Tweak: Log all emails from the TO field. Earlier the plugin was logging only the first email
- Fix: Fixed issues in parsing reply-to and content-type headers

### v1.8.1 (2015-12-27) - (Dev time: 0.5 hour) ###
- Fix: Fixed the "Delete All Logs" issue that was introduced in v1.8

### v1.8 (2015-12-26) - (Dev time: 5 hours) ###
- New: Added filters and actions for addons
- New: Added Resend Email Addon
- Tweak: Optimize for large number of logs
- Tweak: Use charset and collate that is defined in wp-config.php file
- Tweak: Format email content
- Tweak: Remove PHP4 compatible code
- Fix: Sanitize the delete email log url

### v1.7.5  (2014-09-23) - (Dev time: 1 hour) ###
- Tweak: Remove PHP 4.0 compatibility code
- Tweak: Tweak the install code (issue #26)
- Fix: Include JavaScript only when needed
- Fix: Fix a bug in the save user options function (issue #27)

### v1.7.4  (2014-07-24) - (Dev time: 0.5 hours) ###
- Fix: Handle cases where `date_format` or `time_format` are empty (issue #23)
- Tweak: Remove excessive comments from include/class-email-log-list-table.php (issue #10)

### v1.7.3  (2014-05-14) - (Dev time: 0.5 hours) ###
- Fix: Fixed a compatibility issue with wpmandrill plugin (issue #20)

### v1.7.2  (2014-04-16) - (Dev time: 0.5 hours) ###
- Fix: Fix issue in register_activation_hook

### v1.7.1  (2014-04-02) - (Dev time: 0.5 hours) ###
- Fix: Fix the issue that was preventing the tables to be created

### v1.7  (2014-03-29) - (Dev time: 2.5 hours) ###
- Fix: Fix whitespace
- New: Add support for WordPress Multisite (issue #18)
- New: Add ability to delete all logs at once (issue #19)

### v1.6.2  (2014-01-27) - (Dev time: 0.5 hours) ###
- Fix: Fix unexpected output while activating the plugin

### v1.6.1  (2013-12-17) - (Dev time: 0.5 hours) ###
- Fix: Change `prepare_items` function so that it adheres to strict mode
- Fix: Remove `screen_icon` function call which is not used in WordPress 3.8
- New: Compatible with WordPress 3.8

### v1.6  (2013-12-08) - (Dev time: 0.5 hours) ###
- New: Add a link to view the content of the email in the log screen

### v1.5.4  (2013-09-21) - (Dev time: 0.5 hours) ###
- Fix issue in searching non-english characters
- Add addon screenshots

### v1.5.3 (2013-09-14) - (Dev time: 0.5 hours) ###
- Fix issue in bulk deleting logs

### v1.5.2 (2013-09-13) - (Dev time: 0.5 hours) ###
- Add the ability to override the fields displayed in the log page
- Add support for "More Fields" addon

### v1.5.1 (2013-09-09) - (Dev time: 0.5 hours) ###
- Correct the upgrade file include path. Issue #7
- Fix undfined notice error. Issue #8
- Update screenshots. Issue #6

### v1.5 (2013-09-09) - (Dev time: 10 hours) ###
- Rewrote Admin interface using native tables

### v1.1 (2013-04-27) - (Dev time: 0.5 hour)  ###
- Added more documentation

### v1.0 (2013-04-17) - (Dev time: 0.5 hour)  ###
- Added support for buying pro addons

### v0.9.3 (2013-04-01) - (Dev time: 0.5 hour)  ###
- Moved table name into a separate constants file

### v0.9.2 (2013-03-14) - (Dev time: 0.5 hour)  ###
- Added support for filters which can be used while logging emails

### v0.9.1 (2013-01-08) - (Dev time: 0.5 hour)  ###
- Moved the menu under tools (Thanks samuelaguilera)

### v0.9(2013-01-08) - (Dev time: 1 hour)  ###
- Use blog date/time for send date instead of server time
- Handle cases where the headers send is an array

### v0.8.1 (2012-07-23) (Dev time: 0.5 hour) ###
- Reworded most error messages and fixed lot of typos

### v0.8 (2012-07-12) (Dev time: 1 hour) ###
- Fixed undefined notices - http://wordpress.org/support/topic/plugin-email-log-notices-undefined-indices
- Added Dutch translations

### v0.7 (2012-06-23) (Dev time: 1 hour) ###
- Changed Timestamp(n) MySQL datatype to Timestamp (now compatible with MySQL 5.5+)
- Added the ability to bulk delete checkboxes

### v0.6 (2012-04-29) (Dev time: 2 hours) ###
- Added option to delete individual email logs
- Moved pages per screen option to Screen options panel
- Added information to the screen help tab
- Added Lithuanian translations

### v0.5 (2012-01-01) ###
- Fixed a deprecation notice

### v0.4 (2010-01-02) ###
- Added German translation (Thanks Frank)

### v0.3 (2009-10-19) ###
- Added compatibility for MySQL 4 (Thanks Frank)

### v0.2 (2009-10-15) ###
- Added compatibility for MySQL 4

### v0.1 (2009-10-08) ###
- Initial Release

## Upgrade Notice ##

### 2.4.6 ###
Tested upto WordPress 5.8

### 2.4.5 ###
Improved admin performance and reduced network calls.

### 2.4.1 ###
Starting v2.4.0 the plugin now captures the error message when an email fails to be delivered.

### 2.4.0 ###
Starting v2.4.0 the plugin now captures the error message when an email fails to be delivered.

### 2.3.0 ###
The 'To' and 'Subject' fields now supports text upto 500 characters.

### 2.2.5 ###
Hide Dashboard Widget for users who can't view email logs.

### 2.2.3 ###
Improved the security of the plugin by escaping all the fields before displaying them.

### 2.2.2 ###
Improved the way nonce checks are done. No visible changes from functionality perspective.

### 2.2.1 ###
Improved the UX of the view email content screen.

### 2.2.0 ###
Added a Dashboard Widget that display summary information about email logs.

### 2.1.0 ###
GUI option to choose who can access email logs and performance improvements.

### 2.0.2 ###
Fixed the case of the Util directory. This caused issues in some install.

### 2.0.1 ###
Fixed a JavaScript issue that was introduced in v2.0.0

### 2.0.0 ###
Ability to search logs by date. Dropped support to PHP 5.2

### 1.9.1 ###
- Fixed a minor security issue that allowed unprevilleged users to view content of logged emails

### 1.9 ###
- Fixed issues with pagination.

### 1.8.2 ###
Added the ability to log all emails in the TO field instead of just the first one

### 1.8.1 ###
Fixed issue with "Delete All Logs" action that was introduced in v1.8

### 1.8 ###
Added support for resending emails through addon

### 1.7.5 ###
Fix a bug in the save user options function

### 1.7.4 ###
Handle cases where `date_format` or `time_format` are empty

### 1.7.2 ###
Fix the bug that was introduced in v1.7

### 1.7.1 ###
Fix the bug that was introduced in v1.7

### 1.6 ###
Ability to view content of the email

### 1.5.4 ###
Fixed issue in searching for non-english characters

### 1.5.3 ###
Fix issue in bulk deleting logs

### 1.5 ###
Rewrote Admin interface using native tables

### 1.0 ###
Added support for buying pro addons

### 0.9.2 ###
Added filters for more customizing
