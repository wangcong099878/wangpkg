<?php
/**
 * Created by PhpStorm.
 * User: wangcong 251957448@qq.com
 * Date: 2020/9/9
 * Time: 6:07 下午
 */


namespace Wang\Pkg\Extensions\Form;

use Dcat\Admin\Form\Field;

class WangEditor extends Field
{
    protected $view = 'wangpkg::admin.form.wang-editor';

    protected static $css = [
        'vendor/wangpkg/lib/wangEditor/wangEditor.min.css',
    ];

    protected static $js = [
        'vendor/wangpkg/lib/wangEditor/wangEditor.min.js',
        'vendor/wangpkg/js/wangEditor.js',
    ];

    public function render()
    {
        $name = $this->formatName($this->column);

        $this->script = <<<EOT


EOT;
        $this->variables['id'] = uniqid();
        return parent::render();
    }
}
