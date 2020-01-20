<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Database;

use Mammoth\DI\DIClass;
use Nette\Database\Connection;
use PDO;
use PDOException;

/**
 * DB wrapper
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Templates
 */
class DB extends Connection
{
    use DIClass;

    /**
     * PDO config
     */
    private const PDO_CONFIG = [
        // Allow exceptions
        PDO::ATTR_ERRMODE          => PDO::ERRMODE_EXCEPTION,
        // Turn off MySQL query cache
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    /**
     * DB construct
     *
     * @param string $host Host's address (DB server's)
     * @param string $database DB name
     * @param string $userName User with access to DB
     * @param string $userPassword User's password
     */
    public function __construct(string $host, string $database, string $userName, string $userPassword)
    {
        parent::__construct(
            "mysql:host={$host};dbname={$database};charset=utf8",
            $userName,
            $userPassword,
            self::PDO_CONFIG
        );
    }

    /**
     * Executes a query with expecting more return rows
     *
     * @param $query string SQL query
     * @param $params array Params (question marks replace)
     *
     * @return array 2 dimensional array with return values
     */
    public function moreResults(string $query, ...$params): array
    {
        return parent::fetchAll($query, $params);
    }

    /**
     * Executes a query without expecting any return
     *
     * @param $query string SQL query
     * @param $params array Params (question marks replace)
     */
    public function withoutResult(string $query, ...$params): void
    {
        parent::query($query, $params);
    }

    /**
     * Executes a query with expecting one return value
     *
     * @param string $query SQL query
     * @param array $params Params (question mark replace)
     *
     * @return mixed Value from DB
     * @throws PDOException No result found
     */
    public function oneValue(string $query, ...$params)
    {
        if (($value = parent::fetchField($query, $params)) !== null) {
            return $value;
        } else {
            throw new PDOException("No value was found");
        }
    }

    /**
     * Executes a query with expecting one return row
     *
     * @param $query string SQL query
     * @param $params array Params (question marks replace)
     *
     * @return array 1 dimensional array with return values
     */
    public function oneResult(string $query, ...$params): array
    {
        if (($data = parent::fetch($query, $params)) !== null) {
            return (array)$data;
        } else {
            throw new PDOException("No data was found");
        }
    }

    /**
     * Returns last insert ID
     *
     * @return int Last insert ID
     */
    public function getLastId(): int
    {
        return (int)parent::getInsertId();
    }

    /**
     * Returns PDO instance
     *
     * @return \PDO Connection instance
     */
    public function getPDOInstance(): PDO
    {
        return parent::getPdo();
    }
}
