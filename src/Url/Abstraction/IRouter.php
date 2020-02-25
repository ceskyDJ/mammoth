<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Url\Abstraction;

use Mammoth\Url\Entity\ParsedUrl;

/**
 * Router for changing URLs and refreshing
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Routing\Abstraction
 */
interface IRouter
{
    /**
     * Redirects to some in-application URL
     *
     * @param \Mammoth\Url\Entity\ParsedUrl $newParsedUrl Edited Parsed URL object
     * @param string|null $jsData Data for JavaScript (after #)
     */
    public function route(ParsedUrl $newParsedUrl, ?string $jsData = null): void;

    /**
     * Redirects to error page for page not found error (404)
     *
     * @param \Mammoth\Url\Entity\ParsedUrl $parsedUrl Parsed URL (for getting lang)
     */
    public function routeToNotFound(ParsedUrl $parsedUrl): void;

    /**
     * Redirects to error page for forbidden error (403)
     *
     * @param \Mammoth\Url\Entity\ParsedUrl $parsedUrl Parsed URL (for getting lang)
     */
    public function routeToForbidden(ParsedUrl $parsedUrl): void;

    /**
     * Redirects to error page for system error (500)
     * This error page is used for any errors on the system side,
     * that doesn't have their own code
     *
     * @param \Mammoth\Url\Entity\ParsedUrl $parsedUrl Parsed URL (for getting lang)
     */
    public function routeToSystemError(ParsedUrl $parsedUrl): void;

    /**
     * Redirects to login page
     * It loads login page from config files, so you can change it there
     *
     * @param \Mammoth\Url\Entity\ParsedUrl $parsedUrl ParsedURL (for getting lang)
     */
    public function routeToLoginPage(ParsedUrl $parsedUrl): void;

    /**
     * Changes application language (redirect to URL with the new language)
     *
     * @param \Mammoth\Url\Entity\ParsedUrl $oldParsedUrl Parsed URL (old)
     * @param string $newLang New language
     *
     * @throws \Mammoth\Exceptions\InvalidLangException Invalid language
     */
    public function changeLang(ParsedUrl $oldParsedUrl, string $newLang): void;

    /**
     * Refreshes a page for user
     */
    public function refresh(): void;
}