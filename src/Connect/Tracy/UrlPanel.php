<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Connect\Tracy;

use Mammoth\Config\Configurator;
use Mammoth\Controller\Common\Controller;
use Mammoth\Http\Entity\Request;
use Mammoth\Templates\Abstraction\IPrinter;
use ReflectionObject;
use Tracy\IBarPanel;
use function implode;
use function is_array;
use function str_replace;

/**
 * URL (Request, ParsedUrl, selected Controller, ...) panel for Tracy debugger
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Connect\Tracy
 */
class UrlPanel implements IBarPanel
{
    private IPrinter $printer;
    private Configurator $configurator;

    /**
     * @var \Mammoth\Http\Entity\Request|null Request object
     */
    private ?Request $request;
    /**
     * @var \Mammoth\Controller\Common\Controller|null Active controller
     */
    private ?Controller $controller;

    /**
     * UrlPanel constructor
     *
     * @param \Mammoth\Templates\Abstraction\IPrinter $printer
     * @param \Mammoth\Config\Configurator $configurator
     * @param \Mammoth\Http\Entity\Request|null $request
     */
    public function __construct(IPrinter $printer, Configurator $configurator, ?Request $request)
    {
        $this->printer = $printer;
        $this->configurator = $configurator;
        $this->request = $request;
    }

    /**
     * @inheritDoc
     */
    public function getTab()
    {
        if ($this->request !== null) {
            $data = [
                'activate'  => true,
                'parsedUrl' => $this->request->getParsedUrl(),
            ];
        } else {
            $data = [
                'activate' => false,
            ];
        }

        return $this->printer->getFileHTML(__DIR__."/templates/url-panel-tab.latte", $data);
    }

    /**
     * @inheritDoc
     */
    public function getPanel()
    {
        if ($this->request === null) {
            return null;
        }

        $parsedUrl = $this->request->getParsedUrl();

        $route = $this->configurator->getRoute();
        $route = str_replace("%", "", $route);
        $route = str_replace("language", "lang", $route);

        if ($this->controller !== null) {
            $controllerReflection = new ReflectionObject($this->controller);

            $controllerFile = $controllerReflection->getFileName();
            $controllerNamespace = $controllerReflection->getName();
        }

        $data = [
            'parsedUrl'      => $parsedUrl,
            'url'            => $this->request->getUrl(),
            'previousUrl'    => $this->request->getPreviousUrl(),
            'route'          => $route,
            'parsedData'     => (is_array($parsedUrl->getData()) ? implode("/", $parsedUrl->getData()) : null),
            'controller'     => $controllerNamespace ?? null,
            'controllerPath' => $controllerFile ?? null,
        ];

        return $this->printer->getFileHTML(__DIR__."/templates/url-panel-content.latte", $data);
    }

    /**
     * Setter for controller
     *
     * @param \Mammoth\Controller\Common\Controller|null $controller
     */
    public function setController(?Controller $controller): void
    {
        $this->controller = $controller;
    }
}