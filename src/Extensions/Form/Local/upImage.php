<?php


namespace Wang\Pkg\Extensions\Form\Local;

use Encore\Admin\Form\Field;

class upImage extends Field
{

    protected $view = 'wangpkg::admin.form.local.upImage';

    protected static $css = [];

    protected static $js = [
    ];

    public function render()
    {
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
