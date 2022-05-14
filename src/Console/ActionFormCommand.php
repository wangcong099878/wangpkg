<?php

namespace Wang\Pkg\Console;

//use Dcat\Admin\Console\GeneratorCommand;

class ActionFormCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wangpkg:aForm {classname?} {textname?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a admin action';

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
        'grid-batch' => 'Grid',
        'grid-row' => 'Grid',
        'grid-tool' => 'Grid',
        'form-tool' => 'Form',
        'show-tool' => 'Show',
        'tree-row' => 'Tree',
        'tree-tool' => 'Tree',
        'action-form' => 'ActionForm',
        'action-tab' => 'ActionTab',
    ];

    public function handle()
    {

        //php artisan wangpkg:aForm AddTest 添加测试
        //action-form
        $actiontype = 'action-form';
        $classname = $this->argument('classname');
        $textname = $this->argument('textname');

        // echo 123456;
        $this->choice = $actiontype;
        $this->className = $classname;
        $this->textName = $textname;
        // exit;
        /*echo $this->choice = $this->choice(
            '选择类型',
            $choices = $this->actionTyps()
        );

exit;
/*
        INPUT_NAME:

        $this->className = ucfirst(trim($this->ask('输入文件类名')));

        if (! $this->className) {
            goto INPUT_NAME;
        }

        INPUT_TEXT_NAME:

        $this->textName = ucfirst(trim($this->ask('Please enter a name of action class')));

        if (! $this->className) {
            goto INPUT_TEXT_NAME;
        }*/

        //$this->namespace = ucfirst(trim($this->ask('Please enter the namespace of action class', $this->getDefaultNamespace(null))));

        //询问默认路径 App\Admin\Actions
        //$this->askBaseDirectory();


        if ($actiontype == 'action-form') {
            //生成action类
            $name = $this->qualifyClass($this->getNameInput());

            $path = $this->getPath($name.'Action');
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


            //生成form类
            $name = $this->qualifyClass($this->getNameInput());

            $path = $this->getPath($name.'Form');

            // First we will check to see if the class already exists. If it does, we don't want
            // to create the class and overwrite the user's code. So, we will bail out so the
            // code is untouched. Otherwise, we will continue generating this class' files.
            if ((!$this->hasOption('force') ||
                    !$this->option('force')) &&
                $this->alreadyExists($this->getNameInput())) {
                $this->error($this->type . ' already exists!');

                return false;
            }

            // Next, we will generate the path to the location where this class' file should get
            // written. Then, we will build the class and make the proper replacements on the
            // stub files so that it gets the correctly formatted namespace and class name.
            $this->makeDirectory($path);

            $stub = $this->files->get(__DIR__ . "/stubs/actions/form.stub");

            $buildClass = $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);

            $this->files->put($path, $this->sortImports($buildClass));

            $this->info($this->type . ' created successfully.');
        }


    }

    /**
     * @return array
     */
    protected function actionTyps()
    {
        return [
            'default',
            'grid-batch',
            'grid-row',
            'grid-tool',
            'form-tool',
            'show-tool',
            'tree-row',
            'tree-tool',
            'action-form',
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
