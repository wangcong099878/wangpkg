<?php

namespace Wang\Pkg\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Wang\Pkg\Lib\ResourceGenerator;

class DcatCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'wangpkg:dmake {name}
        {--model=}
        {--title=}
        {--repositories=}
        {--stub= : Path to the custom stub file. }
        {--namespace=}
        {--O|output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make admin controller';

    /**
     * @var ResourceGenerator
     */
    protected $generator;

    /**
     * @var string
     */
    protected $controllerName;

    /**
     * @var string
     */
    protected $modelName;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        //php artisan my:make tt
        $this->modelName = $this->getModelName();
        $this->controllerName = $this->getControllerName();

        if (!$this->modelExists()) {
            $this->error('Model does not exists !');
            return false;
        }

        $stub = $this->option('stub');

        if ($stub and !is_file($stub)) {
            $this->error('The stub file dose not exist.');

            return false;
        }

        $this->generator = new ResourceGenerator($this->modelName);

        if ($this->option('output')) {
            return $this->output($this->modelName);
        }
        //执行上级
        if (parent::handle() !== false) {
            //$path = Str::plural(Str::kebab(class_basename($this->modelName)));
            $path = lcfirst(class_basename($this->modelName));

            $this->line('');
            $this->comment('Add the following route to app/Admin/routes.php:');
            $this->line('');
            $this->info("    \$router->resource('{$path}', {$this->controllerName}::class);");
            $this->line('');
        }
    }

    /**
     * @return array|string|null
     */
    protected function getControllerName()
    {
        return $this->argument('name');
    }

    /**
     * @return array|string|null
     */
    protected function getModelName()
    {
        return $this->option('model');
    }

    protected function getRepositories()
    {
        return $this->option('repositories');
    }

    /**
     * @return array|bool|string|null
     * @throws \ReflectionException
     *
     */
    protected function getTitle()
    {
        if ($title = $this->option('title')) {
            return $title;
        }

        return __((new \ReflectionClass($this->modelName))->getShortName());
    }

    /**
     * @param string $modelName
     */
    protected function output($modelName)
    {
        $this->alert("laravel-admin controller code for model [{$modelName}]");

        $this->info($this->generator->generateGrid());
        $this->info($this->generator->generateShow());
        $this->info($this->generator->generateForm());
    }

    /**
     * Determine if the model is exists.
     *
     * @return bool
     */
    protected function modelExists()
    {
        if (empty($this->modelName)) {
            return true;
        }

        return class_exists($this->modelName) && is_subclass_of($this->modelName, Model::class);
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
        $stub = parent::replaceClass($stub, $name);

        //echo lcfirst(str_replace('Controller','',$name));
        $controllerName = str_replace($this->getNamespace($name).'\\', '', $name);
        return str_replace(
            [
                'DummyModelNamespace',
                'DummyTitle',
                'DummyModel',
                'DummyGrid',
                'DummyShow',
                'DummyForm',
                'DummyRepositories',
                'DummyRedirect'
            ],
            [
                $this->modelName,
                $this->getTitle(),
                class_basename($this->modelName),
                $this->indentCodes($this->generator->generateGrid()),
                $this->indentCodes($this->generator->generateShow()),
                $this->indentCodes($this->generator->generateForm()),
                //$this->getRepositories()
                'App\Admin\Repositories\\'.class_basename($this->modelName),
                lcfirst(str_replace('Controller','',$controllerName))
            ],
            $stub
        );
    }

    /**
     * @param string $code
     *
     * @return string
     */
    protected function indentCodes($code)
    {
        $indent = str_repeat(' ', 8);

        return rtrim($indent . preg_replace("/\r\n/", "\r\n{$indent}", $code));
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        if ($stub = $this->option('stub')) {
            return $stub;
        }

        if ($this->modelName) {
            return __DIR__ . '/dcat/controller.stub';
        }

        return __DIR__ . '/stubs/blank.stub';
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
        if ($namespace = $this->option('namespace')) {
            return $namespace;
        }

        return config('admin.route.namespace');
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        $this->type = $this->qualifyClass($this->controllerName);

        return $this->controllerName;
    }
}
