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

$app->get('/api', function (Request $request, Response $response){
    $data = array('this URL' => 'API endpoint', 'status' => 'success', 'version' => '1.0');
    return $response->withJson($data);

});

$app->post('/api/discount/', function(Request $request, Response $response){
    $order = $request->getParsedBody();
    $url = "src/services/products.json";
    $productsArray = NetworkUtils::getData($url);

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
    $getSumOfDiscounts =  $discount->getSumOfDiscounts();
    $calcTotalDiscounted =  $discount->calcTotalDiscounted();

});
