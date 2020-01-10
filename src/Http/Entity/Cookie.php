<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Http\Entity;

use Mammoth\Exceptions\NonExistingKeyException;

/**
 * Object of super global array _COOKIE
 *
 * @author Michal ŠMAHEL (ceskyDJ)
 * @package Mammoth\Http\Entity
 */
final class Cookie
{
    /**
     * @var array cookies Data stored at user's computer
     */
    private array $cookies;

    /**
     * Cookie constructor
     *
     * @param array $cookies
     */
    public function __construct(array $cookies)
    {
        $this->cookies = $cookies;
    }

    /**
     * Getter for cookies
     *
     * @return array
     */
    public function getCookies(): array
    {
        return $this->cookies;
    }

    /**
     * Verifies cookies approve
     *
     * @return bool Are cookies approved?
     */
    public function isCookiesApproved(): bool
    {
        try {
            return ((bool)$this->getCookieByName('cookies-ok'));
        } catch (NonExistingKeyException $e) {
            return false;
        }
    }

    /**
     * Gets cookies by name
     *
     * @param string $name Cookie name
     *
     * @return string|string[] Cookie value
     * @throws \Mammoth\Exceptions\NonExistingKeyException Non existing cookie
     */
    public function getCookieByName(string $name)
    {
        if (!isset($this->cookies[$name])) {
            throw new NonExistingKeyException("There's no cookie with name '{$name}'");
        }

        return (string)$this->cookies[$name];
    }

    /**
     * Deletes cookie
     *
     * @param string $name Cookie name
     */
    public function deleteCookie(string $name): void
    {
        unset($this->cookies[$name]);
        $this->setCookie($name, "", -1);
    }

    /**
     * Sets (adds or edits) cookie
     *
     * @param string $name Name
     * @param string $value Value
     * @param int $expiresIn Expiration time in UNIX format or 0 for auto canceling with browser closing
     * @param string $path Valid path
     * @param string $domain Valid domain
     * @param bool $secure Allow only secure transfer? (over HTTPS)
     * @param bool $httpOnly Disallow JavaScript access?
     */
    public function setCookie(
        string $name,
        string $value,
        int $expiresIn = 0,
        string $path = "/",
        string $domain = "",
        bool $secure = true,
        bool $httpOnly = true
    ): void {
        setcookie($name, $value, $expiresIn, $path, $domain, $secure, $httpOnly);
    }
}