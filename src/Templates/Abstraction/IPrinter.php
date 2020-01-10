<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Templates\Abstraction;

use Mammoth\Http\Entity\Response;

/**
 * Views and output data manager
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Templates\Abstraction
 */
interface IPrinter
{
    /**
     * Writes template content
     *
     * @param \Mammoth\Http\Entity\Response response Data for constructing response for client
     */

    public function writeContent(Response $response): void;
}