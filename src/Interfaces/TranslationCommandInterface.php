<?php
namespace Gsferro\TranslationSolutionEasy\Interfaces;

interface TranslationCommandInterface
{
    /**
     * Execute into exec()
     *
     * @param string $lang
     */
    public function execInCommand(string $lang);
}