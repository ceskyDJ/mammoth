<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Http\Factory;

use Mammoth\DI\DIClass;
use Mammoth\Http\Entity\Request;
use Mammoth\Http\Entity\Server;
use Mammoth\Http\Entity\Session;
use Mammoth\Url\UrlManager;

/**
 * Factory for creating Request instances
 *
 * @author Michal ŠMAHEL (ceskyDJ)
 * @package Mammoth\Http\Factory
 */
class RequestFactory
{
    use DIClass;

    /**
     * @inject
     */
    private Session $session;
    /**
     * @inject
     */
    private Server $server;
    /**
     * @inject
     */
    private UrlManager $urlManager;

    /**
     * Creates Request instance
     *
     * @return \Mammoth\Http\Entity\Request
     */
    public function create(): Request
    {
        return new Request(
            $this->session, $this->server, $this->urlManager, $_POST, $_GET, $_FILES
        );
    }
}