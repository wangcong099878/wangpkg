<?php
/**
 * Created by PhpStorm.
 * User: wangcong
 * Email: 251957448@qq.com
 * Date: 2018/5/10
 * Time: 22:44
 */
namespace Wang\Pkg\Extensions\Form\Local;
use Encore\Admin\Form\Field;

/**
 * 百度编辑器
 * Class uEditor
 * @package App\Admin\Extensions\Form
 */
class uEditor extends Field
{
    // 定义视图
    protected $view = 'wangpkg::admin.form.local.uEditor';

    // css资源
    protected static $css = [];

    // js资源
    protected static $js = [
        'vendor/wangpkg/lib/laravel-u-editor/ueditor.config.js',
        'vendor/wangpkg/lib/laravel-u-editor/ueditor.all.min.js',
        'vendor/wangpkg/lib/laravel-u-editor/lang/zh-cn/zh-cn.js'
    ];

    public function render()
    {
        return parent::render();
    }
}
