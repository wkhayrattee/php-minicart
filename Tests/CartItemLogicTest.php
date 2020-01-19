<?php

Use \PHPUnit\Framework\TestCase;
use Project\CartItemLogic;

/**
 * Class CartItemLogicTest
 *
 * @author Khayrattee Wasseem <wasseem@khayrattee.com>
 * @copyright Copyright (c) 2020 Wasseem Khayrattee
 * @license GPL-3.0
 * @link https://7php.com (website)
 */
class CartItemLogicTest extends TestCase
{
    /**
     * @test
     * @dataProvider expectedProvider
     *
     * @param $sku
     * @param $prod_qty
     * @param $expected_total_price
     * @throws Exception
     */
    public function doApplicablePricingRuleTest($sku, $prod_qty, $expected_total_price)
    {
        //Fetch needed data from providers
        $item_list   = $this->itemProvider();
        $price_rules = $this->pricingRulesProvider();

        //Prepare our main object which is CartItemLogic object
        $cart_item              = new \stdClass();
        $cart_item->sku         = $sku;
        $cart_item->prod_qty    = $prod_qty;
        $cart_item->unit_price  = $item_list[$sku]['price'];
        $cart_item->cart_id     = '454c39f6-3513-11ea-b804-0242ac110002'; // a random uuid as my class need this
        $cartItemLogicObject    = new CartItemLogic($cart_item, new \Pimple\Container());
        if (array_key_exists($sku, $price_rules)) {
            $cartItemLogicObject->is_discounted = 1;
        }
        $cartItemLogicObject->discount_list = self::ToStdObject($price_rules[$sku]);
        $cartItemLogicObject->total_price   = 0;

        //Now Test our Main function logic for handling the Pricing Rules
        $cartItemLogicObject->doApplicablePricingRule($prod_qty);
        $this->assertEquals($expected_total_price, $cartItemLogicObject->total_price);
    }

    /**
     * A set of data as per the email, to run the test agains
     * @return array
     */
    public function expectedProvider()
    {
        return [
            [
                'sku'      => 'A',  // product entered
                'prod_qty' => 1,    // quantity entered
                'total'    => 50,   // expected total price for the given quantity, taking into consideration Pricing Rule
            ],
            [
                'sku'      => 'A',
                'prod_qty' => 2,
                'total'    => 100,
            ],
            [
                'sku'      => 'A',
                'prod_qty' => 3,
                'total'    => 130,
            ],
            [
                'sku'      => 'A',
                'prod_qty' => 4,
                'total'    => 185, //WARNING: SHOULD FAIl, as expected is 180
            ],
            [
                'sku'      => 'A',
                'prod_qty' => 5,
                'total'    => 230,
            ],
            [
                'sku'      => 'A',
                'prod_qty' => 6,
                'total'    => 260,
            ],
            [
                'sku'      => 'B',
                'prod_qty' => 1,
                'total'    => 30,
            ],
            [
                'sku'      => 'B',
                'prod_qty' => 2,
                'total'    => 45,
            ],
            [
                'sku'      => 'B',
                'prod_qty' => 3,
                'total'    => 75,
            ],
            [
                'sku'      => 'B',
                'prod_qty' => 4,
                'total'    => 95, ////WARNING: SHOULD FAIl, as expected is 90
            ],
        ];
    }

    /**
     * Supply list of Pricing Rules
     * @return array
     */
    public function pricingRulesProvider()
    {
        return [
            'A' => [
                'sku'                => 'A',
                'product_occurrence' => 3,
                'promo_price'        => 130
            ],
            'B' => [
                'sku'                => 'B',
                'product_occurrence' => 2,
                'promo_price'        => 45
            ]
        ];
    }

    /**
     * Supply list of items / Products
     * @return array
     */
    public function itemProvider()
    {
        return [
            'A' => [
                'sku'   => 'A',
                'price' => 50,
            ],
            'B' => [
                'sku'   => 'B',
                'price' => 30,
            ],
            'C' => [
                'sku'   => 'C',
                'price' => 20,
            ],
            'D' => [
                'sku'   => 'D',
                'price' => 15,
            ]
        ];
    }

    public static function ToStdObject($this_array)
    {
        $object = new stdClass();
        foreach ($this_array as $key => $value) {
            if (is_array($value)) {
                $value =  self::ToStdObject($value);
            }
            $object->$key = $value;
        }
        return $object;
    }
}
