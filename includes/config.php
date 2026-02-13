<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'coldston_SedibengDb');
define('DB_USER', 'coldston_mysqldb');
define('DB_PASS', 'Sedibeng3@');


// URL path to the sedibeng folder (browser paths)
define('BASE_URL', '/sedibeng');

// File system path (server paths)
define('BASE_PATH', __DIR__ . '/..');


define('APP_NAME', 'Sedibeng');
define('TOTP_ISSUER', 'Sedibeng');

// Path to this config file
$configPath = __FILE__;
// Get full document root path
$docRoot = $_SERVER['DOCUMENT_ROOT'];

// Extract /home/username
if (preg_match('#^(/home/[^/]+)/#', $docRoot, $matches)) {
    $accountRoot = $matches[1];
} else {
    die(
        'Cannot determine account root path.' . 
        '<br>Document root: ' . $docRoot .
        '<br>Config path: ' . $configPath
    );
}

$secureFile = $accountRoot . '/private/reCAPTCHA.php';

if (is_readable($secureFile)) {
    require $secureFile;
} else {
    die(
        'reCAPTCHA configuration file not found.' .
        '<br>Attempted path: ' . $secureFile .
        '<br>Config path: ' . $configPath
    );
}

