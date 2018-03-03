<?php

/**
 * @param $order
 * @param $productsArray
 * @return array|int
 */
function discountForSwitchesCategory($order, $productsArray){
    $items = $order['items'];
    $categorySwitches = 2;
    $discountPattern = 5 + 1;

    $totalSwitchesItemsDiscountedArray = array('discount-metadata' => 'For every product of category "Switches" (id 2), when you buy five, you get a sixth for free.');
    $totalSwitchesDiscounts = 0;
    foreach ($items as $item) {
        foreach ($productsArray as $product) {
            if ($item['product-id'] === $product['id'] && intval($product['category']) == $categorySwitches) {
                $totalForProductsOfOneItemDiscounted = floatval($item['total']) - floatval($item['unit-price']) * (intval($item['quantity']) - intdiv(intval($item['quantity']), $discountPattern));
                if (floatval($item['total']) > $totalForProductsOfOneItemDiscounted) {
                    $itemDiscountedArray = createArrayOutOfObjects($item, $totalForProductsOfOneItemDiscounted);
                    $totalSwitchesItemsDiscountedArray[$item['product-id']] = $itemDiscountedArray;
                }
                $totalSwitchesDiscounts += $totalForProductsOfOneItemDiscounted;
            }
        }
    }

    if ($totalSwitchesDiscounts > 0) {
        $totalSwitchesItemsDiscountedArray['discount'] = formatNumber($totalSwitchesDiscounts);
        return $totalSwitchesItemsDiscountedArray;
    }
    return 0;
}

/**
 * @param $order
 * @param $productsArray
 * @return array
 */
function discountForToolsCategory($order, $productsArray){
    $items = $order['items'];
    $categoryTools = 1;
    $amountForDiscount = 2;
    $discountPercentageForTools = 20;

    /* Array that holds items Tools (id 1) category */
    $allToolsItemsArray = $this->getArrayOfInterest($items, $productsArray, $categoryTools);

    /* A variable holding a value of the minimum price among items of items */
    $minPrice = calcMinUnitPrice($allToolsItemsArray);
    $totalToolsItemsDiscountedArray = array('discount-metadata' => 'If you buy two or more products of category "Tools" (id 1), you get a 20% discount on the cheapest product');
    $totalToolsDiscounts = 0;
    if (count($allToolsItemsArray) >= $amountForDiscount) {
        foreach ($items as $item) {
            if (isProductIdFromCategory($item['product-id'], $categoryTools, $productsArray) && floatval($item['unit-price']) == $minPrice){
                $totalForProductsOfOneItemDiscounted = applyPercentageDiscount(floatval($item['total']), $discountPercentageForTools);
                $itemToolsDiscountedArray = createArrayOutOfObjects($item,$totalForProductsOfOneItemDiscounted);
                $totalToolsItemsDiscountedArray[$item['product-id']] = $itemToolsDiscountedArray;
                $totalToolsDiscounts += $totalForProductsOfOneItemDiscounted;
            }
        }
    }
    if ($totalToolsDiscounts > 0) {
        $totalToolsItemsDiscountedArray['discount'] = formatNumber($totalToolsDiscounts);
        return $totalToolsItemsDiscountedArray;
    }
    return 0;
}


function discountForLargePurchase($order, $discountPercentageByTotalAmount, $thresholdTotalAmount){
    /* This discount is considered after all possible discounts are applied */
    $discByTotal = applyDiscountByTotalAmount(floatval($order['total']), $thresholdTotalAmount, $discountPercentageByTotalAmount);
    $discByTotalArray = array(
        'discount-metadata' => 'A customer who has already bought for over € 1000, gets a discount of 10% on the whole order.',
        'discount' => $discByTotal);
    array_push($finalArray, $discByTotalArray);
}


/**
 * Creates an array of items of certain category
 * @param $items
 * @param $productsArray
 * @param $productCategory
 * @return array
 */
function getArrayOfInterest($items, $productsArray, $productCategory){
    $arrayOfItemsOfInterest = Array();
    foreach ($items as $item) {
        foreach ($productsArray as $product) {
            if ($item['product-id'] === $product['id'] && intval($product['category']) == $productCategory) {
                array_push($arrayOfItemsOfInterest, $item);
            }
        }
    }
    return $arrayOfItemsOfInterest;
}

/** Format a float number to 2 decimals
 * @param $number
 * @return string
 */
function formatNumber($number){
    return number_format($number, 2, '.', '');
}

/** Forms an array out of the items
 * @param $item
 * @param $totalForProductsOfOneItemDiscounted
 * @return array
 */
function createArrayOutOfObjects($item, $totalForProductsOfOneItemDiscounted){
    $arrToReturn = array(
        'product-id' => $item['product-id'],
        'quantity' => $item['quantity'],
        'unit-price' => $item['unit-price'],
        'total' => $item['total'],
        //'discount' => number_format($totalForProductsOfOneItemDiscounted, 2, '.', ''));
        'discount' => formatNumber($totalForProductsOfOneItemDiscounted));
    return $arrToReturn;
}

/**
 * Checks if a given item belongs to a certain category
 * @param $productId
 * @param $categoryToolsId
 * @param $products
 * @return bool
 */
function isProductIdFromCategory($productId, $categoryToolsId, $products){
    foreach ($products as $product) {
        if ($product['id'] === $productId && intval($product['category']) === $categoryToolsId){
            /*echo $product['id'] . "\n";
            echo $product['category'] . "\n";
            echo $product['price'] . "\n";
            echo "------------------\n";*/
            return true;
        }
    }
    return false;
}

/**
 * Calculates a minimum price out of an array of several items
 * @param $items
 * @return float
 */
function calcMinUnitPrice($items){
    $minPrice = floatval($items[0]['unit-price']);
    for ($i = 0; $i < count($items); $i++){
        if (floatval($items[$i]['unit-price']) < $minPrice){
            $minPrice = floatval($items[$i]['unit-price']);
        }
    }
    return $minPrice;
}

/**
 * Applies a discount given the percentage
 * @param $total
 * @param $discountPercentage
 * @return float|int
 */
function applyPercentageDiscount($total, $discountPercentage){
    if (is_numeric($total) && is_numeric($discountPercentage)){
        return floatval($total * ($discountPercentage / 100));
    }
    return 0;
}

/**
 * Checks the cost of the whole order, and if exceeds the threshold a discount is calculated
 * @param $amount
 * @param $threshold
 * @param $discountPercentage
 * @return float|int
 */
function applyDiscountByTotalAmount($amount, $threshold, $discountPercentage){
    if ($amount > $threshold && ($amount) && is_numeric($threshold) && is_numeric($discountPercentage)){
        return $amount * ($discountPercentage / 100);
    }
    return 0;
}