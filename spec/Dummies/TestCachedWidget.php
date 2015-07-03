<?php

namespace Brainkit\Widgets\Test\Dummies;

use Brainkit\Widgets\AbstractWidget;

class TestCachedWidget extends AbstractWidget
{
    public $cacheTime = 60;

    protected $slides = 6;

    public function run()
    {
        return "Feed was executed with \$slides = ".$this->slides;
    }
}
