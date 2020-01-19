<?php
/**
 * Handle dal layer for table cart
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

class CartDal
{
    /**
     * Create new cart object
     *
     * @param Container $container
     * @param \stdClass $cart
     * @return string
     * @throws \Exception
     */
    public static function insertCart(Container $container, \stdClass $cart)
    {
        /** @var \PDO $conn_handler */
        $conn_handler = null;
        try {
            $conn_handler = $container['connection'];
            $conn_handler->beginTransaction();

            ///1st insert
            $sql = " INSERT INTO " . ConstantList::DB_TABLE_CART . "
                        (cart_id, checkout_status, date_created)
                      values
                        (:cart_id, :checkout_status, :date_created);";

            $statement_handler = $conn_handler->prepare($sql);
            $statement_handler->execute((array)$cart);

            $conn_handler->commit();
        } catch ( \PDOException $error) {
            $conn_handler->rollBack();
            Helper::logErrorAndSendEmail($container, $error, 'CartDal::insertCart');
            throw $error; //THROW BACK TO MAKE PARENT CATCH IT
        } catch (\Exception $error) {
            $conn_handler->rollBack();
            Helper::logErrorAndSendEmail($container, $error, 'CartDal::insertCart');
            throw $error; //THROW BACK TO MAKE PARENT CATCH IT
        } finally {
            unset($statement_handler);
            return 'FAIL';
        }
    }

    /**
     * Used for checkout, update cart table with final total
     *
     * @param Container $container
     * @param $cart_id
     * @return string
     * @throws \Exception
     */
    public static function updateCart(Container $container, $cart_id)
    {
        /** @var \PDO $conn_handler */
        $conn_handler = null;
        try {
            $conn_handler = $container['connection'];
            $conn_handler->beginTransaction();

            ///1st insert
            $sql = " UPDATE " . ConstantList::DB_TABLE_CART . "
                        SET 
                            checkout_status = 1, 
                            total_price = (
                                SELECT 
                                    SUM(`total_price`) 
                                FROM " . ConstantList::DB_TABLE_CART_ITEM . " 
                                    WHERE cart_id = :cart_id
                            )
                      WHERE cart_id = :cart_id 
                      ";
            $statement_handler = $conn_handler->prepare($sql);
            $statement_handler->execute(['cart_id'=>$cart_id]);

            $conn_handler->commit();
        } catch ( \PDOException $error) {
            $conn_handler->rollBack();
            Helper::logErrorAndSendEmail($container, $error, 'CartDal::updateCart');
            throw $error; //THROW BACK TO MAKE PARENT CATCH IT
        } catch (\Exception $error) {
            $conn_handler->rollBack();
            Helper::logErrorAndSendEmail($container, $error, 'CartDal::updateCart');
            throw $error; //THROW BACK TO MAKE PARENT CATCH IT
        } finally {
            unset($statement_handler);
            return 'FAIL';
        }
    }

    public static function fetchInvoiceItemsByCart_id(Container $container, $cart_id)
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
                            ci.total_price,
                            p.price AS unit_price
                         FROM " . ConstantList::DB_TABLE_CART . " c
                         INNER JOIN
                            " . ConstantList::DB_TABLE_CART_ITEM . " ci
                            ON c.cart_id = ci.cart_id
                         INNER JOIN
                         	" . ConstantList::DB_TABLE_PRODUCT . " p
                            ON p.sku = ci.sku
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
            Helper::logErrorAndSendEmail($container, $error, 'CartDal::fetchInvoiceItemsByCart_id');
            throw $error; //THROW BACK TO MAKE PARENT CATCH IT
        } catch (\Exception $error) {
            Helper::logErrorAndSendEmail($container, $error, 'CartDal::fetchInvoiceItemsByCart_id');
            throw $error; //THROW BACK TO MAKE PARENT CATCH IT
        }
    }
}
