<?php

namespace Brainkit\Widgets\Console;

use Illuminate\Console\AppNamespaceDetectorTrait;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class WidgetMakeCommand extends GeneratorCommand
{
    use AppNamespaceDetectorTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:widget';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new widget (brainkit/laravel-widgets)';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Widget';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        parent::fire();

        if (!$this->option('plain')) {
            $this->createView();
        }
    }

    /**
     * Build the class with the given name.
     *
     * @param string $name
     *
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        $stub = $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);

        if (!$this->option('plain')) {
            $stub = $this->replaceView($stub);
        }

        return $stub;
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        if ($this->option('plain')) {
            return __DIR__.'/stubs/widget_plain.stub';
        }

        return __DIR__.'/stubs/widget.stub';
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param string $stub
     * @param string $name
     *
     * @return $this
     */
    protected function replaceNamespace(&$stub, $name)
    {
        $stub = str_replace(
            '{{namespace}}', $this->getNamespace($name), $stub
        );

        $stub = str_replace(
            '{{rootNamespace}}', $this->getAppNamespace(), $stub
        );

        return $this;
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param string $stub
     * @param string $name
     *
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $class = str_replace($this->getNamespace($name).'\\', '', $name);

        return str_replace('{{class}}', $class, $stub);
    }

    /**
     * Replace the view name for the given stub.
     *
     * @param string $stub
     *
     * @return string
     */
    protected function replaceView($stub)
    {
        $view = 'widgets.'.str_replace('/', '.', $this->makeViewName());

        return str_replace('{{view}}', $view, $stub);
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Widgets';
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['plain', null, InputOption::VALUE_NONE, 'Use plain stub. No view is being created too.'],
        ];
    }

    /**
     * Create a new view file for the widget.
     *
     * return void
     */
    protected function createView()
    {
        if ($this->files->exists($path = $this->getViewPath())) {
            $this->error('View already exists!');

            return;
        }

        $this->makeDirectory($path);

        $this->files->put($path, '');

        $this->info('View created successfully.');
    }

    /**
     * Get the destination view path.
     *
     * @return string
     */
    protected function getViewPath()
    {
        return base_path('resources/views').'/widgets/'.$this->makeViewName().'.blade.php';
    }

    /**
     * Get the destination view name without extensions.
     *
     * @return string
     */
    protected function makeViewName()
    {
        $name = str_replace($this->getAppNamespace(), '', $this->argument('name'));
        $name = str_replace('\\', '/', $name);

        // convert to snake_case part by part to avoid unexpected underscores.
        $nameArray = explode('/', $name);
        array_walk($nameArray, function (&$part) {
            $part = snake_case($part);
        });

        return implode('/', $nameArray);
    }
}
