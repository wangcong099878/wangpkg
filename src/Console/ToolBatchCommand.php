<?php

namespace Wang\Pkg\Console;

//use Dcat\Admin\Console\GeneratorCommand;

class ToolBatchCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wangpkg:toolBatch {classname?} {textname?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '创建批量操作工具栏';

    /**
     * @var string
     */
    protected $choice;

    /**
     * @var string
     */
    protected $className;


    protected $textName;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var array
     */
    protected $namespaceMap = [
        'action-form' => 'ToolBatch',
    ];

    public function handle()
    {

        //php artisan wangpkg:toolBatch Reset 批量重置
        //action-form
        $actiontype = 'action-form';
        $classname = $this->argument('classname');
        $textname = $this->argument('textname');

        $this->choice = $actiontype;
        $this->className = $classname;
        $this->textName = $textname;


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
        $stub = $this->files->get(__DIR__ . "/stubs/actions/tool-batch.stub");

        $buildClass = $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);

        $this->files->put($path, $this->sortImports($buildClass));

        $this->info('created successfully.');



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
                $this->textName,
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
