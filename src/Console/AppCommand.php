<?php

namespace Wang\Pkg\Console;

//use Dcat\Admin\Console\GeneratorCommand;

class AppCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wangpkg:app {prefix?} {appname?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成一个新应用';

    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var string
     */
    protected $prefixCapital;


    protected $appName;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var array
     */
    protected $namespaceMap = [
        'action' => 'Action',
    ];

    public function makeAppFile($name)
    {

        $stub = $name . '.stub';
        $file = $name . '.php';
        $stub = $this->files->get(__DIR__ . "/app/" . $stub);
        $filepath = app_path("{$this->prefixCapital}Admin/" . $file);

        $dir = dirname($filepath);

        @mkdir($dir, 0777, true);
        //$this->makeDirectory($dir);

        $buildText = $this->replaceField($stub);
        $this->files->put($filepath, $buildText);
    }

    public function makeConfigFile()
    {
        $stub = $this->files->get(__DIR__ . "/app/config/admin.stub");
        $filepath = config_path("{$this->prefix}-admin.php");
        $buildText = $this->replaceField($stub);
        $this->files->put($filepath, $buildText);
    }

    public function makeDBFile($name)
    {

        $stub = $name . '.stub';
        $name = str_replace('2016_01_04_173148',date('Y_m_d_His',time()),$name);
        $name = str_replace('2020_09_07_090635',date('Y_m_d_His',time()+1),$name);
        $name = str_replace('2020_09_22_015815',date('Y_m_d_His',time()+2),$name);
        $name = str_replace('2020_11_01_083237',date('Y_m_d_His',time()+3),$name);

        $file = $name . '.php';
        $stub = $this->files->get(__DIR__ . "/migration/" . $stub);
        $filepath = base_path("database/{$this->prefix}_admin/" . $file);

        //php artisan migrate --path=database/tp_admin
        $dir = dirname($filepath);


        @mkdir($dir, 0777, true);
        //$this->makeDirectory($dir);

        $buildText = $this->replaceField($stub);
        $this->files->put($filepath, $buildText);
    }

    //初始化数据库
    public function makeShsFile()
    {
/*        $stub = $this->files->get(__DIR__ . "/app/shs/db.stub");
        $filepath = base_path("shs/{$this->prefix}_admin.shs");
        $buildText = $this->replaceField($stub);
        $this->files->put($filepath, $buildText);*/

        $id = rand(100000,999999);

        $arr = ['2016_01_04_173148_create_admin_tables',
'2020_09_07_090635_create_admin_settings_table',
'2020_09_22_015815_create_admin_extensions_table',
'2020_11_01_083237_update_admin_menu_table'];

        foreach($arr as $v){
            $this->makeDBFile($v);
        }

    }

    public function makeFrontend()
    {
        //生成controller
        $controllersPath = app_path("Http/Controllers/{$this->prefixCapital}");
        $controllersName = "/{$this->prefixCapital}Controller.php";
        @mkdir($controllersPath, 0777, true);
        $stub = $this->files->get(__DIR__ . "/app/frontend/Controller.stub");
        $buildText = $this->replaceField($stub);
        $this->files->put($controllersPath.$controllersName, $buildText);

        //生成中间件
        $middlewarePath = app_path("Http/Middleware/{$this->prefixCapital}Auth.php");
        $stub = $this->files->get(__DIR__ . "/app/frontend/Auth.stub");
        $buildText = $this->replaceField($stub);
        $this->files->put($middlewarePath, $buildText);

        //生成路由
        $routePath = base_path("routes/{$this->prefix}.php");
        $stub = $this->files->get(__DIR__ . "/app/frontend/route.stub");
        $buildText = $this->replaceField($stub);
        $this->files->put($routePath, $buildText);

        //生成前端目录和文件
        $frontendPath = public_path("{$this->prefix}");
        @mkdir($frontendPath, 0777, true);
        $stub = $this->files->get(__DIR__ . "/app/frontend/index.stub");
        $buildText = $this->replaceField($stub);
        $this->files->put($frontendPath.'/index.html', $buildText);

    }

    public function makeCommand(){
        $stub = $this->files->get(__DIR__ . "/app/console/init.stub");
        $buildText = $this->replaceField($stub);
        $commandPath = app_path('Console/Commands');
        @mkdir($commandPath, 0777, true);
        $this->files->put($commandPath."/{$this->prefixCapital}Command.php", $buildText);
    }

    public function handle()
    {
        //php artisan wangpkg:app tp 投票
        $prefix = $this->argument('prefix');   //前缀
        $prefixCapital = ucwords($prefix);  //首字母大写前缀
        $appName = $this->argument('appname'); //app名称

        $this->prefix = $prefix;
        $this->prefixCapital = $prefixCapital;
        $this->appName = $appName;

        //替换
        //DummyPrefixCapitalAdmin DummyDXNameAdmin
        //media-admin DummyPrefix-admin
        //MediaAdmin  DummyPrefixCapitalAdmin
        //media_admin DummyPrefix_admin
        $appfiles = [
            'Auth/Permission',
            'Controllers/AuthController',
            'Controllers/HomeController',
            'Controllers/UserController',
            'Controllers/MenuController',
            'Controllers/RoleController',
            'Controllers/PermissionController',
            'Models/Administrator',
            'Models/AdminTablesSeeder',
            'Models/Extension',
            'Models/ExtensionHistory',
            'Models/Menu',
            'Models/MenuCache',
            'Models/Permission',
            'Models/Role',
            'Models/Setting',
            'Repositories/Administrator',
            'Repositories/Menu',
            'Repositories/Permission',
            'Repositories/Role',
            'bootstrap',
            'routes'
        ];

        foreach ($appfiles as $appfile) {
            //生成app目录的文件
            $this->makeAppFile($appfile);
        }
        $this->makeConfigFile();
        $this->makeShsFile();
        $this->makeFrontend();
        $this->makeCommand();

        //生成前台路由文件
        //生成前端代码目录
        $this->info("php artisan runshs {$this->prefix}_admin {$this->prefix}_admin");
        $this->info("php artisan migrate --path=database/{$this->prefix}_admin");
        $this->info("config/admin.php中添加      'multi_app' => ['{$this->prefix}-admin' => true,],  ");
        $this->info("app/Providers/RouteServiceProvider.php中添加：");
        //$this->info("Route::middleware('api')->namespace(\$this->namespace)->group(base_path('routes/{$this->prefix}.php'));");
        $this->info("Route::prefix('{$this->prefix}api')->namespace('App\Http\Controllers')->group(base_path('routes/{$this->prefix}.php'));");
        $this->info("php artisan {$this->prefix}command initAdmin");
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

    public function replaceField($stub)
    {
        return str_replace(
            [
                'DummyPrefix',
                'DummyDXName',
                'DummyAppName',
            ],
            [
                $this->prefix,
                $this->prefixCapital,
                $this->appName,
            ],
            $stub
        );
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
                'DummyPrefix',
                'DummyPrefixCapital',
                'DummyAppName',
            ],
            [
                $this->prefix,
                $this->prefixCapital,
                $this->appName,
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
        array_push($segments, $this->modelName);

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
