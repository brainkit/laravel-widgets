<?php

namespace App\Widgets\Profile\TestNamespace;

use Brainkit\Widgets\AbstractWidget;

class TestFeed extends AbstractWidget
{
    protected $slides = 6;

    public function run()
    {
        return "Feed was executed with \$slides = ".$this->slides;
    }
}
