<?php
/**
 * This will create a connection Object for PDO only
 * If you want a more generic approach, Zend DB seems a good fit, see: https://github.com/zendframework/zend-db
 *
 * But I prefer to go without such a wrapper, as I like writing my SQL queries - besides it helps me stay current & learn my SQL dialects
 *
 * @author Khayrattee Wasseem <wasseem@khayrattee.com>
 * @copyright Copyright (c) 2020 Wasseem Khayrattee
 * @license GPL-3.0
 * @link https://7php.com (website)
 */
namespace Model;

use Pimple\Container;

/**
 * Class PdoConnection
 * @package Model
 */
class PdoConnection implements ConnectionInterface
{
    private $host;
    private $db_name;
    private $db_user;
    private $db_password;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->host         = $container['config']['db.host'];
        $this->db_name      = $container['config']['db.name'];
        $this->db_user      = $container['config']['db.username'];
        $this->db_password  = $container['config']['db.password'];
    }

    /**
     * @return null|\PDO
     */
    public function getConnection()
    {
        $connectionObj = null;
        try {
            $connectionObj = new  \PDO('mysql:host=' . $this->host . ';dbname=' . $this->db_name, $this->db_user, $this->db_password);
            $connectionObj->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION); //Throw a PDOException if an error occurs
            $connectionObj->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC); //Throw a PDOException if an error occurs
            $connectionObj->setAttribute(\PDO::ATTR_AUTOCOMMIT, 0); //If this value is FALSE, PDO attempts to disable autocommit so that the connection begins a transaction.

        } catch (\PDOException $e) {
            throw $e;
        }
        return $connectionObj;
    }
}
