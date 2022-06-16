<?php

namespace Wang\Pkg\Console;

//use Dcat\Admin\Console\GeneratorCommand;

class ActionModalCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wangpkg:actionModal {classname?} {btnname?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '创建Modal操作';

    /**
     * @var string
     */
    protected $choice;

    /**
     * @var string
     */
    protected $className;


    protected $actionName;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var array
     */
    protected $namespaceMap = [
        'action-modal' => 'ActionModal',
    ];

    public function handle()
    {

        //php artisan wangpkg:actionModal Test 测试
        //action-form
        $actiontype = 'action-modal';
        $classname = $this->argument('classname');
        $actionname = $this->argument('btnname');

        // echo 123456;
        $this->choice = $actiontype;
        $this->className = $classname;
        $this->actionName = $actionname;


        //生成action类
        $name = $this->qualifyClass($this->getNameInput());

        $path = $this->getPath($name);
        //判断是否强制覆盖
        if ((!$this->hasOption('force') ||
                !$this->option('force')) &&
            $this->alreadyExists($this->getNameInput())) {
            $this->error($this->type . ' already exists!');
            return false;
        }

        $this->makeDirectory($path);

        //写入文件
        $stub = $this->files->get(__DIR__ . "/stubs/actions/{$this->choice}.stub");

        $buildClass = $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);

        $this->files->put($path, $this->sortImports($buildClass));

        $this->info($this->type . ' created successfully.');


    }

    /**
     * @return array
     */
    protected function actionTyps()
    {
        return [
            'action-form',
        ];
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param string $stub
     * @param string $name
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $stub = parent::replaceClass($stub, $name);

        return str_replace(
            [
                'DummyName',
                'DummyTitle',
            ],
            [
                $this->className,
                $this->actionName,
            ],
            $stub
        );
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    public function getStub()
    {
        return __DIR__ . "/stubs/actions/{$this->choice}.stub";
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        if ($this->namespace) {
            return $this->namespace;
        }

        $segments = explode('\\', config('admin.route.namespace'));
        array_pop($segments);
        array_push($segments, 'Actions');

        //按model名称分目录
        //array_push($segments, $this->modelName);

        if (isset($this->namespaceMap[$this->choice])) {
            array_push($segments, $this->namespaceMap[$this->choice]);
        }

        return implode('\\', $segments);
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        $this->type = $this->qualifyClass($this->className);

        return $this->className;
    }
}
