<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Url;

use Mammoth\Common\DIClass;
use Mammoth\Config\Configurator;
use Mammoth\Exceptions\ApplicationNotUseComponentsException;
use Mammoth\Exceptions\InvalidLangException;
use Mammoth\Http\Entity\Server;
use Mammoth\Translating\Abstraction\ITranslateManager;
use Mammoth\Url\Abstraction\IRouter;
use Mammoth\Url\Abstraction\IUrlManager;
use Mammoth\Url\Entity\ParsedUrl;
use function header;
use function str_replace;

/**
 * Router for changing URLs and refreshing
 *
 * @author Michal ŠMAHEL (ceskyDJ)
 * @package Mammoth\Routing
 */
class Router implements IRouter
{
    use DIClass;

    /**
     * @inject
     */
    private ITranslateManager $translateManager;
    /**
     * @inject
     */
    private Server $server;
    /**
     * @inject
     */
    private Configurator $configurator;
    /**
     * @inject
     */
    private IUrlManager $urlManager;

    /**
     * @inheritDoc
     */
    public function refresh(): void
    {
        header("Refresh:0");
        header("Connection: close");
        exit;
    }

    /**
     * @inheritDoc
     */
    public function changeLang(ParsedUrl $oldParsedUrl, string $newLang): void
    {
        if (!$this->translateManager->isValidLang($newLang)) {
            throw new InvalidLangException("Zadaný jazyk je neplatný.");
        }

        // Change to the same language doesn't make sense
        if ($oldParsedUrl->getLanguage() !== null && $oldParsedUrl->getLanguage() === $newLang) {
            return;
        }

        $this->route($oldParsedUrl->setLanguage($newLang));
    }

    /**
     * @inheritDoc
     */
    public function route(ParsedUrl $newParsedUrl, ?string $jsData = null): void
    {
        // Address construction
        $address = ($this->server->isActiveHttps() ? "https" : "http")."://{$this->server->getDomain()}";

        // Get target address (after repairing etc.)
        $address .= "/".$this->urlManager->getResultAddress(
                $this->urlManager->constructAddressFromParsedUrl(
                    $newParsedUrl
                )
            );

        // Add JavaScript data
        if ($jsData !== null) {
            $jsData = str_replace("#", "", $jsData); // # could be in parameter
            $address .= "#{$jsData}";
        }

        header("Location: $address");
        header("Connection: close");
        exit;
    }

    /**
     * @inheritDoc
     */
    public function routeToNotFound(ParsedUrl $parsedUrl): void
    {
        try {
            // Application with active component system
            $parsedUrl->setComponent($this->configurator->getAppDefaultComponent());
        } catch (ApplicationNotUseComponentsException $e) {
            // Application without component system
            // Preventive setting null (repair some error cases)
            $parsedUrl->setComponent(null);
        }

        $parsedUrl->setController("error")
            ->setData([404]);

        $this->route($parsedUrl);
    }

    /**
     * @inheritDoc
     */
    public function routeToForbidden(ParsedUrl $parsedUrl): void
    {
        try {
            // Application with active component system
            $parsedUrl->setComponent($this->configurator->getAppDefaultComponent());
        } catch (ApplicationNotUseComponentsException $e) {
            // Application without component system
            // Preventive setting null (repair some error cases)
            $parsedUrl->setComponent(null);
        }

        $parsedUrl->setController("error")
            ->setData([403]);

        $this->route($parsedUrl);
    }

    /**
     * @inheritDoc
     */
    public function routeToSystemError(ParsedUrl $parsedUrl): void
    {
        try {
            // Application with active component system
            $parsedUrl->setComponent($this->configurator->getAppDefaultComponent());
        } catch (ApplicationNotUseComponentsException $e) {
            // Application without component system
            // Preventive setting null (repair some error cases)
            $parsedUrl->setComponent(null);
        }

        $parsedUrl->setController("error")
            ->setData([500]);

        $this->route($parsedUrl);
    }
}