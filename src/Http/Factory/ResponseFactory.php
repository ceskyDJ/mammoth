<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Factory;

use Mammoth\Common\DIClass;
use Mammoth\Http\Entity\Cookie;
use Mammoth\Http\Entity\Request;
use Mammoth\Http\Entity\Response;
use Mammoth\Http\Entity\Server;
use Mammoth\Http\Entity\Session;
use Mammoth\Templates\MessageManager;

/**
 * Factory for creating Response instance
 *
 * @author Michal ŠMAHEL (ceskyDJ)
 * @package Mammoth\Factory
 */
class ResponseFactory
{
    use DIClass;

    /**
     * @inject
     */
    private MessageManager $messageManager;
    /**
     * @inject
     */
    private Session $session;
    /**
     * @inject
     */
    private Cookie $cookie;
    /**
     * @inject
     */
    private Server $server;

    /**
     * Creates Response instance
     *
     * @param \Mammoth\Http\Entity\Request $request
     *
     * @return \Mammoth\Http\Entity\Response
     */
    public function create(Request $request): Response
    {
        return new Response(
            $request, $this->messageManager, $this->session, $this->cookie, $this->server
        );
    }
}