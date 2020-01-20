<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Templates;

use Mammoth\DI\DIClass;
use Mammoth\Exceptions\NonExistingKeyException;
use Mammoth\Http\Entity\Session;
use Mammoth\Templates\Abstraction\IMessageManager;

/**
 * Manager of messages for user (alerts etc.)
 *
 * @author Michal ŠMAHEL (ceskyDJ)
 * @package Mammoth\Templates
 */
class MessageManager implements IMessageManager
{
    use DIClass;

    /**
     * @inject
     */
    private Session $session;

    /**
     * @inheritDoc
     */
    public function getMessages(): ?array
    {
        try {
            return $this->session->getSessionItemByKey("messages");
        } catch (NonExistingKeyException $e) {
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function addMessage(string $text, string $type = "info"): void
    {
        $message = [
            'text' => $text,
            'type' => $type,
        ];
        $this->session->pushItemToSessionItemArray("messages", $message);
    }

    /**
     * @inheritDoc
     */
    public function dropMessages(): void
    {
        $this->session->deleteSessionItem("messages");
    }
}