<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Base;

use Mammoth\Common\DIClass;
use Mammoth\Config\Configurator;
use Mammoth\Controller\Common\Controller;
use Mammoth\DI\DIContainer;
use Mammoth\Exceptions\InsufficientPermissionsException;
use Mammoth\Exceptions\InvalidLangException;
use Mammoth\Exceptions\NonExistingKeyException;
use Mammoth\Http\Entity\Cookie;
use Mammoth\Http\Entity\Request;
use Mammoth\Http\Entity\Response;
use Mammoth\Http\Entity\Server;
use Mammoth\Http\Entity\Session;
use Mammoth\Logging\Abstraction\ILogger;
use Mammoth\Security\Abstraction\IPermissionManager;
use Mammoth\Security\Abstraction\IUserManager;
use Mammoth\Templates\Abstraction\IMessageManager;
use Mammoth\Templates\Abstraction\IPrinter;
use Mammoth\Translating\Abstraction\ITranslateManager;
use Mammoth\Url\Abstraction\IRouter;
use Mammoth\Url\Entity\ParsedUrl;
use ReflectionException;
use function date;

/**
 * Main controller for control other ones and load system
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Controller
 */
class SystemController
{
    use DIClass;

    /**
     * @inject
     */
    private DIContainer $container;
    /**
     * @inject
     */
    private Server $server;
    /**
     * @inject
     */
    private Cookie $cookie;
    /**
     * @inject
     */
    private Session $session;
    /**
     * @inject
     */
    private Configurator $configurator;
    /**
     * @inject
     */
    private ILogger $logger;
    /**
     * @inject
     */
    private IRouter $router;
    /**
     * @inject
     */
    private IUserManager $userManager;
    /**
     * @inject
     */
    private IPermissionManager $permissionManager;
    /**
     * @inject
     */
    private IMessageManager $messageManager;
    /**
     * @inject
     */
    private ITranslateManager $translateManager;
    /**
     * @inject
     */
    private IPrinter $printer;

    /**
     * Starts system and controls it
     *
     * @param \Mammoth\Http\Entity\Request $request Request object
     *
     * @noinspection PhpDocMissingThrowsInspection Controller class should be valid (UrlManager guarantees it)
     */
    public function startSystem(Request $request): void
    {
        $parsedUrl = $request->getParsedUrl();

        //        bdump($parsedUrl);

        // If application uses component system -> component
        // If not -> null
        $component = $parsedUrl->getComponent();

        // Verify access to component (if application uses components)
        if ($component !== null) {
            if ($this->verifyAccessToComponent($component) === false) {
                $this->solveInsufficientPermissions();
            }
        }

        // Check for user language change
        $this->autoChangeLanguageByUser($request);

        // Bad controller in URL -> route to 404
        if ($parsedUrl->getController() === null) {
            // Set error controller from config and 404 as parameter
            $parsedUrl->setController($this->configurator->getAppErrorControllerName())
                ->setAction("notFound");

            // Verify that application has implemented error controller
            try {
                $this->getController($parsedUrl);
            } catch (ReflectionException $e) {
                // If not, use framework one
                $parsedUrl->setComponent(ParsedUrl::FRAMEWORK_COMPONENT);
            }

            $this->router->route($parsedUrl);
        }

        /**
         * @noinspection PhpUnhandledExceptionInspection Controller class should be valid (UrlManager guarantees it)
         */
        $controller = $this->getController($parsedUrl);
        $action = $parsedUrl->getAction(true);

        // Call controller and get final output (response)
        try {
            /**
             * @var $response Response
             */
            $response = $controller->$action($request);
        } /**
         * @noinspection PhpRedundantCatchClauseInspection Controller can really throw InsufficientPermissionsException
         */ catch (InsufficientPermissionsException $e) {
            $this->solveInsufficientPermissions();

            return;
        }

        // Set layout view to default one, if not set
        if ($response->getLayoutView() === null) {
            $response->setLayoutView("#layout");
        }

        // Add some template vars generated in framework
        $this->addTemplateVarsToResponse($response);

        // Write templates
        $this->printer->writeContent($response);
    }

    /**
     * Verifies user's acces to component (for application uses components only)
     *
     * @param string $component Component for verify
     *
     * @return bool Has user access to the component?
     */
    private function verifyAccessToComponent(string $component): bool
    {
        // TODO: implement this method
        return true;
    }

    /**
     * Resolves insufficient permissions
     * It's called when user hasn't access to component or if controller throws
     * an exception about insufficient permissions to some action
     */
    private function solveInsufficientPermissions(): void
    {
        // TODO: implement this method
    }

    /**
     * Automatically changes language by user's preferences if there is param in GET
     *
     * @param \Mammoth\Http\Entity\Request $request Request object
     */
    private function autoChangeLanguageByUser(Request $request): void
    {
        try {
            $newLang = $request->getGetItemByKey("set-lang");

            try {
                $this->router->changeLang($request->getParsedUrl(), $newLang);
            } catch (InvalidLangException $e) {
                // Invalid lang => nothing to do
            }
        } catch (NonExistingKeyException $e) {
            // Nothing to do
        }
    }

    /**
     * Returns controller instance to call for next processing
     *
     * @param \Mammoth\Url\Entity\ParsedUrl $parsedUrl Parsed URL object for getting some data
     *
     * @return Controller Controller instance
     * @throws \ReflectionException Invalid Controller class
     * @noinspection PhpDocMissingThrowsInspection Controller should be injectable
     */
    private function getController(ParsedUrl $parsedUrl): Controller
    {
        // Root namespace
        if ($parsedUrl->getComponent() !== ParsedUrl::FRAMEWORK_COMPONENT) {
            // Application controller
            $controllerFullQualifiedName = $this->configurator->getAppRootNamespace()."\\Controller";

            // Application uses component system
            if ((($component = $parsedUrl->getComponent(true)) !== null)) {
                $controllerFullQualifiedName .= "\\{$component}";
            }
        } else {
            // Framework controller
            $controllerFullQualifiedName = "Mammoth\\Controller";
        }

        $controllerFullQualifiedName .= "\\{$parsedUrl->getController(true)}";

        /**
         * @var $controller Controller
         * @noinspection PhpUnhandledExceptionInspection Data in ParsedUrl should be valid (UrlManager guarantees it)
         */
        $controller = $this->container->getInstance($controllerFullQualifiedName);

        return $controller;
    }

    /**
     * Adds some template vars provided by framework to Response
     *
     * @param \Mammoth\Http\Entity\Response $response Response
     */
    private function addTemplateVarsToResponse(Response $response): void
    {
        // Add-on for file sources (anti-cache tool)
        if (!$this->configurator->isActualServerDevelopment()) {
            if (($version = $this->configurator->getAppVersion()) !== null) {
                $response->setDataVar("antiCache", "v=".$version);
            } else {
                // Unfortunately there is no version specified in config files
                $response->setDataVar("antiCache", "");
            }
        } else {
            $response->setDataVar("antiCache", "t=".date("d-m-Y-H.m:s"));
        }
    }

    /**
     * Automaticky přihlásí uživatele
     * V COOKIES musí být přítomen platný přihlašovací klíč
     */
    /*private function logInUserAutomatically(): void
    {
        // Ověření existence přihlašovacího klíče
        try {
            $loginKey = $this->cookie->getCookieByName("login-key");
        } catch (NonExistingKeyException $e) {
            return;
        }

        try {
            $this->userManager->loginAutomatically($loginKey);
        } catch (InvalidLoginKeyException $e) {
            // Neplatný přihlašovací klíč
            return;
        }

        // Výměna kódu kvůli bezpečnosti
        $this->userManager->repairLoginKey();
    }*/
}