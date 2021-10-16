# Vavok v1.5.15.a1
- Added method show_notification() in Vavok.class.php
- Added page for blog category management, possiblility add category, delete, edit, move up and down
- Added method show_danger() in Vavok.class.php
- Updated Navigation.class.php, use Bootstrap for pagination
- Updated localization
- Removed process.php from adminpanel
- Removed method show_error() in Vavok.class.php



# Vavok v1.5.14
- Added support for cookie-free domains
- Added current_connection() method to Vavok.class.php
- Added method update_default_permissions() to Users.class.php
- Added option to add and edit access to administration panel options
- Added method db_queries() to count database requests while debugging
- Updated localization
- Updated handling GET and POST data
- Updated default theme
- Updated is_moderator() and is_administrator() methods
- Updated default theme to show number of database queries in debug mode
- Updated node modules
- Updated method erase_img() in Vavok.class.php
- Updated blog and template files
- Moved folder images to themes/images
- Refresh users browser name after login
- Show time zone in profile
- Removed file include/functions_extra.php
- Removed deprecated method insert_data() from Db.class.php
- Don't count as new users click in statistics if request is ajax/dynamic
- Reduced number of DB queries



# Vavok v1.5.13.5
- Removed xoft_encode method from Vavok.class.php
- Removed xoft_decode method from Vavok.class.php
- Removed safe_encode method from Vavok.class.php
- Removed safe_decode method from Vavok.class.php
- Removed deprecated method insert_data
- Remove saved token when user logout
- Implemented handling of POST and GET data
- Added method user_info() in Users.class.php
- Added methods update_user() and filter_user_fields_values() in Users.class.php
- Added updated date for blog pages
- Use update_user() method for updatins users data
- Use user_info() method for retrieving users info
- Updated localization



# Vavok v1.5.10
- Added support for blog categories
- Added priority levels for email queue
- Added limit_file_lines() method in Vavok.class.php
- Added minimal time between emails while resending email for registration confirmation code
- Added reCAPTCHA
- Updated Bootstrap to v5 for default theme
- Updated contact form to include sender's email and name in email text
- Updated error logging
- Use PHPMailer for sending emails
- Use email queue for sending most of the mails from the site
- Use token for cookie remember me function
- Removed securimage plugin
- Template updates
- Localization updates
- HOMEDIR constant bugfix



# Vavok v1.5.8
- Vavok CMS in now fully compatible with PHP 8
- Check if functon get_magic_quotes_gpc() exists
- Default theme updated
- Added website search
- Show blog post published date instead of page creation date
- Show current date for blog post when page is not published
- Created new function clean_request_uri()
- Use default theme for admin panel



# Vavok v1.5.7.1
- Updated localization
- Updated how settings are stored in database
- Bugfix: Update links stored in header when page link is renamed
- Create configuration .env file when CMS installation is started



# Vavok v1.5.6
- Updated form templates
- Updated localization
- Updated site links
- Updated handling globals
- Site search improvements
- Added option to delete profile
- Addes page tags
- Added cronjob to send email from queue
- Added cronjob to delete email from queue
- New option for email confirmation



# Vavok v1.5.5
- System functions moved to Vavok.class.php
- Created class Counter.class.php
- Created class Manageip.class.php
- Created class Referer.class.php
- Created form templates
- Updated localization files
- Updated site installation
- Updated cookie management
- Updated setting page title
- Updated loading page head tags
- Updated setting additional head tags
- Updated pages: Separate presentation from logic
- Updated Db.class.php
- Updated default template: Use Sass and minify CSS
- Added page tags
- Added form templates
- Removed file include/cookies.php
- Removed file include/config.php
- Removed file include/functions.php
- Removed file include/prepare_header.php
- Removed file include/antidos.php
- Removed file include/counters.php
- Removed file include/referer.php
- Removed file include/pages.php
- Removed file inlude/load_header.php



# Vavok v1.5.4
- Updated constants
- Updated retrieving configuration data
- Updated localization
- Updated .htaccess to protect .env
- Updated website settings page
- Updated website installer
- Update: Use new configuration constants
- Created class Localization.class.php
- Created class Vavok.class.php
- Created .env for website configuration
- Removed pages/inprof.php
- Removed install/install.php
- Removed lang/en/.*
- Removed lang/sr/.*
- Moved files from 'lang' to 'include/lang'



# Vavok v1.5.3.1
- Updated inbox
- Updated User.class
- Updated language files
- Added: New functon detect_bot()
- Removed include/header.php



# Vavok v1.5.3
- Updated: User permissions
- Updated: Administration panel access levels update
- Update: Upload directory can be changed
- Update: Language handling improvements
- Update: Get page data from class
- Update: Language updates
- Update: Theme updates
- Update: Page updates
- Update: Include header and footer
- Added: New function clear_directory()
- Added: New function show_page()



# Vavok v1.5.2
- Updated navigation
- Updated language files
- Update use new API for IP informations
- Update main page optimisations
- Updated login and register pages to use page templates
- Updated page templates
- Updated registration
- Updated functions
- Added comments
- Added support for page language attribute &lt;html lang="(lang)"&gt;
- Added blog author and post creation date preview



# Vavok v1.5.1
- Update session and cookie improvements
- Update function update in Db.class.php return update result
- Update navigation improvements
- Update page generation improvements
- Update use access rights from session
- Update to default theme
- Updated registration key improvements
- Updated private message system
- Updated language parsing
- Added new languages
- Added custom page structure option
- Added function select_page_name() in Db.class.php
- Fixed when user agent is empty
- Removed page settings



# Vavok v1.5
- Update new password hashing
- Update removed redirection from main page
- Update installer improvements
- Update user can create separate tables for cross domain website
- Updated caching
- Updated default theme
- Updated functions
- Added function get_age() in Users.class.php
- Renamed pages/setting.php to pages/settings.php
- Renamed pages/profil.php to pages/profile.php
- Renamed adminpanel/setting.php to adminpanel/settings.php
- Renamed include/functions1.php to include/functions_extra.php



# Vavok v1.4.1.1
- Update add missing open graph tags
- Update page for changing language updated
- Update blog page view
- Update default theme, new navigation
- Update navigation improved
- Bugfix when page is redirected
- Update connection protocol and browser functions



# Vavok v1.4.1
- Added files for creating new pages
- Added option to show page as a blog post
- Added page pages/blog.php
- Added view counter for pages
- Added page's default image
- Update .htaccess file updated to show blog pages
- Update improved navigation
- Update function page_exists() in Page.class return page id if page exists
- Update function select_page() in Page.class update page views
- Update pages/pages.php with new functions



# Vavok v1.4

- Added Bootstrap support
- Update default theme use Bootstrap
- Update from now use browser's preferred browser language/script if there are two scripts or more
- Update Users.class.php with new functions
- Update language and code improvements for some pages
- Update security imrovements
- Moved pages.php to pages/pages.php
- Moved input.php to pages/input.php
- Added include/load_header.php to remove code from theme file
- Added include/prepare_header.php to remove code from theme file



# Vavok v1.3.2

- Update to catch error if language file does not exist
- Updated mailer class to catch errors
- Bugfix cached pages not updated
- Bugfix zero considered as empty string



# Vavok v1.3.1

- Added option to block IP address
- Security bugfix
- Removed old plugin directory



# Vavok v1.3

- Updates and improvements to Page class
- Updated installer
- Updated visitor counter
- Added class for user management - Users.class
- Added class for website configuration management Config.class
- Added database table prefixes for some crossdomain functionalities
- Deprecated functions for users management. Those functions are inside class now
