<?php

namespace Brainkit\Widgets\Misc;

class InvalidWidgetClassException extends \Exception
{
    /**
     * Exception message.
     *
     * @var string
     */
    protected $message = 'Widget class must extend Brainkit\Widgets\AbstractWidget class';
}
