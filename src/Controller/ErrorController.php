<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Controller;

use Mammoth\Controller\Common\Controller;
use Mammoth\DI\DIClass;
use Mammoth\Http\Entity\Request;
use Mammoth\Http\Entity\Response;
use Mammoth\Http\Factory\ResponseFactory;

/**
 * Controller for error pages
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Controller
 */
class ErrorController extends Controller
{
    use DIClass;

    /**
     * @inject
     */
    private ResponseFactory $responseFactory;

    /**
     * @inheritDoc
     */
    public function defaultAction(Request $request): Response
    {
        $error = ($request->getParsedUrl()
                ->getData()[0] ?? null);

        // Aliases of actions controlled by this controller
        switch ($error) {
            case 404:
                return $this->notFoundAction($request);
            case 403:
                return $this->accessDeniedAction($request);
            default:
                return $this->systemErrorAction($request);
        }
    }

    /**
     * @see Controller::defaultAction()
     * @noinspection PhpDocSignatureInspection
     */
    public function notFoundAction(Request $request): Response
    {
        $response = $this->responseFactory->create($request);

        return $response->setContentView("error-404");
    }

    /**
     * @see Controller::defaultAction()
     * @noinspection PhpDocSignatureInspection
     */
    public function accessDeniedAction(Request $request): Response
    {
        $response = $this->responseFactory->create($request);

        return $response->setLayoutView("error-403");
    }

    /**
     * @see Controller::defaultAction()
     * @noinspection PhpDocSignatureInspection
     */
    public function systemErrorAction(Request $request): Response
    {
        $response = $this->responseFactory->create($request);

        return $response->setContentView("error-500");
    }
}