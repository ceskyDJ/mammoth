<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Mailing\Abstraction;

/**
 * Mailer - model for better mailing
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Mailing\Abstraction
 */
interface IMailer
{
    /**
     * Sends mail
     *
     * @param string $senderName Sender's name
     * @param string $senderAddress Sender's email
     * @param array $recipients Recipients (syntax: ['address' => "", 'name' => ""], address is required)
     * @param string $subject Subject
     * @param string $htmlMessage Message in HTML format
     * @param string $textMessage Message in text raw format
     * @param array $attachments Attachments (syntax: ['path', 'name'], path is required)
     */
    public function sendMail(
        string $senderName,
        string $senderAddress,
        array $recipients,
        string $subject,
        string $htmlMessage,
        string $textMessage = "",
        array $attachments = []
    ): void;
}