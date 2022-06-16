<?php


namespace Wang\Pkg\Extensions\Form\Local;

use Dcat\Admin\Form\Field;

class upImage extends Field
{

    protected $view = 'wangpkg::admin.form.local.upImage';

    protected static $css = [];

    protected static $js = [
    ];

    protected $variables = [];

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
