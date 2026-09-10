<?php
// Empty string = served at the web root (e.g. the PHP built-in server run
// from inside public/). Set to '/chrontex' if hosted under a sub-path.
define('BASE_URL', getenv('BASE_URL') !== false ? getenv('BASE_URL') : '');
define('SITE_URL', getenv('SITE_URL') ?: 'http://localhost');

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'chrontex');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
// Optional unix socket. Leave unset for a normal host/port TCP connection.
define('DB_SOCKET', getenv('DB_SOCKET') ?: null);

define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_USER', getenv('SMTP_USER') ?: 'your-account@gmail.com');
define('SMTP_PASS', getenv('SMTP_PASS') ?: 'your-google-app-password');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
define('SMTP_FROM_EMAIL', getenv('SMTP_FROM_EMAIL') ?: 'no-reply@chrontex.app');
define('SMTP_FROM_NAME', getenv('SMTP_FROM_NAME') ?: 'Chrontex Service');