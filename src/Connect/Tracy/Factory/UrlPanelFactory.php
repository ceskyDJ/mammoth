<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Connect\Tracy\Factory;

use Mammoth\Config\Configurator;
use Mammoth\Connect\Tracy\UrlPanel;
use Mammoth\Http\Entity\Request;
use Mammoth\Templates\Abstraction\IPrinter;

/**
 * Factory for creating UrlPanel instances
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Connect\Tracy\Factory
 */
class UrlPanelFactory
{
    /**
     * @inject
     */
    private IPrinter $printer;
    /**
     * @inject
     */
    private Configurator $configurator;

    /**
     * Creates UrlPanel object
     *
     * @param \Mammoth\Http\Entity\Request|null $request Request object for getting some data
     *
     * @return \Mammoth\Connect\Tracy\UrlPanel Instance of UrlPanel class
     */
    public function create(?Request $request): UrlPanel
    {
        return new UrlPanel($this->printer, $this->configurator, $request);
    }
}