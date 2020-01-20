<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Translating;

use Latte\Runtime\FilterInfo;
use Mammoth\Config\Configurator;
use Mammoth\DI\DIClass;
use Mammoth\Exceptions\NotPartOfFrameworkException;
use Mammoth\Translating\Abstraction\ITranslateManager;
use function in_array;

/**
 * Translate manager
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Templates
 */
class TranslateManager implements ITranslateManager
{
    use DIClass;

    /**
     * @inject
     */
    private Configurator $configurator;

    /**
     * @inheritDoc
     * It isn't part of framework, implement it yourself
     * @throws \Mammoth\Exceptions\NotPartOfFrameworkException Feature has to be implemented by application
     */
    public function translatePageText(FilterInfo $info, $message): string
    {
        throw new NotPartOfFrameworkException("This feature has to be implemented by application");
    }

    /**
     * @inheritDoc
     */
    public function isValidLang(string $lang): bool
    {
        return in_array($lang, $this->configurator->getValidLangs());
    }
}