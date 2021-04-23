<?php

// Should be set to 0 in production
error_reporting(getenv('ERROR_REPORTING'));

// Should be set to '0' in production
ini_set('display_errors', getenv('DISPLAY_ERRORS'));

// Timezone
date_default_timezone_set('America/Recife');

// Settings
$settings = [];

// Path settings
$settings['root'] = dirname(__DIR__);
$settings['temp'] = $settings['root'] . '/tmp';
$settings['public'] = $settings['root'] . '/public';

// Error Handling Middleware settings
$settings['error'] = [

    // Should be set to false in production
    'display_error_details' => getenv('DISPLAY_ERRORS_DETAILS'),

    // Parameter is passed to the default ErrorHandler
    // View in rendered output by enabling the "displayErrorDetails" setting.
    // For the console and unit tests we also disable it
    'log_errors' => getenv('LOG_ERROS'),

    // Display error details in error log
    'log_error_details' => getenv('LOG_ERROR_DETAILS'),
];

// Database settings
$settings['db'] = [
    'driver' => 'mysql',
    'host' => getenv('MYSQL_HOST'),
    'username' => getenv('MYSQL_USER'),
    'password' => getenv('MYSQL_PASSWORD'),
    'database' => getenv('MYSQL_DBNAME'),
    'charset' => 'utf8',
    'collation' => 'utf8_general_ci',
    'flags' => [
        // Turn off persistent connections
        PDO::ATTR_PERSISTENT => false,
        // Enable exceptions
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        // Emulate prepared statements
        PDO::ATTR_EMULATE_PREPARES => true,
        // Set default fetch mode to array
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // Set character set
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8 COLLATE utf8_general_ci'
    ],
];

return $settings;
