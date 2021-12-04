<?php
/**
 * Created by PhpStorm.
 * User: wangcong 251957448@qq.com
 * Date: 2020/9/10
 * Time: 5:58 下午
 */

namespace Wang\Pkg\Extensions\Form;

use Dcat\Admin\Form\Field;

class multipleImage extends Field
{

    protected $view = 'wangpkg::admin.form.multipleImage';

    protected static $css = [];

    protected static $js = [
        'vendor/wangpkg/js/uploader/h5/qiniu.min.js',
        'vendor/wangpkg/js/uploader/h5/h5.js',
        'vendor/wangpkg/lib/multipleImage/images.js',
    ];

    public function render()
    {
        $this->variables['id'] = uniqid();
        return parent::render();
    }

    public function setValue($value = '')
    {
        if ($value) {
            $this->value = $value;
        }
        return $this;
    }
}
