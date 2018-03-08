<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';
$app = new \Slim\App;

require 'src/routes/discount_controller.php';
/** Removed this module since all functionality transferred in my_discount.php
 * There are class methods in my_discounts.php that have the same name in discount_service.php
 * and were accessed without $this->
 * As a result functions instead of methods have been used.
 * Now it is fixed
 * require 'src/services/discount_service.php';
 */
require 'src/services/network_service.php';
require 'src/domains/my_discounts.php';

$app->run();
