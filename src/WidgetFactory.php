<?php namespace Arrilot\Widgets;

class WidgetFactory extends AbstractWidgetFactory {

    protected $config;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->config = [
            'defaultNamespace' => config('laravel-widgets.default_namespace'),
            'customNamespaces' => config('laravel-widgets.custom_namespaces_for_specific_widgets', [])
        ];
    }

    /**
     * Magic method that catches all widget calls
     *
     * @param $widgetName
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function __call($widgetName, $params = [])
    {
        $config = isset($params[0]) ? $params[0] : [];

        $widgetName = studly_case($widgetName);

        $namespace   = $this->determineNamespace($widgetName);
        $widgetClass = $namespace . '\\' . $widgetName;

        $widget = new $widgetClass($config);
        if ($widget instanceof AbstractWidget === false)
        {
            throw new InvalidWidgetClassException;
        }

        return $widget->run();
    }

}