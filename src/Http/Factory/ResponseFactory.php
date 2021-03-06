<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Http\Factory;

use Mammoth\DI\DIClass;
use Mammoth\Http\Entity\Cookie;
use Mammoth\Http\Entity\Request;
use Mammoth\Http\Entity\Response;
use Mammoth\Http\Entity\Server;
use Mammoth\Http\Entity\Session;
use Mammoth\Security\Abstraction\IUserManager;
use Mammoth\Templates\Abstraction\IMessageManager;

/**
 * Factory for creating Response instance
 *
 * @author Michal ŠMAHEL (ceskyDJ)
 * @package Mammoth\Http\Factory
 */
class ResponseFactory
{
    use DIClass;

    /**
     * @inject
     */
    private IMessageManager $messageManager;
    /**
     * @inject
     */
    private IUserManager $userManager;
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
            $request, $this->messageManager, $this->userManager, $this->session, $this->cookie, $this->server
        );
    }
}