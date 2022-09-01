<?php

namespace Wang\Pkg\Console;

//use Dcat\Admin\Console\GeneratorCommand;

class ActionTabCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wangpkg:aTab {classname?} {textname?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '创建行操作表格';

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
        'action-tab' => 'ActionTab',
    ];

    public function handle()
    {
        //php artisan wangpkg:aTab User 表格弹窗操作
        //action-form
        $actiontype = 'action-tab';
        $classname = $this->argument('classname');
        $textname = $this->argument('textname');

        // echo 123456;
        $this->choice = $actiontype;
        $this->className = $classname;
        $this->textName = $textname;

        //生成action类
        $name = $this->qualifyClass($this->getNameInput());

        $path = $this->getPath($name . 'Action');
        //判断是否强制覆盖
        if ((!$this->hasOption('force') ||
                !$this->option('force')) &&
            $this->alreadyExists($this->getNameInput())) {
            $this->error($this->type . ' already exists!');
            return false;
        }

        $this->makeDirectory($path);

        //写入文件
        $stub = $this->files->get(__DIR__ . "/stubs/actions/action-tab-btn.stub");

        $buildClass = $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);

        $this->files->put($path, $this->sortImports($buildClass));

        $this->info($this->type . ' created successfully.');


        //生成表单类
        $name = $this->qualifyClass($this->getNameInput());


        //print_r($name);exit;
        $path = $this->getPath($name . 'Tab');

        //强制覆盖
        if ((!$this->hasOption('force') ||
                !$this->option('force')) &&
            $this->alreadyExists($this->getNameInput())) {
            $this->error($this->type . ' already exists!');

            return false;
        }

        $this->makeDirectory($path);

        $stub = $this->files->get(__DIR__ . "/stubs/actions/action-tab.stub");

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
            'action-tab',
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

        $arr = explode('\\',$this->className);
        $DummyName = end($arr);
/*        if(count($arr)>1){

        }else{

        }*/

/*        print_r($arr);
        exit;*/
        return str_replace(
            [
                'DummyName',
                'DummyModelClass',
                'DummyTitle',
            ],
            [
                $DummyName,
                $DummyName,
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
