<?php

namespace Brainkit\Widgets;

class InvalidWidgetClassException extends \Exception
{
    /**
     * Exception message.
     *
     * @var string
     */
    protected $message = 'Widget class must extend Brainkit\Widgets\AbstractWidget class';
}
