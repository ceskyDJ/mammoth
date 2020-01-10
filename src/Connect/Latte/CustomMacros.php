<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Connect\Latte;

use Latte\Compiler;
use Latte\MacroNode;
use Latte\Macros\MacroSet;
use Latte\PhpWriter;
use Mammoth\Url\Abstraction\IUrlManager;

/**
 * Own macros to Latte template system
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Connect\Latte
 */
class CustomMacros extends MacroSet
{
    private IUrlManager $urlManager;

    /**
     * Adds defined macros to Latte system
     *
     * @param \Latte\Compiler $compiler Latte compiler
     * @param \Mammoth\Url\Abstraction\IUrlManager $linkManager
     */
    public static function install(Compiler $compiler, IUrlManager $linkManager): void
    {
        $me = new static($compiler);

        $me->setUrlManager($linkManager);

        $me->addMacro("href", null, null, [$me, "macroLink"]);
    }

    /**
     * Fluent setter for urlManager
     *
     * @param \Mammoth\Url\Abstraction\IUrlManager $urlManager
     *
     * @return CustomMacros
     */
    public function setUrlManager(IUrlManager $urlManager): CustomMacros
    {
        $this->urlManager = $urlManager;

        return $this;
    }

    /**
     * Macro for controlling links in templates
     *
     * @param \Latte\MacroNode $node Previous tag's node
     * @param \Latte\PhpWriter $writer Tool for generating HTML code by this macro
     *
     * @return string New href attribute
     */
    public function macroLink(MacroNode $node, PhpWriter $writer): string
    {
        $startAddress = $node->args;

        return "echo ' href=\"{$this->urlManager->getResultAddress($startAddress)}\"'";
    }
}