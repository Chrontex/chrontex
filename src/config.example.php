<?php
// Cloud Run production variables with local development fallbacks
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'chrontex');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_USER', getenv('SMTP_USER') ?: 'your-account@gmail.com');
define('SMTP_PASS', getenv('SMTP_PASS') ?: 'your-google-app-password');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
define('FROM_EMAIL', getenv('FROM_EMAIL') ?: 'no-reply@chrontex.app');
define('FROM_NAME', getenv('FROM_NAME') ?: 'Chrontex Service');