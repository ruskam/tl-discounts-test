<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';
$app = new \Slim\App;

require 'src/routes/discount_controller.php';
require 'src/services/discount_service.php';
require 'src/services/network_service.php';
require 'src/domains/my_discounts.php';

$app->run();
