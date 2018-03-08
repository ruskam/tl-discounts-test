<?php

class Discount
{
    private $discounts;
    private $total;

    private $order;
    private $productsArray;

    private $thresholdTotalAmount;
    private $discountPercentageByTotalAmount;

    private $finalArray;

    /**
     * Discount constructor.
     * @param $order
     * @param $productsArray
     */
    public function __construct($order, $productsArray)
    {
        $this->total = floatval($order['total']);
        $this->order = $order;
        $this->productsArray = $productsArray;
        $this->discounts = array();
        $this->finalArray = array();
    }

    /**
     * @param $thresholdTotalAmount
     */
    public function setThresholdTotalAmount($thresholdTotalAmount)
    {
        $this->thresholdTotalAmount = $thresholdTotalAmount;
    }

    /**
     * @param $discountPercentageByTotalAmount
     */
    public function setDiscountPercentageByTotalAmount($discountPercentageByTotalAmount)
    {
        $this->discountPercentageByTotalAmount = $discountPercentageByTotalAmount;
    }

    /**
     * @param $element
     */
    private function addDiscount($element)
    {
        $this->discounts[] = $element;
    }

    /**
     * @return int|mixed
     */
    private function getSumOfDiscounts()
    {
        $sum = 0;
        foreach ($this->discounts as $discount){
            $sum += $discount;
        }
        return $sum;
    }

    /**
     * @return float|int|mixed
     */
    private function calcTotalDiscounted()
    {
        return $this->total - $this->getSumOfDiscounts();
    }

    /** The API to build and return a response array
     * @return array
     */
    public function buildDiscountsArray()
    {
        /* Get a discount for Switches category */
        $totalSwitchesDiscounts = $this->discountForSwitchesCategory($this->order, $this->productsArray);
        /* Add a discount value to the array holding all discount values */
        $this->finalArray[] = $totalSwitchesDiscounts;

        /* Get a discount for Tools category */
        $totalToolsDiscounts = $this->discountForToolsCategory($this->order, $this->productsArray);
        /* Add a discount value to the array holding all discount values */
        $this->finalArray[] = $totalToolsDiscounts;

        /* If a discount on the total amount is applicable, it is applied */
        if ($this->isDiscountOnTotalAmountApplicable()){
            $discountOnTotalAmount = $this->discountOnTotalAmount($this->order, $this->thresholdTotalAmount, $this->discountPercentageByTotalAmount);
            $this->addDiscount($discountOnTotalAmount['discount']);
            $this->finalArray[] = $discountOnTotalAmount;
        }

        /* Adding discount value to the response array */
        $this->finalArray['total'] = $this->formatNumber($this->total);
        $this->finalArray['total-discounts'] = $this->formatNumber($this->getSumOfDiscounts());

        /* This is the core output amount to be paid by a customer */
        $this->finalArray['total-after-discounts'] = $this->formatNumber($this->calcTotalDiscounted());

        return $this->finalArray;
    }

    /** Checks if discount on total amount is applicable
     * @return bool
     */
    private function isDiscountOnTotalAmountApplicable()
    {
        if ($this->getSumOfDiscounts() > $this->thresholdTotalAmount){
            return true;
        }
        return false;
    }

    /** Applies discount on the total amount after all possible discounts are applied
     * @return array|int
     */
    private function discountOnTotalAmount()
    {
        /* This discount is considered after all possible discounts are applied */
        $discByTotalArray = array('discount-metadata' => 'A customer who has already bought for over € 1000, gets a discount of 10% on the whole order.');
        $discByTotal = $this->applyDiscountByTotalAmount(floatval($this->order['total'] - $this->getSumOfDiscounts()), $this->thresholdTotalAmount, $this->discountPercentageByTotalAmount);

        if ($discByTotal > 0) {
            $discByTotalArray['discount'] = $this->formatNumber($discByTotal);
            return $discByTotalArray;
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
    private function applyDiscountByTotalAmount($amount, $threshold, $discountPercentage){
        if ($amount > $threshold && ($amount) && is_numeric($threshold) && is_numeric($discountPercentage)){
            return $amount * ($discountPercentage / 100);
        }
        return 0;
    }

    /**
     * @param $order
     * @param $productsArray
     * @return array|int
     */
    private function discountForSwitchesCategory($order, $productsArray)
    {

        $items = $order['items'];
        /* Id of the Switches category */
        $categorySwitches = 2;
        /* Discount pattern */
        $discountPattern = 5 + 1;

        $totalSwitchesItemsDiscountedArray = array('discount-metadata' => 'For every product of category "Switches" (id 2), when you buy five, you get a sixth for free.');
        $totalSwitchesDiscounts = 0;

        /* Calculating the price for the whole bought amount while applying the discount */
        foreach ($items as $item) {
            foreach ($productsArray as $product) {
                if ($item['product-id'] === $product['id'] && intval($product['category']) == $categorySwitches) {
                    $totalForProductsOfOneItemDiscounted = floatval($item['total']) - floatval($item['unit-price']) * (intval($item['quantity']) - intdiv(intval($item['quantity']), $discountPattern));
                    if (floatval($item['total']) > $totalForProductsOfOneItemDiscounted) {
                        $itemDiscountedArray = $this->createArrayOutOfObjects($item, $totalForProductsOfOneItemDiscounted);
                        $totalSwitchesItemsDiscountedArray[$item['product-id']] = $itemDiscountedArray;
                    }
                    $totalSwitchesDiscounts += $totalForProductsOfOneItemDiscounted;
                }
            }
        }
        if ($totalSwitchesDiscounts > 0) {
            $this->addDiscount($totalSwitchesDiscounts);
            $totalSwitchesItemsDiscountedArray['discount'] = $this->formatNumber($totalSwitchesDiscounts);
            return $totalSwitchesItemsDiscountedArray;
        }
        return 0;
    }

    /**
     * @param $order
     * @param $productsArray
     * @return array
     */
    private function discountForToolsCategory($order, $productsArray)
    {
        $items = $order['items'];
        $categoryTools = 1;
        $amountForDiscount = 2;
        $discountPercentageForTools = 20;

        /* Array that holds items Tools (id 1) category */
        $allToolsItemsArray = $this->getArrayOfInterest($items, $productsArray, $categoryTools);

        /* A variable holding a value of the minimum price among items of items */
        $minPrice = $this->calcMinUnitPrice($allToolsItemsArray);
        $totalToolsItemsDiscountedArray = array('discount-metadata' => 'If you buy two or more products of category "Tools" (id 1), you get a 20% discount on the cheapest product');
        $totalToolsDiscounts = 0;
        if (count($allToolsItemsArray) >= $amountForDiscount) {
            foreach ($items as $item) {
                if ($this->isProductIdFromCategory($item['product-id'], $categoryTools, $productsArray) && floatval($item['unit-price']) == $minPrice){
                    $totalForProductsOfOneItemDiscounted = $this->applyPercentageDiscount(floatval($item['total']), $discountPercentageForTools);
                    $itemToolsDiscountedArray = $this->createArrayOutOfObjects($item,$totalForProductsOfOneItemDiscounted);
                    $totalToolsItemsDiscountedArray[$item['product-id']] = $itemToolsDiscountedArray;
                    $totalToolsDiscounts += $totalForProductsOfOneItemDiscounted;
                }
            }
        }
        if ($totalToolsDiscounts > 0) {
            $this->addDiscount($totalToolsDiscounts);
            $totalToolsItemsDiscountedArray['discount'] = $this->formatNumber($totalToolsDiscounts);
            return $totalToolsItemsDiscountedArray;
        }
        return 0;
    }


    /**
     * Creates an array of items of certain category
     * @param $items
     * @param $productsArray
     * @param $productCategory
     * @return array
     */
    private function getArrayOfInterest($items, $productsArray, $productCategory){
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
    private function formatNumber($number){
        return number_format($number, 2, '.', '');
    }

    /** Forms an array out of the items
     * @param $item
     * @param $totalForProductsOfOneItemDiscounted
     * @return array
     */
    private function createArrayOutOfObjects($item, $totalForProductsOfOneItemDiscounted){
        $arrToReturn = array(
            'product-id' => $item['product-id'],
            'quantity' => $item['quantity'],
            'unit-price' => $item['unit-price'],
            'total' => $item['total'],
            //'discount' => number_format($totalForProductsOfOneItemDiscounted, 2, '.', ''));
            'discount' => $this->formatNumber($totalForProductsOfOneItemDiscounted));
        return $arrToReturn;
    }

    /**
     * Checks if a given item belongs to a certain category
     * @param $productId
     * @param $categoryToolsId
     * @param $products
     * @return bool
     */
    private function isProductIdFromCategory($productId, $categoryToolsId, $products){
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
    private function calcMinUnitPrice($items){
        $minPrice = floatval($items[0]['unit-price']);
        for ($i = 1; $i < count($items); $i++){
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
    private function applyPercentageDiscount($total, $discountPercentage){
        if (is_numeric($total) && is_numeric($discountPercentage)){
            return floatval($total * ($discountPercentage / 100));
        }
        return 0;
    }


}
