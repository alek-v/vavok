# Vavok v1.5.3
- Updated: User permissions
- Updated: Administration panel access levels update
- Update: Upload directory can be changed
- Update: Language handling improvements
- Added: New function clear_directory()



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
