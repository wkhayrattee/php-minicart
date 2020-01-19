<?php
/**
 * Handle the CRUD for Cart Item
 *
 * @author Khayrattee Wasseem <wasseem@khayrattee.com>
 * @copyright Copyright (c) 2020 Wasseem Khayrattee
 * @license GPL-3.0
 * @link https://7php.com (website)
 */

namespace Model;


use Pimple\Container;
use Wak\Common\ConstantList;
use Wak\Common\Helper;

class CartItemsDal
{
    /**
     * Fetch all cart items for this specific cart_id
     *
     * @param Container $container
     * @param $cart_id
     * @return array|bool
     * @throws \Exception
     */
    public static function fetchAllItemsByCartId(Container $container, $cart_id)
    {
        /** @var \PDO $conn_handler */
        $conn_handler = null;
        try {
            $conn_handler = $container['connection'];
            $sql = " SELECT c.cart_id,
                            c.checkout_status, 
                            c.total_price AS checkout_total,
                            ci.sku,
                            ci.prod_qty,
                            ci.is_discounted,
                            ci.total_price
                         FROM " . ConstantList::DB_TABLE_CART . " c
                         INNER JOIN
                            " . ConstantList::DB_TABLE_CART_ITEM . " ci
                            ON c.cart_id = ci.cart_id
                      WHERE c.cart_id = :cart_id
                        ORDER by ci.sku
                   ";
            $statement_handler = $conn_handler->prepare($sql);
            $statement_handler->execute(['cart_id'=>$cart_id]);
            if($statement_handler->rowCount() > 0) {//or might have used columnCount()
                return $statement_handler->fetchAll();
            }
            return false;
        } catch ( \PDOException $error) {
            Helper::logErrorAndSendEmail($container, $error, 'CartItemsDal::fetchAllItemsByCartId');
            throw $error; //THROW BACK TO MAKE PARENT CATCH IT
        } catch (\Exception $error) {
            Helper::logErrorAndSendEmail($container, $error, 'CartItemsDal::fetchAllItemsByCartId');
            throw $error; //THROW BACK TO MAKE PARENT CATCH IT
        }
    }


    /** Insert a new cart item
     *
     * @param Container $container
     * @param \stdClass $cart
     * @return string
     * @throws \Exception
     */
    public static function insertCartItem(Container $container, \stdClass $cart)
    {
        /** @var \PDO $conn_handler */
        $conn_handler = null;
        try {
            $conn_handler = $container['connection'];
            $conn_handler->beginTransaction();

            ///1st insert
            $sql = " INSERT INTO " . ConstantList::DB_TABLE_CART_ITEM . "
                        (cart_id, sku, prod_qty, is_discounted, total_price, date_created)
                      values
                        (:cart_id, :sku, :prod_qty, :is_discounted, :total_price, :date_created);";

            $statement_handler = $conn_handler->prepare($sql);
            $statement_handler->execute((array)$cart);

            $conn_handler->commit();
        } catch ( \PDOException $error) {
            $conn_handler->rollBack();
            Helper::logErrorAndSendEmail($container, $error, 'CartItemsDal::insertCartItem');
            throw $error; //THROW BACK TO MAKE PARENT CATCH IT
        } catch (\Exception $error) {
            $conn_handler->rollBack();
            Helper::logErrorAndSendEmail($container, $error, 'CartItemsDal::insertCartItem');
            throw $error; //THROW BACK TO MAKE PARENT CATCH IT
        } finally {
            unset($statement_handler);
            return 'FAIL';
        }
    }

    /**
     * Update cart item
     *
     * @param Container $container
     * @param \stdClass $cart
     * @return string
     * @throws \Exception
     */
    public static function updateCartItem(Container $container, \stdClass $cart)
    {
        /** @var \PDO $conn_handler */
        $conn_handler = null;
        try {
            $conn_handler = $container['connection'];
            $conn_handler->beginTransaction();

            ///1st insert
            $sql = " UPDATE " . ConstantList::DB_TABLE_CART_ITEM . "
                        SET 
                            prod_qty = :prod_qty, 
                            is_discounted = :is_discounted, 
                            total_price = :total_price, 
                            date_created = :date_created
                      WHERE cart_id = :cart_id 
                        AND
                            sku = :sku
                      ";

            $statement_handler = $conn_handler->prepare($sql);
            $statement_handler->execute((array)$cart);

            $conn_handler->commit();
        } catch ( \PDOException $error) {
            $conn_handler->rollBack();
            Helper::logErrorAndSendEmail($container, $error, 'CartItemsDal::updateCartItem');
            throw $error; //THROW BACK TO MAKE PARENT CATCH IT
        } catch (\Exception $error) {
            $conn_handler->rollBack();
            Helper::logErrorAndSendEmail($container, $error, 'CartItemsDal::updateCartItem');
            throw $error; //THROW BACK TO MAKE PARENT CATCH IT
        } finally {
            unset($statement_handler);
            return 'FAIL';
        }
    }

    /**
     * To see if any product exist currently in this cart
     * If yes, return the full object as we will need it for process
     * ELSE simply return -1 so that we know we should do an INSERT next
     *
     * @param Container $container
     * @param $sku
     * @param $cart_id
     * @return int|mixed
     * @throws \Exception
     */
    public static function getProductCountBySku(Container $container, $sku, $cart_id)
    {
        /** @var \PDO $conn_handler */
        $conn_handler = null;
        try {
            $conn_handler = $container['connection'];
            $sql = "SELECT
                        cart_id,
                        sku,
                        prod_qty
                    FROM " . ConstantList::DB_TABLE_CART_ITEM . " c
                    WHERE 
                        c.cart_id = :cart_id
                            AND
                        c.sku = :sku
                           
            ";
            $statement_handler = $conn_handler->prepare($sql);
            $statement_handler->execute(['sku' => $sku, 'cart_id' => $cart_id]);
            if($statement_handler->rowCount() > 0) {
                return $statement_handler->fetchObject();
            }
            return -1;
        } catch( \PDOException $error) {
            Helper::logErrorAndSendEmail($container, $error, 'CartItemsDal::getProductCountBySku');
            throw $error; //THROW BACK TO MAKE PARENT CATCH IT
        } catch(\Exception $error) {
            Helper::logErrorAndSendEmail($container, $error, 'CartItemsDal::getProductCountBySku');
            throw $error; //THROW BACK TO MAKE PARENT CATCH IT
        }
    }

    /**
     * Used to fetch and also to see if there's any existing rules by this SKU
     * Return -1, if none exist - which would also give us a clue, we have to INSERT next in our Add cart logic
     *
     * @param Container $container
     * @param $sku
     * @return int|mixed
     * @throws \Exception
     */
    public static function getPromoDiscountBySku(Container $container, $sku)
    {
        /** @var \PDO $conn_handler */
        $conn_handler = null;
        try {
            $conn_handler = $container['connection'];
            $sql = "SELECT
                        sku,
                        product_occurrence,
                        promo_price
                    FROM " . ConstantList::DB_TABLE_PRICING_RULES . " c
                    WHERE 
                        c.sku = :sku
                           
            ";
            $statement_handler = $conn_handler->prepare($sql);
            $statement_handler->execute(['sku' => $sku]);
            if($statement_handler->rowCount() > 0) {
                return $statement_handler->fetchObject();
            }
            return -1;
        } catch( \PDOException $error) {
            Helper::logErrorAndSendEmail($container, $error, 'CartItemsDal::getPromoDiscountBySku');
            throw $error; //THROW BACK TO MAKE PARENT CATCH IT
        } catch(\Exception $error) {
            Helper::logErrorAndSendEmail($container, $error, 'CartItemsDal::getPromoDiscountBySku');
            throw $error; //THROW BACK TO MAKE PARENT CATCH IT
        }
    }

    /**
     * Get pricing rules count
     *
     * @param Container $container
     * @return int
     * @throws \Exception
     */
    public static function getRulesCount(Container $container)
    {
        /** @var \PDO $conn_handler */
        $conn_handler = null;
        try {
            $conn_handler = $container['connection'];
            $sql = "SELECT
                        COUNT(*) as record_count
                    FROM " . ConstantList::DB_TABLE_PRICING_RULES . "
            ";
            $statement_handler = $conn_handler->prepare($sql);
            $statement_handler->execute();
            if($statement_handler->rowCount() > 0) {
                return (int)$statement_handler->fetchObject()->record_count;
            }
            return -1;
        } catch( \PDOException $error) {
            Helper::logErrorAndSendEmail($container, $error, 'CartItemsDal::getRulesCount');
            throw $error; //THROW BACK TO MAKE PARENT CATCH IT
        } catch(\Exception $error) {
            Helper::logErrorAndSendEmail($container, $error, 'CartItemsDal::getRulesCount');
            throw $error; //THROW BACK TO MAKE PARENT CATCH IT
        }
    }

}
