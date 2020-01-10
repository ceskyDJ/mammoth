<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Http\Entity;

use Mammoth\Exceptions\NonExistingKeyException;
use function array_shift;
use function count;
use function is_array;

/**
 * Object of super global array _SESSION
 *
 * @author Michal ŠMAHEL (ceskyDJ)
 * @package Mammoth\Http\Entity
 */
final class Session
{
    /**
     * @var array session Data stored on server
     */
    private array $session;

    /**
     * Session constructor
     *
     * @param array $session
     */
    public function __construct(array &$session)
    {
        $this->session = &$session;
    }

    /**
     * Getter for session
     *
     * @return array
     */
    public function getSession(): array
    {
        return $this->session;
    }

    /**
     * Returns value from SESSION by its key
     *
     * @param string $key Key
     *
     * @return string|string[] Values saved with the key
     * @throws \Mammoth\Exceptions\NonExistingKeyException Non existing key in SESSION
     */
    public function getSessionItemByKey(string $key)
    {
        if (!isset($this->session[$key])) {
            throw new NonExistingKeyException("There's no session with key '{$key}'");
        }

        return $this->session[$key];
    }

    /**
     * Returns history of visited URL addresses
     * There're the last 5 visited URL addresses in the history
     *
     * @return array History of visited URL addresses
     */
    public function getUrlHistory(): array
    {
        return $this->session['url-history'];
    }

    /**
     * Sets value to the SESSION key
     *
     * @param string $key Key
     * @param string $value New value for the key
     */
    public function setSessionItem(string $key, string $value): void
    {
        $this->session[$key] = $value;
    }

    /**
     * Adds value to array of some SESSION key
     * If the count of elements in the array is over limit,
     * the oldest element (the first one) will be deleted
     *
     * @param string $key SESSION key
     * @param mixed $item Element for adding
     * @param int $limit Maximum number of elements
     */
    public function pushItemToSessionItemArray(string $key, $item, int $limit = 0): void
    {
        // Creating an array in empty key
        if (!isset($this->session[$key]) || !is_array($this->session[$key])) {
            $this->session[$key] = [];
        }

        // If the count of array elements is AT the limit, the oldest element will be deleted
        if ($limit > 0 && count($this->session[$key]) === $limit) {
            array_shift($this->session[$key]);
        }

        // If the count of array elements is OVER the limit, array will be destroyed
        if ($limit > 0 && count($this->session[$key]) > $limit) {
            $this->deleteSessionItem($key);
        }

        $this->session[$key][] = $item;
    }

    /**
     * Deletes the SESSION key
     *
     * @param string $key Key
     */
    public function deleteSessionItem(string $key): void
    {
        unset($this->session[$key]);
    }
}