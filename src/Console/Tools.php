<?php

namespace Wang\Pkg\Console;


trait Tools
{
    private $rootPath;
    private $prefix;
    private $prefixCapital;
    private $appName;

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

    public function getStub()
    {
        return __DIR__ . "/stubs/actions/{$this->choice}.stub";
    }

    protected function checkDir($classPath){
        //根据类名目录获取文件夹路径 与文件类名
        echo 1234567;
        //判断目录是否存在  并创建目录
        $dir = $classPath;

        return $dir;
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        ///Users/wangcong/php/pkgdev_dcat
        $segments = explode('\\', config('admin.route.namespace'));
        //删除数组最后一个元素 App\\Admin\\
        array_pop($segments);
        //数组最后插入元素
        array_push($segments, 'Actions');

        //按model名称分目录
        array_push($segments, $this->modelName);

        if (isset($this->namespaceMap[$this->choice])) {
            array_push($segments, $this->namespaceMap[$this->choice]);
        }

        return implode('\\', $segments);
    }
}
