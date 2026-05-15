<?php
define('APP_NAME', 'CV Builder Pro');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://217.174.245.14');
define('BASE_PATH', '/var/www/html');
define('UPLOAD_PATH', BASE_PATH . '/uploads');
define('TEMPLATE_PATH', BASE_PATH . '/templates');
define('ASSETS_URL', BASE_URL . '/assets');

define('SESSION_LIFETIME', 3600 * 24);
define('CSRF_TOKEN_NAME', 'csrf_token');

define('PDF_EXPORT_PATH', UPLOAD_PATH . '/pdf');
define('WORD_EXPORT_PATH', UPLOAD_PATH . '/word');
define('IMPORT_TEMP_PATH', UPLOAD_PATH . '/imports');

$uploadDirs = [UPLOAD_PATH, PDF_EXPORT_PATH, WORD_EXPORT_PATH, IMPORT_TEMP_PATH];
foreach ($uploadDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}
