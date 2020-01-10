<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Http\Entity;

use Mammoth\Exceptions\NonExistingContentTypeException;
use Mammoth\Templates\Abstraction\IMessageManager;
use Mammoth\Templates\MessageManager;
use function array_merge;
use function header;

/**
 * Server's answer to client's request
 *
 * @author Michal ŠMAHEL (ceskyDJ)
 * @package Mammoth\Http\Entity
 */
final class Response
{
    /**
     * @var string[] head HTML head data
     */
    private array $head;
    /**
     * @var array data Template data
     */
    private array $data;
    /**
     * @var string layoutView Layout template
     */
    private string $layoutView;
    /**
     * @var string contentView Page template
     */
    private string $contentView;

    /**
     * @var \Mammoth\Http\Entity\Request request Original client's request
     */
    private Request $request;

    private IMessageManager $messageManager;
    private Session $session;
    private Cookie $cookie;
    private Server $server;

    /**
     * Response constructor
     *
     * @param \Mammoth\Http\Entity\Request $request
     * @param \Mammoth\Templates\MessageManager $messageManager
     * @param \Mammoth\Http\Entity\Session $session
     * @param \Mammoth\Http\Entity\Cookie $cookie
     * @param \Mammoth\Http\Entity\Server $server
     */
    public function __construct(
        Request $request,
        MessageManager $messageManager,
        Session $session,
        Cookie $cookie,
        Server $server
    ) {
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->session = $session;
        $this->cookie = $cookie;
        $this->server = $server;

        $this->prepare();
    }

    /**
     * Prepares object data
     * Sets the default values for required items
     */
    private function prepare(): void
    {
        $this->setTitle("");
        $this->setDescription("");
        $this->setKeywords("");

        $this->setLayoutView("#layout");
    }

    /**
     * Sets HTML page title
     *
     * @param string $title Page title
     *
     * @return \Mammoth\Http\Entity\Response
     */
    public function setTitle(string $title): Response
    {
        $this->head['title'] = $title;

        return $this;
    }

    /**
     * Sets HTML page description
     *
     * @param string $description Page description
     *
     * @return \Mammoth\Http\Entity\Response
     */
    public function setDescription(string $description): Response
    {
        $this->head['description'] = $description;

        return $this;
    }

    /**
     * Sets HTML page keywords
     *
     * @param string $keywords Page keywords
     *
     * @return \Mammoth\Http\Entity\Response
     */
    public function setKeywords(string $keywords): Response
    {
        $this->head['keywords'] = $keywords;

        return $this;
    }

    /**
     * Sets HTTP state code
     *
     * @param $code int HTTP state code
     */
    public function setHTTPCode(int $code): void
    {
        switch ($code) {
            // Not found
            case 404:
                header("HTTP/1.1 404 Not Found");
                break;
            // Access denied
            case 403:
                header("HTTP/1.1 403 Forbidden");
                break;
            // Internal server error
            case 500: // There's no break; by design - default is error 500
            default:
                header("HTTP/1.1 500 Internal Server Error");
                break;
        }
    }

    /**
     * Sets content type (for generating files etc.)
     *
     * @param string $contentType Content type
     *
     * @throws \Mammoth\Exceptions\NonExistingContentTypeException Invalid content type
     */
    public function setHTTPContentType(string $contentType): void
    {
        // Valid (approved by programmer) content types
        switch ($contentType) {
            case "css":
                header("Content-type: text/css");
                break;
            case "js":
                header("Content-type: text/javascript");
                break;
            default:
                throw new NonExistingContentTypeException("'{$contentType}' is invalid content type.");
        }
    }

    /**
     * Getter for layoutView
     *
     * @return string
     */
    public function getLayoutView(): string
    {
        return $this->layoutView;
    }

    /**
     * Fluent setter for layoutView
     *
     * @param string $layoutView
     *
     * @return Response
     */
    public function setLayoutView(string $layoutView): Response
    {
        $this->layoutView = $layoutView;

        return $this;
    }

    /**
     * Gets data variables for template (include HTML head data)
     *
     * @return array Data vars for template
     */
    public function getDataVarsForTemplate(): array
    {
        // Add system data to template variables
        $this->setDataVar("request", $this->request)
            ->setDataVar("session", $this->session)
            ->setDataVar("cookie", $this->cookie)
            ->setDataVar("server", $this->server);

        // Views for loading
        $this->setDataVar("contentView", $this->getContentView().".latte");

        // Messages for user
        $this->saveMessages();

        // When there's somewhere null instead of array, array_merge returns null as result
        // there is a little fix (if there is null value, replace it with empty array):
        if ($this->head === null) {
            $this->head = [];
        }
        if ($this->data === null) {
            $this->data = [];
        }

        return array_merge($this->head, $this->data);
    }

    /**
     * Adds or edits data variable for template
     *
     * @param string $name Variable name (will be translated to: $name)
     * @param mixed $value Variable value
     *
     * @return \Mammoth\Http\Entity\Response
     */
    public function setDataVar(string $name, $value): Response
    {
        $this->data[$name] = $value;

        return $this;
    }

    /**
     * Getter for contentView
     *
     * @return string
     */
    public function getContentView(): string
    {
        return $this->contentView;
    }

    /**
     * Fluent setter for contentView
     *
     * @param string $contentView
     *
     * @return Response
     */
    public function setContentView(string $contentView): Response
    {
        $this->contentView = $contentView;

        return $this;
    }

    /**
     * Saves messages for user
     */
    private function saveMessages(): void
    {
        $this->setDataVar("messages", $this->messageManager->getMessages());
        $this->messageManager->dropMessages();
    }

    /**
     * Getter for request
     *
     * @return \Mammoth\Http\Entity\Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }
}