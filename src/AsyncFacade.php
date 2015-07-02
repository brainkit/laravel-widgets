<?php

namespace Brainkit\Widgets;

class AsyncFacade extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor()
    {
        return 'brainkit.async-widget';
    }
}
