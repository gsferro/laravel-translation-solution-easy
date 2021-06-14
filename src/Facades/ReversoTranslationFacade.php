<?php

namespace Gsferro\TranslationSolutionEasy\Facades;

use Illuminate\Support\Facades\Facade;

class ReversoTranslationFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'reversotranslation'; // em minusculo
    }
}