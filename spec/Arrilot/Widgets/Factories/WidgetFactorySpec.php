<?php

namespace spec\Brainkit\Widgets\Factories;

use Brainkit\Widgets\Misc\LaravelApplicationWrapper;
use Brainkit\Widgets\Test\Dummies\Profile\TestNamespace\TestFeed;
use Brainkit\Widgets\Test\Dummies\Slider;
use Brainkit\Widgets\Test\Dummies\TestCachedWidget;
use Brainkit\Widgets\Test\Dummies\TestDefaultSlider;
use Brainkit\Widgets\Test\Dummies\TestMyClass;
use Brainkit\Widgets\Test\Dummies\TestRepeatableFeed;
use Brainkit\Widgets\Test\Dummies\TestWidgetWithCustomContainer;
use Brainkit\Widgets\Test\Dummies\TestWidgetWithDIInRun;
use Brainkit\Widgets\Test\Dummies\TestWidgetWithParamsInRun;
use Brainkit\Widgets\WidgetId;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class WidgetFactorySpec extends ObjectBehavior
{
    protected $config = [
        'defaultNamespace' => 'Brainkit\Widgets\Test\Dummies',
    ];

    /**
     * A mock for producing JS object for ajax.
     *
     * @param $widgetName
     * @param $widgetParams
     *
     * @return string
     */
    private function mockProduceJavascriptData($widgetName, $widgetParams = [])
    {
        return json_encode([
            'id'     => 1,
            'name'   => $widgetName,
            'params' => serialize($widgetParams),
            '_token' => 'token_stub',
        ]);
    }

    public function let(LaravelApplicationWrapper $wrapper)
    {
        $this->beConstructedWith($this->config, $wrapper);
        WidgetId::reset();
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Brainkit\Widgets\Factories\WidgetFactory');
    }

    public function it_can_run_widget_from_default_namespace(LaravelApplicationWrapper $wrapper)
    {
        $wrapper->call(Argument::any(), Argument::any())->willReturn(
            call_user_func_array([new TestDefaultSlider([]), 'run'], [])
        );
        $this->testDefaultSlider()
            ->shouldReturn(
                'Default test slider was executed with $slides = 6'
            );
    }

    public function it_allows_its_config_to_be_partly_overwritten(LaravelApplicationWrapper $wrapper)
    {
        $wrapper->call(Argument::any(), Argument::any())->willReturn(
            call_user_func_array([new Slider(['slides' => 5]), 'run'], ['slides' => 5])
        );
        $this->slider(['slides' => 5])
            ->shouldReturn(
                'Slider was executed with $slides = 5 foo: bar'
            );
    }

    public function it_allows_its_config_to_be_overwritten(LaravelApplicationWrapper $wrapper)
    {
        $wrapper->call(Argument::any(), Argument::any())->willReturn(
            call_user_func_array([new Slider(['slides' => 5, 'foo' => 'baz']), 'run'], ['slides' => 5, 'foo' => 'baz'])
        );
        $this->slider(['slides' => 5, 'foo' => 'baz'])
            ->shouldReturn(
                'Slider was executed with $slides = 5 foo: baz'
            );
    }

    public function it_throws_exception_for_bad_widget_class()
    {
        $this->shouldThrow('\Brainkit\Widgets\Misc\InvalidWidgetClassException')->during('testBadSlider');
    }

    public function it_can_run_widgets_with_additional_params(LaravelApplicationWrapper $wrapper)
    {
        $wrapper->call(Argument::any(), Argument::any())->willReturn(
            call_user_func_array([new TestWidgetWithParamsInRun([]), 'run'], ['asc'])
        );
        $this->testWidgetWithParamsInRun([], 'asc')
            ->shouldReturn(
                'TestWidgetWithParamsInRun was executed with $flag = asc'
            );
    }

    public function it_can_run_widgets_with_method_injection(LaravelApplicationWrapper $wrapper)
    {
        $wrapper->call(Argument::any(), Argument::any())->willReturn(
            call_user_func_array([new TestWidgetWithDIInRun([]), 'run'], [new TestMyClass()])
        );
        $this->testWidgetWithParamsInRun()
            ->shouldReturn(
                'bar'
            );
    }

    public function it_can_run_widgets_with_run_method_and_config_override(LaravelApplicationWrapper $wrapper)
    {
        $wrapper->call(Argument::any(), Argument::any())->willReturn(
            call_user_func_array([new Slider(['slides' => 5]), 'run'], ['slides' => 5])
        );
        $this->run('slider', ['slides' => 5])
            ->shouldReturn(
                'Slider was executed with $slides = 5 foo: bar'
            );
    }

    public function it_can_run_widgets_using_global_namespace(LaravelApplicationWrapper $wrapper)
    {
        $wrapper->call(Argument::any(), Argument::any())->willReturn(
            call_user_func_array([new TestDefaultSlider([]), 'run'], [])
        );
        $this->run('\Brainkit\Widgets\Test\Dummies\TestDefaultSlider')
            ->shouldReturn(
                'Default test slider was executed with $slides = 6'
            );
    }

    public function it_can_run_nested_widgets(LaravelApplicationWrapper $wrapper)
    {
        $wrapper->call(Argument::any(), Argument::any())->willReturn(
            call_user_func_array([new TestFeed([]), 'run'], [])
        );
        $this->run('Profile\TestNamespace\TestFeed', ['slides' => 5])
            ->shouldReturn(
                'Feed was executed with $slides = 6'
            );
    }

    public function it_can_run_nested_widgets_with_dot_notation(LaravelApplicationWrapper $wrapper)
    {
        $wrapper->call(Argument::any(), Argument::any())->willReturn(
            call_user_func_array([new TestFeed([]), 'run'], [])
        );
        $this->run('profile.testNamespace.testFeed', ['slides' => 5])
            ->shouldReturn(
                'Feed was executed with $slides = 6'
            );
    }

    public function it_can_run_multiple_widgets(LaravelApplicationWrapper $wrapper)
    {
        $wrapper->call(Argument::any(), Argument::any())->willReturn(
            call_user_func_array([new Slider([]), 'run'], [])
        );
        $this->slider()
            ->shouldReturn(
                'Slider was executed with $slides = 6 foo: bar'
            );

        $wrapper->call(Argument::any(), Argument::any())->willReturn(
            call_user_func_array([new Slider(['slides' => 5]), 'run'], ['slides' => 5])
        );
        $this->slider(['slides' => 5])
            ->shouldReturn(
                'Slider was executed with $slides = 5 foo: bar'
            );
    }

    public function it_can_run_reloadable_widget(LaravelApplicationWrapper $wrapper)
    {
        $config = [];
        $params = [$config];

        $wrapper->csrf_token()->willReturn('token_stub');
        $wrapper->call(Argument::any(), Argument::any())->willReturn(
            call_user_func_array([new TestRepeatableFeed([]), 'run'], [])
        );

        $this->testRepeatableFeed($config)
            ->shouldReturn(
                '<div id="brainkit-widget-container-1" style="display:inline" class="brainkit-widget-container">Feed was executed with $slides = 6'.
                '<script type="text/javascript">setTimeout( function() { $(\'#brainkit-widget-container-1\').load(\'/brainkit/load-widget\', '.$this->mockProduceJavascriptData('TestRepeatableFeed', $params).') }, 10000)</script>'.
                '</div>'
            );
    }

    public function it_can_run_widget_with_custom_container(LaravelApplicationWrapper $wrapper)
    {
        $config = [];
        $params = [$config];

        $wrapper->csrf_token()->willReturn('token_stub');
        $wrapper->call(Argument::any(), Argument::any())->willReturn(
            call_user_func_array([new TestWidgetWithCustomContainer([]), 'run'], [])
        );

        $this->testWidgetWithCustomContainer($config)
            ->shouldReturn(
                '<p id="brainkit-widget-container-1" data-id="123">Dummy Content'.
                '<script type="text/javascript">setTimeout( function() { $(\'#brainkit-widget-container-1\').load(\'/brainkit/load-widget\', '.$this->mockProduceJavascriptData('TestWidgetWithCustomContainer', $params).') }, 10000)</script>'.
                '</p>'
            );
    }

    public function it_can_cache_widgets(LaravelApplicationWrapper $wrapper)
    {
        $wrapper->call(Argument::any(), Argument::any())->willReturn(
            call_user_func_array([new TestCachedWidget(['slides' => 5]), 'run'], [])
        );
        $wrapper->cache(Argument::any(), Argument::any(), Argument::any())->shouldBeCalled();

        $this->run('testCachedWidget', ['slides' => 5]);
    }
}
