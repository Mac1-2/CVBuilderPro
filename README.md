# CV Builder Pro - Installation & Setup Guide

Professional CV Builder with Wizard/WYSIWYG editors, 15 templates, PDF/Word import/export, drag-and-drop template creator, and a 1000-phrase library.

## Table of Contents

- [System Requirements](#system-requirements)
- [Server Installation (Debian/Ubuntu)](#server-installation-debianubuntu)
  - [1. System Update](#1-system-update)
  - [2. Install Apache2](#2-install-apache2)
  - [3. Install PHP 8.4](#3-install-php-84)
  - [4. Install MariaDB](#4-install-mariadb)
  - [5. Install External Tools](#5-install-external-tools)
  - [6. Install Composer](#6-install-composer)
- [Application Setup](#application-setup)
  - [1. Clone/Deploy the Application](#1-clonedeploy-the-application)
  - [2. Install PHP Dependencies](#2-install-php-dependencies)
  - [3. Configure the Database](#3-configure-the-database)
  - [4. Run Database Migrations](#4-run-database-migrations)
  - [5. Seed the Database](#5-seed-the-database)
  - [6. Set Directory Permissions](#6-set-directory-permissions)
  - [7. Configure Apache](#7-configure-apache)
  - [8. Configure PHP](#8-configure-php)
  - [9. Configure Application Settings](#9-configure-application-settings)
  - [10. Restart Services](#10-restart-services)
- [Verification](#verification)
- [Application Features](#application-features)
- [Directory Structure](#directory-structure)
- [Database Schema](#database-schema)
- [Routing](#routing)
- [Application Logic](#application-logic)
  - [Request Lifecycle](#request-lifecycle)
  - [Authentication Flow](#authentication-flow)
  - [Dashboard Flow](#dashboard-flow)
  - [CV Creation & Editing](#cv-creation--editing)
  - [Section Save/Load (AJAX)](#section-saveload-ajax)
  - [Template System](#template-system)
  - [Template Creator (Drag-and-Drop)](#template-creator-drag-and-drop)
  - [Import Flow](#import-flow)
  - [Export Flow](#export-flow)
  - [Phrase Library](#phrase-library)
  - [CV Management AJAX](#cv-management-ajax)
  - [Data Models](#data-models)
  - [Helper Functions](#helper-functions)
  - [Security Model](#security-model)
  - [Database Relationships](#database-relationships)
- [Troubleshooting](#troubleshooting)

---

## System Requirements

| Component | Minimum | Recommended |
|-----------|---------|-------------|
| OS | Debian 12 / Ubuntu 22.04 | Debian 12 / Ubuntu 24.04 |
| RAM | 1 GB | 2 GB |
| Disk | 5 GB | 10 GB |
| PHP | 8.2 | 8.4 |
| Database | MariaDB 10.6 | MariaDB 11.x |
| Web Server | Apache 2.4 | Apache 2.4 |

### Required PHP Extensions

- `pdo_mysql` - Database connectivity
- `mbstring` - Multi-byte string handling
- `xml` - XML parsing (required by PHPWord)
- `zip` - ZIP archive support (required by PHPWord/Dompdf)
- `gd` or `imagick` - Image processing
- `intl` - Internationalization
- `curl` - HTTP requests
- `dom` - DOM manipulation (required by Dompdf)
- `fileinfo` - File type detection
- `json` - JSON handling
- `openssl` - Encryption

### Required System Packages

- `poppler-utils` - PDF text extraction (`pdftotext`)
- `enscript` + `ghostscript` - Optional: text-to-PDF conversion for testing

---

## Server Installation (Debian/Ubuntu)

### 1. System Update

```bash
sudo apt update && sudo apt upgrade -y
```

### 2. Install Apache2

```bash
sudo apt install -y apache2

# Enable required modules
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod deflate

# Enable Apache to start on boot
sudo systemctl enable apache2
sudo systemctl start apache2
```

**Verify Apache is running:**

```bash
sudo systemctl status apache2
curl -I http://localhost
```

### 3. Install PHP 8.4

**On Debian 12 / Ubuntu 24.04 (PHP 8.4 in default repos):**

```bash
sudo apt install -y php8.4 libapache2-mod-php8.4 \
    php8.4-mysql \
    php8.4-mbstring \
    php8.4-xml \
    php8.4-zip \
    php8.4-gd \
    php8.4-intl \
    php8.4-curl \
    php8.4-dom \
    php8.4-fileinfo \
    php8.4-json \
    php8.4-openssl \
    php8.4-opcache \
    php8.4-cli
```

**On Ubuntu 22.04 (requires Ondrej PPA):**

```bash
sudo apt install -y software-properties-common
sudo add-apt-repository -y ppa:ondrej/php
sudo apt update

sudo apt install -y php8.4 libapache2-mod-php8.4 \
    php8.4-mysql php8.4-mbstring php8.4-xml php8.4-zip \
    php8.4-gd php8.4-intl php8.4-curl php8.4-dom \
    php8.4-fileinfo php8.4-json php8.4-opcache php8.4-cli
```

**Verify PHP:**

```bash
php -v
php -m | grep -E "pdo_mysql|mbstring|xml|zip|gd|intl|curl|dom"
```

### 4. Install MariaDB

```bash
sudo apt install -y mariadb-server mariadb-client

# Enable MariaDB to start on boot
sudo systemctl enable mariadb
sudo systemctl start mariadb

# Secure the installation
sudo mysql_secure_installation
```

During `mysql_secure_installation`:
- Set root password: **Yes** (enter a strong password)
- Remove anonymous users: **Yes**
- Disallow root login remotely: **Yes**
- Remove test database: **Yes**
- Reload privilege tables: **Yes**

**Verify MariaDB:**

```bash
sudo systemctl status mariadb
mysql --version
```

### 5. Install External Tools

```bash
# PDF text extraction (required for CV import from PDF)
sudo apt install -y poppler-utils

# Optional: text-to-PDF conversion (for testing)
sudo apt install -y enscript ghostscript
```

**Verify:**

```bash
pdftotext -v
```

### 6. Install Composer

```bash
# Download and install Composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === file_get_contents('https://composer.github.io/installer.sig')) { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
php -r "unlink('composer-setup.php');"

# Verify
composer --version
```

---

## Application Setup

### 1. Clone/Deploy the Application

```bash
# If using Git:
cd /var/www
sudo git clone <repository-url> html

# Or copy files manually:
sudo mkdir -p /var/www/html
# Copy application files to /var/www/html
```

### 2. Install PHP Dependencies

```bash
cd /var/www/html
sudo composer install --no-dev --optimize-autoloader
```

This installs:
- `dompdf/dompdf` - PDF export
- `phpoffice/phpword` - DOCX import/export

### 3. Configure the Database

```bash
# Log into MariaDB as root
sudo mysql -u root -p
```

```sql
-- Create the database
CREATE DATABASE IF NOT EXISTS cv_builder
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

-- Create the application user
CREATE USER IF NOT EXISTS 'cv_builder'@'127.0.0.1'
    IDENTIFIED BY 'cv_builder_pass_2024';

-- Grant privileges
GRANT ALL PRIVILEGES ON cv_builder.* TO 'cv_builder'@'127.0.0.1';
FLUSH PRIVILEGES;
EXIT;
```

> **Security Note:** Change the default password `cv_builder_pass_2024` to a strong, unique password in production.

### 4. Run Database Migrations

```bash
# Main schema
sudo mysql -u cv_builder -p cv_builder < /var/www/html/database/schema.sql

# Template creator migration
sudo mysql -u cv_builder -p cv_builder < /var/www/html/database/migration_template_creator.sql
```

### 5. Seed the Database

```bash
# Seed templates (15 CV templates)
sudo mysql -u cv_builder -p cv_builder < /var/www/html/database/seed_templates.sql

# Seed phrases (1000 industry-specific phrases)
sudo mysql -u cv_builder -p cv_builder < /var/www/html/database/seed_phrases.sql
```

**Verify the seed data:**

```bash
mysql -u cv_builder -p cv_builder -e "
    SELECT COUNT(*) AS templates FROM cv_templates;
    SELECT COUNT(*) AS phrases FROM phrase_library;
    SELECT COUNT(*) AS industries FROM industries;
"
```

Expected output:
- Templates: 15
- Phrases: 1000
- Industries: 20

### 6. Set Directory Permissions

```bash
# Set ownership to www-data (Apache user)
sudo chown -R www-data:www-data /var/www/html

# Set directory permissions
sudo find /var/www/html -type d -exec chmod 755 {} \;
sudo find /var/www/html -type f -exec chmod 644 {} \;

# Ensure upload directories exist and are writable
sudo mkdir -p /var/www/html/uploads/{pdf,word,imports}
sudo chown -R www-data:www-data /var/www/html/uploads
sudo chmod -R 755 /var/www/html/uploads
```

### 7. Configure Apache

**Create a virtual host configuration:**

```bash
sudo nano /etc/apache2/sites-available/cvbuilder.conf
```

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    ServerAdmin webmaster@your-domain.com
    DocumentRoot /var/www/html

    <Directory /var/www/html>
        AllowOverride All
        Require all granted
        Options -Indexes +FollowSymLinks
    </Directory>

    # Security headers
    <IfModule mod_headers.c>
        Header always set X-Content-Type-Options "nosniff"
        Header always set X-Frame-Options "SAMEORIGIN"
        Header always set X-XSS-Protection "1; mode=block"
    </IfModule>

    # Logging
    ErrorLog ${APACHE_LOG_DIR}/cvbuilder-error.log
    CustomLog ${APACHE_LOG_DIR}/cvbuilder-access.log combined
</VirtualHost>
```

**Enable the site and disable the default:**

```bash
sudo a2ensite cvbuilder.conf
sudo a2dissite 000-default.conf

# Set ServerName to suppress warning
echo "ServerName localhost" | sudo tee /etc/apache2/conf-available/servername.conf
sudo a2enconf servername

# Test configuration
sudo apache2ctl configtest

# Reload Apache
sudo systemctl reload apache2
```

**The `.htaccess` file** (included in the project) handles URL rewriting:

```apache
RewriteEngine On
RewriteBase /

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?page=$1 [QSA,L]
```

### 8. Configure PHP

```bash
sudo nano /etc/php/8.4/apache2/php.ini
```

Recommended settings:

```ini
; Resource limits
memory_limit = 256M
max_execution_time = 120
max_input_time = 120
post_max_size = 20M
upload_max_filesize = 10M

; Security
expose_php = Off
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log

; Session
session.cookie_httponly = 1
session.use_strict_mode = 1
session.gc_maxlifetime = 86400

; PCRE (fix JIT warning on some systems)
pcre.jit = 0
```

**Restart Apache after changes:**

```bash
sudo systemctl restart apache2
```

### 9. Configure Application Settings

**Edit the database configuration:**

```bash
sudo nano /var/www/html/config/database.php
```

Update the credentials to match your database setup:

```php
<?php
return [
    'host' => '127.0.0.1',
    'port' => 3306,
    'database' => 'cv_builder',
    'username' => 'cv_builder',
    'password' => 'YOUR_SECURE_PASSWORD',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
```

**Edit the application constants:**

```bash
sudo nano /var/www/html/config/constants.php
```

Update `BASE_URL` to match your domain or IP:

```php
<?php
define('APP_NAME', 'CV Builder Pro');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://your-domain.com');  // <-- Change this
define('BASE_PATH', '/var/www/html');
define('UPLOAD_PATH', BASE_PATH . '/uploads');
define('TEMPLATE_PATH', BASE_PATH . '/templates');
define('ASSETS_URL', BASE_URL . '/assets');

define('SESSION_LIFETIME', 3600 * 24);
define('CSRF_TOKEN_NAME', 'csrf_token');

define('PDF_EXPORT_PATH', UPLOAD_PATH . '/pdf');
define('WORD_EXPORT_PATH', UPLOAD_PATH . '/word');
define('IMPORT_TEMP_PATH', UPLOAD_PATH . '/imports');
```

### 10. Restart Services

```bash
sudo systemctl restart apache2
sudo systemctl restart mariadb
```

---

## Verification

**1. Check Apache:**

```bash
sudo systemctl status apache2
curl -I http://localhost
```

Expected: `HTTP/1.1 200 OK`

**2. Check PHP:**

```bash
php -v
```

Expected: `PHP 8.4.x`

**3. Check Database:**

```bash
mysql -u cv_builder -p -e "USE cv_builder; SHOW TABLES;"
```

Expected: 10 tables (`users`, `cv_templates`, `cvs`, `cv_sections`, `industries`, `phrase_library`, `user_saved_phrases`, `template_customizations`, `import_history`, `template_blocks`, `template_graphics`)

**4. Check Application:**

Open a browser and navigate to `http://your-server-ip/`

You should see the login page.

**5. Create a Demo User:**

```bash
mysql -u cv_builder -p cv_builder -e "
INSERT INTO users (email, password_hash, first_name, last_name)
VALUES (
    'demo@cvbuilder.com',
    '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Demo',
    'User'
);
"
```

Password: `demo123456` (the hash above is for `demo123456`)

---

## Application Features

### CV Editors
- **Wizard Editor** - 8-step guided CV creation (personal info, summary, experience, education, skills, languages, certifications, references)
- **WYSIWYG Editor** - Live preview with toolbar for real-time editing

### Templates
- **15 Pre-built Templates** - Professional, Creative, Minimal, Academic, Executive categories
- **Template Preview** - Visual thumbnails before selection
- **"Shanks" Custom Template** - Included from external source

### Template Creator
- **Drag-and-Drop Interface** - SortableJS-powered block arrangement
- **13 Block Types** - header, sidebar, section, text, image, divider, spacer, columns, grid, personal-field, contact-line, skill-bar, entry-loop
- **Customization Panel** - Colors, fonts, spacing, punctuation controls
- **Graphics Upload** - SVG, PNG, JPG, WebP support
- **Undo/Redo** - Full history management
- **Save & Regenerate** - Pipeline updates template HTML/CSS

### Import Feature
- **Supported Formats** - PDF, DOCX, TXT, RTF
- **Auto-Detection** - Parses sections, personal info, experience, education, skills, languages, certifications, references
- **Review Page** - Edit parsed data before creating CV
- **One-Click Creation** - Import directly into a new CV

### Export Feature
- **PDF Export** - Powered by Dompdf
- **Word Export** - Powered by PHPWord

### Phrase Library
- **1000 Phrases** - Industry-specific professional phrases
- **20 Industries** - Technology, Healthcare, Finance, Education, etc.
- **6 Categories** - Summary, Experience, Skills, Achievement, Objective, Interests
- **Search & Filter** - Full-text search with tags

### Security
- **CSRF Protection** - Token-based form protection
- **Password Hashing** - bcrypt via `password_hash()`
- **Prepared Statements** - All SQL queries use PDO prepared statements
- **XSS Prevention** - Output escaping via `htmlspecialchars()`
- **Session Security** - HTTP-only cookies, strict mode

---

## Directory Structure

```
/var/www/html/
├── .htaccess
├── README.md
├── composer.json
├── composer.lock
├── index.html
├── index.php
├── test_parser.php
│
├── assets/
│   ├── css/
│   │   ├── editor.css
│   │   ├── import.css
│   │   ├── main.css
│   │   └── template-creator.css
│   └── js/
│       ├── app.js
│       ├── editor.js
│       └── template-creator.js
│
├── config/
│   ├── constants.php                  # Application constants & paths
│   └── database.php                   # Database credentials
│
├── controllers/
│   ├── AjaxController.php             # AJAX endpoints (creator, import)
│   ├── AuthController.php             # Login, registration, logout
│   ├── CvController.php               # CV CRUD operations
│   ├── DashboardController.php        # User dashboard
│   ├── ExportController.php           # PDF/Word export
│   ├── ImportController.php           # CV file import & parsing
│   ├── PhraseController.php           # Phrase library management
│   ├── TemplateController.php         # Template browsing/selection
│   └── TemplateCreatorController.php  # Template creator UI
│
├── database/
│   ├── migration_template_creator.sql # Template creator tables
│   ├── schema.sql                     # Main database schema
│   ├── seed_phrases.sql               # 1000 phrase seed data
│   └── seed_templates.sql             # 15 template seed data
│
├── includes/
│   ├── BlockRenderer.php              # Template block HTML/CSS generator
│   └── bootstrap.php                  # Core functions, DB class, helpers
│
├── models/
│   ├── Cv.php                         # CV data model
│   ├── Phrase.php                     # Phrase library model
│   ├── Template.php                   # Template model
│   ├── TemplateBlock.php              # Template block model
│   └── User.php                       # User authentication model
│
├── tests/
│   ├── test_cv.docx
│   ├── test_cv.pdf
│   └── test_cv.txt
│
├── uploads/
│   ├── graphics/
│   │   ├── icons/
│   │   └── uploads/
│   ├── imports/                       # Uploaded CV files for import
│   ├── pdf/                           # Generated PDF exports
│   └── word/                          # Generated Word exports
│
├── vendor/                            # Composer dependencies
│
└── views/
    ├── auth/
    │   ├── login.php
    │   └── register.php
    ├── cv/
    │   ├── create.php
    │   ├── preview.php
    │   ├── wizard/
    │   │   └── index.php
    │   └── wysiwyg/
    │       └── index.php
    ├── import/
    │   ├── index.php
    │   └── review.php
    ├── layouts/
    │   ├── footer.php
    │   ├── header.php
    │   └── nav.php
    ├── phrases/
    │   └── library.php
    ├── template-creator/
    │   ├── index.php
    │   ├── new.php
    │   └── preview.php
    └── templates/
        ├── gallery.php
        └── preview.php
```

---

## Database Schema

### Tables Overview

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `users` | User accounts | id, email, password_hash, first_name, last_name |
| `cv_templates` | CV templates | id, name, slug, html_structure, css_styles, category |
| `cvs` | User CVs | id, user_id, template_id, title, custom_css, custom_colors |
| `cv_sections` | CV content sections | id, cv_id, section_type, content_json, section_order |
| `industries` | Industry categories | id, name, slug, icon |
| `phrase_library` | Professional phrases | id, industry_id, category, phrase_text, tags |
| `user_saved_phrases` | User's saved phrases | id, user_id, phrase_id |
| `template_customizations` | User template prefs | id, user_id, template_id, colors, fonts, spacing |
| `import_history` | Import tracking | id, user_id, cv_id, file_name, file_type, status |
| `template_blocks` | Creator block definitions | id, template_id, block_type, config_json, block_order |
| `template_graphics` | Uploaded graphics | id, template_id, file_name, file_path, file_type |

### Section Types

The `cv_sections.section_type` ENUM supports:
`personal`, `summary`, `experience`, `education`, `skills`, `languages`, `certifications`, `references`, `projects`, `volunteer`, `awards`, `custom`

### Template Categories

The `cv_templates.category` ENUM supports:
`professional`, `creative`, `minimal`, `academic`, `executive`

---

## Routing

The application uses a front-controller pattern via `index.php`. URLs are rewritten by `.htaccess` to pass through `index.php?page=...`.

| URL Path | Controller | Description |
|----------|-----------|-------------|
| `/` | (login page) | Redirects to login or dashboard |
| `/auth/login` | AuthController | Login page |
| `/auth/register` | AuthController | Registration page |
| `/auth/logout` | AuthController | Logout handler |
| `/dashboard` | DashboardController | User dashboard with CV list |
| `/cv/create` | CvController | Create new CV |
| `/cv/edit/{id}` | CvController | Edit existing CV |
| `/cv/wizard/{id}` | CvController | Wizard editor |
| `/cv/wysiwyg/{id}` | CvController | WYSIWYG editor |
| `/cv/delete/{id}` | CvController | Delete CV |
| `/templates` | TemplateController | Browse templates |
| `/templates/preview/{id}` | TemplateController | Preview template |
| `/creator` | TemplateCreatorController | Template creator UI |
| `/phrases` | PhraseController | Phrase library |
| `/export/pdf/{id}` | ExportController | Export CV as PDF |
| `/export/word/{id}` | ExportController | Export CV as Word |
| `/import` | ImportController | Upload CV for import |
| `/import/review/{id}` | ImportController | Review imported data |
| `/ajax/*` | AjaxController | AJAX endpoints |

---

## Application Logic

### Architecture Overview

CV Builder Pro is a PHP 8.4 MVC-style application using a **front-controller pattern** with no framework. All requests funnel through `index.php`, which routes to controllers based on the `page` query parameter (rewritten via `.htaccess`). Data persists in MariaDB via PDO with prepared statements. Session-based authentication gates all routes except login/register.

### Request Lifecycle

```
Browser Request
    │
    ▼
.htaccess (mod_rewrite)
    │  Rewrites /dashboard → index.php?page=dashboard
    │  Rewrites /cv/edit/5 → index.php?page=cv/edit/5
    │  Skips existing files/directories (assets, images)
    ▼
index.php (Front Controller)
    │  1. Loads bootstrap.php (session, DB, helpers)
    │  2. Loads all models (User, Cv, Template, Phrase, TemplateBlock)
    │  3. Parses $_GET['page'] into route segments
    │  4. switch() on controller name
    │  5. Calls require_auth() for protected routes
    │  6. Requires the matching controller file
    ▼
Controller
    │  1. Reads $route array for action/id
    │  2. Validates CSRF on POST requests
    │  3. Calls model methods for data operations
    │  4. Loads view(s) with data variables
    ▼
View (PHP template)
    │  Renders HTML using header.php / footer.php layout
    │  Outputs to browser
```

### Authentication Flow

**Registration (`/auth/register`):**
1. User submits form with email, password, password_confirm, first_name, last_name
2. `verify_csrf()` validates CSRF token
3. Validation: email format, password ≥ 8 chars, passwords match
4. `User::register()` checks for duplicate email, creates bcrypt hash, inserts into `users`
5. Session variables set: `user_id`, `user_email`, `user_name`
6. Redirect to `/dashboard`

**Login (`/auth/login`):**
1. User submits email + password
2. `User::login()` queries by email, verifies password with `password_verify()`
3. Updates `last_login` timestamp
4. Session variables set
5. Redirect to `/dashboard`

**Logout (`/auth/logout`):**
1. `User::logout()` calls `session_destroy()`
2. Redirect to `/auth/login`

**Auth Gate (`require_auth()`):**
- Called by every protected route in `index.php`
- Checks `$_SESSION['user_id']`
- If empty and AJAX → returns 401 JSON
- If empty and browser → redirects to `/auth/login`

### Dashboard Flow (`/dashboard`)

1. `DashboardController` loads
2. Fetches user's CVs via `Cv::getByUser(userId)` ordered by `updated_at DESC`
3. Fetches all active templates via `Template::getAll()`
4. Fetches 5 featured phrases via `Phrase::getFeatured(null, 5)`
5. Renders `views/layouts/header.php` → dashboard view → `views/layouts/footer.php`

**Dashboard displays:**
- Stats cards: total CVs, templates count, phrase count, export formats
- If no CVs: empty state with "Create from Scratch" and "Import Existing CV" buttons
- If CVs exist: card list with Edit, Preview, Duplicate, Delete actions
- Quick action cards: Import, Browse Templates, Phrase Library, Start from Scratch

**Client-side actions (via AJAX):**
- `duplicateCv(id)` → POST `/ajax/duplicate-cv` → reloads page
- `deleteCv(id)` → POST `/ajax/delete-cv` → reloads page

### CV Creation & Editing

**Create (`/cv/create`):**
1. GET: Shows template selection form with all available templates
2. POST: `Cv::create(userId, title, templateId)`
   - Generates unique slug via `generate_slug(title)` (lowercase + hyphens + 6-char MD5 suffix)
   - Inserts row into `cvs` table
   - Calls `initSections(cvId)` — creates 8 default section rows in `cv_sections`:
     1. personal (order 1)
     2. summary (order 2)
     3. experience (order 3)
     4. education (order 4)
     5. skills (order 5)
     6. languages (order 6)
     7. certifications (order 7)
     8. references (order 8)
   - Returns new `cvId`
3. Redirect to `/cv/edit/{cvId}`

**Edit (`/cv/edit/{id}`):**
1. `Cv::getFullData(cvId, userId)` loads CV + template HTML/CSS + all 8 sections
2. Mode parameter `?mode=wysiwyg` or `?mode=wizard` (default: wizard)
3. Loads appropriate view with `editor.css` and `editor.js`

**Wizard Editor (`/cv/wizard/index.php`):**
- 8-step form, one section per step
- Each step loads existing section data via `Cv::getSection(cvId, sectionType)`
- Step navigation: Previous / Next / Save
- **Personal**: first_name, last_name, job_title, email, phone, address, linkedin, website
- **Summary**: single textarea
- **Experience**: repeatable entries (title, company, dates, location, description) with add/remove
- **Education**: repeatable entries (degree, school, year, description) with add/remove
- **Skills**: repeatable entries (name) with add/remove
- **Languages**: repeatable entries (language, level) with add/remove
- **Certifications**: repeatable entries (name, issuer, date) with add/remove
- **References**: repeatable entries (name, title, contact) with add/remove
- Each "Save" triggers AJAX `save-section` → `Cv::saveSection()`

**WYSIWYG Editor (`/cv/wysiwyg/index.php`):**
- Live preview panel alongside editing controls
- Toolbar for formatting
- Real-time section editing with preview updates
- Template switching without losing data

**Preview (`/cv/preview/{id}`):**
1. Loads full CV data via `Cv::getFullData()`
2. Renders using template's `html_structure` with placeholder substitution
3. Applies template's `css_styles`

### Section Save/Load (AJAX)

**`save-section`:**
1. Validates CV ownership via `Cv::getById(cvId, userId)`
2. `Cv::saveSection(cvId, sectionType, content, title)`:
   - Finds existing section row
   - Updates `content_json` (JSON-encoded), `html_content` (null), `updated_at`
   - Optionally updates `title`

**`load-section`:**
1. Validates CV ownership
2. `Cv::getSection(cvId, sectionType)` returns single section row

**`load-cv`:**
1. `Cv::getFullData(cvId, userId)` returns CV + template + all sections as JSON

**`apply-template`:**
1. Validates CV and template exist
2. `Cv::update(cvId, userId, ['template_id' => newTemplateId])`
3. Returns new template data

### Template System

**Template Gallery (`/templates`):**
1. `Template::getAll()` fetches all active templates ordered by `sort_order`
2. `Template::getCategories()` returns distinct categories
3. Optional `?category=professional` filter
4. Renders gallery with thumbnails

**Template Preview (`/templates/preview/{slug}`):**
1. `Template::getBySlug(slug)` fetches template
2. Renders preview view with sample data

**Template Preview via AJAX (`/ajax/preview-template`):**
1. Fetches template by slug
2. Replaces all `{{placeholders}}` in `html_structure` with sample data:
   - `{{first_name}}`, `{{last_name}}`, `{{first_initial}}`, `{{last_initial}}`
   - `{{job_title}}`, `{{email}}`, `{{phone}}`, `{{address}}`, `{{linkedin}}`, `{{website}}`, `{{photo}}`
   - `{{contact_entries}}`, `{{summary}}`
   - `{{experience_entries}}`, `{{education_entries}}`, `{{skills_entries}}`
   - `{{language_entries}}`, `{{certification_entries}}`
   - Unused placeholders replaced with empty string
3. Returns HTML + CSS for client-side preview

### Template Creator (Drag-and-Drop)

**Create Custom Template (`/creator/new`):**
1. GET: Shows form with name input and optional base template selector
2. POST: `TemplateBlock::createCustomTemplate(userId, name, baseTemplateId)`
   - Generates slug
   - If base template provided, copies its `html_structure`, `css_styles`, `global_styles`
   - Inserts into `cv_templates` with `is_custom=1`, `user_id`, `base_template_id`
   - Returns new template ID
3. Redirect to `/creator/edit/{newId}`

**Clone Template (`/creator/clone/{id}`):**
1. `TemplateBlock::cloneTemplateWithBlocks(templateId, userId, newName)`
   - Creates new template record
   - Copies all `template_blocks` from source template
2. Redirect to editor

**Edit Template (`/creator/edit/{id}`):**
1. Loads template, blocks (`TemplateBlock::getByTemplate`), graphics, global styles
2. Renders 3-column layout:
   - **Left panel**: Block palette (13 types: header, sidebar, section, text, image, divider, spacer, columns, grid, personal-field, contact-line, skill-bar, entry-loop)
   - **Center**: A4 canvas preview (live HTML rendering)
   - **Right panel**: Properties editor (colors, fonts, spacing, punctuation, graphics)
3. SortableJS enables drag-and-drop reordering

**AJAX Operations for Template Creator:**

| Action | Logic |
|--------|-------|
| `create-template` | `TemplateBlock::createCustomTemplate()` |
| `load-template-blocks` | Returns blocks + global styles for template |
| `save-block` | Creates new block or updates existing (config_json, css_overrides) |
| `delete-block` | Removes block from `template_blocks` |
| `reorder-blocks` | Updates `block_order` for each block ID in sequence |
| `duplicate-block` | Copies block with `block_order + 1` |
| `regenerate-template` | `BlockRenderer::renderFromBlocks()` → updates `cv_templates.html_structure`, `css_styles`, `global_styles` |
| `upload-graphic` | Validates file type (svg/png/jpg/webp), saves to `/uploads/graphics/uploads/`, records in `template_graphics` |
| `list-graphics` | Returns graphics for template or user |
| `delete-graphic` | Deletes file from disk + DB record |
| `save-global-styles` | Updates `cv_templates.global_styles` JSON column |
| `clone-template` | Clones template + all blocks |

**BlockRenderer:**
- Takes array of blocks + global styles
- Generates `html_structure` and `css_styles` strings
- Each block type has specific rendering logic (config → HTML/CSS)
- Result written back to `cv_templates` table

### Import Flow

**Upload (`/import` → POST `/import/upload`):**
1. Validates file: exists, allowed extension (pdf/docx/doc/txt/rtf), size ≤ 10MB
2. Saves to `/uploads/imports/` with unique name
3. Extracts text based on format:
   - **DOCX**: PHPWord `IOFactory::load()` → iterates sections/elements → extracts text → deduplicates consecutive lines
   - **PDF**: `pdftotext -layout` → normalizes mangled bullet/Unicode characters
   - **TXT**: `file_get_contents()`
   - **RTF**: `strip_tags(file_get_contents())`
4. Deletes temp file
5. Calls `parseTextToCvData(text)` → structured data array
6. Stores parsed data in session: `$_SESSION['import_data_{randomHex32}']`
7. Returns JSON with `import_id` for review redirect

**Parser Logic (`parseTextToCvData`):**

*Phase 1 — Header Extraction (first 8 lines):*
- Scans for email, phone, LinkedIn URL, general URL patterns
- First non-contact line → name (split into first_name + last_name)
- Second meaningful line → job_title
- Stops if a section header is detected

*Phase 2 — Section Parsing (remaining lines):*
- `detectSection()` matches line against known section header patterns
- Routes each line to the appropriate parser function

| Section | Parser | Logic |
|---------|--------|-------|
| summary | inline | Collects all lines until next section header; strips header keywords |
| experience | `parseExperienceLine()` | Detects title-like lines, company lines (with Inc/LLC/etc or comma/pipe), date lines, bullet points. Uses `pendingTitle` state machine to handle title→company→date→description sequence. Auto-starts new entry when title-like line appears after complete entry |
| education | `parseEducationLine()` | Detects degree keywords (bachelor, master, PhD, etc.) and date lines. Fills degree → school → year → description in sequence |
| skills | `parseSkillsLine()` | Splits by comma/semicolon/pipe; trims bullets; filters out proficiency-only words |
| languages | `parseLanguageLine()` | Regex match: `Language - Level` pattern; fallback to raw line if short |
| certifications | `parseCertificationLine()` | Name line → optional issuer line (matching org names) → date line triggers save |
| references | `parseReferenceLine()` | Name line → title line → contact line (email/phone) |

**Review (`/import/review/{importId}`):**
1. Retrieves parsed data from session
2. If expired/missing → redirect to `/import`
3. Renders editable review form with all parsed sections
4. User can modify any field before final creation

**Create CV from Import (`POST /import/create-cv`):**
1. Reads `import_id`, `overrides` (edited data), `title`, `template_id` from POST body
2. Merges: `array_replace_recursive(parsedData, overrides)`
3. `Cv::create(userId, title, templateId)` → creates CV with empty sections
4. Iterates merged data, calls `Cv::saveSection()` for each section type
5. Clears session import data
6. Returns `cv_id` for redirect

### Export Flow

**PDF Export (`/export/pdf/{cvId}`):**
1. `Cv::getFullData(cvId, userId)` loads all data
2. `renderCvHtml(cv)` function:
   - Decodes each section's `content_json`
   - Substitutes all `{{placeholders}}` in template's `html_structure`
   - Renders experience, education, skills, languages, certifications as HTML blocks
   - Wraps in `<!DOCTYPE html>` with template CSS
3. Dompdf renders HTML → PDF
4. Streams as download with sanitized filename

**Word Export (`/export/word/{cvId}`):**
1. Loads full CV data
2. Iterates visible sections, builds PHPWord document:
   - **Personal**: name (22pt bold), job title (14pt colored), contact line
   - **Summary**: paragraph text
   - **Experience**: entries with title (bold), company (colored), date (italic gray), description
   - **Education**: entries with degree (bold), school (colored), year (gray)
   - **Skills**: comma-separated list
   - **Languages**: "Language - Level" per line
   - **Certifications**: "Name - Issuer (Date)" per line
3. Streams as `.docx` download

### Phrase Library (`/phrases`)

1. `Phrase::getAllIndustries()` loads 20 industries for filter dropdown
2. Query params: `q` (search), `industry` (filter), `category` (filter), `p` (page)
3. `Phrase::search(query, industryId, category, perPage=24, offset)`:
   - Filters by industry_id, category
   - LIKE search on `phrase_text` and `tags`
   - Orders by `is_featured DESC, usage_count DESC`
   - Paginates at 24 per page
4. `Phrase::count()` for pagination calculation
5. Renders phrase library browser with filters and pagination

**Phrase AJAX Operations:**

| Action | Logic |
|--------|-------|
| `get-phrases` | `Phrase::search()` with query params from AJAX |
| `favorite-phrase` | Toggles: checks `Phrase::isFavorite()`, then `saveFavorite()` or `removeFavorite()` |
| `increment-phrase-usage` | `Phrase::incrementUsage(phraseId)` — bumps `usage_count` by 1 |

### CV Management AJAX

| Action | Logic |
|--------|-------|
| `duplicate-cv` | `Cv::duplicate(cvId, userId)` — creates new CV, copies all section content |
| `delete-cv` | `Cv::delete(cvId, userId)` — CASCADE deletes all `cv_sections` |
| `save-cv-title` | `Cv::update(cvId, userId, ['title' => title])` |

### Data Models

**User Model:**

| Method | SQL | Purpose |
|--------|-----|---------|
| `register()` | INSERT users | Create account with bcrypt hash |
| `login()` | SELECT users | Authenticate + set session |
| `logout()` | session_destroy() | Clear session |
| `getById()` | SELECT users | Fetch user profile |
| `updateProfile()` | UPDATE users | Update first_name, last_name, avatar |

**Cv Model:**

| Method | SQL | Purpose |
|--------|-----|---------|
| `create()` | INSERT cvs + 8x INSERT cv_sections | New CV with default sections |
| `getById()` | SELECT cvs JOIN cv_templates | Single CV with template data |
| `getByUser()` | SELECT cvs JOIN cv_templates | All user CVs |
| `update()` | UPDATE cvs | Modify title, template_id, custom_css, custom_colors |
| `delete()` | DELETE cvs | Remove CV (CASCADE deletes sections) |
| `getSection()` | SELECT cv_sections | Single section by type |
| `getAllSections()` | SELECT cv_sections ORDER BY section_order | All sections for a CV |
| `saveSection()` | UPDATE cv_sections | Save content_json |
| `getFullData()` | getById + getAllSections | Complete CV data |
| `duplicate()` | INSERT cvs + UPDATE cv_sections | Clone CV with all content |
| `countByUser()` | COUNT cvs | Dashboard stat |

**Template Model:**

| Method | SQL | Purpose |
|--------|-----|---------|
| `getAll()` | SELECT cv_templates WHERE is_active=1 | All templates, optional category filter |
| `getBySlug()` | SELECT cv_templates WHERE slug=? | Lookup by URL slug |
| `getById()` | SELECT cv_templates WHERE id=? | Lookup by ID |
| `getCategories()` | SELECT DISTINCT category | Available categories |

**Phrase Model:**

| Method | SQL | Purpose |
|--------|-----|---------|
| `search()` | SELECT phrase_library JOIN industries | Filtered/paginated search |
| `count()` | COUNT phrase_library | Total for pagination |
| `getByIndustry()` | SELECT phrase_library WHERE industry_id=? | All phrases for industry |
| `getFeatured()` | SELECT phrase_library WHERE is_featured=1 | Featured phrases |
| `incrementUsage()` | UPDATE phrase_library | Bump usage_count |
| `saveFavorite()` | INSERT user_saved_phrases | Save user's favorite |
| `removeFavorite()` | DELETE user_saved_phrases | Remove favorite |
| `getFavorites()` | SELECT user_saved_phrases JOIN phrase_library | User's saved phrases |
| `isFavorite()` | SELECT user_saved_phrases | Check if favorited |
| `getAllIndustries()` | SELECT industries | All 20 industries |

**TemplateBlock Model:**

| Method | SQL | Purpose |
|--------|-----|---------|
| `getByTemplate()` | SELECT template_blocks ORDER BY block_order | All blocks for template |
| `create()` | INSERT template_blocks | New block |
| `update()` | UPDATE template_blocks | Modify config_json, css_overrides |
| `delete()` | DELETE template_blocks | Remove block |
| `reorder()` | UPDATE template_blocks (multiple) | Set new block_order sequence |
| `duplicate()` | INSERT template_blocks | Copy block |
| `createCustomTemplate()` | INSERT cv_templates | New custom template |
| `cloneTemplateWithBlocks()` | INSERT cv_templates + INSERT template_blocks | Full clone |
| `uploadGraphic()` | INSERT template_graphics | Save graphic file + record |
| `getGraphics()` | SELECT template_graphics | List graphics |
| `deleteGraphic()` | DELETE template_graphics + unlink file | Remove graphic |
| `getGlobalStyles()` | SELECT cv_templates.global_styles | Get styles JSON |
| `saveGlobalStyles()` | UPDATE cv_templates.global_styles | Save styles JSON |
| `regenerateTemplate()` | SELECT template_blocks + UPDATE cv_templates | Re-render HTML/CSS from blocks |

### Helper Functions (bootstrap.php)

| Function | Purpose |
|----------|---------|
| `Database::getInstance()` | Singleton PDO connection |
| `e(string)` | XSS-safe HTML escape via `htmlspecialchars()` |
| `csrf_token()` | Generate/return session CSRF token |
| `csrf_field()` | HTML hidden input with CSRF token |
| `verify_csrf()` | Validate POST/AJAX CSRF token via `hash_equals()` |
| `redirect(url)` | HTTP 302 redirect + exit |
| `json_response(data, status)` | Set HTTP status, Content-Type: application/json, echo JSON, exit |
| `require_auth()` | Check session user_id; redirect or 401 |
| `is_ajax_request()` | Check `X-Requested-With: XMLHttpRequest` header |
| `generate_slug(text)` | Lowercase + replace non-alphanumeric with hyphens + 6-char MD5 suffix |
| `format_date(date)` | Format as "M Y" (e.g., "Jan 2024") |
| `flash_message(type, message)` | Store one-time message in session |
| `get_flash_message()` | Retrieve and clear flash message |

### Security Model

| Layer | Implementation |
|-------|---------------|
| **Authentication** | Session-based, bcrypt password hashing |
| **Authorization** | `require_auth()` on all routes except login/register; CV ownership checks on every read/write |
| **CSRF** | Token stored in session, verified on all POST/AJAX via `hash_equals()` (timing-safe) |
| **XSS** | All output escaped via `e()` helper (`htmlspecialchars` with ENT_QUOTES, UTF-8) |
| **SQL Injection** | All queries use PDO prepared statements with bound parameters |
| **File Upload** | Extension whitelist, 10MB size limit, `move_uploaded_file()`, temp file cleanup |
| **Session** | HTTP-only cookies, strict mode, 24-hour lifetime |
| **File Access** | CV ownership verified before any read/write/delete operation |

### Database Relationships

```
users (1) ────────< cvs (N) >──────── cv_templates (1)
  │                    │
  │                    └───────< cv_sections (N)
  │
  ├───────< template_customizations (N) >─── cv_templates (1)
  │
  ├───────< user_saved_phrases (N) >─────── phrase_library (1)
  │                                              │
  │                                              >── industries (1)
  │
  ├───────< import_history (N) >─────────── cvs (1)
  │
  └───────< template_graphics (N) >──────── cv_templates (1)

cv_templates (1) ────────< template_blocks (N)
```

---

## Troubleshooting

### Apache shows 404 for all routes

```bash
# Ensure mod_rewrite is enabled
sudo a2enmod rewrite
sudo systemctl restart apache2

# Verify .htaccess exists and is readable
ls -la /var/www/html/.htaccess

# Check AllowOverride is set to All in Apache config
grep -r "AllowOverride" /etc/apache2/
```

### Database connection error

```bash
# Test database connection
mysql -u cv_builder -p -h 127.0.0.1 cv_builder

# Verify credentials match config
cat /var/www/html/config/database.php

# Check MariaDB is running
sudo systemctl status mariadb
```

### PDF export fails

```bash
# Check upload directory permissions
ls -la /var/www/html/uploads/pdf/

# Check PHP memory limit (Dompdf needs memory)
php -r "echo ini_get('memory_limit');"

# Check error logs
sudo tail -50 /var/log/apache2/cvbuilder-error.log
```

### Import fails with "No text content found"

```bash
# Verify pdftotext is installed
which pdftotext
pdftotext -v

# Test PDF extraction manually
pdftotext -layout /path/to/file.pdf -

# Check file size limit (10MB max)
# Check PHP upload limits
php -r "echo ini_get('upload_max_filesize');"
php -r "echo ini_get('post_max_size');"
```

### Skills not extracted during import

The parser splits skills by comma, semicolon, and pipe characters. Ensure skills in the source CV are separated by one of these delimiters.

### Session data expires too quickly

```bash
# Increase session lifetime in constants.php
# Change: define('SESSION_LIFETIME', 3600 * 24);
# To: define('SESSION_LIFETIME', 3600 * 24 * 7);  # 7 days

# Also update php.ini
# session.gc_maxlifetime = 604800
```

### PCRE JIT Warning

If you see `Warning: preg_match(): JIT compilation failed`:

```bash
sudo nano /etc/php/8.4/apache2/php.ini
# Set: pcre.jit = 0
sudo systemctl restart apache2
```

### Permission denied errors

```bash
# Reset ownership and permissions
sudo chown -R www-data:www-data /var/www/html
sudo find /var/www/html -type d -exec chmod 755 {} \;
sudo find /var/www/html -type f -exec chmod 644 {} \;
sudo chmod -R 755 /var/www/html/uploads
```

### Composer install fails

```bash
# Check PHP version
php -v  # Must be >= 8.2

# Check required extensions
php -m | grep -E "pdo_mysql|mbstring|xml|zip|dom"

# Install missing extensions
sudo apt install -y php8.4-mbstring php8.4-xml php8.4-zip

# Retry install
cd /var/www/html
composer install --no-dev --optimize-autoloader
```

### Apache "Could not reliably determine server's fully qualified domain name"

```bash
echo "ServerName localhost" | sudo tee /etc/apache2/conf-available/servername.conf
sudo a2enconf servername
sudo systemctl reload apache2
```

---

## Quick Install Script

For a fresh Debian/Ubuntu server, run this script to install everything:

```bash
#!/bin/bash
set -e

echo "=== CV Builder Pro - Quick Install ==="

# 1. Update system
apt update && apt upgrade -y

# 2. Install packages
apt install -y apache2 mariadb-server poppler-utils \
    php8.4 libapache2-mod-php8.4 php8.4-mysql php8.4-mbstring \
    php8.4-xml php8.4-zip php8.4-gd php8.4-intl php8.4-curl \
    php8.4-dom php8.4-fileinfo php8.4-json php8.4-opcache \
    php8.4-cli git unzip curl

# 3. Enable Apache modules
a2enmod rewrite headers deflate

# 4. Install Composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
php -r "unlink('composer-setup.php');"

# 5. Start services
systemctl enable apache2 mariadb
systemctl start apache2 mariadb

# 6. Create database
mysql -e "CREATE DATABASE IF NOT EXISTS cv_builder CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS 'cv_builder'@'127.0.0.1' IDENTIFIED BY 'cv_builder_pass_2024';"
mysql -e "GRANT ALL PRIVILEGES ON cv_builder.* TO 'cv_builder'@'127.0.0.1'; FLUSH PRIVILEGES;"

# 7. Run migrations (run from /var/www/html)
cd /var/www/html
composer install --no-dev --optimize-autoloader
mysql -u cv_builder -pcv_builder_pass_2024 cv_builder < database/schema.sql
mysql -u cv_builder -pcv_builder_pass_2024 cv_builder < database/migration_template_creator.sql
mysql -u cv_builder -pcv_builder_pass_2024 cv_builder < database/seed_templates.sql
mysql -u cv_builder -pcv_builder_pass_2024 cv_builder < database/seed_phrases.sql

# 8. Set permissions
mkdir -p uploads/{pdf,word,imports}
chown -R www-data:www-data /var/www/html
find /var/www/html -type d -exec chmod 755 {} \;
find /var/www/html -type f -exec chmod 644 {} \;
chmod -R 755 uploads/

# 9. Configure Apache
cat > /etc/apache2/sites-available/cvbuilder.conf << 'APACHE'
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot /var/www/html
    <Directory /var/www/html>
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog \${APACHE_LOG_DIR}/cvbuilder-error.log
    CustomLog \${APACHE_LOG_DIR}/cvbuilder-access.log combined
</VirtualHost>
APACHE

a2ensite cvbuilder.conf
a2dissite 000-default.conf

# 10. Restart
systemctl restart apache2

echo "=== Installation Complete ==="
echo "Visit: http://$(hostname -I | awk '{print $1}')/"
echo "Demo login: demo@cvbuilder.com / demo123456"
```
