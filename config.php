<?php
 
// increase loading speed
ini_set('max_execution_time', 120);
error_reporting(E_ALL ^ E_DEPRECATED);

// start session
session_start();

// Info
$CONFIG_WEBSITE_URL = 'http://localhost/;
$CONFIG_DEBUG = true;
$CONFIG_ENABLED = true;
$CONFIG_DISABLED_REASON = '';

// News Info
$CONFIG_NEWS_INFO = '';
$CONFIH_NEWS_DATE = '2016';

// Database Info
$db_host = 'localhost';
$db_user = 'root';
$db_password = '';
$db_database = 'search';

// Login Credentials
$CONFIG_USERNAME = 'admin;
$CONFIG_PASSWORD = 'admin';
$CONFIG_EMAIL = 'admin@admin.com';

// Includes/Requires
require(__DIR__ . '/includes/functions.php');

if($CONFIG_ENABLED == false) {
    die('Search engine is temporary disabled. Reason: <i>' . $CONFIG_DISABLED_REASON . '</i>.');
}

?>
