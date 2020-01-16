<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Http\Entity;

use Mammoth\Exceptions\NonExistingKeyException;

/**
 * Object of super global array _SERVER
 *
 * @author Michal ŠMAHEL (ceskyDJ)
 * @package Mammoth\Http\Entity
 */
final class Server
{
    /**
     * @var array serverData Server data
     */
    private array $serverData;

    /**
     * Server constructor
     *
     * @param array $serverData
     */
    public function __construct(array $serverData)
    {
        $this->serverData = $serverData;
    }

    /**
     * Getter for serverData
     *
     * @return array
     */
    public function getServerData(): array
    {
        return $this->serverData;
    }

    /**
     * Returns variable from server data
     *
     * @param string $name Variable name
     *
     * @return mixed Variable value
     * @throws \Mammoth\Exceptions\NonExistingKeyException Non existing variable
     */
    public function getServerVar(string $name)
    {
        if (!isset($this->serverData[$name])) {
            throw new NonExistingKeyException("System var '{$name}' not exists");
        }

        return $this->serverData[$name];
    }

    /**
     * Returns actually using protocol
     *
     * @return string Using protocol (http or https)
     */
    public function getProtocol(): string
    {
        return $this->isActiveHttps() ? "https" : "http";
    }

    /**
     * Find out if secure transfer (HTTP protocol) is active
     *
     * @return bool Is secure transfer active?
     */
    public function isActiveHttps()
    {
        return (!empty($this->serverData['HTTPS']) && $this->serverData['HTTPS'] != "off");
    }

    /**
     * Returns string with accepted languages by user's browser
     *
     * @return string String with accepted languages
     */
    public function getAcceptedLangString(): string
    {
        return $this->serverData['HTTP_ACCEPT_LANGUAGE'];
    }

    /**
     * Returns using domain
     *
     * @return string Domain name (fully qualified name)
     */
    public function getDomain(): string
    {
        return $this->serverData['SERVER_NAME'];
    }

    /**
     * Returns user's IP address
     *
     * @return string IP address
     */
    public function getUserIP(): string
    {
        return $this->serverData['REMOTE_ADDR'];
    }

    /**
     * Returns identification string of user's browser
     *
     * @return string Browser's identification string
     */
    public function getUserBrowserString(): string
    {
        return $this->serverData['HTTP_USER_AGENT'];
    }

    /**
     * Returns currently requested URL address
     *
     * @return string Requested URL address (without domain and protocol)
     */
    public function getUrl(): string
    {
        return $this->serverData['REQUEST_URI'];
    }

    /**
     * Returns base href for <base /> tag
     *
     * @return string Href attribute content of <base> tag
     * @noinspection PhpDocMissingThrowsInspection It cannot occur (typed manually)
     */
    public function getBaseHref(): string
    {
        /**
         * @noinspection PhpUnhandledExceptionInspection It's OK - typed manually
         */
        return "{$this->getProtocol()}://{$this->getServerVar("SERVER_NAME")}/";
    }
}