<?php

namespace TerminalFaApi\Facades;

use Illuminate\Support\Facades\Facade;
use TerminalFaApi\Services\TerminalFaApi;

class TerminalFa extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return TerminalFaApi::class;
    }
}
