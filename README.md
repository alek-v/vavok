# Vavok CMS

## Overview

With Vavok CMS you can easily manage the site, create content, upload files, manage user profiles, and much more.

Documentation can be found here: https://docs.vavok.net



## Features

1. Users
2. Administration panel
3. Pages
4. Blog posts
5. Site search

### Users

1. Registration and login
2. Profile, manage profile data such as first name, last name, city...
3. Settings, how to use site features
4. Option to delete a profile
5. Subscribe to the newsletter
6. Contact list
7. Blocklist
8. Inbox

### Administration panel

1. Administration chat
2. List of the administrators
3. List of the unconfirmed registrations
4. User's list
5. File upload
6. List of uploaded files
7. Search uploaded files
8. Option to ban user
9. List of the banned users
10. Statistics (visits and clicks today and total)
11. Show online users, registered users, and guests. This option can be shown for all users, per site settings.
12. Manage profiles of the users, edit data, delete profile option
13. Page manager (builder), create, edit, and delete pages and blog posts
14. Manage site settings
    Default language
    Open or close users registration
    Turn off or on the registration confirmation
    Manage reCaptcha site key
15. List of newsletter subscribers
16. Add email to the queue
17. Manage subscription options
18. Ban IP address
19. Error log (401, 402, 403, 404, 406, 500, 502)
20. Generate a sitemap



## How to install

1. Go to the root directory and install composer dependencies:
```bash
composer install
```
2. Enter database data in the .env file
3. Go to your_site.com/install to complete the installation

Requirements:

> PHP 8.0 or newer
> MySQL or MariaDB



## Dev Dependencies

Pimple
PHPMailer