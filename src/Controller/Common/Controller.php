<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Controller\Common;

use Mammoth\Common\DIClass;
use Mammoth\Http\Entity\Request;
use Mammoth\Http\Entity\Response;

/**
 * Parent controller of all specific controllers
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Controller\Common
 */
abstract class Controller
{
    use DIClass;

    /**
     * Processes parameters and does some control stuff
     *
     * @param \Mammoth\Http\Entity\Request $request Instance of HTTP request
     *
     * @return \Mammoth\Http\Entity\Response HTTP response (backward request)
     * @throws \Mammoth\Exceptions\InsufficientPermissionsException Insufficient permissions to some action
     */
    abstract public function defaultAction(Request $request): Response;
}