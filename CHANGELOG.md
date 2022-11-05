# Vavok v4.3 - 5.11.2022.

    feat: Add option to create newsletter categories (options)
    refactor: Delete unused files
    refactor: Remove unnecessary USE for traits
    refactor: Add exceptions while writing to the file
    refactor: Split code into traits, make code to follow more SOLID
    refactor: Add backlink when confirmation code is resent
    refactor: Send only int as timestamp to correctDate() method, remove unused code
    refactor: Remove unused code
    refactor: Remove duplicated code
    refactor: Disable displaying errors in production
    fix: Show ;) emoji properly
    fix: Property declaration has been fixed
    fix: Registration confirmation code fixed



# Vavok v4.2
    Feature:
        - Add option to create newsletter categories (options)
    Refactor:
        - Split code into traits, make code to follow more SOLID
        - Add backlink when confirmation code is resent
        - Remove unused files
        - Send only int as timestamp to correctDate() method, remove unused code
        - Remove unused code
        - Remove duplicated code
        - Disable displaying errors in the production
    Fixed:
        - Property declaration has been fixed
        - Registration confirmation code fixed



# Vavok v4.1
    Feature:
        - Use HTML templates for emails
    Refactor:
        - Check if attribute is Container while passing a container as attribute
        - Use Validations trait instead of using Validations class
        - Change storage directory location
        - Use Core trait instead of using Core class
        - Use localization object from dependency injection container
        - Update localization class, new method loadAdditional() to add additional localization data



# Vavok v4.0
- Use Pimple as dependency container
- Some models moved to classes directory
- New classes added, trying to make code follow SOLID principles
- Deprecated getData() method in Database class
- Code comments updated



# Vavok v3.1
- Composer setup moved to app root directory
- New method selectData() in Database class
- Removed ?> from the end of the scripts
- All tabs replaced with spaces
- Use InnoDB instead of MyISAM
- Deprecated method getData() in Database class



# Vavok v3.0
- Use namespaces
- Use composer autoload
- Updated dependencies
- Use static method to make a connection with a database



# Vavok v2.3
- Renamed method count_row() to camelCase countRow()
- Fixed: After file upload show the file location
- Fixed bot detection
- Fixed how blog categories are shown (grouped) in administration panel



# Vavok v2.2
- Added email queue option
- Added sitemap generator
- New option to manage email subscriptions
- New method updatePageLocalization() in model User.php
- User authentication improvements
- Updated app/composer.lock, install new versions by default
- Updated framework.scss in default theme
- Removed bb code parsing from blog pages
- Applebot now can be detected in user list
- Method detect_bot() renamed to camelCase detectBot() in Core.php class
- Renamed method to camel case emailSubscriptionOptions()
- Renamed method load_page() to camelCase loadPage()
- Code comments updated
- Fixed page view counter
- Removed method secureConection()
- Show blog categories and blog posts showing data with current site localization
- Added possibility to manage blog categories with multiple localizations
- Renamed method media_page_url() to camelCase cleanPageUrl()
- Updated localization
- Updated site search and cleaned code



# Vavok v2.1

- Fixed showing {@code}} in page editor
- Removed unused files
- Removed deprecated strftime() function