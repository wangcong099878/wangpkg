<?php
/**
 * Created by PhpStorm.
 * User: wangcong
 * Email: 251957448@qq.com
 * Date: 2018/5/10
 * Time: 22:44
 */
namespace Wang\Pkg\Extensions\Form\Local;
use Dcat\Admin\Form\Field;

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
        $id = md5($this->getElementClassSelector());

        $this->variables['id'] =$id;
        $serverUrl = config('wangpkg.local.ueditor_api');
        $UEDITOR_HOME_URL = config('wangpkg.UEDITOR_HOME_URL');
        $this->script = <<<JS
(function () {
    var ueditor{$id} = UE.getEditor('ueditor{$id}', {
        UEDITOR_HOME_URL:"{$UEDITOR_HOME_URL}",
    serverUrl: "{$serverUrl}",
    // 自定义工具栏
    toolbars: [
        ['bold', 'italic', 'underline', 'strikethrough', 'blockquote', 'insertunorderedlist', 'insertorderedlist', 'justifyleft', 'justifycenter', 'justifyright', 'link', 'unlink', 'insertimage', 'source',
            'insertvideo', '|', 'removeformat', 'formatmatch',
            'customstyle', 'paragraph', 'fontfamily', 'fontsize', '|', 'preview','forecolor', 'backcolor',
            'fullscreen']
    ],
    elementPathEnabled: false,
    enableContextMenu: false,
    autoClearEmptyNode: true,
    wordCount: false,
    imagePopup: false,
    autoHeightEnabled: false,
    autotypeset: {indent: true, imageBlockLine: 'center'}
});
})();
JS;

        return parent::render();
    }
}
