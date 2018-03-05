<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app = new \Slim\App;

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});
$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

/**
 * The endpoint is for testing
 */
$app->get('/api', function (Request $request, Response $response){
    $data = array('this URL' => 'Test API endpoint', 'status' => 'success', 'version' => '1.0');
    return $response->withJson($data);

});
/**
 * Main endpoint to access discounts
 */
$app->post('/api/discount/', function(Request $request, Response $response){
    /* Order data passed to the service */
    $order = $request->getParsedBody();

    /* Products data retrieved via another service */
    $url = "src/services/products.json";
    $productsArray = NetworkUtils::getData($url);

    /* Main business logic class */
    $discount = new Discount($order, $productsArray);

    /* Defining conditions for total value discount*/
    $discountPercentageByTotalAmount = 10;
    $thresholdTotalAmount = 1000;

    /* Setting conditions for total value discount*/
    $discount->setThresholdTotalAmount($thresholdTotalAmount);
    $discount->setDiscountPercentageByTotalAmount($discountPercentageByTotalAmount);

    /* Create a response array */
    $responseArray = $discount->buildDiscountsArray();
    return $response->withJson($responseArray);

});
