<?php
/**
 * To handle CRUD for products
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

class ProductsDal
{
    /**
     * Save a new product
     *
     * @param Container $container
     * @param \stdClass $product
     * @return bool
     * @throws \Exception
     */
    public static function insertProduct(Container $container, \stdClass $product)
    {
        /** @var \PDO $conn_handler */
        $conn_handler = null;
        try {
            $conn_handler = $container['connection'];
            $conn_handler->beginTransaction();

            ///1st insert
            $sql = " INSERT INTO " . ConstantList::DB_TABLE_PRODUCT . "
                        (sku, prod_name, price, date_created)
                      values
                        (:sku, :prod_name, :price, :date_created);";

            $statement_handler = $conn_handler->prepare($sql);
            $statement_handler->execute((array)$product);

            $conn_handler->commit();
        } catch ( \PDOException $error) {
            $conn_handler->rollBack();
            Helper::logErrorAndSendEmail($container, $error, 'ProductsDal::insertProduct');
            throw $error; //THROW BACK TO MAKE PARENT CATCH IT
        } catch (\Exception $error) {
            $conn_handler->rollBack();
            Helper::logErrorAndSendEmail($container, $error, 'ProductsDal::insertProduct');
            throw $error; //THROW BACK TO MAKE PARENT CATCH IT
        } finally {
            unset($statement_handler);
            return 'FAIL';
        }
    }

    /**
     * Create a new pricing rule
     *
     * @param Container $container
     * @param \stdClass $pricing_rule
     * @return string
     * @throws \Exception
     */
    public static function insertNewRule(Container $container, \stdClass $pricing_rule)
    {
        /** @var \PDO $conn_handler */
        $conn_handler = null;
        try {
            $conn_handler = $container['connection'];
            $conn_handler->beginTransaction();

            ///1st insert
            $sql = " INSERT INTO " . ConstantList::DB_TABLE_PRICING_RULES . "
                        (sku, product_occurrence, promo_price, date_created)
                      values
                        (:sku, :product_occurrence, :promo_price, :date_created);";

            $statement_handler = $conn_handler->prepare($sql);
            $statement_handler->execute((array)$pricing_rule);

            $conn_handler->commit();
        } catch ( \PDOException $error) {
            $conn_handler->rollBack();
            Helper::logErrorAndSendEmail($container, $error, 'ProductsDal::insertNewRule');
            throw $error; //THROW BACK TO MAKE PARENT CATCH IT
        } catch (\Exception $error) {
            $conn_handler->rollBack();
            Helper::logErrorAndSendEmail($container, $error, 'ProductsDal::insertNewRule');
            throw $error; //THROW BACK TO MAKE PARENT CATCH IT
        } finally {
            unset($statement_handler);
            return 'FAIL';
        }
    }

    /**
     * Help us to know if there's any duplicate as well
     *
     * @param Container $container
     * @param $sku
     * @return bool
     * @throws \Exception
     */
    public static function findProductBySKU(Container $container, $sku)
    {
        /** @var \PDO $conn_handler */
        $conn_handler = null;
        try {
            $conn_handler = $container['connection'];
            $statement_handler = $conn_handler->prepare(
                " SELECT p.prod_id
                             FROM " . ConstantList::DB_TABLE_PRODUCT . " p
                            WHERE p.sku = :sku ORDER by p.prod_id LIMIT 1
                                                        ");
            $statement_handler->execute(['sku' => $sku]);
            if($statement_handler->rowCount() > 0) {//or might have used columnCount()
                return true;
            }
            return false;
        } catch ( \PDOException $error) {
            Helper::logErrorAndSendEmail($container, $error, 'ProductsDal::findProductBySKU');
            throw $error; //THROW BACK TO MAKE PARENT CATCH IT
        } catch (\Exception $error) {
            Helper::logErrorAndSendEmail($container, $error, 'ProductsDal::findProductBySKU');
            throw $error; //THROW BACK TO MAKE PARENT CATCH IT
        }
    }

    /**
     * Get list of all products
     *
     * @param Container $container
     * @return bool
     * @throws \Exception
     */
    public static function fetchAllProduct(Container $container)
    {
        /** @var \PDO $conn_handler */
        $conn_handler = null;
        try {
            $conn_handler = $container['connection'];
            $statement_handler = $conn_handler->prepare(
                " SELECT  p.prod_id, 
                                    p.sku, 
                                    price
                             FROM " . ConstantList::DB_TABLE_PRODUCT . " p
                            ORDER by p.sku
                                                        ");
            $statement_handler->execute();
            if($statement_handler->rowCount() > 0) {//or might have used columnCount()
                return $statement_handler->fetchAll(\PDO::FETCH_ASSOC | \PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE);
            }
            return false;
        } catch ( \PDOException $error) {
            Helper::logErrorAndSendEmail($container, $error, 'ProductsDal::fetchAllProduct');
            throw $error; //THROW BACK TO MAKE PARENT CATCH IT
        } catch (\Exception $error) {
            Helper::logErrorAndSendEmail($container, $error, 'ProductsDal::fetchAllProduct');
            throw $error; //THROW BACK TO MAKE PARENT CATCH IT
        }
    }

    /**
     * fetch a single product for a given SKU
     *
     * @param Container $container
     * @param $sku
     * @return array|bool
     * @throws \Exception
     */
    public static function getProductBySku(Container $container, $sku)
    {
        /** @var \PDO $conn_handler */
        $conn_handler = null;
        try {
            $conn_handler = $container['connection'];
            $statement_handler = $conn_handler->prepare(
                " SELECT  p.sku
                                    p.prod_name,
                                    p.price
                             FROM " . ConstantList::DB_TABLE_PRODUCT . " p
                            ORDER by p.sku");
            $statement_handler->execute();
            if($statement_handler->rowCount() > 0) {//or might have used columnCount()
                return $statement_handler->fetchAll();
            }
            return false;
        } catch ( \PDOException $error) {
            Helper::logErrorAndSendEmail($container, $error, 'ProductsDal::fetchAllProduct');
            throw $error; //THROW BACK TO MAKE PARENT CATCH IT
        } catch (\Exception $error) {
            Helper::logErrorAndSendEmail($container, $error, 'ProductsDal::fetchAllProduct');
            throw $error; //THROW BACK TO MAKE PARENT CATCH IT
        }
    }

    /**
     * Fetch all products by Pagination
     *
     * @param Container $container
     * @param int $start_page_number
     * @param int $page_display_size
     * @return array|bool
     * @throws \Exception
     */
    public static function getProductList(Container $container, $start_page_number=0, $page_display_size=10)
    {
        /** @var \PDO $conn_handler */
        $conn_handler = null;
        try {
            $conn_handler = $container['connection'];
            $sql = "SELECT
                        sku,
                        prod_name,
                        price,
                        date_created
                    FROM " . ConstantList::DB_TABLE_PRODUCT . " o
                    ORDER BY o.date_created DESC
                    LIMIT :start_page_number, :page_display_size
            ";

            $statement_handler = $conn_handler->prepare($sql);
            /* Bind all INT values first, as the execute() will bind in a string fashion for int as well */
            $statement_handler->bindValue(':start_page_number', intval($start_page_number), \PDO::PARAM_INT);
            $statement_handler->bindValue(':page_display_size', intval($page_display_size), \PDO::PARAM_INT);
            $statement_handler->execute();
            if($statement_handler->rowCount() > 0) {//or might have used columnCount()
                return $statement_handler->fetchAll();
            }
            return false;
        } catch( \PDOException $error) {
            Helper::logErrorAndSendEmail($container, $error, 'ProductsDal::getProductList');
            throw $error; //THROW BACK TO MAKE PARENT CATCH IT
        } catch(\Exception $error) {
            Helper::logErrorAndSendEmail($container, $error, 'ProductsDal::getProductList');
            throw $error; //THROW BACK TO MAKE PARENT CATCH IT
        }
    }

    /**
     * Fetch all pricing rules by Pagination
     *
     * @param Container $container
     * @param int $start_page_number
     * @param int $page_display_size
     * @return array|bool
     * @throws \Exception
     */
    public static function getRuleList(Container $container, $start_page_number=0, $page_display_size=10)
    {
        /** @var \PDO $conn_handler */
        $conn_handler = null;
        try {
            $conn_handler = $container['connection'];
            $sql = "SELECT
                        sku,
                        product_occurrence,
                        promo_price,
                        date_created
                    FROM " . ConstantList::DB_TABLE_PRICING_RULES . " o
                    ORDER BY o.date_created DESC
                    LIMIT :start_page_number, :page_display_size
            ";

            $statement_handler = $conn_handler->prepare($sql);
            /* Bind all INT values first, as the execute() will bind in a string fashion for int as well */
            $statement_handler->bindValue(':start_page_number', intval($start_page_number), \PDO::PARAM_INT);
            $statement_handler->bindValue(':page_display_size', intval($page_display_size), \PDO::PARAM_INT);
            $statement_handler->execute();
            if($statement_handler->rowCount() > 0) {//or might have used columnCount()
                return $statement_handler->fetchAll();
            }
            return false;
        } catch( \PDOException $error) {
            Helper::logErrorAndSendEmail($container, $error, 'ProductsDal::getRuleList');
            throw $error; //THROW BACK TO MAKE PARENT CATCH IT
        } catch(\Exception $error) {
            Helper::logErrorAndSendEmail($container, $error, 'ProductsDal::getRuleList');
            throw $error; //THROW BACK TO MAKE PARENT CATCH IT
        }
    }

    /**
     * Get products count
     *
     * @param Container $container
     * @return int
     * @throws \Exception
     */
    public static function getProductsCount(Container $container)
    {
        /** @var \PDO $conn_handler */
        $conn_handler = null;
        try {
            $conn_handler = $container['connection'];
            $sql = "SELECT
                        COUNT(*) as record_count
                    FROM " . ConstantList::DB_TABLE_PRODUCT . "
            ";
            $statement_handler = $conn_handler->prepare($sql);
            $statement_handler->execute();
            if($statement_handler->rowCount() > 0) {
                return (int)$statement_handler->fetchObject()->record_count;
            }
            return -1;
        } catch( \PDOException $error) {
            Helper::logErrorAndSendEmail($container, $error, 'ProductsDal::getProductsCount');
            throw $error; //THROW BACK TO MAKE PARENT CATCH IT
        } catch(\Exception $error) {
            Helper::logErrorAndSendEmail($container, $error, 'ProductsDal::getProductsCount');
            throw $error; //THROW BACK TO MAKE PARENT CATCH IT
        }
    }
}
