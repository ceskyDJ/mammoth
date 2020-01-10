<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Url\Abstraction;

use Mammoth\Url\Entity\ParsedUrl;

/**
 * URL routes manager (helps system to recognize URL, translate Parsed Url object to address and more)
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Security\Abstraction
 */
interface IUrlManager
{
    /**
     * Parses URL into ParsedUrl object
     *
     * @param string $url Short type of URL (everything after domain name)
     *
     * @return \Mammoth\Url\Entity\ParsedUrl URL as a object
     */
    public function parseUrl(string $url): ParsedUrl;

    /**
     * Simulate system URL parsing (include repairing) and returns output URL address
     *
     * @param string $startAddress Input URL address
     *
     * @return string Output URL address
     */
    public function getResultAddress(string $startAddress): string;

    /**
     * Constructs address from parsed URL
     *
     * @param \Mammoth\Url\Entity\ParsedUrl $parsedUrl Parsed URL object
     *
     * @return string URL address (without protocol, domain) in string form (from data stored in ParsedUrl)
     */
    public function constructAddressFromParsedUrl(ParsedUrl $parsedUrl): string;
}