## Changelog ##

### v2.1.0 - In Development ###
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
