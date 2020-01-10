<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Http\Entity;

use Mammoth\Exceptions\NonExistingKeyException;
use Mammoth\Url\Entity\ParsedUrl;
use Mammoth\Url\UrlManager;
use function count;
use function explode;
use function is_array;

/**
 * Request sent by client over HTTP(S)
 *
 * @author Michal ŠMAHEL (ceskyDJ)
 * @package Mammoth\Http\Entity
 */
final class Request
{
    /**
     * @var array post Data from POST request
     */
    private array $post;
    /**
     * @var array get Data from GET request
     */
    private array $get;
    /**
     * @var array files File(s) from POST request
     */
    private array $files;

    /**
     * @var string url URL address
     */
    private string $url;
    /**
     * @var \Mammoth\Url\Entity\ParsedUrl parsedUrl Parsed URL address
     * URL data are split into language, component, controller and parameters
     * If some key is null, it's a bad parameter (requires further processing)
     */
    private ParsedUrl $parsedUrl;

    private Session $session;
    private Server $server;
    private UrlManager $urlManager;

    /**
     * Request constructor
     *
     * @param \Mammoth\Http\Entity\Session $session
     * @param \Mammoth\Http\Entity\Server $server
     * @param \Mammoth\Url\UrlManager $urlManager
     * @param array $post
     * @param array $get
     * @param array $files
     */
    public function __construct(
        Session $session,
        Server $server,
        UrlManager $urlManager,
        array &$post,
        array &$get,
        array &$files
    ) {
        $this->session = $session;
        $this->server = $server;
        $this->urlManager = $urlManager;

        $this->server = &$server;
        $this->post = &$post;
        $this->get = &$get;
        $this->files = &$files;

        $this->prepare();
    }

    /**
     * Prepares object data
     */
    private function prepare(): void
    {
        $this->url = $this->server->getUrl();
        $this->addUrlToHistory($this->url);
        $this->parsedUrl = $this->urlManager->parseUrl($this->url);
    }

    /**
     * Adds URL address to history of visited addresses
     *
     * @param string $url URL address
     */
    private function addUrlToHistory(string $url): void
    {
        $this->session->pushItemToSessionItemArray("url-history", $url, 5);
    }

    /**
     * Returns previous visited URL
     *
     * @return string|null Previous URL or null, if there's no one
     */
    public function getPreviousUrl(): ?string
    {
        // Any records haven't been saved so far
        if (!is_array($this->session->getSession()['url-history'])) {
            return null;
        }

        // -1 -> convert to numbering from 0, -1 -> move to penultimate key
        $penultimateUrlKey = count($this->session->getSession()['url-history']) - 2;

        if ($penultimateUrlKey >= 0) {
            return $this->session->getSession()['url-history'][$penultimateUrlKey];
        } else {
            return null;
        }
    }

    /**
     * Getter for server
     *
     * @return \Mammoth\Http\Entity\Server
     */
    public function getServer(): Server
    {
        return $this->server;
    }

    /**
     * Getter for post
     *
     * @return array
     */
    public function getPost(): array
    {
        return $this->post;
    }

    /**
     * Returns value from POST request by its key
     *
     * @param string $key Key
     *
     * @return string|string[] Value saved with the key
     * @throws \Mammoth\Exceptions\NonExistingKeyException Non existing POST key
     */
    public function getPostItemByKey(string $key)
    {
        if (!isset($this->post[$key])) {
            throw new NonExistingKeyException("The key wasn't found in POST request");
        }

        return (string)$this->post[$key];
    }

    /**
     * Getter for get
     *
     * @return array
     */
    public function getGet(): array
    {
        return $this->get;
    }

    /**
     * Returns value from GET request by its key
     *
     * @param string $key Key
     *
     * @return string|string[] Value saved with the key
     * @throws \Mammoth\Exceptions\NonExistingKeyException Non existing GET key
     */
    public function getGetItemByKey(string $key)
    {
        if (!isset($this->get[$key])) {
            throw new NonExistingKeyException("The key wasn't found in POST request");
        }

        return (string)$this->get[$key];
    }

    /**
     * Getter for files
     *
     * @return array
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * Returns concrete file from POST request
     *
     * @param string $name File name
     *
     * @return array File data
     * @throws \Mammoth\Exceptions\NonExistingKeyException File wasn't sent
     */
    public function getFileByName(string $name): array
    {
        if (!isset($this->files[$name])) {
            throw new NonExistingKeyException("File with given name wasn't sent to server");
        }

        return $this->files[$name];
    }

    /**
     * Getter for url
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Getter for parsedUrl
     * Data z URL jsou již přímo rozdělena na jazyk, komponentu, kontroler a parametry
     * Pokud je hodnota null, jde o chybný parametr (je třeba dále zpracovat)
     *
     * @return \Mammoth\Url\Entity\ParsedUrl
     */
    public function getParsedUrl(): ParsedUrl
    {
        return $this->parsedUrl;
    }

    /**
     * Returns "clean URL" - URL without HTTP data of GET request
     *
     * @return string URL without HTTP data of GET request
     */
    public function getCleanUrl(): string
    {
        [$cleanUrl,] = explode("?", $this->url);

        return $cleanUrl;
    }
}