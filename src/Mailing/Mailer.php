<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Mailing;

use Mammoth\Config\Configurator;
use Mammoth\DI\DIClass;
use Mammoth\Mailing\Abstraction\IMailer;
use Nette\Mail\Message;
use Nette\Mail\SmtpMailer;
use function file_get_contents;
use function strip_tags;

/**
 * Mail manager
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Mailing
 */
class Mailer implements IMailer
{
    use DIClass;

    /**
     * @inject
     */
    private SmtpMailer $mailer;

    /**
     * @inheritDoc
     *
     * <strong>If there is no content in $textMessage, it'll be generated from $htmlMessage by removing HTML
     *     tags</strong>
     */
    public function sendMail(
        string $senderName,
        string $senderAddress,
        array $recipients,
        string $subject,
        string $htmlMessage,
        string $textMessage = "",
        array $attachments = []
    ): void
    {
        $message = new Message;

        // Sender
        $message->setFrom($senderAddress, $senderName);
        $message->addReplyTo($senderAddress, $senderName);

        // Recipients
        foreach ($recipients as $recipient) {
            if (isset($recipient['name'])) {
                $message->addTo($recipient['address'], $recipient['name']);
            } else {
                $message->addTo($recipient['address']);
            }
        }

        // Attachments
        foreach ($attachments as $attachment) {
            if (isset($attachment['name'])) {
                $message->addAttachment($attachment['name'], file_get_contents($attachment['path']));
            } else {
                $message->addAttachment($attachment['path']);
            }
        }

        // Content
        $message->setSubject($subject);
        $message->setHtmlBody($htmlMessage);
        if ($textMessage === "") {
            strip_tags($htmlMessage);
        }
        $message->setBody($textMessage);

        // Send
        $this->mailer->send($message);
    }
}
