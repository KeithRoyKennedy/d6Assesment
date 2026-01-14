<?php
// Front controller for the application

// Load Composer autoloader
require_once '../vendor/autoload.php';

// Load configuration
require_once '../config/config.php';

// Import the App class
use Keith\D6assesment\App;

// Initialize the application
$app = new App();
$app->run();
?>
