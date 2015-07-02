<?php namespace Brainkit\Widgets\Factories;

use Brainkit\Widgets\AbstractWidget;
use Brainkit\Widgets\InvalidWidgetClassException;

abstract class AbstractWidgetFactory {

    /**
     * Factory config.
     *
     * @var array
     */
    protected $factoryConfig;

    /**
     * Widget configuration array.
     *
     * @var array
     */
    protected $widgetConfig;

    /**
     * The name of the widget being called.
     *
     * @var string
     */
    protected $widgetName;

    /**
     * Array of widget parameters excluding the first one (config).
     *
     * @var array
     */
    protected $widgetParams;

    /**
     * Array of widget parameters including the first one (config).
     *
     * @var array
     */
    protected $widgetFullParams;

    /**
     * Laravel application wrapper for better testability.
     *
     * @var \Brainkit\Widgets\Misc\Wrapper;
     */
    protected $wrapper;

    /**
     * @param $factoryConfig
     * @param $wrapper
     */
    public function __construct($factoryConfig, $wrapper) {
        $this->factoryConfig = $factoryConfig;
        $this->wrapper = $wrapper;
    }

    /**
     * Magic method that catches all widget calls.
     *
     * @param string $widgetName
     * @param array  $params
     *
     * @return mixed
     */
    public function __call($widgetName, array $params = []) {
        array_unshift($params, $widgetName);

        return call_user_func_array([$this, 'run'], $params);
    }

    /**
     * Determine widget namespace.
     *
     * @return mixed
     */
    protected function determineNamespace() {
        foreach ([$this->widgetName, strtolower($this->widgetName)] as $name)
        {
            if (array_key_exists($name, $this->factoryConfig['customNamespaces']))
            {
                return $this->factoryConfig['customNamespaces'][$name];
            }
        }

        return $this->factoryConfig['defaultNamespace'];
    }

    /**
     * Set class properties and instantiate a widget object.
     *
     * @param $params
     *
     * @throws InvalidWidgetClassException
     *
     * @return mixed
     */
    protected function instantiateWidget(array $params = []) {

        $widgetClass = $this->determineNamespace() . '\\' . $this->widgetName . '\\Widget' . $this->widgetName;
        /* if ($config = $this->getConfigFile($widgetClass))
          {
          if (is_array($config) and isset($params[0]))  $params[0] = array_merge($config, $params[0]);
          } */
        $this->widgetFullParams = $params;
        $this->widgetConfig = array_shift($params);
        $this->widgetParams = $params;
        // \Debugbar::info($widgetClass);


        $widget = new $widgetClass($this->widgetConfig);
        if ($widget instanceof AbstractWidget === false)
        {
            throw new InvalidWidgetClassException();
        }

        return $widget;
    }

    /**
     * Convert stuff like 'profile.feedWidget' to 'Profile\FeedWidget'.
     *
     * @param $widgetName
     *
     * @return string
     */
    protected function parseFullWidgetNameFromString($widgetName) {
        $this->widgetName = studly_case(str_replace('.', '\\', $widgetName));
    }

    /* private function getConfigFile($widget) {
      $config = false;

      $reflector = new \ReflectionClass($widget);
      $fn = $reflector->getFileName();
      $fpath = dirname($fn);
      $fileConfig = $fpath . "/Config" . "$this->widgetName" . ".php";
      if (is_file($fileConfig))
      {

      $config = require_once ($fileConfig);
      }

      // \Debugbar::info($config);
      return $config;
      } */
}
