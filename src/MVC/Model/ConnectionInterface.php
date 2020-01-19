<?php
/**
 * A connection interface to bind a contract of any object delivering a connection Object
 *
 * @author Khayrattee Wasseem <wasseem@khayrattee.com>
 * @copyright Copyright (c) 2020 Wasseem Khayrattee
 * @license GPL-3.0
 * @link https://7php.com (website)
 */
namespace Model;

/**
 * Interface ConnectionInterface
 * @package Model
 */
interface ConnectionInterface
{
    /** @return \PDO */
    public function getConnection();
}
