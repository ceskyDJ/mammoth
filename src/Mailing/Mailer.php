<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Mailing;

use Mammoth\Exceptions\MailerException;
use Mammoth\Config\Configurator;
use Mammoth\DI\DIClass;
use Mammoth\Http\Entity\Server;
use Mammoth\Mailing\Abstraction\IMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use function count;
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
    private Configurator $configurator;
    /**
     * @inject
     */
    private Server $server;

    /**
     * @var \PHPMailer\PHPMailer\PHPMailer PHPMailer's instance
     */
    private PHPMailer $phpMailer;

    /**
     * Mailer constructor
     */
    public function __construct()
    {
        $this->phpMailer = new PHPMailer(true);
    }

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
    ): void {
        $config = $this->configurator->getMailConfig();

        try {
            // Server config
            // For testing: $this->phpMailer->SMTPDebug = 4;
            $this->phpMailer->SMTPDebug = 0;
            $this->phpMailer->isSMTP();
            $this->phpMailer->Host = $config['host'];
            $this->phpMailer->SMTPAuth = true;
            $this->phpMailer->Username = $config['username'];
            $this->phpMailer->Password = $config['password'];
            $this->phpMailer->SMTPSecure = $config['secure-type'];
            $this->phpMailer->Port = $config['port'];
            $this->phpMailer->SMTPSecure = true;
            $this->phpMailer->SMTPAutoTLS = true;
            $this->phpMailer->SMTPOptions = [
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true,
                ],
            ];

            // Message config
            $this->phpMailer->CharSet = "UTF-8";

            // Recipients
            $this->phpMailer->setFrom($senderAddress, $senderName);
            foreach ($recipients as $recipient) {
                if (isset($recipient['name'])) {
                    $this->phpMailer->addAddress($recipient['address'], $recipient['name']);
                } else {
                    $this->phpMailer->addAddress($recipient['address']);
                }
            }
            $this->phpMailer->addReplyTo($senderAddress, $senderName);

            // Attachments
            if (count($attachments) > 0) {
                foreach ($attachments as $attachment) {
                    if (isset($attachment['name'])) {
                        $this->phpMailer->addAttachment($attachment['path'], $attachment['name']);
                    } else {
                        $this->phpMailer->addAttachment($attachment['path']);
                    }
                }
            }

            // Content
            $this->phpMailer->isHTML(true);
            $this->phpMailer->Subject = $subject;
            $this->phpMailer->Body = $htmlMessage;
            if ($textMessage != "") {
                $this->phpMailer->AltBody = $textMessage;
            } else {
                $this->phpMailer->AltBody = strip_tags($htmlMessage);
            }

            $this->phpMailer->send();
        } catch (Exception $e) {
            throw new MailerException("Sending email has ended with error: ".$this->phpMailer->ErrorInfo);
        }
    }
}
