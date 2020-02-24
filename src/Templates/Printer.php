<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Templates;

use Latte;
use Latte\Runtime\FilterInfo;
use Mammoth\Config\Configurator;
use Mammoth\Connect\Latte\CustomMacros;
use Mammoth\DI\DIClass;
use Mammoth\Exceptions\NotPartOfFrameworkException;
use Mammoth\Http\Entity\Response;
use Mammoth\Http\Entity\Server;
use Mammoth\Templates\Abstraction\IPrinter;
use Mammoth\Translating\Abstraction\ITranslateManager;
use Mammoth\Url\Abstraction\IUrlManager;
use Mammoth\Url\Entity\ParsedUrl;
use function bdump;
use function dump;
use function file_exists;

/**
 * Views and output data manager
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Templates
 */
class Printer implements IPrinter
{
    use DIClass;

    /**
     * @inject
     */
    private ITranslateManager $translateManager;
    /**
     * @inject
     */
    private Configurator $configurator;
    /**
     * @inject
     */
    private Server $server;
    /**
     * @inject
     */
    private IUrlManager $urlManager;

    /**
     * @inheritDoc
     */
    public function writeContent(Response $response): void
    {
        $parsedUrl = $response->getRequest()
            ->getParsedUrl();

        // Special layout (for ajax and API)
        if ($response->getLayoutView() === "code") {
            if (file_exists($this->getDirectory($parsedUrl)."/#code.latte")) {
                include $this->getDirectory($parsedUrl)."/#code.latte";
            } else {
                include __DIR__."/default-views/#code.latte";
            }

            return;
        }

        // Template writing
        print $this->getFileHTML(
            $this->getDirectory($parsedUrl)."/".$response->getContentView().".latte",
            $response->getDataVarsForTemplate()
        );
    }

    /**
     * Returns path to folder with templates
     *
     * @param \Mammoth\Url\Entity\ParsedUrl Parsed URL object for getting some data
     *
     * @return string Path to folder
     */
    private function getDirectory(ParsedUrl $parsedUrl): string
    {
        // Set root directory by controller type set up in Parsed URL object
        if ($parsedUrl->getComponent() !== ParsedUrl::FRAMEWORK_COMPONENT) {
            $templatesDir = "{$this->configurator->getAppSrcRootDir()}/../".$this->configurator->getAppTemplatesDir();
        } else {
            // Framework
            return __DIR__."/default-views";
        }

        if ($parsedUrl->getComponent() !== null) {
            return "{$templatesDir}/{$parsedUrl->getComponent(true)}";
        } else {
            return $templatesDir;
        }
    }

    /**
     * @inheritDoc
     */
    public function getFileHTML(string $path, array $data = []): string
    {
        // Latte instance
        $latte = new Latte\Engine;

        // Latte's cache file
        $latte->setTempDirectory($this->configurator->getTempDir());

        // Add filter for translating (if translate manager implementation is loaded)
        try {
            $this->translateManager->translatePageText(new FilterInfo, "");

            $latte->addFilter("translate", [$this->translateManager, "translatePageText"]);
        } /**
         * @noinspection PhpRedundantCatchClauseInspection
         */ catch (NotPartOfFrameworkException $e) {
            // Translate method is not part of a framework but isn't implement by application, too
        }

        // Add framework's macros
        CustomMacros::install($latte->getCompiler(), $this->urlManager);

        return $latte->renderToString($path, $data);
    }
}
