<?php

/**
 * Created by PhpStorm.
 * User:  wangcong
 * Date: 2018/4/16
 * Time: 上午11:38
 */

namespace Wang\Pkg\Extensions\Form;

use Dcat\Admin\Form\Field;


class formData extends Field
{

    protected $view = 'wangpkg::admin.form.formData';

    protected static $css = [];

    protected static $js = [
        '/vendor/wangpkg/lib/formdata/formdata.js'
    ];

    public function getenv(){
        return $this;
    }

    public function render()
    {
        $this->variables['id'] = uniqid();
        return parent::render();
    }
}
